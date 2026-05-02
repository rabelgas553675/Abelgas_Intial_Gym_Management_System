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
 * Used in: MemberController::subscribe()
 */
class GreedyScheduler
{
    /**
     * Gym membership price map (PHP Pesos).
     * Greedy rates — the system always picks the fixed price for the chosen plan.
     */
    private const GYM_PRICES = [
        'Monthly'   => 800,
        'Quarterly' => 3200,
        'Annually'  => 9600,
    ];

    /**
     * Coach/instructor fee price map (PHP Pesos).
     */
    private const COACH_PRICES = [
        'Monthly'   => 300,
        'Quarterly' => 1200,
        'Annually'  => 3600,
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
     * @param  string $membershipType  'Monthly' | 'Quarterly' | 'Annually'
     * @return Carbon                  Computed end date (copy of $start + interval)
     * @throws \InvalidArgumentException  If an unrecognised type is passed
     */
    public static function computeEndDate(Carbon $start, string $membershipType): Carbon
    {
        return match ($membershipType) {
            'Monthly'   => $start->copy()->addMonth(),
            'Quarterly' => $start->copy()->addMonths(3),
            'Annually'  => $start->copy()->addYear(),
            default     => throw new \InvalidArgumentException(
                "GreedyScheduler: unknown membership type \"{$membershipType}\"."
            ),
        };
    }

    /**
     * Greedy gym fee lookup.
     *
     * Returns the flat-rate gym fee for the given plan.
     * Returns 0 if the type is unrecognised (safe default).
     *
     * @param  string $membershipType  'Monthly' | 'Quarterly' | 'Annually'
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
     * Returns 0 if the type is unrecognised or null is passed.
     *
     * @param  string|null $membershipType  'Monthly' | 'Quarterly' | 'Annually' | null
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
     * Greedily sums the applicable fees:
     *   - Always adds the gym fee for the chosen plan.
     *   - Appends the coach fee only when a coach plan is selected.
     *
     * This mirrors the greedy principle: commit immediately to the
     * full cost of every selected component without deferring.
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