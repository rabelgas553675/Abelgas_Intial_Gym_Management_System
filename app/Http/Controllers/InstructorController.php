<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Models\CoachRequest;
use App\Services\Algorithms\GraphManager;
use App\Services\Algorithms\MergeSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class InstructorController extends Controller
{
    public function dashboard()
    {
        $instructor   = Auth::user();
        $instructorId = $instructor->id;

        // 1. Load all members that have ANY instructor assignment
        $allAssigned = Member::with('user')
            ->whereNotNull('instructor_id')
            ->get();

        // 2. Build the directed graph: instructor_id → member_id edges
        $graph = GraphManager::buildFromMembers($allAssigned->all());

        // 3. BFS from this instructor's node to get assigned member objects
        $myMemberObjects = $graph->bfsData($instructorId);

        // 4. MergeSort the result by member name ascending
        $sorted = MergeSort::sortBy($myMemberObjects, 'name', 'asc');

        // 5. Wrap in a collection so blade can call ->count()
        $members = collect($sorted);

        // 6. Stats
        $totalAssigned = $members->count();
        $active        = $members->filter(fn($m) => !$m->isExpired() && !$m->isDueWithinDays(7))->count();
        $nearDue       = $members->filter(fn($m) =>  $m->isDueWithinDays(7) && !$m->isExpired())->count();
        $pendingCount  = CoachRequest::where('instructor_id', $instructorId)
                                     ->where('status', 'pending')
                                     ->count();

        // 7. Graph degree = number of direct member edges for this instructor
        $graphDegree = $graph->degree($instructorId);

        // 8. Recent payments for this instructor (keep as Eloquent for dashboard widget)
        $payments = Payment::where('instructor_id', $instructorId)
                           ->with('member')
                           ->latest('payment_date')
                           ->take(10)
                           ->get();

        return view('instructor.dashboard', compact(
            'instructor',
            'members',
            'totalAssigned',
            'active',
            'nearDue',
            'pendingCount',
            'graphDegree',
            'payments'
        ));
    }

    public function showMember(Member $member)
    {
        $instructorId = Auth::id();

        $allAssigned = Member::whereNotNull('instructor_id')->get();
        $graph       = GraphManager::buildFromMembers($allAssigned->all());

        if (!$graph->isReachable($instructorId, $member->id)) {
            abort(403, 'This member is not assigned to you.');
        }

        return view('instructor.member-detail', compact('member'));
    }

    public function profile()
    {
        $instructor = Auth::user();
        return view('instructor.profile', compact('instructor'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|in:Male,Female,Other',
            'birthdate'        => 'nullable|date',
            'address'          => 'nullable|string|max:500',
            'specialization'   => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'photo'            => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('profiles', 'public');
        }

        $user->fill($request->only([
            'name', 'phone', 'gender', 'birthdate',
            'address', 'specialization', 'experience_years',
        ]))->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Instructor's own payment/earnings history.
     *
     * DSA integration:
     *   - MergeSort::sortBy() sorts all payments by date descending
     *     before paginating, replacing ->latest('payment_date').
     *
     * Also computes the three stats the blade requires:
     *   - $thisMonthTotal  — sum of earnings in the current month
     *   - $totalEarned     — all-time earnings sum
     *   - $payments        — LengthAwarePaginator (blade calls ->total(), ->hasPages(), ->links())
     */
    public function paymentHistory(Request $request)
    {
        $instructorId = Auth::id();

        // 1. Load all payments for this instructor with member relationship
        $allPayments = Payment::where('instructor_id', $instructorId)
                              ->with('member.user')
                              ->get();

        // 2. Compute stat totals from the full collection (before pagination)
        $thisMonthTotal = $allPayments
            ->filter(fn($p) => $p->payment_date
                && \Carbon\Carbon::parse($p->payment_date)->month === now()->month
                && \Carbon\Carbon::parse($p->payment_date)->year  === now()->year
            )
            ->sum('amount');

        $totalEarned = $allPayments->sum('amount');

        // 3. MergeSort all payments by payment_date descending
        //    (replaces ->latest('payment_date') — fulfils DSA requirement)
        $sorted = MergeSort::sortBy($allPayments->all(), 'payment_date', 'desc');

        // 4. Manual pagination over the sorted array
        $perPage     = 15;
        $currentPage = (int) ($request->page ?? 1);
        $offset      = ($currentPage - 1) * $perPage;
        $pageItems   = array_slice($sorted, $offset, $perPage);

        // 5. Wrap in LengthAwarePaginator so blade ->total(), ->hasPages(),
        //    ->links() and the @forelse all work as expected
        $payments = new LengthAwarePaginator(
            $pageItems,
            count($sorted),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('instructor.payments', compact(
            'payments',
            'thisMonthTotal',
            'totalEarned'
        ));
    }
}