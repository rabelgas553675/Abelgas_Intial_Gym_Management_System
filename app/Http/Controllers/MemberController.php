<?php

namespace App\Http\Controllers;

use App\Exceptions\MemberHasHistoryException;
use App\Models\Member;
use App\Models\User;
use App\Models\Payment;
use App\Models\CoachRequest;
use App\Services\Algorithms\BinarySearch;
use App\Services\Algorithms\MergeSort;
use App\Services\Algorithms\GreedyScheduler;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            $users = User::where('role', '=', $roleFilter, 'and')->get()->toArray();

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

        // Build base DB query (plan filter only — status is computed, see below)
        $query = Member::with('user');

        if ($request->filled('plan')) {
            // "Annual" and "Annually" are both used in the data — match either.
            if (in_array($request->plan, ['Annual', 'Annually'], true)) {
                $query->whereIn('membership_type', ['Annual', 'Annually']);
            } else {
                $query->where('membership_type', $request->plan);
            }
        }

        $collection = $query->get();

        // The Status shown in the table is COMPUTED from end_date (see Member::status()),
        // so it must be filtered in memory — the stored DB column would miss expired members.
        if ($request->filled('status')) {
            $wanted     = $request->status;
            $collection = $collection->filter(function (Member $m) use ($wanted) {
                return $wanted === 'Active'
                    ? in_array($m->status, ['Active', 'Expiring Soon'], true)
                    : $m->status === $wanted;
            })->values();
        }

        // Load all matching members into memory
        $allMembers = $collection->map(function (Member $m) {
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
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isStaff())) {
            abort(403);
        }
        $instructors = User::where('role', '=', 'instructor', 'and')->get();
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
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isStaff())) {
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

    public function edit(Request $request, Member $member)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isStaff())) {
            abort(403);
        }
        return view('members.edit', compact('member'));
    }

    /**
     * Update a member and (re)activate their subscription.
     *
     * - Plan or start date changed  → end_date is recalculated (GreedyScheduler).
     * - Status "Active" on a member with no / past end_date → renewed from today.
     * - Inactive / Suspended are stored as staff-controlled states.
     */
    public function update(Request $request, Member $member)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isStaff())) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => [
                'required', 'email:rfc', 'max:255',
                Rule::unique('members', 'email')->ignore($member->id),
                Rule::unique('users', 'email')->ignore($member->user_id),
            ],
            'phone'           => 'nullable|string|max:20',
            'membership_type' => ['required', Rule::in(['Monthly', 'Quarterly', 'Semi-Annual', 'Annual', 'Annually'])],
            'status'          => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'start_date'      => 'required|date',
            'fee'             => 'required|numeric|min:0',
            'photo'           => 'nullable|image|max:3072',
        ]);

        DB::beginTransaction();
        try {
            // Legacy rows may store "Annual"; the app's canonical name is "Annually".
            $plan  = $validated['membership_type'] === 'Annual' ? 'Annually' : $validated['membership_type'];
            $start = Carbon::parse($validated['start_date'])->startOfDay();
            $end   = $member->end_date ? Carbon::parse($member->end_date) : null;

            // Plan / start date changed (or never set) → recalculate expiry
            $needsRecalc = !$end
                || !$member->start_date
                || $plan !== ($member->membership_type === 'Annual' ? 'Annually' : $member->membership_type)
                || !$start->isSameDay($member->start_date);

            if ($needsRecalc) {
                $end = Carbon::parse(GreedyScheduler::computeEndDate($start->copy(), $plan));
            }

            // Activating a lapsed / brand-new membership → renew from today
            if ($validated['status'] === 'Active' && $end->isPast()) {
                $start = now()->startOfDay();
                $end   = Carbon::parse(GreedyScheduler::computeEndDate($start->copy(), $plan));
            }

            $data = [
                'name'            => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'first_name'      => $validated['first_name'],
                'last_name'       => $validated['last_name'],
                'email'           => $validated['email'],
                'phone'           => $validated['phone'] ?? null,
                'membership_type' => $plan,
                'status'          => $validated['status'],
                'start_date'      => $start,
                'end_date'        => $end,
                'fee'             => $validated['fee'],
            ];

            // ── Photo (member + linked user share the same file) ──
            $oldPhotos = [];
            if ($request->hasFile('photo')) {
                $newPhoto = $request->file('photo')->store('members', 'public');
                $data['photo'] = $newPhoto;
                $oldPhotos[] = $member->photo;
                if ($member->user) {
                    $oldPhotos[] = $member->user->photo;
                }
            }

            // instructor_id is never updated here — it must go through coach approval
            $member->update($data);

            // Sync the linked portal account
            if ($member->user) {
                $userData = [
                    'name'  => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ];
                if (isset($newPhoto)) {
                    $userData['photo'] = $newPhoto;
                }
                $member->user->update($userData);
            }

            DB::commit();

            foreach (array_unique(array_filter($oldPhotos)) as $old) {
                Storage::disk('public')->delete($old);
            }

            return redirect()->route('members.index')->with('success', 'Member updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($newPhoto)) {
                Storage::disk('public')->delete($newPhoto);
            }
            return back()->withInput()->with('error', 'Failed to update member: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a member — ONLY if they have no history.
     *
     * Protection layers (any one of them is enough to stop the delete):
     *   1. Role check            → only admin / staff (blocks direct requests from other roles).
     *   2. Member::deleting()    → throws MemberHasHistoryException when payments, coach fees,
     *                              attendance, workout plans or coach requests exist.
     *   3. DB foreign keys       → ON DELETE RESTRICT on those tables (see migration
     *                              2026_09_29_000100_restrict_member_deletes_on_history_tables).
     *
     * Nothing related is ever modified or deleted. Files (photo / QR) are removed only
     * after the database delete has succeeded.
     *
     * Web requests get a redirect with a flash message; JSON/API requests get a
     * JSON body (409 Conflict when blocked).
     */
    public function destroy(Request $request, Member $member)
    {
        $user = $request->user();
        if (!$user || (!$user->isAdmin() && !$user->isStaff())) {
            abort(403);
        }

        $photo  = $member->photo;
        $qrPath = $member->qr_code_path;

        try {
            DB::transaction(function () use ($member) {
                // Lock the row so a payment / attendance can't slip in between the check and the delete.
                $locked = Member::whereKey($member->getKey())->lockForUpdate()->firstOrFail();

                // Member::deleting() throws MemberHasHistoryException if any history exists.
                $locked->delete();
            });
        } catch (MemberHasHistoryException $e) {
            return $this->deleteBlocked($request, $e->getMessage(), $e->blockers());
        } catch (QueryException $e) {
            // Database-level RESTRICT foreign key (SQLSTATE 23000) — last line of defence.
            if ((string) $e->getCode() === '23000') {
                return $this->deleteBlocked($request, MemberHasHistoryException::buildMessage());
            }

            report($e);
            return $this->deleteFailed($request);
        } catch (\Throwable $e) {
            report($e);
            return $this->deleteFailed($request);
        }

        // Delete succeeded → now it is safe to remove the files.
        if ($photo)  Storage::disk('public')->delete($photo);
        if ($qrPath) Storage::disk('public')->delete($qrPath);

        $message = 'Member deleted.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->route('members.index')->with('success', $message);
    }

    /** Response used when the delete is refused because the member has history. */
    private function deleteBlocked(Request $request, string $message, array $blockers = [])
    {
        return $request->expectsJson()
            ? response()->json(['success' => false, 'message' => $message, 'blockers' => $blockers], 409)
            : redirect()->route('members.index')->with('error', $message);
    }

    /** Response used for unexpected failures (details are logged, not shown to the user). */
    private function deleteFailed(Request $request)
    {
        $message = 'Failed to delete the member. Please try again.';

        return $request->expectsJson()
            ? response()->json(['success' => false, 'message' => $message], 500)
            : redirect()->route('members.index')->with('error', $message);
    }

    public function selectPlan()
    {
        $member      = Auth::user()->member;
        $instructors = User::where('role', '=', 'instructor', 'and')->get();
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
            'membership_type'       => 'required|in:Monthly,Quarterly,Semi-Annual,Annually',
            'instructor_id'         => 'nullable|string',
            'coach_membership_type' => 'nullable|required_if:instructor_id,!=,null|in:Monthly,Quarterly,Semi-Annual,Annually',
        ]);

        $member = Auth::user()->member;

        // ── GreedyScheduler: compute fees ─────────────────────────────────────
        $coachPlan   = $request->filled('instructor_id') ? $request->coach_membership_type : null;
        $instructorId = $request->filled('instructor_id') ? (int) $request->instructor_id : null;
        $gymAmount   = GreedyScheduler::computeGymFee($request->membership_type);
        $coachAmount = GreedyScheduler::computeCoachFee($coachPlan, $instructorId);
        $totalAmount = GreedyScheduler::computeTotalFee($request->membership_type, $coachPlan, $instructorId);

        $activeCoachIsRunning = $member
            && $member->instructor_id
            && $member->end_date
            && $member->end_date->isFuture();

        $currentCoachStillRunning = $activeCoachIsRunning
            && $request->filled('instructor_id')
            && (int) $member->instructor_id !== (int) $request->instructor_id;

        $shouldKeepCurrentCoach = $activeCoachIsRunning
            && (!$request->filled('instructor_id') || (int) $member->instructor_id === (int) $request->instructor_id);

        // Accumulate renewals by extending the current active plan instead of
        // resetting start/end dates from today when a future term is still active.
        $start = $activeCoachIsRunning || ($member && $member->end_date && $member->end_date->isFuture())
            ? $member->end_date->copy()
            : Carbon::now();
        $end   = GreedyScheduler::computeEndDate($start, $request->membership_type);

        $memberInstructorId = $shouldKeepCurrentCoach
            ? $member->instructor_id
            : ($request->filled('instructor_id') ? null : ($member?->instructor_id ?? null));

        $memberCoachStatus = $shouldKeepCurrentCoach
            ? ($member->coach_status ?? 'approved')
            : ($request->filled('instructor_id') ? 'pending' : 'none');

        $memberCoachMembershipType = $shouldKeepCurrentCoach
            ? ($member->coach_membership_type ?? $coachPlan)
            : $coachPlan;

        $scheduledCoachStartsOn = $member && $member->end_date && $member->end_date->isFuture()
            ? $member->end_date->copy()->startOfDay()
            : null;

        DB::beginTransaction();
        try {
            $member->update([
                'fitness_plan'          => $request->fitness_plan,
                'membership_type'       => $request->membership_type,
                'instructor_id'         => $memberInstructorId,
                'coach_membership_type' => $memberCoachMembershipType,
                'start_date'            => $start,
                'end_date'              => $end,
                'fee'                   => $totalAmount,
                'status'                => 'Active',
                'coach_status'          => $memberCoachStatus,
            ]);

            if ($request->filled('instructor_id')) {
                CoachRequest::where('member_id', '=', $member->id, 'and')
                    ->where('status', '=', 'pending', 'and')
                    ->update(['status' => 'rejected']);

                if ($currentCoachStillRunning && $scheduledCoachStartsOn) {
                    CoachRequest::create([
                        'member_id'             => $member->id,
                        'instructor_id'         => $request->instructor_id,
                        'status'                => 'pending',
                        'message'               => 'Request to replace the current coach after the active term ends.',
                        'coach_membership_type' => $coachPlan,
                        'starts_on'             => $scheduledCoachStartsOn->toDateString(),
                    ]);
                } else {
                    CoachRequest::create([
                        'member_id'             => $member->id,
                        'instructor_id'         => $request->instructor_id,
                        'status'                => 'pending',
                        'message'               => null,
                        'coach_membership_type' => $coachPlan,
                    ]);
                }
            }

            $isAdvanceRenewal = $member && $member->end_date && $member->end_date->isFuture();

            $payment = Payment::create([
                'member_id'       => $member->id,
                'receipt_number'  => 'RCP-' . strtoupper(Str::random(12)),
                'amount'          => $totalAmount,
                'fitness_plan'    => $request->fitness_plan,
                'membership_type' => $request->membership_type,
                'payment_date'    => Carbon::now(),
                'status'          => 'Paid',
                'method'          => 'Cash',
                'notes'           => Payment::paymentNoteFor(null, $request->membership_type, $totalAmount, 'gym_fee', $isAdvanceRenewal) ?: 'Gym membership payment',
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
        $rawPayments = Payment::where('member_id', '=', $member->id, 'and')->get()->toArray();

        // MergeSort by payment_date descending (replaces ->latest('payment_date'))
        $payments = MergeSort::sortBy($rawPayments, 'payment_date', 'desc');

        return view('member.payment-history', compact('payments', 'member'));
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
