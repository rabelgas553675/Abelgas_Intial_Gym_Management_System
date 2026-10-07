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
