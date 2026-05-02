<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Models\Payment;
use App\Models\CoachRequest;
use App\Services\Algorithms\BinarySearch;
use App\Services\Algorithms\MergeSort;
use App\Services\Algorithms\GreedyScheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * List members (admin/staff view).
     *
     * DSA integration:
     *   - MergeSort::sortBy()       replaces ->latest() / ->orderBy()
     *   - BinarySearch::searchByField()  replaces LIKE '%search%' queries
     *
     * Strategy: load all matching records into memory, sort with MergeSort,
     * then narrow with BinarySearch when a search term is present.
     */
    public function index(Request $request)
    {
        $roleFilter = $request->filled('role') ? strtolower($request->role) : null;

        // ── Staff / Instructor filter path ────────────────────────────────────
        if ($roleFilter && in_array($roleFilter, ['staff', 'instructor'])) {
            $users = User::where('role', $roleFilter)->get()->toArray();

            // 1. MergeSort by name ascending (replaces ->latest())
            $sorted = MergeSort::sortBy($users, 'name', 'asc');

            // 2. BinarySearch by name if search term is provided
            if ($request->filled('search')) {
                $sorted = BinarySearch::searchByField(
                    MergeSort::sortBy($users, 'name', 'asc'),
                    'name',
                    $request->search
                );
            }

            // 3. Manual pagination after in-memory sort + search
            $perPage     = 15;
            $currentPage = (int) ($request->page ?? 1);
            $offset      = ($currentPage - 1) * $perPage;
            $pageItems   = array_slice($sorted, $offset, $perPage);

            // 4. Shape into the same stdClass the blade expects
            $shapedItems = array_map(function (array $user) {
                return (object) [
                    'id'              => $user['id'],
                    'name'            => $user['name'],
                    'first_name'      => $user['name'],
                    'last_name'       => '',
                    'email'           => $user['email'],
                    'phone'           => $user['phone']           ?? null,
                    'photo'           => $user['photo']           ?? null,
                    'membership_type' => $user['membership_type'] ?? null,
                    'role'            => ucfirst($user['role']),
                    'status'          => $user['status']          ?? null,
                    'start_date'      => $user['start_date']      ?? null,
                    'end_date'        => $user['end_date']        ?? null,
                    'qr_code_path'    => $user['qr_code_path']    ?? null,
                ];
            }, $pageItems);

            $members = new \Illuminate\Pagination\LengthAwarePaginator(
                $shapedItems,
                count($sorted),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('members.index', compact('members'));
        }

        // ── Regular member filter path ─────────────────────────────────────────

        // Build base DB query (filters only — no ordering, no LIKE search)
        $query = Member::with('user');

        if ($request->filled('plan'))   { $query->where('membership_type', $request->plan); }
        if ($request->filled('status')) { $query->where('status', $request->status); }

        // Load all matching members into memory
        $allMembers = $query->get()->map(function (Member $m) {
            if (!$m->photo && $m->user && $m->user->photo) {
                $m->photo = $m->user->photo;
            }
            $m->role = 'Member';
            return $m;
        })->toArray();

        // 1. MergeSort by name ascending (replaces ->latest())
        $sorted = MergeSort::sortBy($allMembers, 'name', 'asc');

        // 2. BinarySearch by name when a search term is provided
        //    Pre-sort by name so binary search has a sorted input.
        if ($request->filled('search')) {
            $sorted = BinarySearch::searchByField(
                MergeSort::sortBy($allMembers, 'name', 'asc'),
                'name',
                $request->search
            );
        }

        // 3. Manual pagination after in-memory sort + search
        $perPage     = 15;
        $currentPage = (int) ($request->page ?? 1);
        $offset      = ($currentPage - 1) * $perPage;
        $pageItems   = array_slice($sorted, $offset, $perPage);

        // 4. Re-hydrate plain arrays back into Member models for blade compatibility
        $hydratedItems = array_map(function (array $data) {
            $m = new Member();
            foreach ($data as $key => $value) {
                $m->{$key} = $value;
            }
            return $m;
        }, $pageItems);

        $members = new \Illuminate\Pagination\LengthAwarePaginator(
            $hydratedItems,
            count($sorted),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

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

    /**
     * Store a new member AND automatically create their portal User account.
     *
     * Membership details (plan, start date, fee) are NOT collected here.
     * The member will select their plan after logging into the portal.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isStaff()) {
            abort(403);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email:rfc|unique:users,email|unique:members,email',
            'phone'      => 'nullable|string|max:20',
            'gender'     => 'required|in:Male,Female,Other',
            'birthdate'  => 'required|date',
            'address'    => 'nullable|string',
            'photo'      => 'nullable|image|max:3072',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // ── 1. Upload photo if provided ───────────────────────────────────
            $photoPath = $request->hasFile('photo')
                ? $request->file('photo')->store('members', 'public')
                : null;

            // ── 2. Create the User account (portal login) ─────────────────────
            $user = User::create([
                'name'      => $request->first_name . ' ' . $request->last_name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'password'  => Hash::make($request->password),
                'role'      => 'member',
                'photo'     => $photoPath,
                'gender'    => $request->gender,
                'birthdate' => $request->birthdate,
                'address'   => $request->address,
            ]);

            // ── 3. Create the Member record linked to the new User ────────────
            $member = Member::create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'first_name'      => $request->first_name,
                'last_name'       => $request->last_name,
                'email'           => $request->email,
                'phone'           => $request->phone,
                'gender'          => $request->gender,
                'birthdate'       => $request->birthdate,
                'address'         => $request->address,
                'membership_type' => null,
                'start_date'      => null,
                'end_date'        => null,
                'fee'             => 0,
                'status'          => 'Pending',
                'photo'           => $photoPath,
                'instructor_id'   => null,
                'coach_status'    => 'none',
            ]);

            // ── 4. Generate QR code ───────────────────────────────────────────
            Member::generateQrCode($member);

            DB::commit();

            return redirect()->route('members.index')
                ->with('success', "{$user->name}'s account created successfully. They can log in to select a membership plan.");

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($photoPath) && $photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            return back()
                ->withInput()
                ->with('error', 'Failed to create member account: ' . $e->getMessage());
        }
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

        // Never allow direct instructor_id update — must go through approval
        $data = $request->except('instructor_id');
        $member->update($data);

        // Sync the name on the linked user account as well
        if ($member->user) {
            $member->user->update([
                'name'  => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);
        }

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

    /**
     * Process a member's plan subscription.
     *
     * DSA integration:
     *   - GreedyScheduler::computeEndDate()   replaces Carbon match() block
     *   - GreedyScheduler::computeGymFee()    replaces inline $gymPriceMap array
     *   - GreedyScheduler::computeCoachFee()  replaces inline $coachPriceMap array
     *   - GreedyScheduler::computeTotalFee()  computes combined amount
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'fitness_plan'          => 'required|string',
            'membership_type'       => 'required|in:Monthly,Quarterly,Annually',
            'instructor_id'         => 'nullable|string',
            'coach_membership_type' => 'nullable|required_if:instructor_id,!=,null|in:Monthly,Quarterly,Annually',
        ]);

        $member = Auth::user()->member;

        // ── GreedyScheduler: compute fees ─────────────────────────────────────
        $coachPlan   = $request->filled('instructor_id') ? $request->coach_membership_type : null;
        $gymAmount   = GreedyScheduler::computeGymFee($request->membership_type);
        $coachAmount = GreedyScheduler::computeCoachFee($coachPlan);
        $totalAmount = GreedyScheduler::computeTotalFee($request->membership_type, $coachPlan);

        // ── GreedyScheduler: compute end date ─────────────────────────────────
        $start = Carbon::now();
        $end   = GreedyScheduler::computeEndDate($start, $request->membership_type);

        DB::beginTransaction();
        try {
            $member->update([
                'fitness_plan'          => $request->fitness_plan,
                'membership_type'       => $request->membership_type,
                'instructor_id'         => null,
                'coach_membership_type' => $coachPlan,
                'start_date'            => $start,
                'end_date'              => $end,
                'fee'                   => $totalAmount,
                'status'                => 'Active',
                'coach_status'          => $request->filled('instructor_id') ? 'pending' : 'none',
            ]);

            if ($request->filled('instructor_id')) {
                CoachRequest::where('member_id', $member->id)->where('status', 'pending')->update(['status' => 'rejected']);
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

    /**
     * Member's own payment history.
     *
     * DSA integration:
     *   - MergeSort::sortBy() replaces ->latest('payment_date')
     */
    public function paymentHistory()
    {
        $member      = Auth::user()->member;
        $rawPayments = Payment::where('member_id', $member->id)->get()->toArray();

        // MergeSort by payment_date descending (replaces ->latest('payment_date'))
        $payments = MergeSort::sortBy($rawPayments, 'payment_date', 'desc');

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