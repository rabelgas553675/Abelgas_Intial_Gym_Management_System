<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total'   => Member::count(),
            'active'  => Member::where('status', 'Active')->count(),
            'monthly' => Member::where('membership_type', 'Monthly')->count(),
            'yearly'  => Member::where('membership_type', 'Annually')->count(),
        ];

        $recent = Member::latest()->take(6)->get();

        $recentPayments = Payment::with('member')
                            ->latest()
                            ->take(5)
                            ->get();

        $thisMonth = Payment::whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');

        $totalCollected = Payment::sum('amount');

        return view('staff.dashboard', compact(
            'stats', 'recent', 'recentPayments', 'thisMonth', 'totalCollected'
        ));
    }

    public function payments()
    {
        // FIX: Fetch members for the "Select Member" dropdown in the form
        $members = Member::orderBy('name', 'asc')->get();

        // Fetch paginated payments with member relationship
        $payments = Payment::with('member')->latest()->paginate(20);

        // Stats for the summary cards
        $thisMonth = Payment::whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');
        
        $totalCollected = Payment::sum('amount');
        $totalCount     = Payment::count();

        // Pass all 5 variables to the view
        return view('staff.payments', compact(
            'members', 
            'payments', 
            'thisMonth', 
            'totalCollected', 
            'totalCount'
        ));
    }

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