<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    // ─────────────────────────────────────────
    //  Dashboard
    // ─────────────────────────────────────────

    public function dashboard()
    {
        $allMembers = Member::all();

        $stats = [
            'total'   => $allMembers->count(),
            'active'  => $allMembers->filter(
                            fn($m) => in_array($m->status, ['Active', 'Expiring Soon'])
                         )->count(),
            'monthly' => $allMembers->where('membership_type', 'Monthly')->count(),
            'yearly'  => $allMembers->where('membership_type', 'Annually')->count(),
        ];

        // Load member.user so photos resolve correctly in the members list panel
        $members = Member::with('user')->latest()->get();

        $activeCount = $allMembers->filter(
            fn($m) => in_array($m->status, ['Active', 'Expiring Soon'])
        )->count();

        $nearDue = $allMembers->filter(
            fn($m) => $m->status === 'Expiring Soon'
        )->count();

        $recent = Member::with('user')->latest()->take(6)->get();

        // Load member.user so photo resolves via member->user->photo
        $recentPayments = Payment::with('member:id,name,email,user_id', 'member.user:id,photo')
                            ->latest()
                            ->take(5)
                            ->get();

        $thisMonth = Payment::whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');

        $totalCollected = Payment::sum('amount');

        return view('staff.dashboard', compact(
            'stats',
            'members',
            'activeCount',
            'nearDue',
            'recent',
            'recentPayments',
            'thisMonth',
            'totalCollected'
        ));
    }

    // ─────────────────────────────────────────
    //  Payments
    // ─────────────────────────────────────────

    public function payments()
    {
        $members = Member::orderBy('name', 'asc')->get();

        // Load member.user so photo resolves via member->user->photo
        $payments = Payment::with('member:id,name,user_id', 'member.user:id,photo')
                        ->latest()
                        ->paginate(20);

        $thisMonth = Payment::whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');

        $totalCollected = Payment::sum('amount');
        $totalCount     = Payment::count();

        return view('staff.payments', compact(
            'members',
            'payments',
            'thisMonth',
            'totalCollected',
            'totalCount'
        ));
    }

    // ─────────────────────────────────────────
    //  Profile
    // ─────────────────────────────────────────

    public function profile()
    {
        $user = auth()->user();
        return view('staff.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:Male,Female,Other',
            'birthdate' => 'nullable|date',
            'address'   => 'nullable|string',
            'photo'     => 'nullable|image|max:3072',
        ]);

        $data = $request->only(['name', 'phone', 'gender', 'birthdate', 'address']);

        if ($request->hasFile('photo')) {
            if ($user->photo) Storage::disk('public')->delete($user->photo);
            $data['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}