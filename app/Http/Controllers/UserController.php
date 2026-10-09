<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Payment;
use App\Models\WorkoutPlan;
use App\Models\UserQrToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'email'    => 'required|email:rfc|unique:users|unique:members,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,staff,instructor,member',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
                'role'     => $request->role,
            ]);

            // Generate a QR token for any role that needs one to scan into attendance
            if (in_array($user->role, ['admin', 'staff', 'instructor'])) {
                UserQrToken::createForUser($user);
            }

            // Members also need a profile row, otherwise they never appear on the
            // Members page and can't subscribe to a plan.
            if ($user->role === 'member') {
                [$first, $last] = array_pad(explode(' ', trim($user->name), 2), 2, null);

                $member = Member::create([
                    'user_id'         => $user->id,
                    'name'            => $user->name,
                    'first_name'      => $first,
                    'last_name'       => $last,
                    'email'           => $user->email,
                    'membership_type' => null,
                    'start_date'      => null,
                    'end_date'        => null,
                    'fee'             => 0,
                    'status'          => 'Pending',
                    'instructor_id'   => null,
                    'coach_status'    => 'none',
                ]);

                Member::generateQrCode($member);
            }
        });

        return back()->with('success', 'User added successfully!');
    }

    public function promoteToAdmin(User $user)
    {
        $user->update(['role' => 'admin']);
        UserQrToken::createForUser($user);
        return back()->with('success', "{$user->name} promoted to Admin.");
    }

    public function makeInstructor(User $user)
    {
        $user->update(['role' => 'instructor']);
        UserQrToken::createForUser($user);
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