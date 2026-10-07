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
use App\Services\CoachConfirmationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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
        $totalCount     = Payment::gymFees()->count('*');
        $thisMonth      = Payment::gymFees()->thisMonth()->sum('amount');
        $totalCollected = Payment::gymFees()->sum('amount');

        $totalCoachFees     = Payment::coachFees()->sum('amount');
        $thisMonthCoachFees = Payment::coachFees()->thisMonth()->sum('amount');

        // Single source of truth for "which instructor was paid": payments.instructor_id,
        // falling back to the member's assigned instructor only for legacy rows where it is NULL.
        // The Coach Fee Transactions table, this breakdown and the details page all use it.
        $instructorExpr = 'COALESCE(payments.instructor_id, members.instructor_id)';

        $instructorsPaidCount = Payment::coachFees()
            ->join('members', 'payments.member_id', '=', 'members.id', 'inner', false)
            ->whereRaw("$instructorExpr IS NOT NULL")
            ->distinct()
            ->count(DB::raw($instructorExpr));

        // ── Instructor leaderboard ────────────────────────────────────────────
        // No DB-level ORDER BY — MergeSort handles ordering in PHP.
        $leaderboardRaw = Payment::coachFees()
            ->join('members', 'payments.member_id', '=', 'members.id', 'inner', false)
            ->whereRaw("$instructorExpr IS NOT NULL")
            ->select(
                DB::raw("$instructorExpr as instructor_id"),
                DB::raw('SUM(payments.amount) as total'),
                DB::raw('COUNT(*) as txn_count')
            )
            ->groupBy(DB::raw($instructorExpr))
            ->get()
            ->toArray();

        $instructorIds = array_column($leaderboardRaw, 'instructor_id');
        $instructorUsers = User::whereIn('id', $instructorIds, 'and', false)
            ->get(['id', 'name', 'photo'])
            ->keyBy('id')
            ->toArray();

        $leaderboardRaw = array_map(function ($row) use ($instructorUsers) {
            $row['instructor'] = $instructorUsers[$row['instructor_id']] ?? null;
            return $row;
        }, $leaderboardRaw);

        // MergeSort: O(n log n) in-memory sort — no DB ORDER BY
        $instructorLeaderboard = MergeSort::sortBy($leaderboardRaw, 'total', 'desc');

        // MergeSort: alphabetical sort in memory — no DB-level ORDER BY.
        $instructorOptions = User::where('role', '=', 'instructor', 'and')
            ->get(['id', 'name'])
            ->all();
        $instructorOptions = MergeSort::sortBy($instructorOptions, 'name', 'asc');

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
                'member:id,name,user_id,instructor_id',
                'member.user:id,name,photo',
                'member.instructor:id,name,photo',
                'instructor:id,name,photo'   // the instructor actually paid on this transaction (payments.instructor_id)
            )
            ->get()
            ->map(function ($payment) {
                $row = $payment->toArray();
                // Prefer the instructor stored on the payment; fall back to the member's
                // currently assigned instructor for legacy rows with a NULL instructor_id.
                $row['instructor'] = $row['instructor'] ?? ($row['member']['instructor'] ?? null);
                return $row;
            })
            ->all();

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

    /**
     * Instructor Earnings details — every coach_fee payment credited to one instructor.
     * Uses the same instructor resolution as the Instructor Earnings Breakdown, so the
     * count and total here always equal the clicked row.
     */
    public function instructorEarnings(User $instructor)
    {
        $payments = Payment::coachFees()
            ->join('members', 'payments.member_id', '=', 'members.id', 'inner', false)
            ->whereRaw('COALESCE(payments.instructor_id, members.instructor_id) = ?', [$instructor->id])
            ->with('member:id,name')
            ->get(['payments.*']);

        $rows = $payments->map(fn ($p) => [
            'member_name'    => $p->member?->name ?? 'Deleted member',
            'receipt_number' => $p->receipt_number,
            'amount'         => (float) $p->amount,
            'payment_date'   => $p->payment_date?->format('Y-m-d'),
            'created_at'     => $p->created_at?->format('Y-m-d H:i:s'),
            'fitness_plan'   => $p->fitness_plan,
            'membership_type'=> $p->membership_type,
        ])->all();

        // MergeSort: newest first — no DB ORDER BY
        $transactions = MergeSort::sortBy($rows, 'created_at', 'desc');
        $totalEarned  = array_sum(array_column($transactions, 'amount'));
        $txnCount     = count($transactions);

        return view('admin.instructor-earnings', compact('instructor', 'transactions', 'totalEarned', 'txnCount'));
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
                $previousCoachRates[$plan] = (int) (PaymentSetting::where('key', $key)->value('value') ?? Payment::coachRate($plan));
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
            $instructorUser = User::query()->where('id', $instructorId)->first();
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
     * Store a manually recorded membership payment (admin / staff).
     *
     * The gym fee is official immediately. If a coach is selected, the coach part
     * is NOT: it becomes a CoachRequest + a payment held as "Awaiting Coach", and
     * the member is not assigned until that coach approves
     * (see CoachConfirmationService).
     */
    public function store(Request $request, CoachConfirmationService $coachConfirmation)
    {
        $user = Auth::user();

        if (!$user || (! $user->isAdmin() && ! $user->isStaff())) {
            abort(403, 'Only admin and staff can record payments.');
        }

        $request->validate([
            'member_id'             => 'required|exists:members,id',
            'fitness_plan'          => 'nullable|in:Calisthenics,Bodybuilding,Plyometrics,Powerlifting,Endurance,Functional Training,Hybrid Training',
            'membership_type'       => 'required|in:Monthly,Quarterly,Semi-Annual,Annually',
            'instructor_id'         => ['nullable', Rule::exists('users', 'id')->where('role', 'instructor')],
            'coach_membership_type' => 'nullable|in:Monthly,Quarterly,Semi-Annual,Annually',
            'payment_date'          => 'required|date',
            'method'                => 'required|in:Cash,GCash,Bank Transfer,Card',
            'amount'                => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string|max:500',
            'record_type'           => 'nullable|in:gym,coach',
            'subscription_type'     => 'nullable|in:Monthly,Quarterly,Semi-Annual,Annually',
        ]);

        if ($request->filled('instructor_id') && ! $request->filled('coach_membership_type')) {
            return back()->withInput()->with('error', 'Please select a coaching package when assigning an instructor.');
        }

        $member       = Member::findOrFail($request->member_id);
        $gymType      = $request->input('membership_type', $request->input('subscription_type', 'Monthly'));
        $hasCoach     = $request->filled('instructor_id');
        $instructorId = $hasCoach ? (int) $request->instructor_id : null;
        $coachType    = $hasCoach ? $request->input('coach_membership_type') : null;
        $fitnessPlan  = $request->input('fitness_plan', $member->getAttribute('fitness_plan') ?? 'Calisthenics');

        $gymAmount   = GreedyScheduler::computeGymFee($gymType);
        $coachAmount = $hasCoach ? GreedyScheduler::computeCoachFee($coachType, $instructorId) : 0;

        $memberEndDate = $member->getAttribute('end_date');
        $start = $memberEndDate && $memberEndDate->isFuture()
            ? $memberEndDate->copy()
            : Carbon::parse($request->payment_date);
        $end = GreedyScheduler::computeEndDate($start, $gymType);

        // When does the new coaching begin?
        //  • member still has a DIFFERENT coach on a running membership → the new coach
        //    starts when the renewed period starts ($start), so the current coach is not cut off;
        //  • otherwise → as soon as the coach confirms.
        $currentCoachStillRunning = $hasCoach
            && $member->instructor_id
            && (int) $member->instructor_id !== $instructorId
            && $memberEndDate
            && $memberEndDate->isFuture();

        $coachStartsOn = $currentCoachStillRunning ? $start->copy()->startOfDay() : Carbon::today();

        try {
            $coachRequest = DB::transaction(function () use (
                $request, $user, $member, $coachConfirmation,
                $gymType, $gymAmount, $coachAmount, $coachType, $instructorId, $hasCoach,
                $fitnessPlan, $start, $end, $coachStartsOn
            ) {
                // Gym membership: official immediately.
                $memberAttributes = [
                    'fitness_plan'    => $fitnessPlan,
                    'membership_type' => $gymType,
                    'start_date'      => $start,
                    'end_date'        => $end,
                    'fee'             => $gymAmount,
                    'status'          => 'Active',
                ];

                // No coach selected → same behaviour as before (clear any coach).
                // Coach selected   → coach fields are NOT touched until the coach approves.
                if (! $hasCoach) {
                    $memberAttributes += [
                        'instructor_id'         => null,
                        'coach_membership_type' => null,
                        'coach_status'          => 'none',
                    ];
                }

                $member->forceFill($memberAttributes)->save();

                Payment::create([
                    'member_id'       => $member->getKey(),
                    'payment_type'    => 'gym_fee',
                    'platform_fee'    => $gymAmount,
                    'receipt_number'  => Payment::generateReceiptNumber(),
                    'amount'          => $gymAmount,
                    'fitness_plan'    => $fitnessPlan,
                    'membership_type' => $gymType,
                    'payment_date'    => $request->payment_date,
                    'method'          => $request->input('method'),
                    'status'          => Payment::STATUS_PAID,
                    'notes'           => $request->notes ?: 'Gym membership payment',
                    'processed_by'    => $user->id,
                ]);

                if (! $hasCoach) {
                    return null;
                }

                // Coach part: held, not paid. Becomes Paid only when the coach approves.
                $coachPayment = null;
                if ($coachAmount > 0) {
                    $coachPayment = Payment::create([
                        'member_id'       => $member->getKey(),
                        'payment_type'    => 'coach_fee',
                        'instructor_id'   => $instructorId,
                        'receipt_number'  => Payment::generateReceiptNumber(),
                        'amount'          => $coachAmount,
                        'fitness_plan'    => $fitnessPlan,
                        'membership_type' => $coachType,
                        'payment_date'    => $request->payment_date,
                        'method'          => $request->input('method'),
                        'status'          => Payment::STATUS_AWAITING_COACH,
                        'notes'           => $request->notes ?: 'Coach subscription payment',
                        'processed_by'    => $user->id,
                    ]);
                }

                return $coachConfirmation->createRequest(
                    $member,
                    $instructorId,
                    $coachType,
                    $coachPayment,
                    $user,
                    $coachStartsOn,
                    sprintf(
                        'Renewal recorded by %s: %s coaching package%s.',
                        $user->name,
                        $coachType,
                        $coachAmount > 0 ? ' (₱' . number_format($coachAmount, 2) . ')' : ''
                    )
                );
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Could not record the payment: ' . $e->getMessage());
        }

        if ($coachRequest) {
            $coachName = $coachRequest->instructor?->name ?? 'the selected coach';

            return back()->with(
                'success',
                'Gym membership payment recorded. The coach payment is on hold until ' . $coachName . ' confirms the assignment.'
            );
        }

        return back()->with('success', 'Membership payment recorded successfully.');
    }

    /**
     * Delete a payment record (admin only).
     */
    public function destroy(Payment $payment)
    {
        Payment::whereKey($payment->getKey())->toBase()->delete();
        return back()->with('success', 'Transaction deleted.');
    }
}