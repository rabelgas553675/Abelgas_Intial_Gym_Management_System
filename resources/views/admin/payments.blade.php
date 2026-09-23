@extends('layouts.admin')
@section('title', 'Payment Transactions – APEX')
@section('page_title', 'Payments')
@section('active_nav', 'payments')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .payments-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 40px !important;
        cursor: pointer;
        min-height: 44px;
    }

    select.form-control option {
        background-color: var(--surface);
        color: white;
        padding: 8px;
    }

    select.form-control:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, .2);
    }

    .earn-tab {
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--border);
        background: transparent;
        color: var(--muted);
        transition: .15s;
        white-space: nowrap;
        min-height: 40px;
    }

    .earn-tab.active {
        background: var(--accent);
        color: #111;
        border-color: var(--accent);
    }

    .earn-tab:hover:not(.active) {
        border-color: rgba(255, 255, 255, .2);
        color: var(--text);
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        width: 0;
        padding: 0;
        margin: 0;
    }

    input[type="date"] {
        color-scheme: dark;
    }

    /* ── Page header ── */
    .payments-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .payments-header h1 {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .payments-header p {
        color: var(--muted);
        font-size: 14px;
    }

    .payments-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* ── Stat grids ── */
    .payments-stat-grid-4,
    .payments-stat-grid-3 {
        display: grid;
        gap: 16px;
        margin-bottom: 24px;
    }

    .payments-stat-grid-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .payments-stat-grid-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    /* Stat Cards */
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px 24px;
        transition: transform 0.2s, box-shadow 0.2s;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card .stat-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }

    .stat-card .stat-sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px;
    }

    .stat-card.green .stat-value {
        color: var(--warning);
    }

    .stat-card.blue .stat-value {
        color: var(--success);
    }

    /* ── Two-column layouts ── */
    .payments-form-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 24px;
        align-items: start;
    }

    .instructor-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: start;
    }

    .record-payment-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 24px;
    }

    .record-payment-card .card-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }

    .date-field-wrap {
        position: relative;
        display: flex;
    }

    .date-picker-btn {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 44px;
        background: rgba(200, 255, 0, 0.10);
        border: none;
        border-left: 1px solid var(--border);
        border-radius: 0 8px 8px 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s;
    }

    .date-picker-btn:hover {
        background: rgba(200, 255, 0, 0.22);
    }

    .date-picker-btn svg {
        width: 18px;
        height: 18px;
        stroke: var(--accent);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 14px;
        outline: none;
        transition: border-color .15s;
        box-sizing: border-box;
        font-family: inherit;
        min-height: 44px;
    }

    .form-control:focus {
        border-color: var(--accent);
    }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 15px;
    }

    .alert-success {
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.2);
        color: #4ade80;
    }

    .alert-danger {
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.2);
        color: #f87171;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 14px;
        min-height: 44px;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--accent);
        color: #000;
    }

    .btn-primary:hover {
        opacity: .88;
        transform: translateY(-1px);
    }

    .btn-danger-soft {
        background: rgba(248, 113, 113, 0.1);
        color: #f87171;
        border: 1px solid rgba(248, 113, 113, 0.2);
        padding: 6px 12px;
        min-height: 32px;
        font-size: 14px;
    }

    .btn-danger-soft:hover {
        background: rgba(248, 113, 113, 0.2);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
        min-height: 32px;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .section-title {
        font-size: 17px;
        font-weight: 700;
    }

    .section-title .sub {
        font-size: 12px;
        color: var(--muted);
        font-weight: 400;
        margin-left: 8px;
    }

    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    table th {
        padding: 12px 18px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 2px;
        background: var(--surface2);
        white-space: nowrap;
        border-bottom: 1px solid var(--border);
    }

    table td {
        padding: 13px 18px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    table tr:last-child td {
        border-bottom: none;
    }

    table tr {
        transition: .15s;
    }

    table tr:hover {
        background: rgba(255, 255, 255, 0.015);
    }

    .member-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .member-cell-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(200, 255, 0, 0.25);
        flex-shrink: 0;
    }

    .member-cell-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(200, 255, 0, 0.08);
        border: 1px solid rgba(200, 255, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
        flex-shrink: 0;
    }

    .member-cell-name {
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-plan {
        background: rgba(96, 165, 250, .1);
        color: #60a5fa;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        display: inline-block;
    }

    .badge-method {
        background: var(--surface2);
        border: 1px solid var(--border);
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        display: inline-block;
    }

    .receipt-number {
        font-family: monospace;
        font-size: 11px;
        color: var(--muted);
        white-space: nowrap;
    }

    .amount-text {
        font-weight: 700;
        color: var(--accent);
        white-space: nowrap;
    }

    .amount-text-blue {
        font-weight: 700;
        color: #60a5fa;
        white-space: nowrap;
    }

    .text-muted {
        color: var(--muted);
    }

    /* Leaderboard */
    .leaderboard-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
        gap: 10px;
    }

    .leaderboard-row:last-child {
        border-bottom: none;
    }

    .leaderboard-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
    }

    .leaderboard-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }

    .leaderboard-avatar-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(200, 255, 0, 0.08);
        border: 1px solid rgba(200, 255, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--accent);
        flex-shrink: 0;
    }

    .leaderboard-name {
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .leaderboard-sub {
        font-size: 12px;
        color: var(--muted);
    }

    .leaderboard-right {
        text-align: right;
        flex-shrink: 0;
    }

    .leaderboard-total {
        font-size: 16px;
        font-weight: 700;
        color: var(--accent);
    }

    .leaderboard-label {
        font-size: 11px;
        color: var(--muted);
    }

    .empty-state {
        text-align: center;
        color: var(--muted);
        padding: 48px;
    }

    /* ── RESPONSIVE BREAKPOINTS ── */

    @media (max-width: 1024px) {
        .payments-stat-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }

        .payments-stat-grid-3 {
            grid-template-columns: repeat(2, 1fr);
        }

        .payments-form-layout {
            grid-template-columns: 300px 1fr;
            gap: 16px;
        }
    }

    @media (max-width: 860px) {
        .payments-container {
            padding: 0 12px;
        }

        .payments-header h1 {
            font-size: 24px;
        }

        .payments-header p {
            font-size: 13px;
        }

        .payments-form-layout {
            grid-template-columns: 1fr;
        }

        .instructor-layout {
            grid-template-columns: 1fr;
        }

        .payments-stat-grid-4,
        .payments-stat-grid-3 {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .record-payment-card {
            padding: 18px;
        }

        .stat-card {
            padding: 16px 18px;
            min-height: 80px;
        }

        .stat-card .stat-value {
            font-size: 22px;
        }

        .stat-card .stat-label {
            font-size: 10px;
        }

        table {
            min-width: 600px;
        }

        table th,
        table td {
            padding: 10px 14px;
            font-size: 12px;
        }

        .section-title {
            font-size: 15px;
        }

        .section-title .sub {
            font-size: 11px;
            display: block;
            margin-left: 0;
        }

        .earn-tab {
            font-size: 12px;
            padding: 6px 14px;
            min-height: 36px;
        }

        .payments-tabs {
            width: 100%;
        }

        .payments-tabs .earn-tab {
            flex: 1;
            text-align: center;
        }
    }

    @media (max-width: 600px) {
        .payments-stat-grid-4,
        .payments-stat-grid-3 {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-card {
            padding: 12px 14px;
            min-height: 70px;
            border-radius: 10px;
        }

        .stat-card .stat-value {
            font-size: 18px;
        }

        .stat-card .stat-label {
            font-size: 9px;
            letter-spacing: 1px;
        }

        .stat-card .stat-sub {
            font-size: 10px;
        }
    }

    @media (max-width: 480px) {
        .payments-container {
            padding: 0 8px;
        }

        .payments-header h1 {
            font-size: 20px;
        }

        .payments-header p {
            font-size: 12px;
        }

        .payments-stat-grid-4,
        .payments-stat-grid-3 {
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .stat-card {
            padding: 10px 12px;
            min-height: 60px;
            border-radius: 8px;
        }

        .stat-card .stat-value {
            font-size: 16px;
        }

        .stat-card .stat-label {
            font-size: 8px;
            letter-spacing: 0.5px;
        }

        .stat-card .stat-sub {
            font-size: 9px;
        }

        .record-payment-card {
            padding: 14px 12px;
            border-radius: 10px;
        }

        .record-payment-card .card-title {
            font-size: 13px;
            margin-bottom: 16px;
            padding-bottom: 10px;
        }

        .form-label {
            font-size: 10px;
            margin-bottom: 6px;
        }

        .form-control {
            font-size: 13px;
            padding: 8px 12px;
            min-height: 38px;
        }

        select.form-control {
            padding-right: 34px !important;
            background-size: 14px;
            background-position: right 10px center;
        }

        .btn {
            font-size: 13px;
            padding: 8px 16px;
            min-height: 38px;
        }

        .btn-sm {
            font-size: 11px;
            padding: 4px 10px;
            min-height: 28px;
        }

        table {
            min-width: 500px;
        }

        table th,
        table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        table th {
            font-size: 9px;
            letter-spacing: 1px;
        }

        .member-cell-avatar,
        .member-cell-placeholder {
            width: 26px;
            height: 26px;
            font-size: 9px;
        }

        .member-cell-name {
            font-size: 12px;
        }

        .badge-plan,
        .badge-method {
            font-size: 9px;
            padding: 2px 7px;
        }

        .receipt-number {
            font-size: 10px;
        }

        .amount-text,
        .amount-text-blue {
            font-size: 12px;
        }

        .section-title {
            font-size: 13px;
        }

        .section-title .sub {
            font-size: 10px;
        }

        .earn-tab {
            font-size: 11px;
            padding: 5px 10px;
            min-height: 32px;
        }

        .leaderboard-row {
            padding: 8px 0;
        }

        .leaderboard-avatar,
        .leaderboard-avatar-placeholder {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }

        .leaderboard-name {
            font-size: 12px;
        }

        .leaderboard-sub {
            font-size: 10px;
        }

        .leaderboard-total {
            font-size: 14px;
        }

        .leaderboard-label {
            font-size: 10px;
        }

        .empty-state {
            padding: 24px 12px;
            font-size: 12px;
        }

        .date-picker-btn {
            width: 36px;
        }

        .date-picker-btn svg {
            width: 15px;
            height: 15px;
        }

        .payments-tabs {
            gap: 4px;
        }

        .alert {
            font-size: 12px;
            padding: 10px 12px;
        }
    }

    @media (max-width: 360px) {
        .payments-stat-grid-4,
        .payments-stat-grid-3 {
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }

        .stat-card {
            padding: 8px 10px;
            min-height: 50px;
        }

        .stat-card .stat-value {
            font-size: 14px;
        }

        .stat-card .stat-label {
            font-size: 7px;
        }

        table {
            min-width: 420px;
        }

        table th,
        table td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .member-cell-avatar,
        .member-cell-placeholder {
            width: 22px;
            height: 22px;
            font-size: 8px;
        }

        .member-cell-name {
            font-size: 11px;
        }

        .form-control {
            font-size: 12px;
            padding: 6px 10px;
            min-height: 34px;
        }

        .btn {
            font-size: 12px;
            padding: 6px 12px;
            min-height: 34px;
        }

        .earn-tab {
            font-size: 10px;
            padding: 4px 8px;
            min-height: 28px;
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

    {{-- Page header --}}
    <div class="payments-header">
        <div>
            <h1>Payment Transactions</h1>
            <p>Track gym fees, coach fees, and earnings per role.</p>
        </div>
        <div class="payments-tabs">
            <button class="earn-tab active" onclick="showTab('admin')" id="tab-admin">Admin Earnings</button>
            <button class="earn-tab" onclick="showTab('instructor')" id="tab-instructor">Instructor Earnings</button>
        </div>
    </div>

    {{-- ══ ADMIN EARNINGS PANEL ══════════════════════════════════════════════════ --}}
    <div id="panel-admin">

        <div class="payments-stat-grid-4">
            <div class="stat-card">
                <div class="stat-label">Total Transactions</div>
                <div class="stat-value">{{ $totalCount }}</div>
                <div class="stat-sub">Gym fee payments</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">This Month</div>
                <div class="stat-value">₱{{ number_format($thisMonth, 0) }}</div>
                <div class="stat-sub">{{ now()->format('F Y') }}</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">Total Collected</div>
                <div class="stat-value">₱{{ number_format($totalCollected, 0) }}</div>
                <div class="stat-sub">All-time gym revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Coach Fees Paid Out</div>
                <div class="stat-value" style="color:#60a5fa;">₱{{ number_format($totalCoachFees, 0) }}</div>
                <div class="stat-sub">To instructors total</div>
            </div>
        </div>

        <div class="payments-form-layout">

            {{-- Record Payment Form --}}
            <div class="record-payment-card">
                <div class="card-title">+ Record Payment</div>

                @if(session('success'))
                    <div class="alert alert-success">✓ {{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">✕ {{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Member</label>
                        <select name="member_id" class="form-control" required>
                            <option value="" disabled selected>— Select Member —</option>
                            @foreach($members as $member)
                                <option value="{{ $member['id'] }}" {{ old('member_id') == $member['id'] ? 'selected' : '' }}>
                                    {{ $member['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('member_id')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount (₱)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0"
                               placeholder="0.00" value="{{ old('amount') }}" required/>
                        @error('amount')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Date</label>
                        <div class="date-field-wrap">
                            <input type="date" name="payment_date" id="payment_date" class="form-control"
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required
                                   style="padding-right:48px;flex:1;border-radius:8px;"/>
                            <button type="button"
                                    class="date-picker-btn"
                                    onclick="document.getElementById('payment_date').showPicker()"
                                    title="Open calendar">
                                <svg viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </button>
                        </div>
                        @error('payment_date')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-control" required>
                            <option value="" disabled selected>— Select Method —</option>
                            @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
                                <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                        @error('method')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        ✓ Record Payment
                    </button>
                </form>
            </div>

            {{-- Admin Transactions Table --}}
            <div>
                <div class="section-header">
                    <div class="section-title">
                        Gym Fee Transactions
                        <span class="sub">(Platform / Admin earnings only — coach fees excluded)</span>
                    </div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Member</th>
                                    <th>Plan</th>
                                    <th>Duration</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                @php
                                    $memberPhoto = $payment['member']['user']['photo']
                                                ?? $payment['member']['photo']
                                                ?? null;
                                    $memberName  = $payment['member']['name'] ?? '?';
                                    $receiptNum  = $payment['receipt_number']
                                                ?? 'TXN-' . str_pad($payment['id'], 5, '0', STR_PAD_LEFT);
                                @endphp
                                <tr>
                                    <td class="receipt-number">{{ $receiptNum }}</td>
                                    <td>
                                        <div class="member-cell">
                                            @if($memberPhoto)
                                                <img src="{{ asset('storage/'.$memberPhoto) }}" class="member-cell-avatar" alt="">
                                            @else
                                                <div class="member-cell-placeholder">
                                                    {{ strtoupper(substr($memberName, 0, 2)) }}
                                                </div>
                                            @endif
                                            <span class="member-cell-name">{{ $memberName }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $payment['fitness_plan'] ?? '—' }}</td>
                                    <td>
                                        <span class="badge-plan">{{ $payment['membership_type'] ?? '—' }}</span>
                                    </td>
                                    <td class="amount-text">₱{{ number_format($payment['amount'], 0) }}</td>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge-method">{{ $payment['method'] ?? 'Cash' }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('payments.destroy', $payment['id']) }}"
                                              onsubmit="return confirm('Delete this transaction?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="empty-state">No gym fee transactions yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ INSTRUCTOR EARNINGS PANEL ════════════════════════════════════════════ --}}
    <div id="panel-instructor" style="display:none;">

        <div class="payments-stat-grid-3">
            <div class="stat-card">
                <div class="stat-label">Total Coach Fees</div>
                <div class="stat-value" style="color:#60a5fa;">₱{{ number_format($totalCoachFees, 0) }}</div>
                <div class="stat-sub">All-time instructor payouts</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">This Month (Coach)</div>
                <div class="stat-value">₱{{ number_format($thisMonthCoachFees, 0) }}</div>
                <div class="stat-sub">{{ now()->format('F Y') }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Instructors Paid</div>
                <div class="stat-value">{{ $instructorsPaidCount }}</div>
                <div class="stat-sub">With at least one coach fee</div>
            </div>
        </div>

        <div class="instructor-layout">

            {{-- Instructor leaderboard --}}
            <div class="record-payment-card">
                <div class="card-title">Instructor Earnings Breakdown</div>
                @forelse($instructorLeaderboard as $row)
                @php
                    $instrPhoto = $row['instructor']['photo'] ?? null;
                    $instrName  = $row['instructor']['name'] ?? '?';
                @endphp
                <div class="leaderboard-row">
                    <div class="leaderboard-left">
                        @if($instrPhoto)
                            <img src="{{ asset('storage/'.$instrPhoto) }}" class="leaderboard-avatar" alt="">
                        @else
                            <div class="leaderboard-avatar-placeholder">
                                {{ strtoupper(substr($instrName, 0, 2)) }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <div class="leaderboard-name">{{ $instrName }}</div>
                            <div class="leaderboard-sub">{{ $row['txn_count'] }} transaction(s)</div>
                        </div>
                    </div>
                    <div class="leaderboard-right">
                        <div class="leaderboard-total">₱{{ number_format($row['total'], 0) }}</div>
                        <div class="leaderboard-label">total earned</div>
                    </div>
                </div>
                @empty
                <div class="empty-state">No instructor fees recorded yet.</div>
                @endforelse
            </div>

            {{-- Coach fee transaction log --}}
            <div>
                <div class="section-header">
                    <div class="section-title">Coach Fee Transactions</div>
                </div>
                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Receipt</th>
                                    <th>Member → Instructor</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coachFeePayments as $cp)
                                <tr>
                                    <td class="receipt-number">{{ $cp['receipt_number'] ?? '—' }}</td>
                                    <td>
                                        <div style="font-size:13px;white-space:nowrap;">
                                            <span style="font-weight:600;">{{ $cp['member']['name'] ?? '—' }}</span>
                                            <span style="color:var(--muted);margin:0 6px;">→</span>
                                            <span style="color:#60a5fa;font-weight:600;">{{ $cp['instructor']['name'] ?? '—' }}</span>
                                        </div>
                                        <div style="font-size:11px;color:var(--muted);">
                                            {{ $cp['fitness_plan'] ?? '' }} · {{ $cp['membership_type'] ?? '' }}
                                        </div>
                                    </td>
                                    <td class="amount-text-blue">₱{{ number_format($cp['amount'], 0) }}</td>
                                    <td class="text-muted" style="font-size:13px;white-space:nowrap;">
                                        {{ \Carbon\Carbon::parse($cp['payment_date'])->format('M d, Y') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="empty-state">No coach fee payments yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function showTab(tab) {
        document.getElementById('panel-admin').style.display = tab === 'admin' ? 'block' : 'none';
        document.getElementById('panel-instructor').style.display = tab === 'instructor' ? 'block' : 'none';
        document.getElementById('tab-admin').classList.toggle('active', tab === 'admin');
        document.getElementById('tab-instructor').classList.toggle('active', tab === 'instructor');
    }
</script>

@endsection