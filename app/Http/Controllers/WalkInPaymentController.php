<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\WalkInPayment;
use App\Services\Algorithms\MergeSort;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Walk-In / Day Pass payments (Admin + Staff).
 *
 * Completely separate from Member Payments:
 *   - own table (walk_in_payments), own model, own page, own form;
 *   - never creates a Member, never touches membership dates/status,
 *     never writes to the `payments` table.
 * The Day Pass rate is read from the existing gym rate settings
 * (Payment::dayPassRate()) — it is never taken from the request.
 */
class WalkInPaymentController extends Controller
{
    /** Same rule the rest of the app uses for admin/staff-only screens. */
    private function authorizeStaffOrAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && $user->canManageMembers(), 403, 'Only admin and staff can manage walk-in payments.');
    }

    /**
     * Walk-In page: record form + searchable records table.
     *
     * DSA integration (same convention as Member/Payment pages):
     *   - MergeSort::sortBy() orders the records (newest first) in memory.
     */
    public function index(Request $request)
    {
        $this->authorizeStaffOrAdmin($request);

        $request->validate([
            'search'    => 'nullable|string|max:100',
            'method'    => ['nullable', Rule::in(WalkInPayment::METHODS)],
            'status'    => 'nullable|in:Paid,Partial',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $records = WalkInPayment::with('processedBy:id,name')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn ($w) => $w
                    ->where('customer_name', 'like', $s)
                    ->orWhere('contact_number', 'like', $s)
                    ->orWhere('receipt_number', 'like', $s));
            })
            ->when($request->filled('method'),    fn ($q) => $q->where('method', $request->input('method')))
            ->when($request->filled('status'),    fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'),   fn ($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->get()
            ->all();

        $sorted = MergeSort::sortBy($records, 'created_at', 'desc');

        $perPage     = 15;
        $currentPage = max(1, (int) $request->query('page', 1));
        $payments    = new LengthAwarePaginator(
            array_slice($sorted, ($currentPage - 1) * $perPage, $perPage),
            count($sorted),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('walkin.index', [
            'payments'     => $payments,
            'dayPassRate'  => Payment::dayPassRate(),
            'methods'      => WalkInPayment::METHODS,
            'todayCount'   => WalkInPayment::whereDate('payment_date', today())->count(),
            'todayTotal'   => (float) WalkInPayment::whereDate('payment_date', today())->sum('amount'),
            'monthTotal'   => (float) WalkInPayment::thisMonth()->sum('amount'),
            'allTotal'     => (float) WalkInPayment::sum('amount'),
            'allCount'     => WalkInPayment::count(),
        ]);
    }

    /** Record one Day Pass sale. */
    public function store(Request $request)
    {
        $this->authorizeStaffOrAdmin($request);

        $data = $request->validate([
            'customer_name'  => ['required', 'string', 'min:2', 'max:120', "regex:/^[\pL\pM\s.'\-]+$/u"],
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'payment_method' => ['required', Rule::in(WalkInPayment::METHODS)],
            'payment_date'   => 'required|date|before_or_equal:today',
            'amount'         => 'required|numeric|min:0.01|max:100000',
            'notes'          => 'nullable|string|max:500',
        ], [
            'customer_name.regex'  => 'The customer name may only contain letters, spaces, apostrophes, periods and hyphens.',
            'contact_number.regex' => 'Enter a valid contact number (digits, +, -, spaces or brackets; 7–20 characters).',
            'payment_date.before_or_equal' => 'The payment date cannot be in the future.',
        ]);

        $name   = trim(preg_replace('/\s+/', ' ', $data['customer_name']));
        $phone  = trim($data['contact_number']);
        $amount = round((float) $data['amount'], 2);

        // Duplicate guard: the same customer, contact, date and amount saved in the
        // last 2 minutes is almost certainly a double click / double submit.
        $duplicate = WalkInPayment::whereRaw('LOWER(customer_name) = ?', [mb_strtolower($name)])
            ->where('contact_number', $phone)
            ->whereDate('payment_date', $data['payment_date'])
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error',
                'This walk-in payment was just recorded. If it is a different transaction, wait a moment and try again.');
        }

        // Rate always comes from the gym rate settings — never from the form.
        $rate   = (float) Payment::dayPassRate();
        $status = $amount >= $rate ? 'Paid' : 'Partial';

        $payment = DB::transaction(fn () => WalkInPayment::create([
            'receipt_number' => WalkInPayment::generateReceiptNumber(),
            'customer_name'  => $name,
            'contact_number' => $phone,
            'pass_type'      => WalkInPayment::PASS_TYPE,
            'day_pass_rate'  => $rate,
            'amount'         => $amount,
            'method'         => $data['payment_method'],
            'payment_date'   => $data['payment_date'],
            'status'         => $status,
            'notes'          => $data['notes'] ?? null,
            'processed_by'   => $request->user()->id,
        ]));

        return redirect()->back()
            ->with('success', "Walk-in Day Pass recorded for {$payment->customer_name} — receipt {$payment->receipt_number}.");
    }

    /** Remove a mistaken entry (admin only — enforced by the route middleware and here). */
    public function destroy(Request $request, WalkInPayment $walkIn)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Only administrators can delete walk-in records.');

        $walkIn->delete(); // logged by the Auditable trait

        return back()->with('success', 'Walk-in record deleted.');
    }
}