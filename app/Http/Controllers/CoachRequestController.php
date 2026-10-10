<?php

namespace App\Http\Controllers;

use App\Models\CoachRequest;
use App\Models\PlanChangeRequest;
use App\Services\CoachConfirmationService;
use App\Services\PlanChangeService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CoachRequestController extends Controller
{
    public function __construct(
        private CoachConfirmationService $coachConfirmation,
        private PlanChangeService $planChanges,
    ) {
    }

    /**
     * Show all pending and historical coach requests for the authenticated instructor.
     */
    public function index()
    {
        $instructor = Auth::user();

        // Start any approved renewals whose start date has arrived.
        $this->coachConfirmation->activateDue();

        $allRequests = CoachRequest::query()
            ->with(['member.user', 'payment', 'requester'])
            ->where('instructor_id', $instructor->id)
            ->latest()
            ->get();

        $pending = $allRequests->where('status', 'pending');
        $history = $allRequests->whereIn('status', [
            'approved',
            'rejected',
        ]);

        // ── Fitness-plan change requests from members currently assigned to this coach ──
        $planPending = PlanChangeRequest::query()
            ->with('member.user')
            ->where('instructor_id', $instructor->id)
            ->pending()
            ->whereHas('member', fn ($q) => $q
                ->where('instructor_id', $instructor->id)
                ->where('coach_status', 'approved'))
            ->oldest('requested_at')
            ->get();

        $planHistory = PlanChangeRequest::query()
            ->with(['member.user', 'reviewer'])
            ->where('instructor_id', $instructor->id)
            ->whereIn('status', [PlanChangeRequest::APPROVED, PlanChangeRequest::REJECTED])
            ->latest('reviewed_at')
            ->limit(20)
            ->get();

        return view('instructor.requests', compact('allRequests', 'pending', 'history', 'planPending', 'planHistory'));
    }

    /**
     * Approve a coach request. For manual renewals this confirms the held payment
     * and assigns (or schedules) the coach.
     */
    public function approve(CoachRequest $coachRequest)
    {
        $this->authorizeCoach($coachRequest);

        try {
            $result = $this->coachConfirmation->approve($coachRequest->id, Auth::user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result->payment_id) {
            $message = $result->isScheduled()
                ? 'Request approved. The payment is confirmed and coaching is scheduled to start on '
                    . $result->starts_on->format('M d, Y') . '.'
                : 'Request approved. The payment is confirmed and the member is now assigned to you.';
        } else {
            $message = 'Request approved! Member has been notified.';
        }

        return back()->with('success', $message);
    }

    /**
     * Reject a coach request. A reason is required.
     */
    public function reject(Request $request, CoachRequest $coachRequest)
    {
        $this->authorizeCoach($coachRequest);

        $validator = Validator::make($request->all(), [
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Please give a reason for rejecting this request.',
            'rejection_reason.min'      => 'The rejection reason must be at least 5 characters.',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        try {
            $this->coachConfirmation->reject(
                $coachRequest->id,
                Auth::user(),
                $request->input('rejection_reason')
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request rejected. The held payment was cancelled.');
    }

    /**
     * Only the selected coach may respond. (Checked again inside the service
     * after the row is locked.)
     */
    private function authorizeCoach(CoachRequest $coachRequest): void
    {
        $user = Auth::user();

        if (! $user || ! $user->isInstructor() || (int) $coachRequest->instructor_id !== (int) $user->id) {
            abort(403, 'This request does not belong to you.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Fitness-plan change requests
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Approve a plan change. The service re-checks everything on locked rows
     * (still pending, addressed to this coach, member still assigned to this coach)
     * and only then updates the member's fitness plan.
     */
    public function approvePlanChange(int $planChangeRequest)
    {
        $user = Auth::user();

        if (! $user || ! $user->isInstructor()) {
            abort(403, 'Only coaches can review plan change requests.');
        }

        try {
            $result = $this->planChanges->approve($planChangeRequest, $user);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Plan change approved. The member is now on {$result->requested_plan} and has been notified.");
    }

    /**
     * Reject a plan change. A reason is required; the member's plan stays unchanged.
     */
    public function rejectPlanChange(Request $request, int $planChangeRequest)
    {
        $user = Auth::user();

        if (! $user || ! $user->isInstructor()) {
            abort(403, 'Only coaches can review plan change requests.');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Please give a reason for rejecting this request.',
            'rejection_reason.min'      => 'The rejection reason must be at least 5 characters.',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        try {
            $this->planChanges->reject($planChangeRequest, $user, $request->input('rejection_reason'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Plan change rejected. The member keeps their current plan and has been notified.');
    }
}