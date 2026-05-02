<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Services\Algorithms\MergeSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Admin dashboard.
     *
     * DSA integration:
     *   - MergeSort::sortBy() replaces ->latest() for recentMembers,
     *     recentPayments, and recentUsers.
     */
    public function index()
    {
        $stats = [
            'total'   => Member::count(),
            'active'  => Member::where('status', 'Active')->count(),
            'monthly' => Member::where('membership_type', 'Monthly')->count(),
            'annual'  => Member::where('membership_type', 'Annual')
                                   ->orWhere('membership_type', 'Annually')->count(),
        ];

        $thisMonth      = Payment::whereMonth('payment_date', now()->month)
                                 ->whereYear('payment_date',  now()->year)
                                 ->sum('amount');
        $totalCollected = Payment::sum('amount');

        // MergeSort replaces ->latest()->take(8) for recent members
        $allMembers    = Member::get()->all();
        $recentMembers = collect(
            array_slice(MergeSort::sortBy($allMembers, 'created_at', 'desc'), 0, 8)
        );

        // MergeSort replaces ->latest('payment_date')->take(6) for recent payments
        $allPayments    = Payment::with('member')->get()->all();
        $recentPayments = collect(
            array_slice(MergeSort::sortBy($allPayments, 'payment_date', 'desc'), 0, 6)
        );

        // MergeSort replaces ->latest()->take(6) for recent users
        $allUsers    = User::get()->all();
        $recentUsers = collect(
            array_slice(MergeSort::sortBy($allUsers, 'created_at', 'desc'), 0, 6)
        );

        return view('dashboard', compact(
            'stats', 'thisMonth', 'totalCollected',
            'recentMembers', 'recentPayments', 'recentUsers'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'gender'    => 'nullable|in:Male,Female,Other',
            'address'   => 'nullable|string|max:500',
            'photo'     => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('profiles', 'public');
        }

        $user->name      = $request->name;
        $user->phone     = $request->phone;
        $user->birthdate = $request->birthdate;
        $user->gender    = $request->gender;
        $user->address   = $request->address;
        $user->save();

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully!');
    }
}