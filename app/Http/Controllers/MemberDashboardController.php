<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CoachRequest;
use App\Services\Algorithms\GreedyScheduler;
use App\Services\Algorithms\MergeSort;
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
     *
     * DSA integration:
     *   - MergeSort::sortBy() replaces ->latest()
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user   = auth()->user();
        $member = $this->getMember();

        $payments = collect();
        if ($member) {
            // Load every payment type for the member so the dashboard reflects the
            // same payment history as the dedicated payments page.
            $rawPayments = Payment::query()
                                  ->where('member_id', $member->id)
                                  ->get()
                                  ->all();

            // MergeSort replaces ->latest()
            $sorted   = MergeSort::sortBy($rawPayments, 'payment_date', 'desc');
            $payments = collect($sorted);
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

        // Pending or rejected coach requests should return to the member home page
        // instead of leaving them stuck on the waiting screen.
        if (in_array($member->coach_status, ['approved', 'none', null, 'pending', 'rejected'])) {
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
     *
     * DSA integration:
     *   - GreedyScheduler::computeGymFee()    replaces inline $gymPriceMap array
     *   - GreedyScheduler::computeCoachFee()  replaces inline $coachPriceMap array
     *   - GreedyScheduler::computeEndDate()   replaces Carbon match() block
     */
    public function subscribePlan(Request $request)
    {
        $request->validate([
            'fitness_plan'          => 'required|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type'       => 'required|in:Monthly,Quarterly,Semi-Annual,Annually',
            'instructor_id'         => 'nullable|exists:users,id',
            'coach_membership_type' => 'nullable|in:Monthly,Quarterly,Semi-Annual,Annually',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user) {
            return back()->with('error', 'Unauthenticated. Please log in again.');
        }

        $existingMember = Member::where('user_id', $user->id)->first();

        // ── GreedyScheduler: compute fees ────────────────────────────────────
        $coachPlan   = $request->filled('instructor_id') ? $request->coach_membership_type : null;
        $instructorId = $request->filled('instructor_id') ? (int) $request->instructor_id : null;
        $gymAmount   = GreedyScheduler::computeGymFee($request->membership_type);
        $coachAmount = GreedyScheduler::computeCoachFee($coachPlan, $instructorId);

        // Accumulate renewals: extend the active end date when the member already
        // has a future plan instead of resetting the subscription from today.
        $start = $existingMember && $existingMember->end_date && $existingMember->end_date->isFuture()
            ? $existingMember->end_date->copy()
            : Carbon::now();
        $end = GreedyScheduler::computeEndDate($start, $request->membership_type);

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
                    'coach_membership_type' => $coachPlan,
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
                return redirect()->route('member.dashboard')
                                 ->with('success', 'Subscription submitted successfully.');
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
            'membership_type' => 'required|in:Monthly,Quarterly,Semi-Annual,Annually',
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
     *
     * DSA integration:
     *   - MergeSort::sortBy() replaces ->latest() on both gym and coach payments
     */
    public function paymentHistory()
    {
        $member = $this->getMember();

        if (!$member) {
            return view('member.payment-history', ['payments' => collect(), 'member' => null]);
        }

        // Use a single source of truth for all member payments so the history always
        // matches every gym and coach transaction, including advance/manual entries.
        $payments = Payment::forMember($member)->get();
        $payments->each(function ($payment) use ($member) {
            $payment->coach_fee_amount = 0;
            $payment->gym_fee_amount = 0;

            if ($payment->payment_type === 'gym_fee') {
                $payment->gym_fee_amount = $payment->amount;

                $matchingCoach = Payment::query()
                    ->where('member_id', $member->id)
                    ->where('payment_type', 'coach_fee')
                    ->whereDate('payment_date', $payment->payment_date)
                    ->latest('id')
                    ->first();

                $payment->coach_fee_amount = $matchingCoach ? $matchingCoach->amount : 0;
                $payment->coach_fee_payment = $matchingCoach;
            } elseif ($payment->payment_type === 'coach_fee') {
                $payment->coach_fee_amount = $payment->amount;
            }
        });

        return view('member.payment-history', ['payments' => $payments, 'member' => $member]);
    }

    /**
     * Show the member's subscription history.
     */
    public function subscriptionHistory()
    {
        $member = $this->getMember();

        if (!$member) {
            return view('member.subscription-history', ['payments' => collect(), 'member' => null]);
        }

        $payments = Payment::forMember($member)->get();

        return view('member.subscription-history', ['payments' => $payments, 'member' => $member]);
    }

    /**
     * Show the member's attendance history.
     */
    public function attendanceHistory()
    {
        $member = $this->getMember();

        if (!$member) {
            return view('member.attendance-history', ['attendance' => collect(), 'member' => null]);
        }

        $attendance = Attendance::query()
            ->where('member_id', $member->id)
            ->orderByDesc('date')
            ->orderByDesc('time_in')
            ->get();

        return view('member.attendance-history', ['attendance' => $attendance, 'member' => $member]);
    }
}