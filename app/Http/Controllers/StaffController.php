<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Services\Algorithms\MergeSort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffController extends Controller
{
    // ─────────────────────────────────────────
    //  Dashboard
    // ─────────────────────────────────────────

    /**
     * Staff dashboard.
     *
     * DSA integration:
     *   - MergeSort::sortBy() replaces ->latest() for members list,
     *     recent members, and recent payments.
     */
    public function dashboard()
    {
        $allMembers = Member::all();

        $stats = [
            'total'   => $allMembers->count(),
            'active'  => $allMembers->filter(
                            fn($m) => in_array($m->status, ['Active', 'Expiring Soon'])
                         )->count(),
            'monthly' => $allMembers->where('membership_type', 'Monthly')->count(),
            'yearly'  => $allMembers->where('membership_type', 'Annually')->count(),
        ];

        $activeCount = $allMembers->filter(
            fn($m) => in_array($m->status, ['Active', 'Expiring Soon'])
        )->count();

        $nearDue = $allMembers->filter(
            fn($m) => $m->status === 'Expiring Soon'
        )->count();

        // MergeSort replaces ->latest() for full members list (used in members panel)
        $allMembersWithUser = Member::with('user')->get()->all();
        $members = collect(MergeSort::sortBy($allMembersWithUser, 'created_at', 'desc'));

        // MergeSort replaces ->latest()->take(6) for recent members widget
        $recent = collect(
            array_slice(MergeSort::sortBy($allMembersWithUser, 'created_at', 'desc'), 0, 6)
        );

        // MergeSort replaces ->latest()->take(5) for recent payments widget
        $allRecentPayments = Payment::with('member:id,name,email,user_id', 'member.user:id,photo')
                                    ->get()
                                    ->all();
        $recentPayments = collect(
            array_slice(MergeSort::sortBy($allRecentPayments, 'created_at', 'desc'), 0, 5)
        );

        $thisMonth = Payment::whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');

        $totalCollected = Payment::sum('amount');

        return view('staff.dashboard', compact(
            'stats',
            'members',
            'activeCount',
            'nearDue',
            'recent',
            'recentPayments',
            'thisMonth',
            'totalCollected'
        ));
    }

    // ─────────────────────────────────────────
    //  Payments
    // ─────────────────────────────────────────

    /**
     * Staff payments page.
     *
     * DSA integration:
     *   - MergeSort::sortBy() replaces ->latest()->paginate(20) for payments list.
     *   - MergeSort::sortBy() replaces ->orderBy('name') for members dropdown.
     */
    public function payments(Request $request)
    {
        // MergeSort replaces ->orderBy('name', 'asc') for member dropdown
        $membersRaw = Member::get(['id', 'name'])->all();
        $members    = collect(MergeSort::sortBy($membersRaw, 'name', 'asc'));

        // Load all payments, MergeSort by created_at descending, then manually paginate
        $allPayments = Payment::with('member:id,name,user_id', 'member.user:id,photo')
                              ->get()
                              ->all();

        // MergeSort replaces ->latest()
        $sorted = MergeSort::sortBy($allPayments, 'created_at', 'desc');

        // Manual pagination
        $perPage     = 20;
        $currentPage = (int) ($request->page ?? 1);
        $offset      = ($currentPage - 1) * $perPage;
        $pageItems   = array_slice($sorted, $offset, $perPage);

        $payments = new LengthAwarePaginator(
            $pageItems,
            count($sorted),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $thisMonth = Payment::whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');

        $totalCollected = Payment::sum('amount');
        $totalCount     = Payment::count();

        return view('staff.payments', compact(
            'members',
            'payments',
            'thisMonth',
            'totalCollected',
            'totalCount'
        ));
    }

    // ─────────────────────────────────────────
    //  Profile
    // ─────────────────────────────────────────

    public function profile()
    {
        $user = auth()->user();
        return view('staff.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:Male,Female,Other',
            'birthdate' => 'nullable|date',
            'address'   => 'nullable|string',
            'photo'     => 'nullable|image|max:3072',
        ]);

        $data = $request->only(['name', 'phone', 'gender', 'birthdate', 'address']);

        if ($request->hasFile('photo')) {
            if ($user->photo) Storage::disk('public')->delete($user->photo);
            $data['photo'] = $request->file('photo')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}