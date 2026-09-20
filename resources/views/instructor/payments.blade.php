@extends('layouts.member')
@section('title', 'My Earnings – IRONFORGE')
@section('active', 'payments')

@section('content')

<style>
    :root {
        --bg: #0a0a0a;
        --surface: #111111;
        --surface2: #1a1a1a;
        --border: #2a2a2a;
        --accent: #ff2222;
        --accent-hover: #cc0000;
        --accent-glow: rgba(255,0,0,0.15);
        --text: #f0f0f0;
        --muted: #888888;
        --success: #ff4444;
        --warning: #ff6b35;
        --danger: #ff0000;
        --radius: 10px;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--accent);
    }
    .page-header h1 {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 700;
        margin-bottom: 6px;
        color: var(--text);
    }
    .page-header h1 span {
        color: var(--accent);
    }
    .page-header p {
        color: var(--muted);
        font-size: clamp(0.8rem, 1.2vw, 1rem);
    }

    /* Stats Grid */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    .stat-card:hover {
        transform: translateY(-3px);
        border-color: var(--accent);
        box-shadow: 0 8px 30px rgba(255,0,0,0.12);
    }
    .stat-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .stat-value {
        font-size: clamp(1.5rem, 3vw, 2.4rem);
        font-weight: 800;
        line-height: 1;
    }
    .stat-value.accent {
        color: var(--accent);
    }
    .stat-value.success {
        color: var(--success);
    }
    .stat-sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 4px;
    }

    /* Info Banner */
    .info-banner {
        background: rgba(255,0,0,0.05);
        border: 1px solid rgba(255,0,0,0.2);
        border-radius: var(--radius);
        padding: 14px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
    }
    .info-banner:hover {
        border-color: var(--accent);
        background: rgba(255,0,0,0.08);
    }
    .info-banner svg {
        width: 18px;
        height: 18px;
        stroke: var(--accent);
        fill: none;
        flex-shrink: 0;
    }
    .info-banner p {
        font-size: 13px;
        color: var(--muted);
        margin: 0;
    }

    /* Transactions Table */
    .transactions-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        transition: border-color 0.3s ease;
    }
    .transactions-card:hover {
        border-color: var(--accent);
    }
    .transactions-header {
        padding: 20px 24px;
        border-bottom: 2px solid var(--accent);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }
    .transactions-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--accent);
    }
    .transactions-icon {
        width: 20px;
        height: 20px;
        stroke: var(--accent);
        fill: none;
    }

    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }
    thead {
        background: var(--surface2);
        border-bottom: 1px solid var(--border);
    }
    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 10px;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
        white-space: nowrap;
    }
    th:last-child {
        text-align: left;
    }
    td {
        padding: 16px 20px;
        font-size: 13px;
        border-top: 1px solid var(--border);
        vertical-align: middle;
        color: var(--text);
    }
    tr:hover td {
        background: rgba(255,0,0,0.02);
    }

    .receipt-id {
        font-family: 'JetBrains Mono', monospace;
        font-size: 12px;
        color: var(--muted);
    }
    .member-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .member-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(255,0,0,0.2);
        flex-shrink: 0;
    }
    .member-avatar-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,0,0,0.08);
        border: 1px solid rgba(255,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--accent);
        flex-shrink: 0;
    }
    .member-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--text);
    }
    .plan-name {
        font-size: 14px;
        color: var(--text);
    }
    .duration-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(255,68,68,0.12);
        color: var(--accent);
        border: 1px solid rgba(255,68,68,0.15);
    }
    .fee-amount {
        text-align: right;
        font-weight: 700;
        color: var(--accent);
        font-size: 16px;
    }
    .payment-date {
        color: var(--muted);
        font-size: 13px;
        white-space: nowrap;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(255,68,68,0.15);
        color: var(--success);
        border: 1px solid rgba(255,68,68,0.15);
    }

    .empty-state {
        padding: 60px 24px;
        text-align: center;
        color: var(--muted);
    }
    .empty-state svg {
        display: block;
        margin: 0 auto 12px;
        opacity: 0.3;
        stroke: currentColor;
        fill: none;
    }
    .empty-state .empty-sub {
        font-size: 12px;
        margin-top: 8px;
        display: inline-block;
        color: var(--muted);
    }

    .pagination-wrap {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: var(--surface);
    }
    ::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: var(--accent-hover);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .stat-card {
        animation: fadeInUp 0.4s ease forwards;
    }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.1s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .info-banner {
        animation: fadeInUp 0.4s ease 0.2s forwards;
        opacity: 0;
    }
    .transactions-card {
        animation: fadeInUp 0.4s ease 0.25s forwards;
        opacity: 0;
    }

    /* ===== RESPONSIVE ===== */

    @media (max-width: 1024px) {
        .stat-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        table {
            min-width: 600px;
        }
    }

    @media (max-width: 768px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .stat-card {
            padding: 18px;
        }
        .stat-value {
            font-size: clamp(1.2rem, 2.5vw, 1.8rem);
        }
        .transactions-header {
            padding: 16px 18px;
        }
        .transactions-title {
            font-size: 15px;
        }
        th, td {
            padding: 10px 14px;
            font-size: 12px;
        }
        table {
            min-width: 500px;
        }
        .fee-amount {
            font-size: 14px;
        }
        .member-name {
            font-size: 13px;
        }
        .info-banner {
            padding: 12px 16px;
            flex-wrap: wrap;
        }
        .info-banner p {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .stat-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .stat-card {
            padding: 14px;
        }
        .stat-label {
            font-size: 9px;
            margin-bottom: 4px;
        }
        .stat-value {
            font-size: 1.2rem;
        }
        .stat-sub {
            font-size: 10px;
        }
        .transactions-header {
            padding: 12px 14px;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .transactions-title {
            font-size: 14px;
        }
        th, td {
            padding: 8px 10px;
            font-size: 11px;
        }
        table {
            min-width: 400px;
        }
        .receipt-id {
            font-size: 10px;
        }
        .member-avatar,
        .member-avatar-placeholder {
            width: 26px;
            height: 26px;
            font-size: 10px;
        }
        .member-name {
            font-size: 12px;
        }
        .fee-amount {
            font-size: 13px;
        }
        .duration-badge {
            font-size: 10px;
            padding: 2px 8px;
        }
        .status-badge {
            font-size: 10px;
            padding: 2px 8px;
        }
        .payment-date {
            font-size: 11px;
        }
        .info-banner {
            padding: 10px 12px;
            gap: 8px;
        }
        .info-banner svg {
            width: 14px;
            height: 14px;
        }
        .info-banner p {
            font-size: 11px;
        }
        .empty-state {
            padding: 40px 16px;
        }
        .empty-state svg {
            width: 32px;
            height: 32px;
        }
        .pagination-wrap {
            padding: 12px 14px;
        }
    }

    @media (max-width: 360px) {
        .stat-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        table {
            min-width: 350px;
        }
        th, td {
            padding: 6px 8px;
            font-size: 10px;
        }
        .receipt-id {
            font-size: 9px;
        }
        .member-avatar,
        .member-avatar-placeholder {
            width: 22px;
            height: 22px;
            font-size: 9px;
        }
        .member-name {
            font-size: 11px;
        }
        .fee-amount {
            font-size: 12px;
        }
    }
</style>

<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <h1>My <span>Earnings</span></h1>
        <p>Coach subscription fees automatically allocated from member subscriptions</p>
    </div>

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">This Month</div>
            <div class="stat-value accent">₱{{ number_format($thisMonthTotal, 0) }}</div>
            <div class="stat-sub">{{ now()->format('F Y') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Earned</div>
            <div class="stat-value success">₱{{ number_format($totalEarned, 0) }}</div>
            <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Transactions</div>
            <div class="stat-value" style="color:var(--text);">{{ $payments->total() }}</div>
            <div class="stat-sub">Total coach fee payments</div>
        </div>
    </div>

    {{-- Info banner --}}
    <div class="info-banner">
        <svg viewBox="0 0 24 24" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <p>
            These earnings are automatically recorded when members subscribe with you as their instructor.
            Coach fees are separate from the gym's membership fee.
        </p>
    </div>

    {{-- Transactions table --}}
    <div class="transactions-card">
        <div class="transactions-header">
            <div class="transactions-title">Coach Fee Transactions</div>
            <svg class="transactions-icon" viewBox="0 0 24 24" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Member</th>
                        <th>Plan</th>
                        <th>Duration</th>
                        <th style="text-align:right;">Coach Fee</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    @php
                        $pPhoto     = $payment->member?->user?->photo ?? $payment->member?->photo ?? null;
                        $memberName = $payment->member?->name ?? '—';
                    @endphp
                    <tr>
                        <td class="receipt-id">{{ $payment->receipt_number }}</td>
                        <td>
                            <div class="member-cell">
                                @if($pPhoto)
                                    <img src="{{ asset('storage/'.$pPhoto) }}" class="member-avatar"/>
                                @else
                                    <div class="member-avatar-placeholder">
                                        {{ strtoupper(substr($memberName, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="member-name">{{ $memberName }}</span>
                            </div>
                        </td>
                        <td><span class="plan-name">{{ $payment->fitness_plan ?? '—' }}</span></td>
                        <td>
                            <span class="duration-badge">
                                {{ $payment->membership_type ?? '—' }}
                            </span>
                        </td>
                        <td class="fee-amount">₱{{ number_format($payment->amount, 0) }}</td>
                        <td class="payment-date">{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td>
                            <span class="status-badge">
                                {{ $payment->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg width="40" height="40" stroke-width="1.5" viewBox="0 0 24 24">
                                    <rect x="1" y="4" width="22" height="16" rx="2"/>
                                    <line x1="1" y1="10" x2="23" y2="10"/>
                                </svg>
                                No coach fee payments received yet.
                                <br>
                                <span class="empty-sub">
                                    Members who select you as their instructor will automatically generate earnings here.
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="pagination-wrap">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>

@endsection