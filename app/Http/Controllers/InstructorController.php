<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstructorController extends Controller
{
    // ─────────────────────────────────────────
    //  Dashboard
    // ─────────────────────────────────────────

    /**
     * Instructor dashboard.
     * Uses dynamic status (via Member accessor) — never reads raw DB status column.
     * "Active" count includes members who are expiring soon (still have valid subscriptions).
     */
    public function dashboard()
    {
        $instructor = auth()->user();

        $members = Member::where('instructor_id', $instructor->id)
                         ->with('payments')
                         ->get();

        // Active = anyone still subscribed (includes "Expiring Soon")
        $active  = $members->filter(fn($m) => in_array($m->status, ['Active', 'Expiring Soon']))->count();
        $expired = $members->filter(fn($m) => $m->status === 'Expired')->count();
        $nearDue = $members->filter(fn($m) => $m->status === 'Expiring Soon')->count();

        return view('instructor.dashboard', compact(
            'instructor', 'members', 'active', 'expired', 'nearDue'
        ));
    }

    // ─────────────────────────────────────────
    //  Member Detail
    // ─────────────────────────────────────────

    /**
     * View a single member's detail page.
     * Restricted to the member's assigned instructor only.
     */
    public function showMember(Member $member)
    {
        if ($member->instructor_id !== auth()->id()) {
            abort(403, 'This member is not assigned to you.');
        }

        $payments = $member->payments()->latest()->get();

        return view('instructor.member-detail', compact('member', 'payments'));
    }

    // ─────────────────────────────────────────
    //  Profile
    // ─────────────────────────────────────────

    /**
     * Show instructor profile page.
     */
    public function profile()
    {
        $instructor = auth()->user();
        return view('instructor.profile', compact('instructor'));
    }

    /**
     * Update instructor profile.
     */
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
            'name', 'phone', 'specialization', 'experience_years', 'address',
        ]);

        if ($request->hasFile('photo')) {
            if ($instructor->photo) {
                Storage::disk('public')->delete($instructor->photo);
            }
            $data['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        $instructor->update($data);

        return back()->with('success', 'Profile updated!');
    }

    // ─────────────────────────────────────────
    //  Payment History
    // ─────────────────────────────────────────

    /**
     * Instructor's own earnings page.
     * Route: GET /instructor/payments  →  name: instructor.payments
     */
    public function paymentHistory()
    {
        $instructor = auth()->user();

        $payments = Payment::forInstructor($instructor->id)
                           ->with('member:id,name')
                           ->latest('payment_date')
                           ->paginate(15);

        $totalEarned    = Payment::forInstructor($instructor->id)->sum('amount');
        $thisMonthTotal = Payment::forInstructor($instructor->id)->thisMonth()->sum('amount');

        return view('instructor.payments', compact(
            'payments',
            'totalEarned',
            'thisMonthTotal',
            'instructor'
        ));
    }
}