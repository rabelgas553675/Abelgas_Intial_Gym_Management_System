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
     * MergeSort replaces ->latest() for the transaction tables.
     * BinarySearch is exposed as the search hook (if search param is added to the view later).
     */
    public function index(Request $request)
    {
        // ── Aggregates (still via DB — acceptable per consultation) ──────────
        $totalCount     = Payment::gymFees()->count();
        $thisMonth      = Payment::gymFees()->thisMonth()->sum('amount');
        $totalCollected = Payment::gymFees()->sum('amount');

        $totalCoachFees       = Payment::coachFees()->sum('amount');
        $thisMonthCoachFees   = Payment::coachFees()->thisMonth()->sum('amount');
        $instructorsPaidCount = Payment::coachFees()
                                    ->distinct('instructor_id')
                                    ->count('instructor_id');

        // ── Instructor leaderboard ────────────────────────────────────────────
        // Load into memory, then MergeSort by total descending
        $leaderboardRaw = Payment::coachFees()
            ->select('instructor_id',
                     DB::raw('SUM(amount) as total'),
                     DB::raw('COUNT(*) as txn_count'))
            ->with('instructor:id,name,photo')
            ->groupBy('instructor_id')
            ->get()
            ->toArray();

        // MergeSort: sort instructor leaderboard by total earnings, descending
        $instructorLeaderboard = MergeSort::sortBy($leaderboardRaw, 'total', 'desc');

        // ── Gym fee transactions ──────────────────────────────────────────────
        $gymPaymentsRaw = Payment::gymFees()
            ->with('member:id,name,user_id', 'member.user:id,name,photo')
            ->get()
            ->toArray();

        // MergeSort by created_at descending (newest first)
        $payments = MergeSort::sortBy($gymPaymentsRaw, 'created_at', 'desc');

        // Optional: BinarySearch on receipt_number if ?search= is passed
        if ($request->filled('search')) {
            $byReceipt = BinarySearch::searchByField(
                MergeSort::sortBy($gymPaymentsRaw, 'receipt_number', 'asc'),
                'receipt_number',
                $request->search
            );
            $payments = $byReceipt;
        }

        // ── Coach fee transactions ────────────────────────────────────────────
        $coachPaymentsRaw = Payment::coachFees()
            ->with(
                'member:id,name,user_id',
                'member.user:id,name,photo',
                'instructor:id,name,photo'
            )
            ->get()
            ->toArray();

        // MergeSort by created_at descending
        $coachFeePayments = MergeSort::sortBy($coachPaymentsRaw, 'created_at', 'desc');

        // ── Member dropdown ───────────────────────────────────────────────────
        $membersRaw = Member::orderBy('name')->get(['id', 'name'])->toArray();
        // MergeSort ensures consistent alphabetical ordering independent of DB collation
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
     * Store a manually recorded gym_fee payment (admin/staff).
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
     * Delete a payment (admin only).
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Transaction deleted.');
    }
}