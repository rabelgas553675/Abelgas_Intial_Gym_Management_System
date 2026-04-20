<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Admin payments page — shows gym_fee transactions + instructor earnings breakdown.
     */
    public function index()
    {
        // ── Gym fee stats (admin earnings) ────────────────────────────────────
        $totalCount     = Payment::gymFees()->count();
        $thisMonth      = Payment::gymFees()->thisMonth()->sum('amount');
        $totalCollected = Payment::gymFees()->sum('amount');

        // ── Coach fee stats (instructor earnings) ─────────────────────────────
        $totalCoachFees       = Payment::coachFees()->sum('amount');
        $thisMonthCoachFees   = Payment::coachFees()->thisMonth()->sum('amount');
        $instructorsPaidCount = Payment::coachFees()
                                    ->distinct('instructor_id')
                                    ->count('instructor_id');

        // ── Instructor leaderboard ────────────────────────────────────────────
        $instructorLeaderboard = Payment::coachFees()
            ->select('instructor_id',
                     DB::raw('SUM(amount) as total'),
                     DB::raw('COUNT(*) as txn_count'))
            ->with('instructor:id,name,photo')   // instructors ARE users, photo is on users table
            ->groupBy('instructor_id')
            ->orderByDesc('total')
            ->get();

        // ── Table data ────────────────────────────────────────────────────────
        // Admin sees ONLY gym_fee rows.
        // Load member.user so the blade can resolve member photo via member->user->photo
        $payments = Payment::gymFees()
            ->with('member:id,name,user_id', 'member.user:id,name,photo')
            ->latest('payment_date')
            ->get();

        // Coach fee transaction log (shown in Instructor tab)
        // Same fix: load member.user for photo resolution
        $coachFeePayments = Payment::coachFees()
            ->with(
                'member:id,name,user_id',
                'member.user:id,name,photo',
                'instructor:id,name,photo'
            )
            ->latest('payment_date')
            ->get();

        // Member dropdown for manual record form
        $members = Member::orderBy('name')->get(['id', 'name']);

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