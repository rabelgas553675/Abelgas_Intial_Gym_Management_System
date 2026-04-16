<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Payment;
use App\Models\WorkoutPlan;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $instructors = User::where('role', 'instructor')->latest()->get();
        $staff       = User::where('role', 'staff')->latest()->get();
        $members     = User::where('role', 'member')->latest()->get();
        $admins      = User::where('role', 'admin')->latest()->get();

        return view('users.index', compact('instructors', 'staff', 'members', 'admins'));
    }

    public function show(User $user)
    {
        $workoutPlans   = collect();
        $instructorFees = collect();

        if ($user->isInstructor()) {
            // Schedule: workout plans created by this instructor
            $workoutPlans = WorkoutPlan::where('instructor_id', $user->id)
                ->with('member')
                ->orderBy('scheduled_date', 'desc')
                ->get();

            // Fees: coach_fee payments from the payments table linked to this instructor
            $instructorFees = Payment::where('payment_type', 'coach_fee')
                ->where('instructor_id', $user->id)
                ->with('member')
                ->latest('payment_date')
                ->get();
        }

        return view('users.show', compact('user', 'workoutPlans', 'instructorFees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email:rfc|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,staff,instructor,member',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => $request->role,
        ]);

        return back()->with('success', 'User added successfully!');
    }

    public function promoteToAdmin(User $user)
    {
        $user->update(['role' => 'admin']);
        return back()->with('success', "{$user->name} promoted to Admin.");
    }

    public function makeInstructor(User $user)
    {
        $user->update(['role' => 'instructor']);
        return back()->with('success', "{$user->name} is now an Instructor.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}