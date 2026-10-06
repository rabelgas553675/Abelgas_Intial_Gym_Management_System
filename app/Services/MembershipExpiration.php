<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Single source of truth for membership expiration.
 *
 * Every screen (admin, staff, instructor, member, attendance scan) must obtain
 * its status / days-left / progress from here — usually through
 * Member::expiration() — and never recompute it.
 *
 * Rules
 * ─────
 *  • Everything is calculated in whole CALENDAR DAYS in the application
 *    timezone, so the hour/minute of "now" can never change the result.
 *  • daysRemaining = endDate − today, never negative.
 *  • Expired        → today >= endDate        (daysRemaining == 0)
 *  • Expiring soon  → 1..7 days remaining
 *  • Active         → more than 7 days remaining
 *  • progress       → remaining / (endDate − startDate) * 100, clamped 0..100
 *                     (a renewal that is stacked on top of a still-running plan
 *                      has remaining > total; it is simply clamped to 100)
 *  • Staff-controlled states (Suspended / Inactive) override the date-based
 *    label but the dates are still calculated so they remain visible.
 */
final class MembershipExpiration
{
    public const EXPIRING_SOON_DAYS = 7;

    public const STATE_ACTIVE      = 'active';
    public const STATE_EXPIRING    = 'expiring';
    public const STATE_EXPIRED     = 'expired';
    public const STATE_SUSPENDED   = 'suspended';
    public const STATE_INACTIVE    = 'inactive';
    public const STATE_UNAVAILABLE = 'unavailable';

    private function __construct(
        /** machine state: active|expiring|expired|suspended|inactive|unavailable */
        public readonly string $state,
        /** legacy label stored/filtered all over the app: Active|Expiring Soon|Expired|Suspended|Inactive|No Plan */
        public readonly string $status,
        /** whole days left, 0 when expired, null when no usable end date */
        public readonly ?int $daysRemaining,
        /** 0..100 */
        public readonly int $progress,
        public readonly ?Carbon $startDate,
        public readonly ?Carbon $endDate,
        /** end date is today or earlier (independent of Suspended/Inactive overrides) */
        public readonly bool $dateExpired,
        /** start date is still in the future (stacked renewal) */
        public readonly bool $startsInFuture,
    ) {
    }

    // ─────────────────────────────────────────────────────────────
    //  Factory
    // ─────────────────────────────────────────────────────────────

    public static function calculate(
        ?CarbonInterface $start,
        ?CarbonInterface $end,
        ?string $storedStatus = null,
        ?CarbonInterface $now = null,
    ): self {
        $tz    = config('app.timezone', 'UTC');
        $today = self::day($now ?? Carbon::now($tz), $tz);
        $start = $start ? self::day($start, $tz) : null;
        $end   = $end ? self::day($end, $tz) : null;

        $override = match ($storedStatus) {
            'Suspended' => [self::STATE_SUSPENDED, 'Suspended'],
            'Inactive'  => [self::STATE_INACTIVE, 'Inactive'],
            default     => null,
        };

        // No expiration date → nothing reliable to calculate.
        if (!$end) {
            [$state, $status] = $override ?? [self::STATE_UNAVAILABLE, 'No Plan'];

            return new self($state, $status, null, 0, $start, null, false, false);
        }

        $days     = max(0, (int) round($today->diffInDays($end, false)));
        $expired  = $days === 0;
        $total    = $start ? (int) round($start->diffInDays($end, false)) : 0;
        $progress = $expired
            ? 0
            : ($total > 0 ? (int) max(0, min(100, round($days / $total * 100))) : 100);

        if ($override) {
            [$state, $status] = $override;
        } elseif ($expired) {
            [$state, $status] = [self::STATE_EXPIRED, 'Expired'];
        } elseif ($days <= self::EXPIRING_SOON_DAYS) {
            [$state, $status] = [self::STATE_EXPIRING, 'Expiring Soon'];
        } else {
            [$state, $status] = [self::STATE_ACTIVE, 'Active'];
        }

        return new self(
            $state,
            $status,
            $days,
            $progress,
            $start,
            $end,
            $expired,
            $start !== null && $start->greaterThan($today),
        );
    }

    /** Midnight of the given moment's calendar date, in the app timezone (no tz shifting of date-only values). */
    private static function day(CarbonInterface $value, string $tz): Carbon
    {
        return Carbon::create($value->year, $value->month, $value->day, 0, 0, 0, $tz);
    }

    // ─────────────────────────────────────────────────────────────
    //  Presentation helpers (shared by every view)
    // ─────────────────────────────────────────────────────────────

    /** True while the member may use the gym (active or expiring, not suspended/inactive/expired). */
    public function isUsable(): bool
    {
        return in_array($this->state, [self::STATE_ACTIVE, self::STATE_EXPIRING], true);
    }

    public function hasDates(): bool
    {
        return $this->endDate !== null;
    }

    public function isExpiringSoon(): bool
    {
        return $this->state === self::STATE_EXPIRING;
    }

    /** Upper-case badge text: ACTIVE / EXPIRING SOON / EXPIRED / … */
    public function label(): string
    {
        return mb_strtoupper($this->status);
    }

    /** "30 days left", "1 day left", "0 days left", or a fallback. */
    public function daysText(): string
    {
        if ($this->daysRemaining === null) {
            return 'Expiration date unavailable';
        }

        return $this->daysRemaining . ' ' . ($this->daysRemaining === 1 ? 'day' : 'days') . ' left';
    }

    /** Value for the "Days remaining" line: "30 days left" / "Expired" / fallback. Never negative or null. */
    public function displayText(): string
    {
        if ($this->daysRemaining === null) {
            return 'Expiration date unavailable';
        }

        return $this->dateExpired ? 'Expired' : $this->daysText();
    }

    /** Compact text for tables: "30 days left" / "Expired". */
    public function shortText(): string
    {
        if ($this->daysRemaining === null) {
            return '—';
        }
        if ($this->dateExpired) {
            return 'Expired';
        }

        return $this->daysText();
    }

    /** success | warning | danger | muted — maps to CSS variables in every layout. */
    public function tone(): string
    {
        return match ($this->state) {
            self::STATE_ACTIVE    => 'success',
            self::STATE_EXPIRING  => 'warning',
            self::STATE_EXPIRED   => 'danger',
            self::STATE_SUSPENDED => 'warning',
            default               => 'muted',
        };
    }

    /** "Oct 06, 2026 – Nov 06, 2026" (either side falls back to "—"). */
    public function periodText(): string
    {
        return ($this->startDate?->format('M d, Y') ?? '—') . ' – ' . ($this->endDate?->format('M d, Y') ?? '—');
    }

    /** Member-facing warning sentence, or null when nothing needs saying. */
    public function message(): ?string
    {
        return match (true) {
            $this->state === self::STATE_EXPIRING =>
                'Your membership expires in ' . $this->daysRemaining . ' ' . ($this->daysRemaining === 1 ? 'day' : 'days') . '.',
            $this->state === self::STATE_EXPIRED =>
                'Your membership has expired. Please renew your membership to continue accessing member benefits.',
            default => null,
        };
    }

    public function toArray(): array
    {
        return [
            'state'            => $this->state,
            'status'           => $this->status,
            'label'            => $this->label(),
            'days_remaining'   => $this->daysRemaining,
            'days_text'        => $this->daysText(),
            'progress'         => $this->progress,
            'start_date'       => $this->startDate?->toDateString(),
            'end_date'         => $this->endDate?->toDateString(),
            'starts_in_future' => $this->startsInFuture,
        ];
    }
}