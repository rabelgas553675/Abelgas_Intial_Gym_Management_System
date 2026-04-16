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
    const GYM_FEES = [
        'Monthly'     => 800,
        'Quarterly'   => 2100,
        'Semi-Annual' => 4000,
        'Annually'    => 7500,
    ];

    // Coach subscription fees (go to instructor)
    const COACH_FEES = [
        'Monthly'     => 300,
        'Quarterly'   => 1200,
        'Semi-Annual' => 2400,
        'Annually'    => 3600,
    ];

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