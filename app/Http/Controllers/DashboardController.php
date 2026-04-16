<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
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

        $recentMembers  = Member::latest()->take(8)->get();
        $recentPayments = Payment::with('member')->latest('payment_date')->take(6)->get();
        $recentUsers    = User::latest()->take(6)->get();

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

        // Handle photo upload
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