<?php

namespace App\Services;

use App\Models\CoachRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Services\Algorithms\GreedyScheduler;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Single source of truth for everything the member portal shows about the member's
 * subscription, coach and profile. The dashboard, subscription history and payment
 * history all read from here so they can never disagree.
 *
 * ── Active period ───────────────────────────────────────────────────────────
 * members.start_date / end_date only describe the LAST renewal block: every
 * renewal that is stacked on a still-running plan moves start_date forward to the
 * previous end date. Showing them directly displays a future period (e.g.
 * "May 29, 2027 – Jun 29, 2027") while the member is really in an earlier,
 * already-paid block.
 *
 * So the periods are rebuilt from the CONFIRMED gym_fee payments (the Payment
 * global scope already hides "Awaiting Coach" / "Rejected" rows), using exactly the
 * rule the renewal code uses when it writes the dates:
 *
 *      start = previous block's end (when still running) otherwise the payment date
 *      end   = GreedyScheduler::computeEndDate(start, payment.membership_type)
 *
 * The block that contains today is the Active Period. The rebuilt chain must finish
 * on members.end_date; if it does not (an admin edited the dates by hand, legacy
 * rows, …) the stored members.start_date / end_date win and are shown as-is.
 *
 * ── Coach ───────────────────────────────────────────────────────────────────
 *   current  → members.instructor_id (the coach assigned right now)
 *   upcoming → approved coach_requests that have not started (activated_at NULL,
 *              starts_on in the future)
 *   pending  → a coach request still waiting for the coach's answer
 */
final class MemberSnapshot
{
    private const TYPES = ['Monthly', 'Quarterly', 'Semi-Annual', 'Annually'];

    /**
     * @param array{state:string,current:?array,upcoming:?array,pending:?array} $coach
     * @param array<string,mixed> $profile
     */
    private function __construct(
        public readonly Member $member,
        public readonly MembershipExpiration $expiration,
        public readonly ?array $queuedRenewal,
        public readonly array $coach,
        public readonly array $profile,
        /** Duration (Monthly/Quarterly/…) of the block that is running TODAY. */
        public readonly ?string $planType,
    ) {
    }

    public static function for(Member $member): self
    {
        $member->loadMissing(['user', 'instructor']);
        $today = Carbon::today();

        [$expiration, $queued, $planType] = self::resolvePeriod($member, $today);
        $coach                            = self::resolveCoach($member, $expiration, $today);

        return new self(
            $member,
            $expiration,
            $queued,
            $coach,
            self::resolveProfile($member, $expiration),
            $planType,
        );
    }

    // ─────────────────────────────────────────────────────────────
    //  Active period (gym membership)
    // ─────────────────────────────────────────────────────────────

    /** @return array{0:MembershipExpiration,1:?array,2:?string} */
    private static function resolvePeriod(Member $member, Carbon $today): array
    {
        $stored    = $member->getRawOriginal('status');
        $overall   = $member->expiration();                 // stored dates, same rules as the admin side
        $storedEnd = $member->end_date ? self::day($member->end_date) : null;

        $segments = self::gymSegments($member);

        // The chain must land exactly on the stored end date, otherwise trust the stored dates.
        if ($segments->isEmpty() || ! $storedEnd || ! $segments->last()['end']->isSameDay($storedEnd)) {
            return [$overall, null, $member->membership_type];
        }

        $index = null;
        foreach ($segments as $i => $segment) {
            if ($segment['start']->lte($today) && $today->lt($segment['end'])) {
                $index = $i;
                break;
            }
        }
        $index ??= $today->lt($segments->first()['start']) ? 0 : $segments->count() - 1;

        $current = $segments[$index];
        $isLast  = $index === $segments->count() - 1;

        $expiration = $isLast
            ? MembershipExpiration::calculate($current['start'], $current['end'], $stored)
            : MembershipExpiration::forPeriod($current['start'], $current['end'], $segments->last()['end'], $stored);

        $next   = $segments[$index + 1] ?? null;
        $queued = $next ? [
            'start'  => $next['start'],
            'end'    => $next['end'],
            'period' => $next['start']->format('M d, Y') . ' – ' . $next['end']->format('M d, Y'),
            'type'   => $next['type'],
        ] : null;

        return [$expiration, $queued, $current['type']];
    }

    /** @return Collection<int,array{start:Carbon,end:Carbon,type:string}> */
    private static function gymSegments(Member $member): Collection
    {
        $payments = Payment::query()                      // global scope = confirmed payments only
            ->where('member_id', $member->getKey())
            ->where('payment_type', 'gym_fee')
            ->whereNotNull('membership_type')
            ->whereNotNull('payment_date')
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $segments = collect();
        $cursor   = null;

        foreach ($payments as $payment) {
            $type = self::normalizeType($payment->membership_type);
            if (! $type) {
                continue;
            }

            $paid  = self::day($payment->payment_date);
            $start = ($cursor && $cursor->gt($paid)) ? $cursor->copy() : $paid;
            $end   = self::day(GreedyScheduler::computeEndDate($start->copy(), $type));

            $segments->push(['start' => $start, 'end' => $end, 'type' => $type]);
            $cursor = $end;
        }

        return $segments->values();
    }

    // ─────────────────────────────────────────────────────────────
    //  Coach
    // ─────────────────────────────────────────────────────────────

    private static function resolveCoach(Member $member, MembershipExpiration $period, Carbon $today): array
    {
        $upcoming = self::upcomingCoach($member, $today);
        $pending  = $upcoming ? null : self::pendingCoach($member);
        $current  = self::currentCoach($member, $period, $today, $upcoming);

        return [
            'state'    => $current ? 'current' : ($upcoming ? 'upcoming' : ($pending ? 'pending' : 'none')),
            'current'  => $current,
            'upcoming' => $upcoming,
            'pending'  => $pending,
        ];
    }

    private static function upcomingCoach(Member $member, Carbon $today): ?array
    {
        $request = CoachRequest::query()
            ->with(['instructor', 'payment'])
            ->where('member_id', $member->getKey())
            ->where('status', 'approved')
            ->whereNull('activated_at')
            ->whereNotNull('starts_on')
            ->whereDate('starts_on', '>', $today->toDateString())
            ->orderBy('starts_on')
            ->first();

        if (! $request) {
            return null;
        }

        $start = self::day($request->starts_on);
        $type  = self::normalizeType($request->coach_membership_type ?: $request->payment?->membership_type);

        return [
            'name'   => $request->instructor?->name ?? 'Coach',
            'start'  => $start,
            'end'    => $type ? self::inclusiveEnd(GreedyScheduler::computeEndDate($start->copy(), $type)) : null,
            'status' => 'Scheduled',
        ];
    }

    private static function pendingCoach(Member $member): ?array
    {
        $request = CoachRequest::query()
            ->with('instructor')
            ->where('member_id', $member->getKey())
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (! $request) {
            return null;
        }

        return [
            'name'   => $request->instructor?->name ?? 'Coach',
            'start'  => $request->starts_on ? self::day($request->starts_on) : null,
            'end'    => null,
            'status' => 'Pending Approval',
        ];
    }

    private static function currentCoach(Member $member, MembershipExpiration $period, Carbon $today, ?array $upcoming): ?array
    {
        if (! $member->instructor_id) {
            return null;
        }

        $segments = self::coachSegments($member);

        // No coaching records at all (legacy / hand-assigned): fall back to the gym period.
        if ($segments->isEmpty() && $period->hasDates()) {
            $segments->push(['start' => $period->startDate->copy(), 'end' => $period->endDate->copy()]);
        }

        if ($segments->isEmpty()) {
            return [
                'name'   => $member->instructor?->name ?? 'Coach',
                'start'  => null,
                'end'    => null,
                'status' => 'Active',
            ];
        }

        $segment = $segments->first(fn ($s) => $s['start']->lte($today) && $today->lt($s['end'])) ?? $segments->last();

        $end = $segment['end'];
        // The new coach takes over on his start date, so the old coaching ends the day before.
        if ($upcoming && $upcoming['start']->lt($end)) {
            $end = $upcoming['start']->copy();
        }

        return [
            'name'   => $member->instructor?->name ?? 'Coach',
            'start'  => $segment['start'],
            'end'    => self::inclusiveEnd($end),
            'status' => $today->lt($end) ? 'Active' : 'Expired',
        ];
    }

    /** @return Collection<int,array{start:Carbon,end:Carbon}> coaching blocks of the CURRENT coach, renewals stacked */
    private static function coachSegments(Member $member): Collection
    {
        $grants = collect();

        $requests = CoachRequest::query()
            ->with('payment')
            ->where('member_id', $member->getKey())
            ->where('instructor_id', $member->instructor_id)
            ->where('status', 'approved')
            ->whereNotNull('activated_at')
            ->get();

        foreach ($requests as $request) {
            $start = $request->starts_on ?? $request->activated_at;
            $type  = self::normalizeType(
                $request->coach_membership_type
                ?: $request->payment?->membership_type
                ?: $member->coach_membership_type
            );
            if ($start && $type) {
                $grants->push(['start' => self::day($start), 'type' => $type, 'id' => $request->id]);
            }
        }

        if ($grants->isEmpty()) {
            $payments = Payment::query()
                ->where('member_id', $member->getKey())
                ->where('payment_type', 'coach_fee')
                ->where('instructor_id', $member->instructor_id)
                ->whereNotNull('payment_date')
                ->get();

            foreach ($payments as $payment) {
                $type = self::normalizeType($payment->membership_type);
                if ($type) {
                    $grants->push(['start' => self::day($payment->payment_date), 'type' => $type, 'id' => $payment->id]);
                }
            }
        }

        $segments = collect();
        $cursor   = null;

        foreach ($grants->sortBy([['start', 'asc'], ['id', 'asc']]) as $grant) {
            $start = ($cursor && $cursor->gt($grant['start'])) ? $cursor->copy() : $grant['start'];
            $end   = self::day(GreedyScheduler::computeEndDate($start->copy(), $grant['type']));

            $segments->push(['start' => $start, 'end' => $end]);
            $cursor = $end;
        }

        return $segments->values();
    }

    // ─────────────────────────────────────────────────────────────
    //  Profile
    // ─────────────────────────────────────────────────────────────

    private static function resolveProfile(Member $member, MembershipExpiration $expiration): array
    {
        /** @var User|null $user */
        $user = $member->user;

        return [
            'name'         => $user?->name ?: $member->full_name,
            'email'        => $user?->email ?: $member->email,
            'phone'        => $user?->phone ?: $member->phone,
            'member_since' => $user?->created_at ?? $member->created_at,
            // Same expiration object the Subscription card uses → badge can never disagree with it.
            'status'       => $expiration->status,
            'usable'       => $expiration->isUsable(),
            'photo'        => $user?->photo ?: $member->photo,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────

    private static function normalizeType(?string $type): ?string
    {
        if ($type === 'Annual') {
            $type = 'Annually';
        }

        return in_array($type, self::TYPES, true) ? $type : null;
    }

    /** Midnight of the calendar date, in the app timezone. */
    private static function day($value): Carbon
    {
        $value = $value instanceof \DateTimeInterface ? Carbon::instance($value) : Carbon::parse($value);

        return Carbon::create($value->year, $value->month, $value->day, 0, 0, 0, config('app.timezone', 'UTC'));
    }

    /** Computed ends are exclusive (start + interval); coaching is shown to its last day. */
    private static function inclusiveEnd(Carbon $exclusiveEnd): Carbon
    {
        return $exclusiveEnd->copy()->subDay();
    }
}