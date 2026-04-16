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
    /**
     * Get the authenticated member's profile (private helper)
     */
    private function getMember()
    {
        return auth()->user()->memberProfile;
    }

    /**
     * Show the member's own dashboard
     */
    public function index()
    {
        $user   = auth()->user();
        $member = $this->getMember();

        // Only show gym_fee payments in the member's own dashboard/history
        $payments = $member
            ? Payment::where('member_id', $member->id)
                     ->where('payment_type', 'gym_fee')
                     ->latest()
                     ->get()
            : collect();

        $nearDue = $member && $member->isDueWithinDays(7);

        return view('member.dashboard', compact('user', 'member', 'payments', 'nearDue'));
    }

    /**
     * Show profile edit form
     */
    public function editProfile()
    {
        $user        = auth()->user();
        $instructors = User::where('role', 'instructor')->get();
        $member      = Member::where('user_id', auth()->id())->first();

        return view('member.profile', compact('user', 'member', 'instructors'));
    }

    /**
     * Save profile changes
     */
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

        $member = $this->getMember();
        if ($member) {
            $member->update(['name' => $request->name, 'phone' => $request->phone]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Show plan selection form
     */
    public function selectPlan()
    {
        $user        = auth()->user();
        $instructors = User::where('role', 'instructor')->get();
        $member      = $this->getMember();

        return view('member.select-plan', compact('user', 'member', 'instructors'));
    }

    /**
     * Save plan selection and process payment.
     *
     * Payment split logic:
     *  - Gym membership fee  → payment_type = 'gym_fee'  (appears in admin/staff payments)
     *  - Coach/instructor fee → payment_type = 'coach_fee' (appears in instructor's payment history only)
     */
    public function subscribePlan(Request $request)
    {
        $request->validate([
            'fitness_plan'    => 'required|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type' => 'required|in:Monthly,Quarterly,Annually',
            'instructor_id'   => 'nullable|exists:users,id',
        ]);

        $user = auth()->user();

        // ── Gym membership fees (fixed) ───────────────────────────────────────
        $gymFees = [
            'Monthly'   => 800,
            'Quarterly' => 2100,
            'Annually'  => 7500,
        ];

        // ── Coach/instructor subscription fees (separate) ─────────────────────
        $coachFees = [
            'Monthly'   => 300,
            'Quarterly' => 1200,
            'Annually'  => 3600,
        ];

        $gymFee   = $gymFees[$request->membership_type];
        $coachFee = $request->instructor_id ? $coachFees[$request->membership_type] : 0;

        $start = now();
        $end   = match($request->membership_type) {
            'Monthly'   => $start->copy()->addMonth(),
            'Quarterly' => $start->copy()->addMonths(3),
            'Annually'  => $start->copy()->addYear(),
        };

        // ── Find or create member record ──────────────────────────────────────
        $member = Member::where('user_id', $user->id)
                        ->orWhere('email', $user->email)
                        ->first();

        $memberData = [
            'user_id'         => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'instructor_id'   => $request->instructor_id,
            'fitness_plan'    => $request->fitness_plan,
            'membership_type' => $request->membership_type,
            'start_date'      => $start->toDateString(),
            'end_date'        => $end->toDateString(),
            'fee'             => $gymFee,   // member record stores only the gym fee
            'status'          => 'Active',
        ];

        if ($member) {
            $member->update($memberData);
        } else {
            $member = Member::create($memberData);
        }

        // Generate / refresh QR code
        Member::generateQrCode($member);

        // ── Record GYM FEE payment (visible to admin & staff) ─────────────────
        $gymPayment = Payment::create([
            'member_id'      => $member->id,
            'instructor_id'  => null,               // not linked to instructor
            'payment_type'   => 'gym_fee',
            'receipt_number' => Payment::generateReceiptNumber(),
            'fitness_plan'   => $request->fitness_plan,
            'membership_type'=> $request->membership_type,
            'amount'         => $gymFee,
            'payment_date'   => now()->toDateString(),
            'method'         => 'Cash',
            'status'         => 'Paid',
            'notes'          => 'Gym membership fee',
        ]);

        // ── Record COACH FEE payment (visible to instructor only) ─────────────
        if ($request->instructor_id && $coachFee > 0) {
            Payment::create([
                'member_id'      => $member->id,
                'instructor_id'  => $request->instructor_id,  // links to instructor
                'payment_type'   => 'coach_fee',
                'receipt_number' => Payment::generateReceiptNumber(),
                'fitness_plan'   => $request->fitness_plan,
                'membership_type'=> $request->membership_type,
                'amount'         => $coachFee,
                'payment_date'   => now()->toDateString(),
                'method'         => 'Cash',
                'status'         => 'Paid',
                'notes'          => 'Coach subscription fee for ' . optional(User::find($request->instructor_id))->name,
            ]);
        }

        // Redirect to receipt for the gym fee payment
        return redirect()->route('member.receipt', $gymPayment)
            ->with('success', 'Subscription activated!');
    }

    /**
     * Update subscription details only — no payment created
     */
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
            'fitness_plan'  => $request->fitness_plan,
            'membership_type'=> $request->membership_type,
            'instructor_id' => $request->instructor_id,
        ]);

        return back()->with('success', 'Subscription updated successfully!');
    }

    /**
     * Show receipt — locked to this member's own gym_fee payments only
     */
    public function receipt(Payment $payment)
    {
        $member = $this->getMember();

        if (!$member || $payment->member_id !== $member->id) {
            abort(403, 'You are not allowed to view this receipt.');
        }

        return view('member.receipt', compact('payment', 'member'));
    }

    /**
     * Show payment history — only gym_fee payments for this member
     * (coach_fee payments are handled by the instructor portal)
     */
    public function paymentHistory()
    {
        $user   = auth()->user();
        $member = $this->getMember();

        $payments = $member
            ? Payment::where('member_id', $member->id)
                     ->where('payment_type', 'gym_fee')
                     ->latest()
                     ->get()
            : collect();

        return view('member.payment-history', compact('payments', 'member'));
    }
}