<?php

namespace Tests\Unit;

use App\Services\MembershipExpiration;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pins down the single expiration calculation shared by the admin, staff,
 * instructor and member screens (see App\Services\MembershipExpiration).
 */
class MembershipExpirationTest extends TestCase
{
    private function at(string $today, ?string $start = '2026-10-06', ?string $end = '2026-11-06', ?string $stored = null): MembershipExpiration
    {
        // Deliberately mid-day to prove the hour/minute never changes the result.
        return MembershipExpiration::calculate(
            $start ? Carbon::parse($start) : null,
            $end ? Carbon::parse($end) : null,
            $stored,
            Carbon::parse($today . ' 15:42:09', config('app.timezone')),
        );
    }

    public function test_membership_starting_today_is_active_with_full_progress(): void
    {
        $e = $this->at('2026-10-06');
        $this->assertSame('Active', $e->status);
        $this->assertSame(31, $e->daysRemaining); // Oct 6 → Nov 6 is 31 calendar days
        $this->assertSame(100, $e->progress);
    }

    public function test_exactly_seven_days_is_expiring_soon(): void
    {
        $e = $this->at('2026-10-30');
        $this->assertSame('Expiring Soon', $e->status);
        $this->assertSame(7, $e->daysRemaining);
    }

    public function test_eight_days_is_still_active(): void
    {
        $this->assertSame('Active', $this->at('2026-10-29')->status);
    }

    public function test_one_day_left_uses_singular_text(): void
    {
        $e = $this->at('2026-11-05');
        $this->assertSame('Expiring Soon', $e->status);
        $this->assertSame('1 day left', $e->daysText());
    }

    public function test_expires_today_is_expired_with_zero_days_and_empty_bar(): void
    {
        $e = $this->at('2026-11-06');
        $this->assertSame('Expired', $e->status);
        $this->assertSame(0, $e->daysRemaining);
        $this->assertSame('0 days left', $e->daysText());
        $this->assertSame(0, $e->progress);
        $this->assertFalse($e->isUsable());
    }

    public function test_long_expired_never_goes_negative(): void
    {
        $e = $this->at('2027-03-01');
        $this->assertSame('Expired', $e->status);
        $this->assertSame(0, $e->daysRemaining);
        $this->assertSame(0, $e->progress);
        $this->assertSame('Expired', $e->shortText());
    }

    public function test_missing_expiration_date_falls_back_safely(): void
    {
        $e = $this->at('2026-10-06', '2026-10-06', null);
        $this->assertSame('No Plan', $e->status);
        $this->assertNull($e->daysRemaining);
        $this->assertSame('Expiration date unavailable', $e->daysText());
        $this->assertSame(0, $e->progress);
    }

    public function test_stacked_renewal_with_future_start_is_active_and_progress_is_clamped(): void
    {
        $e = $this->at('2026-10-30', '2026-11-06', '2026-12-06');
        $this->assertSame('Active', $e->status);
        $this->assertTrue($e->startsInFuture);
        $this->assertSame(100, $e->progress);
    }

    public function test_staff_controlled_states_override_but_dates_are_kept(): void
    {
        $e = $this->at('2026-10-10', '2026-10-06', '2026-11-06', 'Suspended');
        $this->assertSame('Suspended', $e->status);
        $this->assertSame(27, $e->daysRemaining);
        $this->assertFalse($e->isUsable());
    }

    public function test_progress_is_always_between_0_and_100(): void
    {
        foreach (['2026-09-01', '2026-10-06', '2026-10-21', '2026-11-05', '2026-11-06', '2027-01-01'] as $day) {
            $p = $this->at($day)->progress;
            $this->assertGreaterThanOrEqual(0, $p);
            $this->assertLessThanOrEqual(100, $p);
        }
    }
}