@extends('layouts.member')
@section('title', 'Payment History – APEX')
@section('active', 'payments')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .payments-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .page-header p {
        color: var(--muted);
        font-size: 14px;
    }

    /* Summary Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 24px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card .label {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .stat-card .value {
        font-size: 36px;
        font-weight: 700;
    }

    .stat-card .value.accent {
        color: var(--accent);
    }

    .stat-card .value.muted {
        color: var(--muted);
    }

    .stat-card .sub {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .stat-card .sub-value {
        font-size: 28px;
        font-weight: 700;
    }

    /* Table */
    .table-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    table th {
        padding: 14px 20px;
        text-align: left;
        font-size: 12px;
        color: var(--muted);
        font-weight: 600;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.02);
        white-space: nowrap;
        border-bottom: 1px solid var(--border);
    }

    table td {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        vertical-align: middle;
    }

    table tr:first-child td {
        border-top: none;
    }

    table tr {
        transition: .15s;
    }

    table tr:hover {
        background: rgba(255, 255, 255, 0.015);
    }

    /* Receipt cell */
    .receipt-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .receipt-cell svg {
        width: 14px;
        height: 14px;
        stroke: var(--muted);
        fill: none;
        stroke-width: 2;
        flex-shrink: 0;
    }

    .receipt-cell .id {
        font-family: monospace;
        font-size: 12px;
        color: var(--muted);
        word-break: break-all;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
    }

    .status-badge.pending {
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
    }

    .status-badge.failed {
        background: rgba(248, 113, 113, 0.15);
        color: #f87171;
    }

    .status-badge.cancelled {
        background: rgba(248, 113, 113, 0.15);
        color: #f87171;
    }

    /* Amounts */
    .amount {
        font-size: 14px;
        font-weight: 700;
        color: var(--accent);
        white-space: nowrap;
    }

    .amount-muted {
        font-size: 14px;
        color: var(--muted);
        white-space: nowrap;
    }

    .amount-text {
        font-size: 14px;
        font-weight: 600;
    }

    .coach-fee {
        font-size: 14px;
        color: var(--accent);
        white-space: nowrap;
    }

    .coach-fee.empty {
        color: var(--muted);
    }

    /* Action button */
    .action-link {
        color: var(--muted);
        text-decoration: none;
        transition: color 0.2s;
        display: inline-flex;
        align-items: center;
        margin-right: 12px;
    }

    .action-link:hover {
        color: var(--text);
    }

    .action-link svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
    }

    /* Empty State */
    .empty-state {
        padding: 48px;
        text-align: center;
        color: var(--muted);
    }

    /* Info Section */
    .info-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 24px;
    }

    .info-section .title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .info-section ul {
        list-style: none;
        display: grid;
        gap: 8px;
        padding: 0;
        margin: 0;
    }

    .info-section ul li {
        font-size: 13px;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-section ul li .bullet {
        color: var(--accent);
    }

    .info-section ul li strong {
        color: var(--text);
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .stats-grid {
            gap: 12px;
        }

        .stat-card .value {
            font-size: 30px;
        }

        .stat-card .sub-value {
            font-size: 24px;
        }
    }

    @media (max-width: 768px) {
        .payments-container {
            padding: 0 12px;
        }

        .page-header h1 {
            font-size: 26px;
        }

        .page-header p {
            font-size: 13px;
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .stat-card {
            padding: 16px 14px;
            border-radius: 12px;
        }

        .stat-card .label {
            font-size: 11px;
            margin-bottom: 4px;
        }

        .stat-card .value {
            font-size: 24px;
        }

        .stat-card .sub-value {
            font-size: 20px;
        }

        table {
            min-width: 700px;
        }

        table th,
        table td {
            padding: 12px 14px;
            font-size: 13px;
        }

        table th {
            font-size: 11px;
        }

        .receipt-cell .id {
            font-size: 11px;
        }

        .amount,
        .amount-muted,
        .coach-fee,
        .amount-text {
            font-size: 13px;
        }

        .status-badge {
            font-size: 11px;
            padding: 3px 10px;
        }

        .info-section {
            padding: 18px 16px;
        }

        .info-section ul li {
            font-size: 12px;
        }

        .empty-state {
            padding: 32px 16px;
            font-size: 13px;
        }
    }

    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-card {
            padding: 14px 12px;
        }

        .stat-card .value {
            font-size: 22px;
        }

        .stat-card .sub-value {
            font-size: 18px;
        }

        .stat-card .label {
            font-size: 10px;
        }

        .stat-card:last-child {
            grid-column: span 2;
        }
    }

    @media (max-width: 480px) {
        .payments-container {
            padding: 0 8px;
        }

        .page-header h1 {
            font-size: 22px;
        }

        .page-header p {
            font-size: 12px;
        }

        .stats-grid {
            gap: 6px;
        }

        .stat-card {
            padding: 12px 10px;
            border-radius: 10px;
        }

        .stat-card .label {
            font-size: 9px;
            margin-bottom: 2px;
        }

        .stat-card .value {
            font-size: 18px;
        }

        .stat-card .sub {
            font-size: 11px;
        }

        .stat-card .sub-value {
            font-size: 16px;
        }

        table {
            min-width: 560px;
        }

        table th,
        table td {
            padding: 10px 10px;
            font-size: 12px;
        }

        table th {
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 10px 10px;
        }

        .receipt-cell .id {
            font-size: 10px;
        }

        .receipt-cell svg {
            width: 12px;
            height: 12px;
        }

        .amount,
        .amount-muted,
        .coach-fee,
        .amount-text {
            font-size: 12px;
        }

        .status-badge {
            font-size: 10px;
            padding: 2px 8px;
        }

        .action-link svg {
            width: 16px;
            height: 16px;
        }

        .info-section {
            padding: 14px 12px;
            border-radius: 12px;
        }

        .info-section .title {
            font-size: 13px;
            margin-bottom: 10px;
        }

        .info-section ul li {
            font-size: 11px;
            gap: 6px;
        }

        .info-section ul li .bullet {
            font-size: 14px;
        }

        .empty-state {
            padding: 24px 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 360px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }

        .stat-card {
            padding: 10px 8px;
            border-radius: 8px;
        }

        .stat-card .value {
            font-size: 16px;
        }

        .stat-card .sub-value {
            font-size: 14px;
        }

        .stat-card .label {
            font-size: 8px;
        }

        table {
            min-width: 480px;
        }

        table th,
        table td {
            padding: 8px 8px;
            font-size: 11px;
        }

        .amount,
        .amount-muted,
        .coach-fee,
        .amount-text {
            font-size: 11px;
        }

        .status-badge {
            font-size: 9px;
            padding: 2px 6px;
        }

        .receipt-cell .id {
            font-size: 9px;
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>

<div class="payments-container">

    {{-- Header --}}
    <div class="page-header">
        <h1>Payment History</h1>
        <p>View and download your payment receipts</p>
    </div>

    {{-- Summary Cards --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Payments</div>
            <div class="value">{{ $payments->count() }}</div>
        </div>

        <div class="stat-card">
            <div class="label">Total Paid</div>
            <div class="value accent">
                ₱{{ number_format($payments->sum(fn($p) => $p->amount + ($p->coach_fee_amount ?? 0)), 0) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="label">Last Payment</div>
            @if($payments->first())
                @php
                    $last      = $payments->first();
                    $lastTotal = $last->amount + ($last->coach_fee_amount ?? 0);
                @endphp
                <div class="sub">{{ $last->payment_date->format('M d, Y') }}</div>
                <div class="sub-value">₱{{ number_format($lastTotal, 0) }}</div>
            @else
                <div class="value muted">—</div>
            @endif
        </div>
    </div>

    {{-- Payments Table --}}
    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Receipt ID</th>
                        <th>Date</th>
                        <th>Plan</th>
                        <th>Duration</th>
                        <th>Gym Fee</th>
                        <th>Coach Fee</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                    @php
                        $coachFee = $p->coach_fee_amount ?? 0;
                        $total    = $p->amount + $coachFee;
                        $status   = strtolower($p->status ?? 'Paid');
                    @endphp
                    <tr>
                        <td>
                            <div class="receipt-cell">
                                <svg viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <span class="id">{{ $p->receipt_number }}</span>
                            </div>
                        </td>

                        <td class="amount-text">{{ $p->payment_date->format('M d, Y') }}</td>

                        <td class="amount-text" style="font-weight:600;">{{ $p->fitness_plan }}</td>

                        <td class="amount-text">{{ $p->membership_type }}</td>

                        <td class="amount">₱{{ number_format($p->amount, 0) }}</td>

                        <td>
                            @if($coachFee > 0)
                                <span class="coach-fee">₱{{ number_format($coachFee, 0) }}</span>
                            @else
                                <span class="coach-fee empty">—</span>
                            @endif
                        </td>

                        <td class="amount">₱{{ number_format($total, 0) }}</td>

                        <td>
                            <span class="status-badge {{ $status === 'paid' ? '' : $status }}">
                                {{ strtoupper($p->status ?? 'Paid') }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('member.receipt', $p->id) }}" class="action-link" title="View Receipt">
                                <svg viewBox="0 0 24 24">
                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            No payments recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Receipt Info --}}
    <div class="info-section">
        <div class="title">Receipt Information</div>
        <ul>
            <li>
                <span class="bullet">•</span>
                Click the download icon to view and save a copy of your receipt.
            </li>
            <li>
                <span class="bullet">•</span>
                <strong>Total</strong> includes both Gym and Coaching fees where applicable — matching your receipt exactly.
            </li>
            <li>
                <span class="bullet">•</span>
                Keep your Receipt IDs for any support inquiries.
            </li>
        </ul>
    </div>

</div>

@endsection