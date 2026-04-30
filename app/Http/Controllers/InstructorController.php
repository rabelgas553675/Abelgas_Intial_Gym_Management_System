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

    public function dashboard()
    {
        $instructor = auth()->user();

        // FIX: load member.user so photo resolves via member->user->photo
        $members = Member::where('instructor_id', $instructor->id)
                         ->with('user', 'payments')
                         ->get();

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

    public function showMember(Member $member)
    {
        if ($member->instructor_id !== auth()->id()) {
            abort(403, 'This member is not assigned to you.');
        }

        // FIX: eager-load user so photo resolves via member->user->photo
        $member->loadMissing('user');

        $payments = $member->payments()->latest()->get();

        return view('instructor.member-detail', compact('member', 'payments'));
    }

    // ─────────────────────────────────────────
    //  Profile
    // ─────────────────────────────────────────

    public function profile()
    {
        $instructor = auth()->user();
        return view('instructor.profile', compact('instructor'));
    }

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

    public function paymentHistory()
    {
        $instructor = auth()->user();

        // FIX: load member.user so photo resolves via payment->member->user->photo
        $payments = Payment::forInstructor($instructor->id)
                           ->with('member:id,name,user_id', 'member.user:id,photo')
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