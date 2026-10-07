<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory, Auditable;

    protected string $auditModule = 'Payments';

    public function auditLabel(): string
    {
        return ($this->receipt_number ?: '#' . $this->getKey())
            . ' — ' . ($this->member?->name ?? 'Unknown member')
            . ' (₱' . number_format((float) $this->amount, 2) . ')';
    }

    public function auditAction(string $event, ?array $old, ?array $new): string
    {
        return match ($event) { 'created' => 'recorded', 'deleted' => 'deleted', default => 'updated' };
    }

    protected $fillable = [
        'member_id',
        'payment_type',      // 'gym_fee' | 'coach_fee' | 'platform_fee'
        'instructor_id',     // set when payment_type = 'coach_fee'
        'platform_fee',      // admin's cut stored on gym_fee rows
        'processed_by',
        'receipt_number',
        'fitness_plan',
        'membership_type',
        'amount',
        'payment_date',
        'method',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date:Y-m-d', // plain date on toArray()/JSON; avoids UTC shift (-1 day) in views
        'amount'       => 'decimal:2',
        'platform_fee' => 'decimal:2',
    ];

    // ── Fee split constants ───────────────────────────────────────────────────
    // Gym membership fees (go to admin / platform)
    const DEFAULT_GYM_FEES = [
        'Monthly'     => 800,
        'Quarterly'   => 2100,
        'Semi-Annual' => 4500,
        'Annually'    => 7500,
    ];

    // Coach subscription fees (go to instructor)
    const DEFAULT_COACH_FEES = [
        'Monthly'     => 300,
        'Quarterly'   => 1200,
        'Semi-Annual' => 1800,
        'Annually'    => 3600,
    ];

    public static function defaultGymRates(): array
    {
        return self::DEFAULT_GYM_FEES;
    }

    public static function defaultCoachRates(): array
    {
        return self::DEFAULT_COACH_FEES;
    }

    // Day Pass (walk-in) rate — kept OUT of DEFAULT_GYM_FEES on purpose so it never
    // shows up as a membership plan. It is stored in the same gym rate settings
    // (payment_settings, key 'gym_day_pass') and edited from the same Subscription Rates form.
    const DEFAULT_DAY_PASS_RATE = 100;

    public static function dayPassRate(): int
    {
        return self::resolveRateSetting('gym', 'Day Pass', self::DEFAULT_DAY_PASS_RATE);
    }

    protected static function resolveRateSetting(string $group, string $plan, int $default, ?int $instructorId = null): int
    {
        $settings = PaymentSetting::pluck('value', 'key')->toArray();
        $keys = [];

        if ($group === 'coach' && $instructorId) {
            $keys[] = 'coach_instructor_' . $instructorId . '_' . $plan;
            $keys[] = 'coach_instructor_' . $instructorId . '_' . str_replace(['-', ' '], '_', $plan);
            $keys[] = 'coach_instructor_' . $instructorId . '_' . strtolower($plan);
            $keys[] = 'coach_instructor_' . $instructorId . '_' . str_replace(['-', ' '], '_', strtolower($plan));
        }

        $keys = array_merge($keys, [
            $group . '_' . $plan,
            $group . '_' . str_replace(['-', ' '], '_', $plan),
            $group . '_' . strtolower($plan),
            $group . '_' . str_replace(['-', ' '], '_', strtolower($plan)),
        ]);

        foreach (array_unique($keys) as $key) {
            if (!array_key_exists($key, $settings)) {
                continue;
            }

            $value = filter_var($settings[$key], FILTER_VALIDATE_INT);
            if ($value !== false && $value > 0) {
                return (int) $value;
            }

            break;
        }

        return $default;
    }

    public static function rateSettings(?int $instructorId = null): array
    {
        return [
            'gym' => array_combine(
                array_keys(self::DEFAULT_GYM_FEES),
                array_map(fn ($plan, $amount) => self::resolveRateSetting('gym', $plan, (int) $amount), array_keys(self::DEFAULT_GYM_FEES), self::DEFAULT_GYM_FEES)
            ),
            'coach' => array_combine(
                array_keys(self::DEFAULT_COACH_FEES),
                array_map(fn ($plan, $amount) => self::resolveRateSetting('coach', $plan, (int) $amount, $instructorId), array_keys(self::DEFAULT_COACH_FEES), self::DEFAULT_COACH_FEES)
            ),
        ];
    }

    public static function gymRates(): array
    {
        return self::rateSettings()['gym'];
    }

    public static function coachRates(?int $instructorId = null): array
    {
        return self::rateSettings($instructorId)['coach'];
    }

    public static function gymRate(string $type): int
    {
        return (int) (self::gymRates()[$type] ?? self::DEFAULT_GYM_FEES[$type] ?? 0);
    }

    public static function coachRate(string $type, ?int $instructorId = null): int
    {
        return (int) (self::coachRates($instructorId)[$type] ?? self::DEFAULT_COACH_FEES[$type] ?? 0);
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function instructor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function processedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function assignedInstructorName(): string
    {
        if ($this->instructor_id) {
            return $this->instructor?->name ?? 'Unassigned';
        }

        if ($this->member && $this->member->instructor_id) {
            return $this->member->instructor?->name ?? 'Unassigned';
        }

        return 'Unassigned';
    }

    // ── Query scopes ──────────────────────────────────────────────────────────

    /** Gym membership fees — admin / platform earnings */
    public function scopeGymFees(Builder $query)
    {
        return $query->where('payment_type', 'gym_fee');
    }

    /** Coach fees — instructor earnings */
    public function scopeCoachFees(Builder $query)
    {
        return $query->where('payment_type', 'coach_fee');
    }

    /** All payments visible to admin (gym + platform, NOT coach fees) */
    public function scopeAdminPayments(Builder $query)
    {
        return $query->whereIn('payment_type', ['gym_fee', 'platform_fee']);
    }

    /** All payments (gym, coach, platform, manual) belonging to a member */
    public function scopeForMember(Builder $query, Member|int|string $member)
    {
        $memberId = $member instanceof Member ? $member->getKey() : $member;

        return $query->where('member_id', $memberId);
    }

    /** Coach fee payments for a specific instructor */
    public function scopeForInstructor(Builder $query, int $instructorId)
    {
        return $query->where('payment_type', 'coach_fee')
                     ->where('instructor_id', $instructorId);
    }

    /** This month filter */
    public function scopeThisMonth(Builder $query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date',  now()->year);
    }

    // ── Earnings helpers ──────────────────────────────────────────────────────

    /** Total admin earnings (gym_fee amounts only) */
    public static function adminTotalEarned(): float
    {
        return (float) static::gymFees()->sum('amount');
    }

    /** Admin earnings this month */
    public static function adminThisMonth(): float
    {
        return (float) static::gymFees()->thisMonth()->sum('amount');
    }

    /** Total earned by a specific instructor */
    public static function instructorTotalEarned(int $instructorId): float
    {
        return (float) static::forInstructor($instructorId)->sum('amount');
    }

    /** Instructor earnings this month */
    public static function instructorThisMonth(int $instructorId): float
    {
        return (float) static::forInstructor($instructorId)->thisMonth()->sum('amount');
    }

    /** Leaderboard: top earning instructors */
    public static function instructorEarningsLeaderboard(int $limit = 10): \Illuminate\Support\Collection
    {
        return static::coachFees()
            ->select([
                'instructor_id',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as txn_count'),
            ])
            ->with('instructor:id,name,photo')
            ->groupBy('instructor_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public static function generateReceiptNumber(): string
    {
        return 'RCP-' . strtoupper(uniqid());
    }

    public static function paymentNoteFor(
        ?string $notes,
        ?string $membershipType,
        float|int|string|null $amount,
        ?string $paymentType = 'gym_fee',
        ?bool $isAdvancePayment = null,
        ?int $instructorId = null
    ): string {
        $baseNote = trim((string) ($notes ?? ''));

        if (! in_array(($paymentType ?? 'gym_fee'), ['gym_fee', 'coach_fee'], true) || ! is_string($membershipType) || $membershipType === '') {
            return $baseNote;
        }

        $expected = (float) (($paymentType === 'coach_fee')
            ? self::coachRate($membershipType, $instructorId)
            : self::gymRate($membershipType));
        $paid     = (float) ($amount ?? 0);
        $isAdvance = $isAdvancePayment ?? ($expected > 0 && $paid > $expected);

        if (! $isAdvance) {
            return $baseNote;
        }

        $advanceNote = 'Paid in advance';

        if ($baseNote === '') {
            return $advanceNote;
        }

        if (stripos($baseNote, 'paid in advance') !== false) {
            return $baseNote;
        }

        return $baseNote . ' | Paid in advance';
    }

    public function displayNote(): string
    {
        return self::paymentNoteFor(
            $this->notes,
            $this->membership_type,
            $this->amount,
            $this->payment_type
        ) ?: '—';
    }

    public function isCoachFee(): bool   { return $this->payment_type === 'coach_fee'; }
    public function isGymFee(): bool     { return $this->payment_type === 'gym_fee'; }

    // ── Payment states ────────────────────────────────────────────────────────
    public const STATUS_PAID           = 'Paid';
    public const STATUS_AWAITING_COACH = 'Awaiting Coach'; // held until the coach confirms
    public const STATUS_REJECTED       = 'Rejected';       // coach declined → never official

    /**
     * Payments waiting for / refused by a coach are NOT official payments.
     * This scope hides them from every existing query (admin totals, member history,
     * instructor earnings, reports, leaderboard…) so they cannot leak into any sum.
     * Use Payment::withUnconfirmed() when you really need them.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('confirmed', function (Builder $query) {
            $query->whereNotIn(
                $query->getModel()->getTable() . '.status',
                [self::STATUS_AWAITING_COACH, self::STATUS_REJECTED]
            );
        });
    }

    public static function withUnconfirmed(): Builder
    {
        return static::query()->withoutGlobalScope('confirmed');
    }
}