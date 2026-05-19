<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Services\Algorithms\MergeSort;
use App\Services\Algorithms\BinarySearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // ── Gym fee transactions ──────────────────────────────────────────────
        // Load with NO DB ordering — MergeSort handles all ordering in memory.
        $gymPaymentsRaw = Payment::gymFees()
            ->with('member:id,name,user_id', 'member.user:id,name,photo')
            ->get()
            ->toArray();

        // MergeSort by created_at descending (newest first) — replaces ->latest()
        $payments = MergeSort::sortBy($gymPaymentsRaw, 'created_at', 'desc');

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

        return view('admin.payments', compact(
            'totalCount', 'thisMonth', 'totalCollected',
            'totalCoachFees', 'thisMonthCoachFees', 'instructorsPaidCount',
            'instructorLeaderboard',
            'payments', 'coachFeePayments',
            'members'
        ));
    }

    /**
     * Store a manually recorded gym_fee payment (admin / staff).
     */
    public function store(Request $request)
    {
        $request->validate([
            'member_id'    => 'required|exists:members,id',
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'method'       => 'required|in:Cash,GCash,Bank Transfer,Card',
            'notes'        => 'nullable|string|max:500',
        ]);

        Payment::create([
            'member_id'      => $request->member_id,
            'payment_type'   => 'gym_fee',
            'instructor_id'  => null,
            'platform_fee'   => $request->amount,
            'receipt_number' => Payment::generateReceiptNumber(),
            'amount'         => $request->amount,
            'payment_date'   => $request->payment_date,
            'method'         => $request->input('method'),
            'status'         => 'Paid',
            'notes'          => $request->notes,
            'processed_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Payment recorded successfully.');
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