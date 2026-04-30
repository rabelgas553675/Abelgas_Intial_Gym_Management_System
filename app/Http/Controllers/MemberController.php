<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Models\Payment;
use App\Models\CoachRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $roleFilter = $request->filled('role') ? strtolower($request->role) : null;

        if ($roleFilter && in_array($roleFilter, ['staff', 'instructor'])) {
            $userQuery = User::where('role', $roleFilter);

            if ($request->filled('search')) {
                $userQuery->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $members = $userQuery->latest()->paginate(15)->through(function (User $user) {
                return (object) [
                    'id'              => $user->id,
                    'name'            => $user->name,
                    'first_name'      => $user->name,
                    'last_name'       => '',
                    'email'           => $user->email,
                    'phone'           => $user->phone           ?? null,
                    'photo'           => $user->photo           ?? null,
                    'membership_type' => $user->membership_type ?? null,
                    'role'            => ucfirst($user->role),
                    'status'          => $user->status          ?? null,
                    'start_date'      => $user->start_date      ?? null,
                    'end_date'        => $user->end_date        ?? null,
                    'qr_code_path'    => $user->qr_code_path    ?? null,
                ];
            });

            return view('members.index', compact('members'));
        }

        $query = Member::with('user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('plan')) {
            $query->where('membership_type', $request->plan);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $members = $query->latest()->paginate(15)->through(function (Member $m) {
            if (!$m->photo && $m->user && $m->user->photo) {
                $m->photo = $m->user->photo;
            }
            $m->role = 'Member';
            return $m;
        });

        return view('members.index', compact('members'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isStaff()) {
            abort(403);
        }

        $instructors = User::where('role', 'instructor')->get();
        return view('members.create', compact('instructors'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isStaff()) {
            abort(403);
        }

        $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:members,email',
            'phone'           => 'nullable|string|max:20',
            'gender'          => 'required|in:Male,Female,Other',
            'birthdate'       => 'required|date',
            'address'         => 'required|string',
            'membership_type' => 'required|in:Monthly,Quarterly,Semi-Annual,Annual',
            'start_date'      => 'required|date',
            'fee'             => 'required|numeric|min:0',
            'photo'           => 'nullable|image|max:3072',
            'instructor_id'   => 'nullable|exists:users,id',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('members', 'public')
            : null;

        $start    = Carbon::parse($request->start_date);
        $end_date = match($request->membership_type) {
            'Monthly'     => $start->copy()->addMonth()->toDateString(),
            'Quarterly'   => $start->copy()->addMonths(3)->toDateString(),
            'Semi-Annual' => $start->copy()->addMonths(6)->toDateString(),
            'Annual'      => $start->copy()->addYear()->toDateString(),
        };

        // ✅ instructor_id is always null — never assigned directly
        $member = Member::create([
            'name'            => $request->first_name . ' ' . $request->last_name,
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'gender'          => $request->gender,
            'birthdate'       => $request->birthdate,
            'address'         => $request->address,
            'membership_type' => $request->membership_type,
            'start_date'      => $start->toDateString(),
            'end_date'        => $end_date,
            'fee'             => $request->fee,
            'status'          => 'Active',
            'photo'           => $photoPath,
            'instructor_id'   => null,
        ]);

        // ✅ If instructor selected, create pending coach request instead
        if ($request->filled('instructor_id')) {
            CoachRequest::create([
                'member_id'     => $member->id,
                'instructor_id' => $request->instructor_id,
                'status'        => 'pending',
                'message'       => null,
            ]);
        }

        Member::generateQrCode($member);

        return redirect()->route('members.index')
                         ->with('success', 'Member created! Coach request sent for approval.');
    }

    public function show(Member $member)
    {
        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isStaff()) {
            abort(403);
        }
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isStaff()) {
            abort(403);
        }

        $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:members,email,' . $member->id,
            'membership_type' => 'required',
            'status'          => 'required',
            'fee'             => 'required|numeric',
        ]);

        // ✅ Never allow direct instructor_id update — must go through approval
        $data = $request->except('instructor_id');
        $member->update($data);

        return redirect()->route('members.index')->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        if ($member->photo)        Storage::disk('public')->delete($member->photo);
        if ($member->qr_code_path) Storage::disk('public')->delete($member->qr_code_path);
        $member->delete();
        return redirect()->route('members.index')->with('success', 'Member deleted.');
    }

    public function selectPlan()
    {
        $member      = Auth::user()->member;
        $instructors = User::where('role', 'instructor')->get();
        return view('member.select-plan', compact('member', 'instructors'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'fitness_plan'          => 'required|string',
            'membership_type'       => 'required|in:Monthly,Quarterly,Annually',
            'instructor_id'         => 'nullable|string',
            'coach_membership_type' => 'nullable|required_if:instructor_id,!=,null|in:Monthly,Quarterly,Annually',
        ]);

        $member = Auth::user()->member;

        $gymPriceMap   = ['Monthly' => 800,  'Quarterly' => 3200, 'Annually' => 9600];
        $coachPriceMap = ['Monthly' => 300,  'Quarterly' => 1200, 'Annually' => 3600];

        $gymAmount   = $gymPriceMap[$request->membership_type] ?? 0;
        $coachAmount = 0;

        if ($request->filled('instructor_id')) {
            $coachAmount = $coachPriceMap[$request->coach_membership_type] ?? 0;
        }

        $totalAmount = $gymAmount + $coachAmount;

        $start = Carbon::now();
        $end   = match($request->membership_type) {
            'Monthly'   => $start->copy()->addMonth(),
            'Quarterly' => $start->copy()->addMonths(3),
            'Annually'  => $start->copy()->addYear(),
        };

        DB::beginTransaction();
        try {
            // ✅ instructor_id always null until coach approves
            $member->update([
                'fitness_plan'          => $request->fitness_plan,
                'membership_type'       => $request->membership_type,
                'instructor_id'         => null,
                'coach_membership_type' => $request->filled('instructor_id')
                                            ? $request->coach_membership_type
                                            : null,
                'start_date'            => $start,
                'end_date'              => $end,
                'fee'                   => $totalAmount,
                'status'                => 'Active',
            ]);

            // ✅ Create pending coach request — instructor must approve first
            if ($request->filled('instructor_id')) {
                CoachRequest::where('member_id', $member->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'rejected']);

                CoachRequest::create([
                    'member_id'     => $member->id,
                    'instructor_id' => $request->instructor_id,
                    'status'        => 'pending',
                    'message'       => null,
                ]);
            }

            $payment = Payment::create([
                'member_id'       => $member->id,
                'receipt_number'  => 'RCP-' . strtoupper(Str::random(12)),
                'amount'          => $totalAmount,
                'fitness_plan'    => $request->fitness_plan,
                'membership_type' => $request->membership_type,
                'payment_date'    => Carbon::now(),
                'status'          => 'Paid',
                'method'          => 'Cash',
            ]);

            DB::commit();

            return redirect()->route('member.receipt', $payment->id)
                             ->with('success', 'Subscription processed! Waiting for coach approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function paymentHistory()
    {
        $member   = Auth::user()->member;
        $payments = Payment::where('member_id', $member->id)
                           ->latest('payment_date')
                           ->get();

        return view('member.payments', compact('payments', 'member'));
    }

    public function receipt(Payment $payment)
    {
        if ($payment->member_id !== Auth::user()->member->id) {
            abort(403);
        }

        $member = $payment->member;
        return view('member.receipt', compact('payment', 'member'));
    }
}