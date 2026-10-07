@extends('layouts.admin')
@section('title', $instructor->name . ' – Instructor Earnings – APEX')
@section('page_title', 'Payments')
@section('active_nav', 'payments')

@section('content')

@php
    $avg = $txnCount > 0 ? $totalEarned / $txnCount : 0;
    $initials = collect(explode(' ', trim($instructor->name)))
        ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp

<style>
    .ie-back { display:inline-flex; align-items:center; gap:8px; color:var(--text, inherit); font-size:13px; font-weight:500; text-decoration:none; margin-bottom:16px; padding:8px 14px;
        border:1px solid var(--border); border-radius:10px; background:var(--card, transparent); transition:border-color .15s, color .15s; }
    .ie-back:hover, .ie-back:focus-visible { color:var(--accent-2); border-color:var(--accent-2); outline:none; }

    /* Header */
    .ie-head { display:flex; align-items:center; gap:16px; margin-bottom:20px; padding:20px 24px;
        border:1px solid var(--border); border-radius:14px; background:var(--card, transparent); }
    .ie-avatar { width:56px; height:56px; border-radius:50%; flex-shrink:0; display:grid; place-items:center;
        font-size:20px; font-weight:700; color:var(--accent-2);
        background:color-mix(in srgb, var(--accent-2) 14%, transparent);
        border:1px solid color-mix(in srgb, var(--accent-2) 35%, transparent); }
    .ie-head h1 { font-size:26px; font-weight:700; line-height:1.2; margin:0 0 2px; }
    .ie-head p { color:var(--muted); font-size:14px; margin:0; }

    /* Summary */
    .ie-stats { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:14px; margin-bottom:28px; }
    .ie-stat { padding:18px 20px; border:1px solid var(--border); border-radius:12px; background:var(--card, transparent); }
    .ie-stat-label { font-size:13px; color:var(--muted); margin-bottom:6px; }
    .ie-stat-value { font-size:24px; font-weight:700; line-height:1.1; font-variant-numeric:tabular-nums; }
    .ie-stat--primary .ie-stat-value { color:var(--accent-2); }

    /* Table */
    .ie-table th, .ie-table td { vertical-align:middle; }
    .ie-num { text-align:right; }
    .ie-member { display:flex; flex-direction:column; gap:2px; min-width:160px; }
    .ie-member-name { font-weight:600; }
    .ie-member-meta { font-size:12px; color:var(--muted); }
    .ie-pill { display:inline-block; font-size:12px; padding:2px 10px; border-radius:999px; border:1px solid var(--border); color:var(--muted); white-space:nowrap; }
    .ie-receipt { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:12px; color:var(--muted); white-space:nowrap; }
    .ie-date { font-size:13px; color:var(--muted); white-space:nowrap; }
    .ie-amount { font-weight:700; color:var(--info); white-space:nowrap; font-variant-numeric:tabular-nums; }
    .ie-foot td { font-weight:700; border-top:2px solid var(--border); padding-top:16px; padding-bottom:16px; }
    .ie-foot .ie-amount { color:var(--accent-2); font-size:16px; }

    /* Empty state */
    .ie-empty { text-align:center; padding:56px 24px; }
    .ie-empty strong { display:block; font-size:16px; margin-bottom:4px; }
    .ie-empty span { color:var(--muted); font-size:14px; }

    @media (max-width: 720px) {
        .ie-head h1 { font-size:22px; }
        .ie-avatar { width:46px; height:46px; font-size:17px; }
        .ie-stats { grid-template-columns:1fr 1fr; }
        .ie-stat--primary { grid-column:1 / -1; }
        .ie-hide-sm { display:none; }
    }
</style>

<a href="{{ route('payments.index', ['tab' => 'instructor']) }}" class="ie-back">← Back to instructor earnings</a>

<div class="ie-head">
    <div class="ie-avatar" aria-hidden="true">{{ $initials }}</div>
    <div>
        <h1>{{ $instructor->name }}</h1>
        <p>Payments received from members</p>
    </div>
</div>

<div class="ie-stats">
    <div class="ie-stat ie-stat--primary">
        <div class="ie-stat-label">Total earned</div>
        <div class="ie-stat-value">₱{{ number_format($totalEarned, 0) }}</div>
    </div>
    <div class="ie-stat">
        <div class="ie-stat-label">Transactions</div>
        <div class="ie-stat-value">{{ number_format($txnCount) }}</div>
    </div>
    <div class="ie-stat">
        <div class="ie-stat-label">Average per transaction</div>
        <div class="ie-stat-value">₱{{ number_format($avg, 0) }}</div>
    </div>
</div>

<div class="section-header">
    <div class="section-title">Members who paid this instructor</div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="ie-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th class="ie-hide-sm">Receipt</th>
                    <th>Date</th>
                    <th class="ie-num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                <tr>
                    <td>
                        <div class="ie-member">
                            <span class="ie-member-name">{{ $t['member_name'] }}</span>
                            @if(!empty($t['fitness_plan']) || !empty($t['membership_type']))
                                <span class="ie-member-meta">
                                    {{ $t['fitness_plan'] ?? '' }}
                                    @if(!empty($t['membership_type']))
                                        <span class="ie-pill">{{ $t['membership_type'] }}</span>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="ie-receipt ie-hide-sm">{{ $t['receipt_number'] ?? '—' }}</td>
                    <td class="ie-date">
                        {{ !empty($t['payment_date']) ? \Carbon\Carbon::parse($t['payment_date'])->format('M d, Y') : '—' }}
                    </td>
                    <td class="ie-amount ie-num">₱{{ number_format($t['amount'], 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="ie-empty">
                            <strong>No transactions yet</strong>
                            <span>Payments from members assigned to this instructor will appear here.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($txnCount > 0)
            <tfoot>
                <tr class="ie-foot">
                    <td>Total</td>
                    <td class="ie-hide-sm"></td>
                    <td></td>
                    <td class="ie-amount ie-num">₱{{ number_format($totalEarned, 0) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection