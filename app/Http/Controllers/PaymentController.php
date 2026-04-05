<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
   public function index()
{
    $members  = Member::orderBy('name')->get();  // ← fixed
    $payments = Payment::with(['member', 'recordedBy'])
                    ->latest()
                    ->get();

  return view('payment.index', compact('members', 'payments'));  // singular

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
            'method'       => $request->input('method'),
            'notes'        => $request->notes,
        ]);

    return back()->with('success', 'Payment recorded!');  
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment deleted.');
    }
}