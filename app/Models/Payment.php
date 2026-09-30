<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

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
        'payment_date' => 'date',
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

    protected static function resolveRateSetting(string $group, string $plan, int $default): int
    {
        $settings = PaymentSetting::pluck('value', 'key')->toArray();
        $candidates = [
            $group . '_' . $plan,
            $group . '_' . str_replace(['-', ' '], '_', $plan),
            $group . '_' . strtolower($plan),
            $group . '_' . str_replace(['-', ' '], '_', strtolower($plan)),
        ];

        foreach ($candidates as $key) {
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

    public static function rateSettings(): array
    {
        return [
            'gym' => array_combine(
                array_keys(self::DEFAULT_GYM_FEES),
                array_map(fn ($plan, $amount) => self::resolveRateSetting('gym', $plan, (int) $amount), array_keys(self::DEFAULT_GYM_FEES), self::DEFAULT_GYM_FEES)
            ),
            'coach' => array_combine(
                array_keys(self::DEFAULT_COACH_FEES),
                array_map(fn ($plan, $amount) => self::resolveRateSetting('coach', $plan, (int) $amount), array_keys(self::DEFAULT_COACH_FEES), self::DEFAULT_COACH_FEES)
            ),
        ];
    }

    public static function gymRates(): array
    {
        return self::rateSettings()['gym'];
    }

    public static function coachRates(): array
    {
        return self::rateSettings()['coach'];
    }

    public static function gymRate(string $type): int
    {
        return (int) (self::gymRates()[$type] ?? self::DEFAULT_GYM_FEES[$type] ?? 0);
    }

    public static function coachRate(string $type): int
    {
        return (int) (self::coachRates()[$type] ?? self::DEFAULT_COACH_FEES[$type] ?? 0);
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

    // ── Query scopes ──────────────────────────────────────────────────────────

    /** Gym membership fees — admin / platform earnings */
    public function scopeGymFees($query)
    {
        return $query->where('payment_type', 'gym_fee');
    }

    /** Coach fees — instructor earnings */
    public function scopeCoachFees($query)
    {
        return $query->where('payment_type', 'coach_fee');
    }

    /** All payments visible to admin (gym + platform, NOT coach fees) */
    public function scopeAdminPayments($query)
    {
        return $query->whereIn('payment_type', ['gym_fee', 'platform_fee']);
    }

    /** Coach fee payments for a specific instructor */
    public function scopeForInstructor($query, int $instructorId)
    {
        return $query->where('payment_type', 'coach_fee')
                     ->where('instructor_id', $instructorId);
    }

    /** This month filter */
    public function scopeThisMonth($query)
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
            ->select('instructor_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as txn_count'))
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

    public function isCoachFee(): bool   { return $this->payment_type === 'coach_fee'; }
    public function isGymFee(): bool     { return $this->payment_type === 'gym_fee'; }
}