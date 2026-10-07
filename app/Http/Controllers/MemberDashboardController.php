<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CoachRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Services\Algorithms\GreedyScheduler;
use App\Services\Algorithms\MergeSort;
use App\Services\CoachConfirmationService;
use App\Services\MemberSnapshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberDashboardController extends Controller
{
    /**
     * Get the authenticated member's profile (private helper).
     */
    private function getMember(): ?Member
    {
        return Auth::user()?->memberProfile;
    }

    /**
     * Show the member's own dashboard.
     *
     * Everything about the subscription period, coach and profile comes from
     * MemberSnapshot, the same object the subscription / payment pages use.
     *
     * DSA integration:
     *   - MergeSort::sortBy() replaces ->latest()
     */
    public function index(CoachConfirmationService $coaches)
    {
        /** @var \App\Models\User $user */
        $user     = Auth::user();
        $member   = $this->getMember();
        $payments = collect();
        $snapshot = null;

        if ($member) {
            // A scheduled coach whose start date has arrived becomes the current coach
            // right now — it does not depend on the scheduler having run.
            $coaches->activateDue();
            $member->refresh();

            $snapshot = MemberSnapshot::for($member);

            // Every payment type, same history as the dedicated payments page.
            $rawPayments = Payment::query()
                                  ->where('member_id', $member->id)
                                  ->get()
                                  ->all();

            // MergeSort replaces ->latest()
            $payments = collect(MergeSort::sortBy($rawPayments, 'payment_date', 'desc'));
        }

        return view('member.dashboard', compact('user', 'member', 'payments', 'snapshot'));
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
        $user = Auth::user();

        if (!$user) {
            return back()->with('error', 'Unauthenticated. Please log in again.');
        }

        $existingMember = Member::where('user_id', '=', $user->id, 'and')->first();

        // ── GreedyScheduler: compute fees ────────────────────────────────────
        $coachPlan    = $request->filled('instructor_id') ? $request->coach_membership_type : null;
        $instructorId = $request->filled('instructor_id') ? (int) $request->instructor_id : null;
        $gymAmount    = GreedyScheduler::computeGymFee($request->membership_type);
        $coachAmount  = GreedyScheduler::computeCoachFee($coachPlan, $instructorId);

        $activeCoachIsRunning = $existingMember
            && $existingMember->instructor_id
            && $existingMember->end_date
            && $existingMember->end_date->isFuture();

        $currentCoachStillRunning = $activeCoachIsRunning
            && $request->filled('instructor_id')
            && (int) $existingMember->instructor_id !== (int) $request->instructor_id;

        $shouldKeepCurrentCoach = $activeCoachIsRunning
            && (!$request->filled('instructor_id') || (int) $existingMember->instructor_id === (int) $request->instructor_id);

        // Accumulate renewals: extend the active end date when the member already
        // has a future plan instead of resetting the subscription from today.
        $start = $activeCoachIsRunning || ($existingMember && $existingMember->end_date && $existingMember->end_date->isFuture())
            ? $existingMember->end_date->copy()
            : Carbon::now();
        $end = GreedyScheduler::computeEndDate($start, $request->membership_type);

        $memberInstructorId = $shouldKeepCurrentCoach
            ? $existingMember->instructor_id
            : ($request->filled('instructor_id') ? null : ($existingMember?->instructor_id ?? null));

        $memberCoachStatus = $shouldKeepCurrentCoach
            ? ($existingMember->coach_status ?? 'approved')
            : ($request->filled('instructor_id') ? 'pending' : 'none');

        $memberCoachMembershipType = $shouldKeepCurrentCoach
            ? ($existingMember->coach_membership_type ?? $coachPlan)
            : $coachPlan;

        $scheduledCoachStartsOn = $existingMember && $existingMember->end_date && $existingMember->end_date->isFuture()
            ? $existingMember->end_date->copy()->startOfDay()
            : null;

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
                    'instructor_id'         => $memberInstructorId,
                    'coach_membership_type' => $memberCoachMembershipType,
                    'coach_status'          => $memberCoachStatus,
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
                // If the current active coach is still valid, do not swap them mid-term.
                // Schedule the new coach to take over only after the current term ends.
                if ($currentCoachStillRunning && $scheduledCoachStartsOn) {
                    CoachRequest::supersedePending($member->id);

                    CoachRequest::create([
                        'member_id'             => $member->id,
                        'instructor_id'         => $request->instructor_id,
                        'status'                => 'pending',
                        'message'               => 'Request to replace the current coach after the active term ends.',
                        'coach_membership_type' => $coachPlan,
                        'starts_on'             => $scheduledCoachStartsOn->toDateString(),
                    ]);
                } else {
                    // Supersede any existing pending requests
                    CoachRequest::supersedePending($member->id);

                    CoachRequest::create([
                        'member_id'             => $member->id,
                        'instructor_id'         => $request->instructor_id,
                        'status'                => 'pending',
                        'message'               => 'New subscription request',
                        'coach_membership_type' => $coachPlan,
                    ]);
                }
            }

            $isAdvanceRenewal = $existingMember && $existingMember->end_date && $existingMember->end_date->isFuture();

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
                'notes'           => Payment::paymentNoteFor(null, $request->membership_type, $gymAmount, 'gym_fee', $isAdvanceRenewal) ?: 'Gym membership fee',
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
                    'notes'           => Payment::paymentNoteFor(null, $request->coach_membership_type, $coachAmount, 'coach_fee', $isAdvanceRenewal, (int) $request->instructor_id) ?: 'Coach subscription fee',
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
        $user        = Auth::user();
        $instructors = User::query()->where('role', 'instructor')->get();
        $member      = $this->getMember();
        $snapshot    = $member ? MemberSnapshot::for($member) : null;

        return view('member.profile', compact('user', 'member', 'instructors', 'snapshot'));
    }

    /**
     * Save profile changes.
     *
     * users + members are written in ONE transaction so the dashboard Profile card,
     * the profile page and the admin side can never disagree.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

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

        DB::transaction(function () use ($user, $data, $request) {
            $user->update($data);

            $member = $this->getMember();
            if (!$member) {
                return;
            }

            $memberData = ['name' => $request->name, 'phone' => $request->phone];

            // Member::full_name prefers first_name / last_name, so keep them in step
            // or the admin side would keep showing the old name.
            if ($member->first_name) {
                $parts = preg_split('/\s+/', trim($request->name), 2);
                $memberData['first_name'] = $parts[0];
                $memberData['last_name']  = $parts[1] ?? '';
            }

            if (isset($data['photo'])) {
                $memberData['photo'] = $data['photo'];
            }

            foreach (['gender', 'birthdate', 'address'] as $field) {
                if (array_key_exists($field, $data)) {
                    $memberData[$field] = $data[$field];
                }
            }

            $member->update($memberData);
        });

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Show plan selection form.
     */
    public function selectPlan()
    {
        $user        = Auth::user();
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
            return view('member.payment-history', ['payments' => collect(), 'member' => null, 'snapshot' => null]);
        }

        // Merge gym and coach payments for the same cycle/date so the member sees
        // one row per payment period instead of split rows for each fee type.
        $payments = Payment::forMember($member)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $mergedPayments = $payments
            ->groupBy(function ($payment) {
                $date = $payment->payment_date ? $payment->payment_date->toDateString() : ($payment->created_at ? $payment->created_at->toDateString() : '');

                return ($payment->fitness_plan ?? 'Unknown') . '|' . ($payment->membership_type ?? 'Unknown') . '|' . $date;
            })
            ->map(function ($group) {
                $first = $group->first();
                $gymFee = (float) $group->where('payment_type', 'gym_fee')->sum('amount');
                $coachFee = (float) $group->where('payment_type', 'coach_fee')->sum('amount');
                $total = $gymFee + $coachFee;

                $merged = new \stdClass();
                $merged->id = $first->id;
                $merged->receipt_number = $first->receipt_number;
                $merged->payment_date = $first->payment_date;
                $merged->fitness_plan = $first->fitness_plan;
                $merged->membership_type = $first->membership_type;
                $merged->gym_fee_amount = $gymFee;
                $merged->coach_fee_amount = $coachFee;
                $merged->amount = $total;
                $merged->status = 'Paid';
                $merged->coach_fee_payment = null;
                $merged->payment_type = 'combined';

                return $merged;
            })
            ->sortByDesc('payment_date')
            ->values();

        return view('member.payment-history', [
            'payments' => $mergedPayments,
            'member'   => $member,
            'snapshot' => MemberSnapshot::for($member),
        ]);
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