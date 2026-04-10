<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query();

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

        $members = $query->latest()->paginate(15);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

   public function store(Request $request)
{
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
        'end_date'        => 'required|date',
        'fee'             => 'required|numeric|min:0',
        'photo'           => 'nullable|image|max:3072',
    ]);

    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('members', 'public');
    }

    $start    = \Carbon\Carbon::parse($request->start_date);
    $end_date = $request->end_date ?? match($request->membership_type) {
        'Monthly'     => $start->copy()->addMonth()->toDateString(),
        'Quarterly'   => $start->copy()->addMonths(3)->toDateString(),
        'Semi-Annual' => $start->copy()->addMonths(6)->toDateString(),
        'Annual'      => $start->copy()->addYear()->toDateString(),
    };

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
    ]);

    // ← Redirect to receipt page instead of members list
    return redirect()->route('members.receipt', $member)
                     ->with('success', 'Member registered successfully!');
}

    public function show(Member $member)
    {
        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|unique:members,email,' . $member->id,
            'phone'           => 'nullable|string|max:20',
            'gender'          => 'nullable|in:Male,Female,Other',
            'birthdate'       => 'nullable|date',
            'address'         => 'nullable|string',
            'membership_type' => 'required|in:Trial,Monthly,Yearly',
            'status'          => 'required|in:Active,Expired',
            'start_date'      => 'required|date',
            'fee'             => 'nullable|numeric|min:0',
            'photo'           => 'nullable|image|max:3072',
        ]);

        // Handle photo upload
        $photoPath = $member->photo;
        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $photoPath = $request->file('photo')->store('members', 'public');
        }

        // Auto-calculate end_date
        $start    = Carbon::parse($request->start_date);
        $end_date = match($request->membership_type) {
            'Trial'   => $start->copy()->addDays(7)->toDateString(),
            'Monthly' => $start->copy()->addMonth()->toDateString(),
            'Yearly'  => $start->copy()->addYear()->toDateString(),
        };

        $member->update([
            'name'            => $request->first_name . ' ' . $request->last_name,
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'gender'          => $request->gender,
            'birthdate'       => $request->birthdate,
            'address'         => $request->address,
            'membership_type' => $request->membership_type,
            'status'          => $request->status,
            'start_date'      => $start->toDateString(),
            'end_date'        => $end_date,
            'fee'             => $request->fee ?? $member->fee,
            'photo'           => $photoPath,
        ]);

        return redirect()->route('members.index')
            ->with('success', 'Member updated successfully!');
    }

    public function destroy(Member $member)
    {
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }
        $member->delete();
        return redirect()->route('members.index')
            ->with('success', 'Member deleted.');
    }

    public function receipt(Member $member)
{
    return view('members.receipt', compact('member'));
}

}