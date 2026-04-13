<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorController extends Controller
{
    // Instructor dashboard
    public function dashboard()
    {
        $instructor = auth()->user();

        $members = Member::where('instructor_id', $instructor->id)
                        ->with('payments')
                        ->get();

        $active  = $members->where('status', 'Active')->count();
        $expired = $members->where('status', 'Expired')->count();
        $nearDue = $members->filter(fn($m) => $m->isDueWithinDays(7))->count();

        return view('instructor.dashboard', compact(
            'instructor', 'members', 'active', 'expired', 'nearDue'
        ));
    }

    // View single member detail
    public function showMember(Member $member)
    {
        // Make sure this member belongs to this instructor
        if ($member->instructor_id !== auth()->id()) {
            abort(403, 'This member is not assigned to you.');
        }

        $payments = $member->payments()->latest()->get();

        return view('instructor.member-detail', compact('member', 'payments'));
    }

    // Instructor profile
    public function profile()
    {
        $instructor = auth()->user();
        return view('instructor.profile', compact('instructor'));
    }

    // Update instructor profile
    public function updateProfile(Request $request)
    {
        $instructor = auth()->user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'specialization'   => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'address'          => 'nullable|string',
            'photo'            => 'nullable|image|max:3072',
        ]);

        $data = $request->only([
            'name', 'phone', 'specialization', 'experience_years', 'address'
        ]);

        if ($request->hasFile('photo')) {
            if ($instructor->photo) Storage::disk('public')->delete($instructor->photo);
            $data['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        $instructor->update($data);

        return back()->with('success', 'Profile updated!');
    }
    public function payments()
{
    $instructor = auth()->user();

    $payments = \App\Models\Payment::whereHas('member', function($q) use ($instructor) {
        $q->where('instructor_id', $instructor->id);
    })->with('member')->latest()->get();

    return view('instructor.payments', compact('payments'));
}
}