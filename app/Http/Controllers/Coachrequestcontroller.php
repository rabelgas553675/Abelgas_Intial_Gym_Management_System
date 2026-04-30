<?php

namespace App\Http\Controllers;

use App\Models\CoachRequest;
use Illuminate\Http\Request;

class CoachRequestController extends Controller
{
    public function index()
    {
        $instructor = auth()->user();

        $pending = CoachRequest::where('instructor_id', $instructor->id)
                               ->where('status', 'pending')
                               ->with('member.user')
                               ->latest()
                               ->get();

        $history = CoachRequest::where('instructor_id', $instructor->id)
                               ->whereIn('status', ['approved', 'rejected'])
                               ->with('member.user')
                               ->latest()
                               ->get();

        return view('instructor.requests', compact('pending', 'history'));
    }

    public function approve(CoachRequest $coachRequest)
    {
        if ($coachRequest->instructor_id !== auth()->id()) {
            abort(403);
        }

        $coachRequest->update(['status' => 'approved']);

        // ✅ Only assign instructor to member AFTER approval
        $coachRequest->member->update([
            'instructor_id' => $coachRequest->instructor_id,
        ]);

        return back()->with('success', 'Request approved! Member added to your dashboard.');
    }

    public function reject(CoachRequest $coachRequest)
    {
        if ($coachRequest->instructor_id !== auth()->id()) {
            abort(403);
        }

        $coachRequest->update(['status' => 'rejected']);

        // ✅ Clear coach assignment from member on rejection
        $coachRequest->member->update([
            'instructor_id'         => null,
            'coach_membership_type' => null,
        ]);

        return back()->with('success', 'Request rejected.');
    }
}