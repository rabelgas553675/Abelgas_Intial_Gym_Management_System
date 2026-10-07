<?php

namespace App\Services;

use App\Models\CoachRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * Coach confirmation for manually recorded renewals.
 *
 * Lifecycle of a manual renewal that includes a coach:
 *
 *   created   → CoachRequest(pending) + Payment(Awaiting Coach), member NOT assigned
 *   approved  → Payment(Paid)  [now counts in history + coach earnings]
 *               starts_on <= today → member assigned now   (activated_at set)
 *               starts_on  > today → "Scheduled"           (activated_at NULL)
 *   rejected  → Payment(Rejected), no assignment, no earnings
 *
 * Every state change runs in a transaction on row-locked records so a double
 * click / two tabs can never confirm or reject the same request twice.
 */
class CoachConfirmationService
{
    /**
     * Create the pending request for a manual renewal.
     * Any older pending request of the same member is superseded first
     * (and its held payment is marked Rejected so it can never be orphaned).
     */
    public function createRequest(
        Member $member,
        int $instructorId,
        string $coachType,
        ?Payment $payment,
        User $requester,
        Carbon $startsOn,
        string $message
    ): CoachRequest {
        return DB::transaction(function () use ($member, $instructorId, $coachType, $payment, $requester, $startsOn, $message) {
            CoachRequest::supersedePending($member->getKey());

            return CoachRequest::create([
                'member_id'             => $member->getKey(),
                'instructor_id'         => $instructorId,
                'status'                => 'pending',
                'message'               => $message,
                'payment_id'            => $payment?->getKey(),
                'requested_by'          => $requester->getKey(),
                'coach_membership_type' => $coachType,
                'starts_on'             => $startsOn->toDateString(),
            ]);
        });
    }

    /**
     * Coach approves: confirm the payment, then activate now or schedule.
     */
    public function approve(int $requestId, User $coach): CoachRequest
    {
        return DB::transaction(function () use ($requestId, $coach) {
            $request = $this->lockPendingFor($requestId, $coach);
            $payment = $this->lockHeldPayment($request);

            $request->forceFill([
                'status'       => 'approved',
                'responded_at' => now(),
            ])->save();

            if ($payment) {
                $payment->forceFill(['status' => Payment::STATUS_PAID])->save();
            }

            if ($request->starts_on === null || $request->starts_on->lte(Carbon::today())) {
                $this->activate($request);
            }

            return $request->refresh();
        });
    }

    /**
     * Coach rejects: reason is mandatory, payment is voided, nobody is assigned.
     */
    public function reject(int $requestId, User $coach, string $reason): CoachRequest
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('A rejection reason is required.');
        }

        return DB::transaction(function () use ($requestId, $coach, $reason) {
            $request = $this->lockPendingFor($requestId, $coach);
            $payment = $this->lockHeldPayment($request);

            $request->forceFill([
                'status'           => 'rejected',
                'responded_at'     => now(),
                'rejection_reason' => $reason,
            ])->save();

            if ($payment) {
                $payment->forceFill(['status' => Payment::STATUS_REJECTED])->save();
            }

            // Member-initiated (legacy) requests keep their old behaviour: clear the
            // member's pending assignment. Manual renewals never touched the member's
            // coach fields, so there is nothing to undo.
            if (! $request->isManual() && $request->member) {
                $request->member->update(['instructor_id' => null, 'coach_status' => 'rejected']);
            }

            return $request->refresh();
        });
    }

    /**
     * Assign the coach to the member. Only ever called after approval.
     */
    public function activate(CoachRequest $request): void
    {
        $member = $request->member;

        if ($member) {
            $attributes = [
                'instructor_id' => $request->instructor_id,
                'coach_status'  => 'approved',
            ];

            if ($request->coach_membership_type) {
                $attributes['coach_membership_type'] = $request->coach_membership_type;
            }

            $member->update($attributes);
        }

        $request->forceFill(['activated_at' => now()])->save();
    }

    /**
     * Activate approved requests whose start date has arrived.
     * Run daily by the scheduler (and lazily when a coach opens the requests page).
     */
    public function activateDue(): int
    {
        $activated = 0;

        $due = CoachRequest::query()
            ->where('status', 'approved')
            ->whereNull('activated_at')
            ->whereNotNull('starts_on')
            ->whereDate('starts_on', '<=', Carbon::today())
            ->orderBy('starts_on')
            ->pluck('id');

        foreach ($due as $id) {
            DB::transaction(function () use ($id, &$activated) {
                $request = CoachRequest::whereKey($id)->lockForUpdate()->first();

                if ($request && $request->status === 'approved' && $request->activated_at === null) {
                    $this->activate($request);
                    $activated++;
                }
            });
        }

        return $activated;
    }

    // ── internals ────────────────────────────────────────────────────────────

    /** Lock the request and make sure THIS coach owns it and it is still pending. */
    private function lockPendingFor(int $requestId, User $coach): CoachRequest
    {
        $request = CoachRequest::whereKey($requestId)->lockForUpdate()->first();

        if (! $request) {
            throw new DomainException('This request no longer exists.');
        }

        if (! $coach->isInstructor() || (int) $request->instructor_id !== (int) $coach->getKey()) {
            throw new AuthorizationException('Only the selected coach can respond to this request.');
        }

        if ($request->status !== 'pending') {
            throw new DomainException('This request has already been processed.');
        }

        return $request;
    }

    /** Lock the payment held by this request (null for requests that hold no payment). */
    private function lockHeldPayment(CoachRequest $request): ?Payment
    {
        if (! $request->payment_id) {
            return null;
        }

        $payment = Payment::withUnconfirmed()->whereKey($request->payment_id)->lockForUpdate()->first();

        if (! $payment || $payment->status !== Payment::STATUS_AWAITING_COACH) {
            throw new DomainException('The payment linked to this request is no longer awaiting confirmation.');
        }

        return $payment;
    }
}