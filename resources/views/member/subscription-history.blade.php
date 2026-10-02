@extends('layouts.member')
@section('title', 'Subscription History – APEX')
@section('active', 'subscriptions')

@section('content')
<style>
    .history-container { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
    .page-header { margin-bottom: 24px; }
    .page-header h1 { font-size: 32px; font-weight: 700; margin-bottom: 6px; }
    .page-header p { color: var(--muted); font-size: 14px; }
    .stats-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 22px; }
    .label { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .value { font-size: 30px; font-weight: 700; }
    .accent { color: var(--accent); }
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 14px 20px; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); background: var(--surface2); border-bottom: 2px solid var(--accent); }
    td { padding: 16px 20px; border-top: 1px solid var(--border); }
    .amount { font-weight: 700; color: var(--accent); }
    .muted { color: var(--muted); }
    .empty-state { padding: 48px; text-align: center; color: var(--muted); }
    @media (max-width: 768px) {
        .history-container { padding: 0 12px; }
        .page-header h1 { font-size: 26px; }
        .stats-grid { grid-template-columns: 1fr; }
        th, td { padding: 12px 14px; }
    }
</style>

<div class="history-container">
    <div class="page-header">
        <h1>Subscription History</h1>
        <p>Review your gym subscriptions, plan changes, and payments.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Records</div>
            <div class="value">{{ $payments->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Last Plan</div>
            <div class="value accent">{{ $member?->fitness_plan ?? '—' }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Paid</div>
            <div class="value accent">₱{{ number_format($payments->sum('amount'), 0) }}</div>
        </div>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Plan</th>
                    <th>Duration</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '—' }}</td>
                        <td>{{ $payment->fitness_plan ?? '–' }}</td>
                        <td>{{ $payment->membership_type ?? '—' }}</td>
                        <td class="muted">{{ $payment->payment_type === 'coach_fee' ? 'Coach' : 'Gym' }}</td>
                        <td class="amount">₱{{ number_format($payment->amount, 0) }}</td>
                        <td><span class="pill" style="display:inline-block;padding:4px 10px;border-radius:999px;background:rgba(74,222,128,0.15);color:#4ade80;font-size:12px;font-weight:600;">{{ strtoupper($payment->status ?? 'Paid') }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">No subscription records have been created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
