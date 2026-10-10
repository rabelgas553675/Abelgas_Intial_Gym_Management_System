<?php

namespace App\Services;

use App\Models\Member;
use App\Models\PlanChangeRequest;
use App\Models\User;
use App\Notifications\PlanChangeUpdated;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Coach-approval workflow for fitness-plan changes.
 *
 * The ONLY place that ever writes members.fitness_plan for a plan change is
 * approve(), and only after the assigned coach confirms. Nothing here touches
 * payments, membership dates/type/fee, the coaching period or the assigned coach.
 *
 * Every method throws DomainException with a member/coach-friendly message when
 * a rule is broken; controllers just show that message.
 */
class PlanChangeService
{
    /** Is this member's coach assignment live (assigned AND approved)? */
    public static function assignedCoachId(Member $member): ?int
    {
        if (! $member->instructor_id || $member->coach_status !== 'approved') {
            return null;
        }

        $isCoach = User::query()
            ->whereKey($member->instructor_id)
            ->where('role', 'instructor')
            ->exists();

        return $isCoach ? (int) $member->instructor_id : null;
    }

    /**
     * The member's open request, after cancelling any that went stale because the
     * coach was removed / reassigned. Safe to call on every page view.
     */
    public function pendingFor(Member $member): ?PlanChangeRequest
    {
        $this->cancelStale($member);

        return PlanChangeRequest::query()
            ->where('member_id', $member->id)
            ->pending()
            ->latest('id')
            ->first();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Member: submit
    // ─────────────────────────────────────────────────────────────────────────

    public function submit(Member $member, string $requestedPlan, ?string $reason = null): PlanChangeRequest
    {
        if (! in_array($requestedPlan, PlanChangeRequest::PLANS, true)) {
            throw new DomainException('Please choose a valid fitness plan.');
        }

        try {
            $request = DB::transaction(function () use ($member, $requestedPlan, $reason) {
                // Lock the member row so two simultaneous clicks are serialised.
                $locked = Member::query()->whereKey($member->id)->lockForUpdate()->firstOrFail();

                $this->cancelStale($locked);

                if ($locked->fitness_plan === $requestedPlan) {
                    throw new DomainException("{$requestedPlan} is already your current plan. Please choose a different plan.");
                }

                $hasPending = PlanChangeRequest::query()
                    ->where('member_id', $locked->id)
                    ->pending()
                    ->exists();

                if ($hasPending) {
                    throw new DomainException('You already have a plan change request waiting for your coach. Please wait for it to be reviewed or cancel it first.');
                }

                $coachId = self::assignedCoachId($locked);

                if (! $coachId) {
                    throw new DomainException('You need an approved coach to request a plan change. Your coach reviews every plan change, so please get a coach assigned first.');
                }

                $request = PlanChangeRequest::create([
                    'member_id'         => $locked->id,
                    'instructor_id'     => $coachId,
                    'current_plan'      => $locked->fitness_plan,
                    'requested_plan'    => $requestedPlan,
                    'reason'            => $this->clean($reason),
                    'status'            => PlanChangeRequest::PENDING,
                    'pending_member_id' => $locked->id,
                    'requested_at'      => now(),
                ]);

                AuditLogger::log(
                    'plan_change_requested', 'Plan Changes',
                    "Requested plan change {$request->current_plan} → {$request->requested_plan} for member #{$locked->id}",
                    $request, null,
                    ['current_plan' => $request->current_plan, 'requested_plan' => $request->requested_plan, 'status' => $request->status],
                    $locked->user_id,
                );

                return $request;
            });
        } catch (QueryException $e) {
            // The unique pending_member_id index caught a race the lock did not.
            if ($this->isUniqueViolation($e)) {
                throw new DomainException('You already have a plan change request waiting for your coach.');
            }
            throw $e;
        }

        $request->load('member.user', 'coach');
        $this->notify($request->member?->user, $request, 'submitted');
        $this->notify($request->coach, $request, 'requested');

        return $request;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Member: cancel
    // ─────────────────────────────────────────────────────────────────────────

    public function cancel(Member $member, int $requestId): PlanChangeRequest
    {
        $request = DB::transaction(function () use ($member, $requestId) {
            $request = PlanChangeRequest::query()->whereKey($requestId)->lockForUpdate()->first();

            // Members can only touch their own requests.
            if (! $request || (int) $request->member_id !== (int) $member->id) {
                throw new DomainException('Request not found.');
            }

            if (! $request->isPending()) {
                throw new DomainException('This request has already been reviewed, so it can no longer be cancelled.');
            }

            $request->forceFill([
                'status'            => PlanChangeRequest::CANCELLED,
                'pending_member_id' => null,
                'reviewed_at'       => now(),
            ])->save();

            AuditLogger::log(
                'plan_change_cancelled', 'Plan Changes',
                "Member #{$member->id} cancelled plan change {$request->current_plan} → {$request->requested_plan}",
                $request, ['status' => PlanChangeRequest::PENDING], ['status' => PlanChangeRequest::CANCELLED],
                $member->user_id,
            );

            return $request;
        });

        $request->load('member', 'coach');
        $this->notify($request->coach, $request, 'cancelled');

        return $request;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Coach: approve / reject
    // ─────────────────────────────────────────────────────────────────────────

    public function approve(int $requestId, User $coach): PlanChangeRequest
    {
        $request = DB::transaction(function () use ($requestId, $coach) {
            [$request, $member] = $this->lockForReview($requestId, $coach);

            if ($member->fitness_plan !== $request->current_plan) {
                throw new DomainException('This member\'s plan has changed since the request was made. Please reject it so they can submit a new one.');
            }

            // The one and only write to the member: the fitness plan.
            $member->fitness_plan = $request->requested_plan;
            $member->save();

            $request->forceFill([
                'status'            => PlanChangeRequest::APPROVED,
                'pending_member_id' => null,
                'reviewed_by'       => $coach->id,
                'reviewed_at'       => now(),
            ])->save();

            AuditLogger::log(
                'plan_change_approved', 'Plan Changes',
                "Coach {$coach->name} approved plan change {$request->current_plan} → {$request->requested_plan} for member #{$member->id}",
                $request,
                ['status' => PlanChangeRequest::PENDING, 'fitness_plan' => $request->current_plan],
                ['status' => PlanChangeRequest::APPROVED, 'fitness_plan' => $request->requested_plan],
                $coach->id,
            );

            return $request;
        });

        $request->load('member.user');
        $this->notify($request->member?->user, $request, 'approved');

        return $request;
    }

    public function reject(int $requestId, User $coach, ?string $reason): PlanChangeRequest
    {
        $reason = $this->clean($reason);

        if ($reason === null) {
            throw new DomainException('Please give a reason for rejecting this request.');
        }

        $request = DB::transaction(function () use ($requestId, $coach, $reason) {
            [$request] = $this->lockForReview($requestId, $coach);

            $request->forceFill([
                'status'            => PlanChangeRequest::REJECTED,
                'pending_member_id' => null,
                'coach_feedback'    => $reason,
                'reviewed_by'       => $coach->id,
                'reviewed_at'       => now(),
            ])->save();

            AuditLogger::log(
                'plan_change_rejected', 'Plan Changes',
                "Coach {$coach->name} rejected plan change {$request->current_plan} → {$request->requested_plan} for member #{$request->member_id}",
                $request,
                ['status' => PlanChangeRequest::PENDING],
                ['status' => PlanChangeRequest::REJECTED, 'coach_feedback' => $reason],
                $coach->id,
            );

            return $request;
        });

        $request->load('member.user');
        $this->notify($request->member?->user, $request, 'rejected');

        return $request;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Internals
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lock the request + member and re-check EVERYTHING on the locked rows:
     * still pending, reviewer is a coach, request is addressed to this coach and
     * the member is still assigned to this coach. Nothing from the browser is trusted.
     *
     * @return array{0: PlanChangeRequest, 1: Member}
     */
    private function lockForReview(int $requestId, User $coach): array
    {
        $request = PlanChangeRequest::query()->whereKey($requestId)->lockForUpdate()->first();

        if (! $request) {
            throw new DomainException('Request not found.');
        }

        if (! $coach->isInstructor() || (int) $request->instructor_id !== (int) $coach->id) {
            throw new DomainException('This request does not belong to you.');
        }

        if (! $request->isPending()) {
            throw new DomainException('This request has already been reviewed.');
        }

        $member = Member::query()->whereKey($request->member_id)->lockForUpdate()->first();

        if (! $member || (int) self::assignedCoachId($member) !== (int) $coach->id) {
            throw new DomainException('This member is no longer assigned to you, so you can\'t review this request.');
        }

        return [$request, $member];
    }

    /** Close pending requests whose coach is gone or no longer the member's coach. */
    private function cancelStale(Member $member): void
    {
        $coachId = self::assignedCoachId($member);

        PlanChangeRequest::query()
            ->where('member_id', $member->id)
            ->pending()
            ->when($coachId, fn ($q) => $q->where(function ($q) use ($coachId) {
                $q->whereNull('instructor_id')->orWhere('instructor_id', '!=', $coachId);
            }))
            ->get()
            ->each(function (PlanChangeRequest $stale) {
                $stale->forceFill([
                    'status'            => PlanChangeRequest::CANCELLED,
                    'pending_member_id' => null,
                    'coach_feedback'    => 'Cancelled automatically because your coach assignment changed.',
                    'reviewed_at'       => now(),
                ])->save();

                AuditLogger::log(
                    'plan_change_cancelled', 'Plan Changes',
                    "Plan change request #{$stale->id} auto-cancelled: coach assignment changed",
                    $stale, ['status' => PlanChangeRequest::PENDING], ['status' => PlanChangeRequest::CANCELLED],
                );
            });
    }

    /** Notifications must never break the real action. */
    private function notify(?User $user, PlanChangeRequest $request, string $event): void
    {
        if (! $user) {
            return;
        }

        try {
            $user->notify(new PlanChangeUpdated($request, $event));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function clean(?string $text): ?string
    {
        $text = trim((string) $text);

        return $text === '' ? null : $text;
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $code = (string) ($e->errorInfo[0] ?? $e->getCode());

        return in_array($code, ['23000', '23505'], true);
    }
}