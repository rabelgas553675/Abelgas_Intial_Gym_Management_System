<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Models\CoachRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MemberDashboardController extends Controller
{
    /**
     * Get the authenticated member's profile (private helper).
     */
    private function getMember(): ?Member
    {
        return auth()->user()?->memberProfile;
    }

    /**
     * Show the member's own dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user   = auth()->user();
        $member = $this->getMember();

        $payments = collect();
        if ($member) {
            $payments = Payment::query()
                               ->where('member_id', $member->id)
                               ->where('payment_type', 'gym_fee')
                               ->latest()
                               ->get();
        }

        $nearDue = $member && $member->isDueWithinDays(7);

        return view('member.dashboard', compact('user', 'member', 'payments', 'nearDue'));
    }

    /**
     * Show the "waiting for coach approval" holding page.
     */
    public function waiting()
    {
        $member = $this->getMember();

        if (!$member) {
            return redirect()->route('member.select-plan');
        }

        // If already approved or no coach involved, go straight to dashboard
        if (in_array($member->coach_status, ['approved', 'none', null])) {
            return redirect()->route('member.dashboard');
        }

        return view('member.waiting', compact('member'));
    }

    /**
     * AJAX polling endpoint for the waiting page.
     */
    public function coachStatus()
    {
        $member = $this->getMember();

        if (!$member) {
            return response()->json([
                'coach_status' => 'none',
                'redirect'     => route('member.select-plan'),
            ]);
        }

        $redirect = null;
        if (in_array($member->coach_status, ['approved', 'none', null])) {
            $redirect = route('member.dashboard');
        }

        return response()->json([
            'coach_status' => $member->coach_status,
            'redirect'     => $redirect,
        ]);
    }

    /**
     * Save plan selection and process payment.
     */
    public function subscribePlan(Request $request)
    {
        $request->validate([
            'fitness_plan'          => 'required|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type'       => 'required|in:Monthly,Quarterly,Annually',
            'instructor_id'         => 'nullable|exists:users,id',
            'coach_membership_type' => 'nullable|in:Monthly,Quarterly,Annually',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user) {
            return back()->with('error', 'Unauthenticated. Please log in again.');
        }

        // Pricing maps
        $gymPriceMap   = ['Monthly' => 800,  'Quarterly' => 3200, 'Annually' => 9600];
        $coachPriceMap = ['Monthly' => 300,  'Quarterly' => 1200, 'Annually' => 3600];

        $gymAmount   = $gymPriceMap[$request->membership_type] ?? 0;
        $coachAmount = $request->filled('instructor_id')
                        ? ($coachPriceMap[$request->coach_membership_type] ?? 0)
                        : 0;

        // Date calculation
        $start = Carbon::now();
        $end   = match ($request->membership_type) {
            'Monthly'   => $start->copy()->addMonth(),
            'Quarterly' => $start->copy()->addMonths(3),
            'Annually'  => $start->copy()->addYear(),
        };

        DB::beginTransaction();
        try {
            // Find or create the member record
            $member = Member::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name'                  => $user->name,
                    'email'                 => $user->email,
                    'phone'                 => $user->phone,
                    'fitness_plan'          => $request->fitness_plan,
                    'membership_type'       => $request->membership_type,
                    'instructor_id'         => null, // stays null until coach approved
                    'coach_membership_type' => $request->filled('instructor_id') ? $request->coach_membership_type : null,
                    'coach_status'          => $request->filled('instructor_id') ? 'pending' : 'none',
                    'start_date'            => $start,
                    'end_date'              => $end,
                    'fee'                   => $gymAmount,
                    'status'                => 'Active',
                ]
            );

            // Generate/Update QR Code
            Member::generateQrCode($member);

            // Handle CoachRequest logic
            if ($request->filled('instructor_id')) {
                // Reject existing pending requests
                CoachRequest::query()
                            ->where('member_id', $member->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'rejected']);

                CoachRequest::create([
                    'member_id'     => $member->id,
                    'instructor_id' => $request->instructor_id,
                    'status'        => 'pending',
                    'message'       => 'New subscription request',
                ]);
            }

            // Record gym_fee payment
            $gymPayment = Payment::create([
                'member_id'       => $member->id,
                'payment_type'    => 'gym_fee',
                'receipt_number'  => 'RCP-' . strtoupper(Str::random(12)),
                'amount'          => $gymAmount,
                'fitness_plan'    => $request->fitness_plan,
                'membership_type' => $request->membership_type,
                'payment_date'    => Carbon::now(),
                'status'          => 'Paid',
                'method'          => 'Cash',
                'notes'           => 'Gym membership fee',
            ]);

            // Record coach_fee payment (if applicable)
            if ($request->filled('instructor_id') && $coachAmount > 0) {
                Payment::create([
                    'member_id'       => $member->id,
                    'instructor_id'   => $request->instructor_id,
                    'payment_type'    => 'coach_fee',
                    'receipt_number'  => 'RCP-' . strtoupper(Str::random(12)),
                    'amount'          => $coachAmount,
                    'fitness_plan'    => $request->fitness_plan,
                    'membership_type' => $request->coach_membership_type,
                    'payment_date'    => Carbon::now(),
                    'status'          => 'Paid',
                    'method'          => 'Cash',
                    'notes'           => 'Coach subscription fee',
                ]);
            }

            DB::commit();

            if ($request->filled('instructor_id')) {
                return redirect()->route('member.waiting')
                                 ->with('success', 'Subscription submitted! Waiting for coach approval.');
            }

            return redirect()->route('member.receipt', $gymPayment->id)
                             ->with('success', 'Subscription processed!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Show profile edit form.
     */
    public function editProfile()
    {
        $user        = auth()->user();
        $instructors = User::query()->where('role', 'instructor')->get();
        $member      = $this->getMember();

        return view('member.profile', compact('user', 'member', 'instructors'));
    }

    /**
     * Save profile changes.
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
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
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
     * Show plan selection form.
     */
    public function selectPlan()
    {
        $user        = auth()->user();
        $instructors = User::query()->where('role', 'instructor')->get();
        $member      = $this->getMember();

        return view('member.select-plan', compact('user', 'member', 'instructors'));
    }

    /**
     * Update subscription details only — no payment created.
     */
    public function updateSubscription(Request $request)
    {
        $request->validate([
            'fitness_plan'    => 'required|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type' => 'required|in:Monthly,Quarterly,Annually',
            'instructor_id'   => 'nullable|exists:users,id',
        ]);

        $member = $this->getMember();

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

    /**
     * Show receipt — locked to this member's own gym_fee payments only.
     */
    public function receipt(Payment $payment)
    {
        $member = $this->getMember();

        if (!$member || $payment->member_id !== $member->id) {
            abort(403, 'You are not allowed to view this receipt.');
        }

        $coachPayment = Payment::query()
            ->where('member_id', $member->id)
            ->where('payment_type', 'coach_fee')
            ->whereDate('payment_date', '=', $payment->payment_date)
            ->latest()
            ->first();

        $payment->coach_fee_amount  = $coachPayment ? $coachPayment->amount : 0;
        $payment->coach_fee_payment = $coachPayment;

        return view('member.receipt', compact('payment', 'member'));
    }

    /**
     * Show payment history.
     */
    public function paymentHistory()
    {
        $member = $this->getMember();

        if (!$member) {
            return view('member.payment-history', ['payments' => collect(), 'member' => null]);
        }

        $gymPayments = Payment::query()
            ->where('member_id', $member->id)
            ->where('payment_type', 'gym_fee')
            ->latest()
            ->get();

        $coachPayments = Payment::query()
            ->where('member_id', $member->id)
            ->where('payment_type', 'coach_fee')
            ->get();

        $gymPayments->each(function ($gymPayment) use ($coachPayments) {
            $match = $coachPayments
                ->filter(fn ($cp) =>
                    Carbon::parse($cp->payment_date)->isSameDay($gymPayment->payment_date)
                )
                ->first();

            $gymPayment->coach_fee_amount  = $match ? $match->amount : 0;
            $gymPayment->coach_fee_payment = $match;
        });

        return view('member.payment-history', ['payments' => $gymPayments, 'member' => $member]);
    }
}