<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $members        = Member::orderBy('name')->get();
        $payments       = Payment::with('member')->latest()->get();
        $thisMonth      = Payment::whereMonth('payment_date', now()->month)
                                 ->whereYear('payment_date', now()->year)
                                 ->sum('amount');
        $totalCollected = Payment::sum('amount');
        $totalCount     = Payment::count();

        return view('payment.index', compact('members', 'payments', 'thisMonth', 'totalCollected', 'totalCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id'    => 'required|exists:members,id',
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'method'       => 'required|in:Cash,GCash,Bank Transfer,Card',
            'notes'        => 'nullable|string',
        ]);

        Payment::create([
            'member_id'    => $request->member_id,
            'user_id'      => auth()->id(),
            'amount'       => $request->amount,
            'payment_date' => $request->payment_date,
            'method'       => $request->method,
            'notes'        => $request->notes,
        ]);

        return back()->with('success', 'Payment recorded successfully!');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment deleted.');
    }
}