<?php

namespace App\Http\Controllers;

use App\Models\CoachRequest;
use App\Models\Member;
use Illuminate\Http\Request;

class CoachRequestController extends Controller 
{
    /**
     * Show all pending and historical coach requests for the authenticated instructor.
     */
    public function index()
    {
        /** @var \App\Models\User $instructor */
        $instructor = auth()->user();

        // Get all requests for this instructor with member and user details
        $allRequests = CoachRequest::query()
            ->with('member.user')
            ->where('instructor_id', $instructor->id)
            ->latest()
            ->get();

        // Separate collections for different view sections
        $pending = $allRequests->where('status', 'pending');
        $history = $allRequests->whereIn('status', ['approved', 'rejected']);

        // Passing all variables to satisfy Blade requirements
        return view('instructor.requests', compact('allRequests', 'pending', 'history'));
    }

    /**
     * Approve a coach request.
     */
    public function approve(CoachRequest $coachRequest)
    {
        // Security check
        $this->authorizeRequest($coachRequest);

        // Update the request record
        $coachRequest->update(['status' => 'approved']);

        // Update the member record to link the coach and update status
        if ($coachRequest->member) {
            $coachRequest->member->update([
                'instructor_id' => auth()->id(),
                'coach_status'  => 'approved'
            ]);
        }

        return back()->with('success', 'Request approved! Member has been notified.');
    }

    /**
     * Reject a coach request.
     */
    public function reject(CoachRequest $coachRequest)
    {
        // Security check
        $this->authorizeRequest($coachRequest);

        // Update the request record
        $coachRequest->update(['status' => 'rejected']);

        // Update the member record — clear assignment and mark as rejected
        if ($coachRequest->member) {
            $coachRequest->member->update([
                'instructor_id' => null,
                'coach_status'  => 'rejected'
            ]);
        }

        return back()->with('success', 'Request rejected.');
    }

    /**
     * Ensure this request belongs to the authenticated instructor
     * and is still in a pending state before performing actions.
     */
    private function authorizeRequest(CoachRequest $coachRequest): void
    {
        if ($coachRequest->instructor_id !== auth()->id()) {
            abort(403, 'This request does not belong to you.');
        }

        if ($coachRequest->status !== 'pending') {
            abort(422, 'This request has already been processed.');
        }
    }
}