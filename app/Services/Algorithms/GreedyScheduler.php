<?php

namespace App\Services\Algorithms;

use Carbon\Carbon;

/**
 * GreedyScheduler — DSA Implementation
 *
 * Implements a greedy algorithm for membership scheduling and fee computation.
 *
 * Greedy strategy: at each decision point (plan selection), immediately
 * commit to the locally optimal flat-rate price and canonical interval
 * for the chosen membership type — no backtracking, no partial periods.
 *
 * Supported plans (aligned with proposal):
 *   Monthly    →  1 month   / ₱800   gym  / ₱300   coach
 *   Quarterly  →  3 months  / ₱2,100 gym  / ₱1,200 coach
 *   Semi-Annual→  6 months  / ₱4,500 gym  / ₱1,800 coach
 *   Annually   →  12 months / ₱7,500 gym  / ₱3,600 coach
 *
 * Used in: MemberController::subscribe(), MemberDashboardController::subscribePlan()
 */
class GreedyScheduler
{
    /**
     * Gym membership price map (PHP Pesos).
     *
     * Greedy rates — the algorithm always commits immediately to the
     * flat rate for the chosen plan with no partial-period adjustments.
     */
    private const GYM_PRICES = [
        'Monthly'     => 800,
        'Quarterly'   => 2100,
        'Semi-Annual' => 4500,
        'Annually'    => 7500,
    ];

    /**
     * Coach / instructor fee price map (PHP Pesos).
     */
    private const COACH_PRICES = [
        'Monthly'     => 300,
        'Quarterly'   => 1200,
        'Semi-Annual' => 1800,
        'Annually'    => 3600,
    ];

    /**
     * All valid plan types — use this constant in controller validation rules.
     *
     * Example:
     *   'membership_type' => 'required|in:' . implode(',', GreedyScheduler::VALID_PLANS)
     */
    public const VALID_PLANS = [
        'Monthly',
        'Quarterly',
        'Semi-Annual',
        'Annually',
    ];

    /**
     * Greedy end-date computation.
     *
     * Given a start date and membership type, the greedy algorithm
     * immediately selects the full canonical interval for that plan
     * and returns the resulting end date. No partial-month splitting
     * or pro-rata adjustments — the greedy choice maximises simplicity.
     *
     * @param  Carbon $start           Membership start date
     * @param  string $membershipType  'Monthly' | 'Quarterly' | 'Semi-Annual' | 'Annually'
     * @return Carbon                  Computed end date (copy of $start + interval)
     * @throws \InvalidArgumentException  If an unrecognised type is passed
     */
    public static function computeEndDate(Carbon $start, string $membershipType): Carbon
    {
        return match ($membershipType) {
            'Monthly'     => $start->copy()->addMonth(),
            'Quarterly'   => $start->copy()->addMonths(3),
            'Semi-Annual' => $start->copy()->addMonths(6),
            'Annually'    => $start->copy()->addYear(),
            default       => throw new \InvalidArgumentException(
                "GreedyScheduler: unknown membership type \"{$membershipType}\". "
                . 'Valid: ' . implode(', ', self::VALID_PLANS)
            ),
        };
    }

    /**
     * Greedy gym fee lookup.
     *
     * Returns the flat-rate gym fee for the given plan.
     * Returns 0 for unrecognised types (safe default — no exception thrown).
     *
     * @param  string $membershipType  'Monthly' | 'Quarterly' | 'Semi-Annual' | 'Annually'
     * @return int                     Amount in PHP pesos
     */
    public static function computeGymFee(string $membershipType): int
    {
        return self::GYM_PRICES[$membershipType] ?? 0;
    }

    /**
     * Greedy coach fee lookup.
     *
     * Returns the flat-rate coach fee for the given plan.
     * Returns 0 if null is passed (member has no coach) or type is unrecognised.
     *
     * @param  string|null $membershipType  'Monthly' | 'Quarterly' | 'Semi-Annual' | 'Annually' | null
     * @return int                          Amount in PHP pesos
     */
    public static function computeCoachFee(?string $membershipType): int
    {
        if ($membershipType === null) {
            return 0;
        }

        return self::COACH_PRICES[$membershipType] ?? 0;
    }

    /**
     * Greedy total fee computation.
     *
     * Greedily sums all applicable fees:
     *   - Always adds the gym fee for the chosen plan.
     *   - Appends the coach fee only when a coach plan is provided.
     *
     * This mirrors the greedy principle: commit immediately to the full
     * cost of every selected component without deferring any amount.
     *
     * @param  string      $gymMembershipType    Gym plan type
     * @param  string|null $coachMembershipType  Coach plan type, or null if no coach
     * @return int                               Total amount in PHP pesos
     */
    public static function computeTotalFee(
        string $gymMembershipType,
        ?string $coachMembershipType = null
    ): int {
        return self::computeGymFee($gymMembershipType)
             + self::computeCoachFee($coachMembershipType);
    }
}