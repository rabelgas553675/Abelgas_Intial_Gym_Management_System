<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Models\WorkoutPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $allowedTypes = $this->allowedTypesForUser($user);

        $selectedType = $request->query('type', $allowedTypes[0] ?? 'attendance');
        if (!in_array($selectedType, $allowedTypes, true)) {
            $selectedType = $allowedTypes[0] ?? 'attendance';
        }

        $range = in_array($request->query('range'), ['day', 'week', 'month'], true)
            ? $request->query('range')
            : 'month';

        $selectedDate = $this->normalizeSelectedDate($range, $request->query('date'));
        $window = $this->getDateRange($range, $selectedDate);
        $report = $this->buildReport($selectedType, $window, $user);

        return view('reports.index', compact('user', 'allowedTypes', 'selectedType', 'range', 'selectedDate', 'report'));
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $allowedTypes = $this->allowedTypesForUser($user);

        $type = $request->query('type', $allowedTypes[0] ?? 'attendance');
        if (!in_array($type, $allowedTypes, true)) {
            $type = $allowedTypes[0] ?? 'attendance';
        }

        $range = in_array($request->query('range'), ['day', 'week', 'month'], true)
            ? $request->query('range')
            : 'month';

        $selectedDate = $this->normalizeSelectedDate($range, $request->query('date'));
        $window = $this->getDateRange($range, $selectedDate);
        $report = $this->buildReport($type, $window, $user);
        $format = $request->query('format', 'excel');

        if ($format === 'pdf') {
            return view('reports.export-pdf', compact('user', 'type', 'range', 'report'));
        }

        $csv = $this->buildCsv($type, $report);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $type . '-' . $range . '-report.csv"');
    }

    private function allowedTypesForUser(User $user): array
    {
        if ($user->isAdmin()) {
            return ['payment', 'attendance', 'workout', 'member'];
        }

        if ($user->isStaff()) {
            return ['payment', 'attendance', 'member'];
        }

        if ($user->isInstructor()) {
            return ['attendance', 'workout'];
        }

        return [];
    }

    private function normalizeSelectedDate(string $range, ?string $dateInput): string
    {
        if ($range === 'week') {
            if (!empty($dateInput) && preg_match('/^\d{4}-W\d{2}$/', $dateInput)) {
                return $dateInput;
            }

            return Carbon::parse($dateInput ?: now())->format('o-\WW');
        }

        if ($range === 'month') {
            if (!empty($dateInput) && preg_match('/^\d{4}-\d{2}$/', $dateInput)) {
                return $dateInput;
            }

            return Carbon::parse($dateInput ?: now())->format('Y-m');
        }

        return Carbon::parse($dateInput ?: now())->format('Y-m-d');
    }

    private function getDateRange(string $range, ?string $selectedDate = null): array
    {
        $selectedDate = $this->normalizeSelectedDate($range, $selectedDate);

        return match ($range) {
            'day' => [
                Carbon::parse($selectedDate)->startOfDay()->toDateString(),
                Carbon::parse($selectedDate)->endOfDay()->toDateString(),
            ],
            'week' => [
                Carbon::parse($selectedDate)->startOfWeek()->toDateString(),
                Carbon::parse($selectedDate)->endOfWeek()->toDateString(),
            ],
            default => [
                Carbon::createFromFormat('Y-m', $selectedDate)->startOfMonth()->toDateString(),
                Carbon::createFromFormat('Y-m', $selectedDate)->endOfMonth()->toDateString(),
            ],
        };
    }

    private function buildReport(string $type, array $window, User $user): array
    {
        return match ($type) {
            'payment' => $this->paymentReport($window, $user),
            'attendance' => $this->attendanceReport($window, $user),
            'workout' => $this->workoutReport($window, $user),
            'member' => $this->memberReport($window, $user),
            default => $this->attendanceReport($window, $user),
        };
    }

    private function buildChartData(string $type, array $window, User $user): array
    {
        $start = Carbon::parse($window[0]);
        $end = Carbon::parse($window[1]);
        $labels = [];
        $values = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format($start->diffInDays($end) > 12 ? 'M d' : 'M d');
            $cursor->addDay();
        }

        if ($type === 'payment') {
            $query = Payment::query()->whereBetween('payment_date', $window);
            if ($user->isInstructor()) {
                $query->where('payment_type', 'coach_fee')->where('instructor_id', $user->id);
            }

            $totals = $query->get()->groupBy(fn ($payment) => $payment->payment_date->format('Y-m-d'))
                ->map(fn ($rows) => (float) $rows->sum('amount'));

            foreach ($start->daysUntil($end->copy()->addDay()) as $date) {
                $values[] = (float) ($totals[$date->format('Y-m-d')] ?? 0);
            }
        } elseif ($type === 'attendance') {
            $query = Attendance::query()->whereBetween('date', $window);
            if ($user->isInstructor()) {
                $memberIds = Member::where('instructor_id', $user->id)->pluck('id');
                $query->whereIn('member_id', $memberIds);
            }

            $totals = $query->get()->groupBy(fn ($record) => $record->date->format('Y-m-d'))
                ->map(fn ($rows) => $rows->count());

            foreach ($start->daysUntil($end->copy()->addDay()) as $date) {
                $values[] = (float) ($totals[$date->format('Y-m-d')] ?? 0);
            }
        } elseif ($type === 'workout') {
            $query = WorkoutPlan::query()->whereBetween('scheduled_date', $window);
            if ($user->isInstructor()) {
                $query->where('instructor_id', $user->id);
            }

            $totals = $query->get()->groupBy(fn ($plan) => $plan->scheduled_date->format('Y-m-d'))
                ->map(fn ($rows) => $rows->count());

            foreach ($start->daysUntil($end->copy()->addDay()) as $date) {
                $values[] = (float) ($totals[$date->format('Y-m-d')] ?? 0);
            }
        } else {
            $totals = Member::query()->whereBetween('created_at', $window)->get()->groupBy(fn ($member) => $member->created_at->format('Y-m-d'))
                ->map(fn ($rows) => $rows->count());

            foreach ($start->daysUntil($end->copy()->addDay()) as $date) {
                $values[] = (float) ($totals[$date->format('Y-m-d')] ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function paymentReport(array $window, User $user): array
    {
        $query = Payment::with(['member', 'instructor', 'processedBy'])
            ->whereBetween('payment_date', $window);

        if ($user->isInstructor()) {
            $query->where('payment_type', 'coach_fee')
                ->where('instructor_id', $user->id);
        }

        $payments = $query->orderByDesc('payment_date')->get();

        $stats = [
            [
                'label' => 'Total Collection',
                'value' => '₱' . number_format((float) $payments->sum('amount'), 0),
                'tone' => 'green',
            ],
            [
                'label' => 'Transactions',
                'value' => (string) $payments->count(),
                'tone' => 'blue',
            ],
            [
                'label' => 'Average',
                'value' => '₱' . number_format($payments->count() ? ($payments->sum('amount') / $payments->count()) : 0, 0),
                'tone' => 'orange',
            ],
        ];

        $rows = $payments->map(function ($payment) {
            return [
                'name' => $payment->member?->name ?? 'Unassigned member',
                'type' => ucfirst(str_replace('_', ' ', $payment->payment_type ?? 'payment')),
                'date' => $payment->payment_date ? $payment->payment_date->format('M d, Y') : '—',
                'method' => $payment->method ?? 'Cash',
                'amount' => '₱' . number_format((float) $payment->amount, 0),
            ];
        })->all();

        return [
            'title' => 'Payment report',
            'subtitle' => 'Generated for the selected time frame.',
            'stats' => $stats,
            'rows' => $rows,
            'chart' => $this->buildChartData('payment', $window, $user),
            'empty' => $payments->isEmpty(),
        ];
    }

    private function memberReport(array $window, User $user): array
    {
        $allMembers = Member::query()->get();
        $newMembers = Member::query()
            ->whereBetween('created_at', $window)
            ->orderByDesc('created_at')
            ->get();

        if ($newMembers->isEmpty() && !$allMembers->isEmpty()) {
            $newMembers = $allMembers->sortByDesc('created_at')->take(10);
        }

        $stats = [
            [
                'label' => 'Total Members',
                'value' => (string) $allMembers->count(),
                'tone' => 'green',
            ],
            [
                'label' => 'New This Period',
                'value' => (string) $newMembers->count(),
                'tone' => 'blue',
            ],
            [
                'label' => 'Active Members',
                'value' => (string) $allMembers->where('status', 'Active')->count(),
                'tone' => 'orange',
            ],
        ];

        $rows = $newMembers->map(function ($member) {
            return [
                'name' => $member->name ?? 'Unnamed member',
                'email' => $member->email ?? '—',
                'status' => $member->status ?? 'Active',
                'plan' => $member->fitness_plan ?? '—',
                'date' => $member->created_at ? $member->created_at->format('M d, Y') : '—',
            ];
        })->all();

        return [
            'title' => 'Member report',
            'subtitle' => 'Current membership totals and new member sign-ups.',
            'stats' => $stats,
            'rows' => $rows,
            'chart' => $this->buildChartData('member', $window, $user),
            'empty' => $allMembers->isEmpty() && $newMembers->isEmpty(),
        ];
    }

    private function attendanceReport(array $window, User $user): array
    {
        $query = Attendance::with(['member', 'user'])
            ->whereBetween('date', $window);

        if ($user->isInstructor()) {
            $memberIds = Member::where('instructor_id', $user->id)->pluck('id');
            $query->whereIn('member_id', $memberIds);
        }

        $records = $query->orderByDesc('date')->get();
        $totalMinutes = (int) $records->sum('duration_minutes');

        $stats = [
            [
                'label' => 'Total Check-ins',
                'value' => (string) $records->count(),
                'tone' => 'green',
            ],
            [
                'label' => 'Unique Members',
                'value' => (string) $records->pluck('member_id')->filter()->unique()->count(),
                'tone' => 'blue',
            ],
            [
                'label' => 'Avg Duration',
                'value' => $records->count() ? gmdate('H\h i\m', (int) floor($totalMinutes / $records->count() * 60)) : '0h 0m',
                'tone' => 'orange',
            ],
        ];

        $rows = $records->map(function ($record) {
            $memberName = $record->member?->name ?? 'Staff / Unknown';
            $roleLabel = $record->member ? 'Member' : ($record->user?->role ?? 'Staff');

            return [
                'name' => $memberName,
                'role' => $roleLabel,
                'date' => $record->date ? $record->date->format('M d, Y') : '—',
                'time_in' => $record->time_in ? $record->time_in->format('h:i A') : '—',
                'time_out' => $record->time_out ? $record->time_out->format('h:i A') : '—',
                'duration' => $record->duration_minutes ? gmdate('H\h i\m', $record->duration_minutes * 60) : '0h 0m',
            ];
        })->all();

        return [
            'title' => 'Attendance report',
            'subtitle' => 'Daily attendance and session records.',
            'stats' => $stats,
            'rows' => $rows,
            'chart' => $this->buildChartData('attendance', $window, $user),
            'empty' => $records->isEmpty(),
        ];
    }

    private function workoutReport(array $window, User $user): array
    {
        $query = WorkoutPlan::with(['member', 'instructor'])
            ->whereBetween('scheduled_date', $window);

        if ($user->isInstructor()) {
            $query->where('instructor_id', $user->id);
        }

        $plans = $query->orderByDesc('scheduled_date')->get();

        $stats = [
            [
                'label' => 'Sessions',
                'value' => (string) $plans->count(),
                'tone' => 'green',
            ],
            [
                'label' => 'Completed',
                'value' => (string) $plans->where('is_completed', true)->count(),
                'tone' => 'blue',
            ],
            [
                'label' => 'Pending',
                'value' => (string) $plans->where('is_completed', false)->count(),
                'tone' => 'orange',
            ],
        ];

        $rows = $plans->map(function ($plan) {
            return [
                'name' => $plan->member?->name ?? 'Unassigned member',
                'title' => $plan->title ?? 'Workout session',
                'date' => $plan->scheduled_date ? $plan->scheduled_date->format('M d, Y') : '—',
                'status' => $plan->is_completed ? 'Completed' : 'Pending',
                'instructor' => $plan->instructor?->name ?? '—',
            ];
        })->all();

        return [
            'title' => 'Workout session report',
            'subtitle' => 'Exercise schedules and completion status.',
            'stats' => $stats,
            'rows' => $rows,
            'chart' => $this->buildChartData('workout', $window, $user),
            'empty' => $plans->isEmpty(),
        ];
    }

    private function buildCsv(string $type, array $report): string
    {
        $columns = match ($type) {
            'payment' => ['Member', 'Type', 'Date', 'Method', 'Amount'],
            'attendance' => ['Name', 'Role', 'Date', 'Time In', 'Time Out', 'Duration'],
            'workout' => ['Member', 'Session', 'Date', 'Status', 'Instructor'],
            'member' => ['Name', 'Email', 'Date Joined', 'Plan', 'Status'],
            default => ['Name', 'Details'],
        };

        $buffer = fopen('php://temp', 'r+');
        fputcsv($buffer, $columns);

        foreach ($report['rows'] as $row) {
            if ($type === 'payment') {
                fputcsv($buffer, [$row['name'], $row['type'], $row['date'], $row['method'], $row['amount']]);
                continue;
            }

            if ($type === 'attendance') {
                fputcsv($buffer, [$row['name'], $row['role'], $row['date'], $row['time_in'], $row['time_out'], $row['duration']]);
                continue;
            }

            if ($type === 'member') {
                fputcsv($buffer, [$row['name'], $row['email'], $row['date'], $row['plan'], $row['status']]);
                continue;
            }

            fputcsv($buffer, [$row['name'], $row['title'], $row['date'], $row['status'], $row['instructor']]);
        }

        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return $csv ?: "";
    }
}
