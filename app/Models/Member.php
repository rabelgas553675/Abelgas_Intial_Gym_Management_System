<?php

namespace App\Models;

use App\Exceptions\MemberHasHistoryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Member extends Model
{
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
        'start_date' => 'date',
        'end_date'   => 'date',
        'birthdate'  => 'date',
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
     * Compute status from end_date at runtime.
     *
     *  No end_date                        → 'No Plan'
     *  end_date in the past               → 'Expired'
     *  now → end_date is within 7 days    → 'Expiring Soon'
     *  now → end_date is more than 7 days → 'Active'
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                // These are staff controlled account states. Subscription
                // dates determine the ordinary Active/Expired states.
                $storedStatus = $this->attributes['status'] ?? null;
                if (in_array($storedStatus, ['Suspended', 'Inactive'], true)) {
                    return $storedStatus;
                }

                if (!$this->end_date) {
                    return 'No Plan';
                }

                if ($this->end_date->isPast()) {
                    return 'Expired';
                }

                // now() → end_date: days remaining (always positive for future dates)
                $daysLeft = now()->diffInDays($this->end_date);

                if ($daysLeft <= 7) {
                    return 'Expiring Soon';
                }

                return 'Active';
            }
        );
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
     * True if subscription ends within $days days and is not yet expired.
     * Uses now() → end_date direction to get correct positive day count.
     */
    public function isDueWithinDays(int $days = 7): bool
    {
        if (!$this->end_date) return false;

        return $this->end_date->isFuture()
            && now()->diffInDays($this->end_date) <= $days;
    }

    /**
     * True if the subscription end date is in the past.
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }
}
