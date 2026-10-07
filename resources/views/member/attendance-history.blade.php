@extends('layouts.member')
@section('title', 'Attendance History – APEX')
@section('active', 'attendance')

@section('content')
<style>
    .history-container { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
    .page-header { margin-bottom: 24px; }
    .page-header h1 { font-size: 32px; font-weight: 700; margin-bottom: 6px; }
    .page-header p { color: var(--muted); font-size: 14px; }
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 14px 20px; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); background: var(--surface2); border-bottom: 2px solid var(--accent); }
    td { padding: 16px 20px; border-top: 1px solid var(--border); }
    .muted { color: var(--muted); }
    .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; background: rgba(74,222,128,0.15); color: #4ade80; font-size: 12px; font-weight: 600; }
    .empty-state { padding: 48px; text-align: center; color: var(--muted); }
    @media (max-width: 768px) {
        .history-container { padding: 0 12px; }
        .page-header h1 { font-size: 26px; }
        th, td { padding: 12px 14px; }
    }
</style>

<div class="history-container">
    <div class="page-header">
        <h1>Attendance History</h1>
        <p>Track your recent gym visits and check-in records.</p>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Plan</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Duration</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendance as $log)
                    <tr>
                        <td>{{ $log->date ? $log->date->format('M d, Y') : '—' }}</td>
                        <td>{{ $member?->fitness_plan ?? 'Member Plan' }}</td>
                        <td>{{ $log->time_in ? $log->time_in->format('h:i A') : '—' }}</td>
                        <td>{{ $log->time_out ? $log->time_out->format('h:i A') : '—' }}</td>
                        <td><span class="pill">{{ $log->duration_minutes ? floor($log->duration_minutes / 60) . 'h ' . ($log->duration_minutes % 60) . 'm' : '—' }}</span></td>
                        <td class="muted">{{ $log->entry_method ?? 'QR' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">No attendance records found yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
