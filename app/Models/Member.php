<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Exceptions\MemberHasHistoryException;
use App\Services\MembershipExpiration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Member extends Model
{
    use Auditable;

    protected string $auditModule = 'Members';

    public function auditExcept(): array
    {
        return ['qr_id', 'qr_code_path', 'qr_token', 'qr_code']; // QR regeneration is noise + token is a secret
    }

    /**
     * Relations that hold a member's history. If ANY of these has rows the member
     * can never be permanently deleted — deactivate / suspend them instead.
     *
     *   relation name => label used in the error message
     */
    private const HISTORY_RELATIONS = [
        'payments'       => 'payment',
        'instructorFees' => 'coach fee',
        'attendances'    => 'attendance',
        'workoutPlans'   => 'workout plan',
        'coachRequests'  => 'coach request',
        'planChangeRequests' => 'plan change request',
    ];

    protected $fillable = [
        'user_id',
        'instructor_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'birthdate',
        'address',
        'membership_type',
        'fitness_plan',
        'coach_membership_type',
        'coach_status',
        'start_date',
        'end_date',
        'fee',
        'status',
        'photo',
        'qr_id',
        'qr_code_path',
        'qr_token',
        'qr_code',
    ];

    protected $casts = [
        // 'date:Y-m-d' keeps attribute access as Carbon but serializes toArray()/JSON
        // as a plain calendar date (a bare 'date' cast serializes as UTC and shifts
        // Asia/Manila midnight back one day).
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
        'birthdate'  => 'date:Y-m-d',
    ];

    /**
     * Force the status accessor to always override the DB column.
     */
    protected $appends = ['status'];

    // ─────────────────────────────────────────
    //  Model Events
    // ─────────────────────────────────────────

    protected static function booted(): void
    {
        // Safety net: no matter where a delete comes from (controller, tinker,
        // another service), a member with history can not be deleted.
        static::deleting(function (Member $member) {
            $blockers = $member->deletionBlockers();

            if ($blockers !== []) {
                throw new MemberHasHistoryException($blockers);
            }
        });
    }

    // ─────────────────────────────────────────
    //  Dynamic Status Accessor
    // ─────────────────────────────────────────

    /**
     * Status is ALWAYS derived from the subscription dates (never edited by hand
     * to flip Active → Expired). The calculation lives in MembershipExpiration so
     * every screen agrees:
     *
     *  No end_date                  → 'No Plan'
     *  end_date today or earlier    → 'Expired'
     *  1–7 days remaining           → 'Expiring Soon'
     *  more than 7 days remaining   → 'Active'
     *  stored Suspended / Inactive  → kept as-is (staff controlled)
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expiration()->status
        );
    }

    /**
     * The one and only expiration state for this member
     * (status, days remaining, progress %, dates, badge/tone helpers).
     */
    public function expiration(): MembershipExpiration
    {
        try {
            return MembershipExpiration::calculate(
                $this->start_date,
                $this->end_date,
                $this->attributes['status'] ?? null,
            );
        } catch (\Throwable $e) {
            // Corrupt date data must never take a page down.
            return MembershipExpiration::calculate(null, null, $this->attributes['status'] ?? null);
        }
    }

    // ─────────────────────────────────────────
    //  Portal account ↔ member record
    // ─────────────────────────────────────────

    /**
     * Get (or repair) the Member record that belongs to a portal User.
     *
     * A user with role "member" can end up WITHOUT a usable members row when the
     * account was created outside MemberController@store (seeder, register page,
     * user-management screen, import …). That breaks the profile, plan selection,
     * payments and the QR code. This method heals it, in this order:
     *
     *   1. members.user_id = user.id          → use it
     *   2. members.email   = user.email
     *        - user_id is empty               → link it to this user
     *        - user_id is another user        → refuse (never steal someone's record)
     *   3. nothing found                      → create a fresh "Pending" member
     *
     * Whatever is returned is guaranteed to have a QR code.
     * Returns null for non-member roles, or when the email is owned by another user.
     */
    public static function forUser(User $user): ?self
    {
        if ($user->role !== 'member') {
            return null;
        }

        $member = static::query()->where('user_id', '=', $user->id, 'and')->first();

        if (!$member) {
            $byEmail = static::where('email', '=', $user->email, 'and')->first();

            if ($byEmail) {
                if ($byEmail->user_id && (int) $byEmail->user_id !== (int) $user->id) {
                    report(new \RuntimeException(
                        "Member #{$byEmail->id} ({$byEmail->email}) is linked to user #{$byEmail->user_id}, not user #{$user->id}."
                    ));
                    return null;
                }

                $byEmail->update(['user_id' => $user->id]);
                $member = $byEmail;
            }
        }

        if (!$member) {
            $parts = explode(' ', trim((string) $user->name), 2);

            $member = static::create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'first_name'      => $parts[0] ?? $user->name,
                'last_name'       => $parts[1] ?? '',
                'email'           => $user->email,
                'phone'           => $user->phone,
                'gender'          => $user->gender,
                'birthdate'       => $user->birthdate,
                'address'         => $user->address,
                'photo'           => $user->photo,
                'membership_type' => null,
                'start_date'      => null,
                'end_date'        => null,
                'fee'             => 0,
                'status'          => 'Pending',
                'instructor_id'   => null,
                'coach_status'    => 'none',
            ]);
        }

        return $member->ensureQrCode();
    }

    // ─────────────────────────────────────────
    //  QR Code Generation
    // ─────────────────────────────────────────

    public static function generateQrCode(self $member): void
    {
        $qrId = 'IFG-MEM-' . str_pad($member->id, 6, '0', STR_PAD_LEFT);

        $folder = storage_path('app/public/qrcodes');
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        $path = 'qrcodes/' . $qrId . '.svg';

        QrCode::format('svg')
            ->size(300)
            ->backgroundColor(255, 255, 255)
            ->color(0, 0, 0)
            ->errorCorrection('H')
            ->generate($qrId, storage_path('app/public/' . $path));

        $token = 'MBR-' . strtoupper(bin2hex(random_bytes(16)));

        $member->update([
            'qr_id'        => $qrId,
            'qr_code_path' => $path,
            'qr_token'     => $token,
        ]);
    }

    /**
     * Make sure this member has a QR (record + file on disk).
     * Does nothing when a valid QR already exists, so the token is never rotated by accident.
     * A failure is logged, never thrown — a QR problem must not take a page down.
     */
    public function ensureQrCode(): static
    {
        $fileMissing = $this->qr_code_path
            && !file_exists(storage_path('app/public/' . $this->qr_code_path));

        if (!$this->qr_code_path || !$this->qr_token || $fileMissing) {
            try {
                static::generateQrCode($this);
                $this->refresh();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this;
    }

    // ─────────────────────────────────────────
    //  Accessors
    // ─────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->first_name
            ? trim($this->first_name . ' ' . $this->last_name)
            : $this->name;
    }

    // ─────────────────────────────────────────
    //  Relationships
    // ─────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function workoutPlans()
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function coachRequests(): HasMany
    {
        return $this->hasMany(CoachRequest::class);
    }

    public function planChangeRequests(): HasMany
    {
        return $this->hasMany(PlanChangeRequest::class);
    }

    public function instructorFees(): HasMany
    {
        return $this->hasMany(InstructorFee::class);
    }

    // ─────────────────────────────────────────
    //  Delete Protection
    // ─────────────────────────────────────────

    /**
     * Which history records block deletion?
     *
     * @return array<string,int>  label => number of rows, only for relations that have rows
     *                            e.g. ['payment' => 3, 'attendance' => 12]
     */
    public function deletionBlockers(): array
    {
        $blockers = [];

        foreach (self::HISTORY_RELATIONS as $relation => $label) {
            $query = $this->{$relation}();

            // Skip tables/columns that don't exist in this database (older schemas).
            if (!Schema::hasColumn($query->getRelated()->getTable(), 'member_id')) {
                continue;
            }

            $count = $query->count();

            if ($count > 0) {
                $blockers[$label] = $count;
            }
        }

        return $blockers;
    }

    /**
     * True only when the member has no payment / transaction / attendance / other history.
     */
    public function canBeDeleted(): bool
    {
        return $this->deletionBlockers() === [];
    }

    // ─────────────────────────────────────────
    //  Business Logic
    // ─────────────────────────────────────────

    /**
     * True if the subscription ends within $days days and is not yet expired.
     */
    public function isDueWithinDays(int $days = 7): bool
    {
        $exp = $this->expiration();

        return $exp->hasDates()
            && !$exp->dateExpired
            && $exp->daysRemaining <= $days;
    }

    /**
     * True when the total recorded member payments exceed the current expected plan amount.
     */
    public function hasPaidInAdvance(): bool
    {
        if (! $this->membership_type) {
            return false;
        }

        $expected = (float) (Payment::gymRate($this->membership_type) ?: 0);
        $totalPaid = (float) $this->payments()->where('payment_type', 'gym_fee')->sum('amount');

        return $expected > 0 && $totalPaid > $expected;
    }

    /**
     * True once the end date is today or earlier.
     */
    public function isExpired(): bool
    {
        return $this->expiration()->dateExpired;
    }
}