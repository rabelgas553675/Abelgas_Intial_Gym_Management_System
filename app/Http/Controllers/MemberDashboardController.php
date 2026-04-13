<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MemberDashboardController extends Controller
{
    // Get the authenticated member's profile (private helper)
    private function getMember()
    {
        return auth()->user()->memberProfile;
    }

    // Show the member's own dashboard
    public function index()
    {
        $user   = auth()->user();
        $member = $this->getMember();

        $payments = $member
            ? Payment::where('member_id', $member->id)->latest()->get()
            : collect();

        $nearDue = $member && $member->isDueWithinDays(7);

        return view('member.dashboard', compact('user', 'member', 'payments', 'nearDue'));
    }

    // Show profile edit form
    public function editProfile()
    {
        $user        = auth()->user();
        $instructors = User::where('role', 'instructor')->get();
        $member      = $this->getMember();

        return view('member.profile', compact('user', 'member', 'instructors'));
    }

    // Save profile changes
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

        // Keep member profile name in sync
        $member = $this->getMember();
        if ($member) {
            $member->update(['name' => $request->name, 'phone' => $request->phone]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    // Show plan selection form
    public function selectPlan()
    {
        $user        = auth()->user();
        $instructors = User::where('role', 'instructor')->get();
        $member      = $this->getMember();

        return view('member.select-plan', compact('user', 'member', 'instructors'));
    }

    // Save plan selection and process payment
    public function subscribePlan(Request $request)
    {
        $request->validate([
            'fitness_plan'    => 'required|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type' => 'required|in:Monthly,Quarterly,Annually',
            'instructor_id'   => 'nullable|exists:users,id',
        ]);

        $user  = auth()->user();
        $fees  = ['Monthly' => 800, 'Quarterly' => 2100, 'Annually' => 7500];
        $fee   = $fees[$request->membership_type];
        $start = now();
        $end   = match($request->membership_type) {
            'Monthly'   => $start->copy()->addMonth(),
            'Quarterly' => $start->copy()->addMonths(3),
            'Annually'  => $start->copy()->addYear(),
        };

        // Find existing member by user_id OR email — never insert a duplicate
        $member = Member::where('user_id', $user->id)
                        ->orWhere('email', $user->email)
                        ->first();

        if ($member) {
            $member->update([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'instructor_id'   => $request->instructor_id,
                'fitness_plan'    => $request->fitness_plan,
                'membership_type' => $request->membership_type,
                'start_date'      => $start->toDateString(),
                'end_date'        => $end->toDateString(),
                'fee'             => $fee,
                'status'          => 'Active',
            ]);
        } else {
            $member = Member::create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'instructor_id'   => $request->instructor_id,
                'fitness_plan'    => $request->fitness_plan,
                'membership_type' => $request->membership_type,
                'start_date'      => $start->toDateString(),
                'end_date'        => $end->toDateString(),
                'fee'             => $fee,
                'status'          => 'Active',
            ]);
        }

        // Record payment
        $payment = Payment::create([
            'member_id'       => $member->id,
            'receipt_number'  => Payment::generateReceiptNumber(),
            'fitness_plan'    => $request->fitness_plan,
            'membership_type' => $request->membership_type,
            'amount'          => $fee,
            'payment_date'    => now()->toDateString(),
            'method'          => 'Cash',
            'status'          => 'Paid',
        ]);

        return redirect()->route('member.receipt', $payment)
            ->with('success', 'Subscription activated!');
    }

    // Update subscription details only — no payment created
    public function updateSubscription(Request $request)
    {
        $request->validate([
            'fitness_plan'    => 'required|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type' => 'required|in:Monthly,Quarterly,Annually',
            'instructor_id'   => 'nullable|exists:users,id',
        ]);

        $user   = auth()->user();
        $member = Member::where('user_id', $user->id)
                        ->orWhere('email', $user->email)
                        ->first();

        if (!$member) {
            return back()->with('error', 'No subscription found to update.');
        }

        $member->update([
            'fitness_plan'    => $request->fitness_plan,
            'membership_type' => $request->membership_type,
            'instructor_id'   => $request->instructor_id,
        ]);

        return back()->with('success', 'Subscription updated successfully!');
    }

    // Show receipt — locked to this member's own payments only
    public function receipt(Payment $payment)
    {
        $member = $this->getMember();

        // Prevent members from viewing other members' receipts
        if (!$member || $payment->member_id !== $member->id) {
            abort(403, 'You are not allowed to view this receipt.');
        }

        return view('member.receipt', compact('payment', 'member'));
    }

    // Show payment history — only this member's own payments
    public function paymentHistory()
    {
        $user   = auth()->user();
        $member = $this->getMember();

        // Strictly scoped to this member's own payments
        $payments = $member
            ? Payment::where('member_id', $member->id)->latest()->get()
            : collect();

        return view('member.payment-history', compact('payments', 'member'));
    }
}