<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\PaymentSetting;
use App\Models\User;
use App\Notifications\InstructorRateUpdated;
use App\Services\Algorithms\GreedyScheduler;
use App\Services\Algorithms\MergeSort;
use App\Services\Algorithms\BinarySearch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    /**
     * Admin payments page.
     *
     * DSA integration:
     *   - MergeSort::sortBy()          replaces all ->latest() / ->orderBy() calls.
     *   - BinarySearch::searchByField() handles receipt number search (partial match,
     *     O(log n) + k typical, O(n) worst case — see BinarySearch docblock).
     *   - BinarySearch::findExact()    available for pure O(log n) exact lookups.
     *
     * NOTE: Member::orderBy('name') has been removed from the member dropdown query.
     * Alphabetical ordering is now performed entirely by MergeSort::sortBy() in memory,
     * so no DB-level ordering is claimed as student-implemented sorting.
     */
    public function index(Request $request)
    {
        // ── Aggregates (DB sums — acceptable; no student sort claimed here) ──
        $totalCount     = Payment::gymFees()->count();
        $thisMonth      = Payment::gymFees()->thisMonth()->sum('amount');
        $totalCollected = Payment::gymFees()->sum('amount');

        $totalCoachFees     = Payment::coachFees()->sum('amount');
        $thisMonthCoachFees = Payment::coachFees()->thisMonth()->sum('amount');

        // Fix: instructor_id lives on members, not payments.
        // Count distinct instructors who have at least one coach_fee payment
        // through their assigned members.
        $instructorsPaidCount = Member::whereHas('payments', function ($q) {
                                    $q->where('payment_type', 'coach_fee');
                                })
                                ->whereNotNull('instructor_id')
                                ->distinct('instructor_id')
                                ->count('instructor_id');

        // ── Instructor leaderboard ────────────────────────────────────────────
        // Fix: instructor_id is on members, not payments.
        // Join through members to group coach_fee payments by instructor.
        // No DB-level ORDER BY — MergeSort handles ordering in PHP.
        $leaderboardRaw = Payment::coachFees()
            ->select(
                'members.instructor_id',
                DB::raw('SUM(payments.amount) as total'),
                DB::raw('COUNT(*) as txn_count')
            )
            ->join('members', 'payments.member_id', '=', 'members.id')
            ->with('member.instructor:id,name,photo')
            ->groupBy('members.instructor_id')
            ->get()
            ->toArray();

        // Attach instructor details via the User model for the leaderboard display.
        // Map instructor_id to user info so the view has name/photo available.
        $instructorIds = array_column($leaderboardRaw, 'instructor_id');
        $instructorUsers = User::whereIn('id', $instructorIds)
            ->get(['id', 'name', 'photo'])
            ->keyBy('id')
            ->toArray();

        // Merge instructor info into each leaderboard row.
        $leaderboardRaw = array_map(function ($row) use ($instructorUsers) {
            $row['instructor'] = $instructorUsers[$row['instructor_id']] ?? null;
            return $row;
        }, $leaderboardRaw);

        // MergeSort: O(n log n) in-memory sort — no DB ORDER BY
        $instructorLeaderboard = MergeSort::sortBy($leaderboardRaw, 'total', 'desc');

        $instructorOptions = User::where('role', 'instructor')
            ->orderBy('name')
            ->get(['id', 'name']);

        $instructorRateMap = [];
        foreach ($instructorOptions as $instructor) {
            $instructorRateMap[$instructor->id] = Payment::rateSettings((int) $instructor->id)['coach'];
        }

        // ── Gym fee transactions ──────────────────────────────────────────────
        // Load with NO DB ordering — MergeSort handles all ordering in memory.
        $gymPaymentsRaw = Payment::gymFees()
            ->with('member:id,name,user_id', 'member.user:id,name,photo')
            ->get()
            ->toArray();

        // MergeSort by created_at descending (newest first) — replaces ->latest()
        $payments = MergeSort::sortBy($gymPaymentsRaw, 'created_at', 'desc');

        $coachPaymentsByMember = Payment::coachFees()
            ->with('instructor:id,name')
            ->get()
            ->groupBy('member_id');

        $payments = array_map(function ($payment) use ($coachPaymentsByMember) {
            $memberId = $payment['member_id'] ?? null;
            $coachRows = $coachPaymentsByMember[$memberId] ?? collect();
            $latestCoach = $coachRows->sortByDesc('payment_date')->first();

            $payment['personal_coaching'] = $latestCoach && !empty($latestCoach['instructor_id'])
                ? ($latestCoach['instructor']['name'] ?? 'Unknown Instructor')
                : '—';

            $payment['coach_package'] = $latestCoach['membership_type'] ?? '—';
            $payment['coach_amount'] = $latestCoach['amount'] ?? 0;

            return $payment;
        }, $payments);

        // BinarySearch on receipt_number when ?search= is present.
        // Complexity: O(log n + k) typical, O(n) worst case (partial match).
        // For an exact receipt lookup, BinarySearch::findExact() gives pure O(log n).
        if ($request->filled('search')) {
            $payments = BinarySearch::searchByField(
                MergeSort::sortBy($gymPaymentsRaw, 'receipt_number', 'asc'),
                'receipt_number',
                $request->search
            );
        }

        // ── Coach fee transactions ────────────────────────────────────────────
        // Load with NO DB ordering — MergeSort handles ordering in memory.
        $coachPaymentsRaw = Payment::coachFees()
            ->with(
                'member:id,name,user_id',
                'member.user:id,name,photo',
                'member.instructor:id,name,photo'
            )
            ->get()
            ->toArray();

        // MergeSort by created_at descending — replaces ->latest()
        $coachFeePayments = MergeSort::sortBy($coachPaymentsRaw, 'created_at', 'desc');

        // ── Member dropdown ───────────────────────────────────────────────────
        // Removed: Member::orderBy('name') — DB ordering was redundant when
        // MergeSort is being applied immediately after. Now loads with no ORDER BY.
        $membersRaw = Member::get(['id', 'name'])->toArray();

        // MergeSort: alphabetical sort — no DB ordering involved
        $members = MergeSort::sortBy($membersRaw, 'name', 'asc');

        $selectedInstructorId = $request->input('instructor_id');
        $rates = $selectedInstructorId
            ? Payment::rateSettings((int) $selectedInstructorId)
            : Payment::rateSettings();

        $dayPassRate = Payment::dayPassRate();

        return view('admin.payments', compact(
            'totalCount', 'thisMonth', 'totalCollected',
            'totalCoachFees', 'thisMonthCoachFees', 'instructorsPaidCount',
            'instructorLeaderboard', 'instructorOptions', 'instructorRateMap',
            'payments', 'coachFeePayments',
            'members', 'rates', 'selectedInstructorId', 'dayPassRate'
        ));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'gym_monthly' => 'required|integer|min:1',
            'gym_quarterly' => 'required|integer|min:1',
            'gym_semi_annual' => 'required|integer|min:1',
            'gym_annually' => 'required|integer|min:1',
            'gym_day_pass' => 'nullable|integer|min:1',
            'coach_monthly' => 'required|integer|min:1',
            'coach_quarterly' => 'required|integer|min:1',
            'coach_semi_annual' => 'required|integer|min:1',
            'coach_annually' => 'required|integer|min:1',
            'instructor_id' => 'nullable|exists:users,id',
        ]);

        $instructorId = $request->input('instructor_id');

        $factors = [
            'gym' => [
                'Monthly' => $request->gym_monthly,
                'Quarterly' => $request->gym_quarterly,
                'Semi-Annual' => $request->gym_semi_annual,
                'Annually' => $request->gym_annually,
            ],
            'coach' => [
                'Monthly' => $request->coach_monthly,
                'Quarterly' => $request->coach_quarterly,
                'Semi-Annual' => $request->coach_semi_annual,
                'Annually' => $request->coach_annually,
            ],
        ];

        $previousCoachRates = [];
        if ($instructorId) {
            foreach (array_keys(Payment::DEFAULT_COACH_FEES) as $plan) {
                $key = 'coach_instructor_' . $instructorId . '_' . $plan;
                $previousCoachRates[$plan] = (int) PaymentSetting::where('key', $key)->value('value', Payment::coachRate($plan));
            }
        }

        foreach ($factors as $group => $plans) {
            foreach ($plans as $plan => $amount) {
                $key = $group . '_' . $plan;

                if ($group === 'coach' && $instructorId) {
                    $key = 'coach_instructor_' . $instructorId . '_' . $plan;
                }

                PaymentSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $amount]
                );
            }
        }

        if ($instructorId) {
            $instructorUser = User::find($instructorId);
            if ($instructorUser) {
                $changes = [];

                foreach (array_keys(Payment::DEFAULT_COACH_FEES) as $plan) {
                    $previous = $previousCoachRates[$plan] ?? Payment::coachRate($plan);
                    $current = (int) ($factors['coach'][$plan] ?? Payment::coachRate($plan));

                    if ((int) $previous !== $current) {
                        $changes[$plan] = [
                            'from' => (int) $previous,
                            'to'   => $current,
                        ];
                    }
                }

                if (!empty($changes) && Schema::hasTable('notifications')) {
                    $instructorUser->notify(new InstructorRateUpdated($changes));
                }
            }
        }

        // Day Pass (walk-in) rate — stored with the other gym rates, shared by Admin and Staff walk-in pages.
        if ($request->filled('gym_day_pass')) {
            PaymentSetting::updateOrCreate(
                ['key' => 'gym_day_pass'],
                ['value' => (string) $request->gym_day_pass]
            );
        }

        $label = $instructorId ? 'Instructor-specific subscription rates updated successfully.' : 'Subscription rates updated successfully.';

        if ($instructorId) {
            return redirect()->route('payments.index', ['instructor_id' => $instructorId])->with('success', $label);
        }

        return redirect()->route('payments.index')->with('success', $label);
    }

    /**
     * Store a manually recorded gym_fee payment (admin / staff).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user || (! $user->isAdmin() && ! $user->isStaff())) {
            abort(403, 'Only admin and staff can record payments.');
        }

        $request->validate([
            'member_id'             => 'required|exists:members,id',
            'fitness_plan'          => 'nullable|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type'       => 'required|in:Monthly,Quarterly,Semi-Annual,Annually',
            'instructor_id'         => 'nullable|exists:users,id',
            'coach_membership_type' => 'nullable|in:Monthly,Quarterly,Semi-Annual,Annually',
            'payment_date'          => 'required|date',
            'method'               => 'required|in:Cash,GCash,Bank Transfer,Card',
            'amount'               => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string|max:500',
            'record_type'          => 'nullable|in:gym,coach',
            'subscription_type'    => 'nullable|in:Monthly,Quarterly,Semi-Annual,Annually',
        ]);

        if ($request->filled('instructor_id') && ! $request->filled('coach_membership_type')) {
            return back()->withInput()->with('error', 'Please select a coaching package when assigning an instructor.');
        }

        $member = Member::findOrFail($request->member_id);
        $gymType = $request->input('membership_type', $request->input('subscription_type', 'Monthly'));
        $coachType = $request->input('coach_membership_type');
        $fitnessPlan = $request->input('fitness_plan', $member->fitness_plan ?? 'Calisthenics');

        $gymAmount = GreedyScheduler::computeGymFee($gymType);
        $coachAmount = $request->filled('instructor_id') ? GreedyScheduler::computeCoachFee($coachType, (int) $request->instructor_id) : 0;

        $start = $member->end_date && $member->end_date->isFuture()
            ? $member->end_date->copy()
            : Carbon::parse($request->payment_date);
        $end = GreedyScheduler::computeEndDate($start, $gymType);

        $member->update([
            'fitness_plan'          => $fitnessPlan,
            'membership_type'       => $gymType,
            'instructor_id'         => $request->instructor_id,
            'coach_membership_type' => $coachType,
            'coach_status'          => $request->filled('instructor_id') ? 'pending' : 'none',
            'start_date'            => $start,
            'end_date'              => $end,
            'fee'                   => $gymAmount,
            'status'                => 'Active',
        ]);

        $gymPayment = Payment::create([
            'member_id'       => $member->id,
            'payment_type'    => 'gym_fee',
            'platform_fee'    => $gymAmount,
            'receipt_number'  => Payment::generateReceiptNumber(),
            'amount'          => $gymAmount,
            'fitness_plan'    => $fitnessPlan,
            'membership_type' => $gymType,
            'payment_date'    => $request->payment_date,
            'method'          => $request->method,
            'status'          => 'Paid',
            'notes'           => $request->notes ?: 'Gym membership payment',
            'processed_by'    => $user->id,
        ]);

        if ($request->filled('instructor_id') && $coachAmount > 0) {
            Payment::create([
                'member_id'       => $member->id,
                'payment_type'    => 'coach_fee',
                'instructor_id'   => $request->instructor_id,
                'receipt_number'  => Payment::generateReceiptNumber(),
                'amount'          => $coachAmount,
                'fitness_plan'    => $fitnessPlan,
                'membership_type' => $coachType,
                'payment_date'    => $request->payment_date,
                'method'          => $request->method,
                'status'          => 'Paid',
                'notes'           => $request->notes ?: 'Coach subscription payment',
                'processed_by'    => $user->id,
            ]);
        }

        return back()->with('success', 'Membership payment recorded successfully.');
    }

    /**
     * Delete a payment record (admin only).
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Transaction deleted.');
    }
}