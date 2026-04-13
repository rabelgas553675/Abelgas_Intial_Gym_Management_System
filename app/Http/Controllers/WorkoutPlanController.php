<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\WorkoutPlan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorkoutPlanController extends Controller
{
    // Instructor: show calendar scheduler
    public function index(Request $request)
    {
        $instructor = auth()->user();
        $month = $request->get('month', now()->month);
        $year  = $request->get('year',  now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $plans = WorkoutPlan::where('instructor_id', $instructor->id)
            ->whereBetween('scheduled_date', [$start, $end])
            ->with('member')
            ->get()
            ->groupBy(fn($p) => $p->scheduled_date->format('Y-m-d'));

        $members = Member::where('instructor_id', $instructor->id)->get();

        return view('instructor.workout.index', compact('plans', 'members', 'month', 'year', 'start'));
    }

    // Instructor: store new plan
    public function store(Request $request)
    {
        $request->validate([
            'member_id'      => 'required|exists:members,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'scheduled_date' => 'required|date',
            'category'       => 'nullable|string|max:100',
            'intensity'      => 'required|in:Light,Moderate,Intense',
            'exercises'      => 'nullable|array',
            'exercises.*'    => 'nullable|string',
        ]);

        // Verify member belongs to this instructor
        $member = Member::where('id', $request->member_id)
                        ->where('instructor_id', auth()->id())
                        ->firstOrFail();

        $exercises = collect($request->exercises ?? [])
            ->filter()
            ->values()
            ->toArray();

        WorkoutPlan::create([
            'instructor_id'  => auth()->id(),
            'member_id'      => $member->id,
            'title'          => $request->title,
            'description'    => $request->description,
            'scheduled_date' => $request->scheduled_date,
            'category'       => $request->category,
            'intensity'      => $request->intensity,
            'exercises'      => $exercises,
        ]);

        return back()->with('success', 'Workout plan created successfully!');
    }

    // Instructor: update plan
    public function update(Request $request, WorkoutPlan $workoutPlan)
    {
        if ($workoutPlan->instructor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'scheduled_date' => 'required|date',
            'category'       => 'nullable|string|max:100',
            'intensity'      => 'required|in:Light,Moderate,Intense',
            'exercises'      => 'nullable|array',
            'exercises.*'    => 'nullable|string',
        ]);

        $exercises = collect($request->exercises ?? [])
            ->filter()
            ->values()
            ->toArray();

        $workoutPlan->update([
            'title'          => $request->title,
            'description'    => $request->description,
            'scheduled_date' => $request->scheduled_date,
            'category'       => $request->category,
            'intensity'      => $request->intensity,
            'exercises'      => $exercises,
        ]);

        return back()->with('success', 'Workout plan updated!');
    }

    // Instructor: delete plan
    public function destroy(WorkoutPlan $workoutPlan)
    {
        if ($workoutPlan->instructor_id !== auth()->id()) {
            abort(403);
        }
        $workoutPlan->delete();
        return back()->with('success', 'Workout plan deleted.');
    }

    // Member: view their schedule
    public function memberSchedule(Request $request)
    {
        $user   = auth()->user();
        $member = $user->memberProfile;

        if (!$member) {
            return view('member.workout.index', ['plans' => collect(), 'month' => now()->month, 'year' => now()->year, 'start' => now()->startOfMonth(), 'grouped' => collect()]);
        }

        $month = $request->get('month', now()->month);
        $year  = $request->get('year',  now()->year);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $plans = WorkoutPlan::where('member_id', $member->id)
            ->whereBetween('scheduled_date', [$start, $end])
            ->with('instructor')
            ->orderBy('scheduled_date')
            ->get();

        $grouped = $plans->groupBy(fn($p) => $p->scheduled_date->format('Y-m-d'));

        return view('member.workout.index', compact('plans', 'grouped', 'month', 'year', 'start', 'member'));
    }
}