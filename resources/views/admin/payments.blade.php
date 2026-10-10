@extends('layouts.admin')
@section('title', 'Payment Transactions – APEX')
@section('page_title', 'Payments')
@section('active_nav', 'payments')

@section('content')

<style>
    /* Colours, cards, buttons, forms, tables and alerts come from layouts/admin.blade.php.
       This block only holds layout rules specific to the payments page. */

    /* ── Safety net: stop any grid/flex child from blowing out the page width ── */
    .payments-form-layout > *,
    .instructor-layout > *,
    .payments-stat-grid-4 > *,
    .payments-stat-grid-3 > *,
    .stat-card-left { min-width: 0; }

    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* ── Stat cards ── */
    .payments-stat-grid-4,
    .payments-stat-grid-3 { display: grid; gap: 14px; margin-bottom: 24px; }
    .payments-stat-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .payments-stat-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .payments-stat-grid-4 .stat-value,
    .payments-stat-grid-3 .stat-value {
        font-size: clamp(18px, 2.2vw, 28px);
        overflow-wrap: anywhere;
        line-height: 1.2;
    }

    /* ── Page header ── */
    .pay-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
    }
    .pay-head h1 { font-size: clamp(20px, 3vw, 28px); font-weight: 700; margin-bottom: 4px; }
    .pay-head p { color: var(--muted); font-size: 14px; }

    .payments-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .earn-tab {
        padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
        cursor: pointer; border: 1px solid var(--border); background: transparent;
        color: var(--muted); transition: .15s; white-space: nowrap; min-height: 44px;
        font-family: 'DM Sans', sans-serif;
        -webkit-tap-highlight-color: transparent;
    }
    .earn-tab.active {
        background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
        color: #1a1a1a; border-color: transparent;
    }
    .earn-tab:hover:not(.active) { border-color: rgba(224,169,59,0.45); color: var(--accent); }
    .earn-tab:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

    /* ── Two-column layouts ── */
    .payments-form-layout { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: 24px; align-items: start; }
    .instructor-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 24px; align-items: start; }

    .record-payment-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 14px; padding: 24px;
    }
    html:root[data-theme="light"] .record-payment-card { box-shadow: var(--shadow-card); }
    .record-payment-card .card-title {
        font-size: 15px; font-weight: 700; color: var(--accent);
        margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border);
    }

    .rates-form { margin-top: 22px; border-top: 1px solid var(--border); padding-top: 18px; }
    .rates-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .rates-col-title { font-size: 11px; color: var(--muted); margin-bottom: 10px; font-weight: 700; }

    .selected-instructor-card {
        margin-bottom: 20px; padding: 14px 18px; display: flex; align-items: center;
        justify-content: space-between; gap: 10px; flex-wrap: wrap;
    }
    .selected-instructor-card .si-label { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); }
    .selected-instructor-card .si-name { font-size: 18px; font-weight: 700; color: var(--text); overflow-wrap: anywhere; }
    .selected-instructor-card .si-note { font-size: 12px; color: var(--muted); }

    .form-error-box {
        margin-bottom: 16px; padding: 12px 14px; border: 1px solid var(--danger);
        border-radius: 10px; font-size: 13px;
    }
    .form-error-box ul { margin: 6px 0 0 18px; }

    .btn-block { width: 100%; justify-content: center; min-height: 46px; }

    /* ── Form controls ── */
    .form-control { max-width: 100%; }
    select.form-control {
        appearance: none; -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23e0a93b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;
        padding-right: 40px; cursor: pointer; min-height: 44px;
        color: var(--text);
        background-color: var(--bg-card);
        text-overflow: ellipsis;
    }
    select.form-control option { color: var(--text); background: var(--bg-card); }
    input.form-control { min-height: 44px; }
    #admin_total_amount { font-weight: 700; color: var(--accent-2); }
    .field-error { color: var(--danger); font-size: 12px; margin-top: 4px; }

    input[type="date"]::-webkit-calendar-picker-indicator { opacity: 0; width: 0; padding: 0; margin: 0; }
    html:root[data-theme="dark"] input[type="date"] { color-scheme: dark; }
    input[type="date"] { -webkit-appearance: none; appearance: none; }

    .date-field-wrap { position: relative; display: flex; }
    .date-field-wrap input { padding-right: 52px; flex: 1; min-width: 0; }
    .date-picker-btn {
        position: absolute; right: 0; top: 0; bottom: 0; width: 44px;
        background: var(--accent-soft); border: none; border-left: 1px solid var(--border);
        border-radius: 0 10px 10px 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .15s;
    }
    .date-picker-btn:hover { background: rgba(224,169,59,0.25); }
    .date-picker-btn svg {
        width: 18px; height: 18px; stroke: var(--accent); fill: none;
        stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
    }

    .section-title .sub { font-size: 12px; color: var(--muted); font-weight: 400; margin-left: 8px; }

    /* ── Tables ── */
    .pay-table { width: 100%; }
    .pay-table--wide { min-width: 900px; }
    .pay-table--narrow { min-width: 460px; }
    table th { border-bottom: 1px solid var(--border); white-space: nowrap; }
    table td { color: var(--text-soft); }
    table tr:last-child td { border-bottom: none; }

    .member-cell { display: flex; align-items: center; gap: 10px; }
    .member-cell-avatar {
        width: 32px; height: 32px; border-radius: 50%; object-fit: cover;
        border: 1px solid rgba(224,169,59,0.3); flex-shrink: 0;
    }
    .member-cell-placeholder {
        width: 32px; height: 32px; border-radius: 50%;
        background: var(--accent-soft); border: 1px solid rgba(224,169,59,0.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; color: var(--accent); flex-shrink: 0;
    }
    .member-cell-name { font-weight: 600; white-space: nowrap; color: var(--text); }

    .badge-plan {
        background: rgba(96,165,250,0.15); color: var(--info);
        padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;
        white-space: nowrap; display: inline-block;
    }
    .badge-method {
        background: var(--surface2); border: 1px solid var(--border); color: var(--text-soft);
        padding: 3px 10px; border-radius: 6px; font-size: 12px; white-space: nowrap; display: inline-block;
    }
    .badge-instructor {
        background: rgba(96,165,250,0.15); color: var(--info); border-color: rgba(96,165,250,0.35);
    }
    .badge-unassigned { opacity: 0.75; }
    .receipt-number { font-family: monospace; font-size: 11px; color: var(--muted); white-space: nowrap; }
    .amount-text { font-weight: 700; color: var(--accent-2); white-space: nowrap; }
    html:root[data-theme="light"] .amount-text { color: var(--accent); }
    .amount-text-blue { font-weight: 700; color: var(--info); white-space: nowrap; }
    .text-muted { color: var(--muted); }
    .accent-blue { color: var(--info); }
    .note-cell { font-size: 12px; color: var(--muted); max-width: 220px; white-space: normal; }
    .btn-delete { min-width: 40px; min-height: 36px; }

    /* ── Leaderboard ── */
    .leaderboard-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 0; border-bottom: 1px solid var(--border); gap: 10px;
    }
    .leaderboard-row:last-child { border-bottom: none; }
    .leaderboard-link {
        text-decoration: none; color: inherit; cursor: pointer;
        padding: 12px 10px; margin: 0 -10px; border-radius: 10px;
        transition: background .15s; -webkit-tap-highlight-color: transparent;
    }
    .leaderboard-link:hover,
    .leaderboard-link:focus-visible { background: rgba(224,169,59,0.08); outline: none; }
    .leaderboard-link:hover .leaderboard-name,
    .leaderboard-link:focus-visible .leaderboard-name { color: var(--accent-2); }
    .leaderboard-chevron { color: var(--muted); font-size: 18px; flex-shrink: 0; transition: color .15s, transform .15s; }
    .leaderboard-link:hover .leaderboard-chevron { color: var(--accent-2); transform: translateX(2px); }
    .leaderboard-left { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
    .leaderboard-avatar {
        width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
        border: 1px solid var(--border); flex-shrink: 0;
    }
    .leaderboard-avatar-placeholder {
        width: 36px; height: 36px; border-radius: 50%;
        background: var(--accent-soft); border: 1px solid rgba(224,169,59,0.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: var(--accent); flex-shrink: 0;
    }
    .leaderboard-text { min-width: 0; }
    .leaderboard-name { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .leaderboard-sub { font-size: 12px; color: var(--muted); }
    .leaderboard-right { text-align: right; flex-shrink: 0; }
    .leaderboard-total { font-size: 16px; font-weight: 700; color: var(--accent-2); }
    html:root[data-theme="light"] .leaderboard-total { color: var(--accent); }
    .leaderboard-label { font-size: 11px; color: var(--muted); }

    .empty-state { text-align: center; color: var(--muted); padding: 48px; }

    /* ═══════════════ RESPONSIVE ═══════════════ */

    /* Large tablets / small laptops: stack form above the table */
    @media (max-width: 1180px) {
        .payments-form-layout { grid-template-columns: minmax(0, 1fr); gap: 20px; }
        .instructor-layout { grid-template-columns: minmax(0, 1fr); gap: 20px; }
        .payments-stat-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* Tablets */
    @media (max-width: 860px) {
        .payments-stat-grid-4, .payments-stat-grid-3 { gap: 10px; margin-bottom: 18px; }
        .payments-stat-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .payments-stat-grid-3 > :last-child:nth-child(odd) { grid-column: 1 / -1; }
        .record-payment-card { padding: 18px; }
        .pay-head { margin-bottom: 20px; }
        .section-title { font-size: 15px; }
        .section-title .sub { display: block; margin-left: 0; font-size: 11px; }
        .payments-tabs { width: 100%; }
        .payments-tabs .earn-tab { flex: 1; text-align: center; }
        .pay-table th, .pay-table td { padding: 10px 14px; font-size: 12px; }
        /* 16px stops iOS Safari from zooming into inputs */
        .form-control, select.form-control, input.form-control { font-size: 16px; }
    }

    /* Phones: tables become stacked cards (no sideways scrolling) */
    @media (max-width: 640px) {
        .table-card { background: transparent !important; border: none !important; box-shadow: none !important; padding: 0 !important; }
        .table-card .table-responsive { overflow: visible; }

        .pay-table, .pay-table tbody, .pay-table tr, .pay-table td { display: block; width: 100%; min-width: 0; }
        .pay-table--wide, .pay-table--narrow { min-width: 0; }
        .pay-table thead {
            position: absolute; width: 1px; height: 1px; overflow: hidden;
            clip: rect(0 0 0 0); clip-path: inset(50%); white-space: nowrap;
        }
        .pay-table tr {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 12px; margin: 0 0 12px; padding: 4px 14px;
        }
        .pay-table td {
            display: flex; align-items: center; justify-content: space-between; gap: 14px;
            padding: 9px 0 !important; border-bottom: 1px dashed var(--border) !important;
            text-align: right; white-space: normal; font-size: 13px;
        }
        .pay-table td:last-child { border-bottom: none !important; }
        .pay-table td::before {
            content: attr(data-label); flex-shrink: 0; text-align: left;
            font-size: 10.5px; font-weight: 700; letter-spacing: .6px;
            text-transform: uppercase; color: var(--muted);
        }
        .pay-table td.empty-state { display: block; text-align: center; padding: 24px 12px !important; }
        .pay-table td.empty-state::before { display: none; }
        .pay-table td.coach-cell { display: block; text-align: left; }
        .pay-table td.coach-cell::before { display: block; margin-bottom: 4px; }
        .member-cell-name { white-space: normal; text-align: left; }
        .note-cell { max-width: none; }
        .btn-delete { min-height: 40px; min-width: 48px; }
    }

    /* Small phones */
    @media (max-width: 480px) {
        .pay-head p { font-size: 12px; }
        .payments-stat-grid-4, .payments-stat-grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .payments-stat-grid-4 .stat-value,
        .payments-stat-grid-3 .stat-value { font-size: 18px; }
        .record-payment-card { padding: 14px 12px; border-radius: 10px; }
        .record-payment-card .card-title { margin-bottom: 14px; padding-bottom: 10px; }
        select.form-control { padding-right: 34px; background-size: 14px; background-position: right 10px center; }
        .member-cell-avatar, .member-cell-placeholder { width: 28px; height: 28px; font-size: 10px; }
        .leaderboard-avatar, .leaderboard-avatar-placeholder { width: 30px; height: 30px; font-size: 10px; }
        .leaderboard-name { font-size: 13px; }
        .leaderboard-total { font-size: 14px; }
        .empty-state { padding: 24px 12px; font-size: 12px; }
        .date-picker-btn { width: 40px; }
        .date-field-wrap input { padding-right: 46px; }
        .selected-instructor-card .si-name { font-size: 16px; }
    }

    /* Very small phones: rate inputs stack in one column */
    @media (max-width: 360px) {
        .rates-grid { grid-template-columns: 1fr; }
        .payments-stat-grid-4, .payments-stat-grid-3 { grid-template-columns: 1fr; }
    }

    @media (prefers-reduced-motion: reduce) {
        * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
    }
</style>

{{-- Payments sections: Member Payments | Walk-In Payments --}}
@include('partials.payment-tabs', ['active' => 'member'])

{{-- Page header --}}
<div class="pay-head">
    <div>
        <h1>Payment Transactions</h1>
        <p>Track gym fees, coach fees, and earnings per role.</p>
    </div>
    <div class="payments-tabs" role="tablist" aria-label="Earnings view">
        <button type="button" class="earn-tab active" id="tab-admin" role="tab"
                aria-selected="true" aria-controls="panel-admin" data-tab="admin">Admin Earnings</button>
        <button type="button" class="earn-tab" id="tab-instructor" role="tab"
                aria-selected="false" aria-controls="panel-instructor" data-tab="instructor">Instructor Earnings</button>
    </div>
</div>

@php
    $selectedInstructor = !empty($selectedInstructorId) ? \App\Models\User::find($selectedInstructorId) : null;
    $rateSelectedId = $selectedInstructorId ?? request('instructor_id');
@endphp

@if($selectedInstructor)
    <div class="card selected-instructor-card">
        <div>
            <div class="si-label">Selected Instructor</div>
            <div class="si-name">{{ $selectedInstructor->name }}</div>
        </div>
        <div class="si-note">Rates updated for {{ $selectedInstructor->name }}</div>
    </div>
@endif

{{-- ══ ADMIN EARNINGS PANEL ══ --}}
<div id="panel-admin" role="tabpanel" aria-labelledby="tab-admin">

    <div class="payments-stat-grid-4">
        <div class="stat-card green">
            <div class="stat-card-left">
                <div class="stat-label">Total Transactions</div>
                <div class="stat-value">{{ $totalCount }}</div>
                <div class="stat-sub">Gym fee payments</div>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-card-left">
                <div class="stat-label">This Month</div>
                <div class="stat-value">₱{{ number_format($thisMonth, 0) }}</div>
                <div class="stat-sub">{{ now()->format('F Y') }}</div>
            </div>
        </div>
        <div class="stat-card gold">
            <div class="stat-card-left">
                <div class="stat-label">Total Collected</div>
                <div class="stat-value" style="color:var(--accent-2);">₱{{ number_format($totalCollected, 0) }}</div>
                <div class="stat-sub">All-time gym revenue</div>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-card-left">
                <div class="stat-label">Coach Fees Paid Out</div>
                <div class="stat-value accent-blue">₱{{ number_format($totalCoachFees, 0) }}</div>
                <div class="stat-sub">To instructors total</div>
            </div>
        </div>
    </div>

    <div class="payments-form-layout">

        {{-- Record Payment Form (success/error flashes are shown by the layout) --}}
        <div class="record-payment-card">
            <div class="card-title">+ Record Payment</div>

            @if($errors->any())
                <div class="field-error form-error-box" role="alert">
                    <strong>The payment was not saved:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('payments.store') }}" id="admin-payment-form">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="member_id">Member</label>
                    @include('partials.member-search')
                    <select name="member_id" id="member_id" class="form-control" required>
                        <option value="" disabled {{ old('member_id') ? '' : 'selected' }}>— Select Member —</option>
                        @foreach($members as $member)
                            <option value="{{ $member['id'] }}" {{ old('member_id') == $member['id'] ? 'selected' : '' }}>
                                {{ $member['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="membership_type">Membership Plan</label>
                    <select name="membership_type" id="membership_type" class="form-control">
                        <option value="">— Select Plan —</option>
                        @foreach(['Monthly','Quarterly','Semi-Annual','Annually'] as $period)
                            <option value="{{ $period }}" {{ old('membership_type') == $period ? 'selected' : '' }}>
                                {{ $period }} (₱{{ number_format($rates['gym'][$period] ?? 0, 0) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="instructor_id">Instructor (for coach payments)</label>
                    <select name="instructor_id" id="instructor_id" class="form-control">
                        <option value="">— No personal coaching —</option>
                        @foreach($instructorOptions as $instructor)
                            <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="coach-package-group" style="display:none;">
                    <label class="form-label" for="coach_membership_type">Coaching Package</label>
                    <select name="coach_membership_type" id="coach_membership_type" class="form-control">
                        <option value="">— Select Package —</option>
                        @foreach(['Monthly','Quarterly','Semi-Annual','Annually'] as $period)
                            <option value="{{ $period }}" {{ old('coach_membership_type') == $period ? 'selected' : '' }}>
                                {{ $period }} (₱{{ number_format($rates['coach'][$period] ?? 0, 0) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin_total_amount">Total Amount (₱)</label>
                    <input type="text" id="admin_total_amount" class="form-control" value="₱0" readonly>
                    <input type="hidden" name="amount" id="admin_amount_hidden" value="{{ old('amount', 0) }}">
                    @error('amount')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="payment_date">Payment Date</label>
                    <div class="date-field-wrap">
                        <input type="date" name="payment_date" id="payment_date" class="form-control"
                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        <button type="button" class="date-picker-btn" id="payment-date-btn"
                                title="Open calendar" aria-label="Open calendar">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </button>
                    </div>
                    @error('payment_date')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="method">Method</label>
                    <select name="method" id="method" class="form-control" required>
                        <option value="" disabled {{ old('method') ? '' : 'selected' }}>— Select Method —</option>
                        @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
                            <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                    @error('method')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-block">✓ Record Payment</button>
            </form>

            <form method="POST" action="{{ route('payments.settings') }}" class="rates-form">
                @csrf
                <div class="card-title" style="margin-bottom:14px; font-size:14px;">⚙ Subscription Rates</div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label" for="instructor-rate-select">Instructor to Update</label>
                    <select id="instructor-rate-select" name="instructor_id" class="form-control">
                        <option value="">Global default rates</option>
                        @foreach($instructorOptions as $instructor)
                            <option value="{{ $instructor->id }}" {{ (string) $rateSelectedId === (string) $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rates-grid">
                    <div>
                        <div class="rates-col-title">Gym</div>
                        @foreach(['Monthly','Quarterly','Semi-Annual','Annually'] as $period)
                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label">{{ $period }}</label>
                                <input type="number" inputmode="numeric" name="gym_{{ strtolower(str_replace('-', '_', $period)) }}" class="form-control" min="0" value="{{ $rates['gym'][$period] ?? 0 }}" required>
                            </div>
                        @endforeach
                        {{-- Day Pass rate used by the separate Walk-In Payments module --}}
                        <div class="form-group" style="margin-bottom:10px;">
                            <label class="form-label">Day Pass (Walk-In)</label>
                            <input type="number" inputmode="numeric" name="gym_day_pass" class="form-control" min="1" value="{{ $dayPassRate ?? \App\Models\Payment::dayPassRate() }}">
                        </div>
                    </div>
                    <div>
                        <div class="rates-col-title">Instructor</div>
                        @foreach(['Monthly','Quarterly','Semi-Annual','Annually'] as $period)
                            <div class="form-group" style="margin-bottom:10px;">
                                <label class="form-label">{{ $period }}</label>
                                <input type="number" inputmode="numeric" name="coach_{{ strtolower(str_replace('-', '_', $period)) }}" class="form-control" min="0" value="{{ $rates['coach'][$period] ?? 0 }}" required>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary btn-block" style="margin-top:6px;">Update Rates</button>
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
            <div class="card table-card">
                <div class="table-responsive">
                    <table class="pay-table pay-table--wide">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Member</th>
                                <th>Assigned Instructor</th>
                                <th>Plan</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Note</th>
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
                                <td data-label="Receipt" class="receipt-number">{{ $receiptNum }}</td>
                                <td data-label="Member">
                                    <div class="member-cell">
                                        @if($memberPhoto)
                                            <img src="{{ asset('storage/'.$memberPhoto) }}" class="member-cell-avatar" alt="" loading="lazy">
                                        @else
                                            <div class="member-cell-placeholder">
                                                {{ strtoupper(substr($memberName, 0, 2)) }}
                                            </div>
                                        @endif
                                        <span class="member-cell-name">{{ $memberName }}</span>
                                    </div>
                                </td>
                                <td data-label="Instructor">
                                    @if(($payment['assigned_instructor'] ?? 'Unassigned') === 'Unassigned')
                                        <span class="badge-method badge-unassigned">Unassigned</span>
                                    @else
                                        <span class="badge-method badge-instructor">{{ $payment['assigned_instructor'] }}</span>
                                    @endif
                                </td>
                                <td data-label="Plan">{{ $payment['fitness_plan'] ?? '—' }}</td>
                                <td data-label="Duration">
                                    <span class="badge-plan">{{ $payment['membership_type'] ?? '—' }}</span>
                                </td>
                                <td data-label="Amount" class="amount-text">₱{{ number_format($payment['amount'], 0) }}</td>
                                <td data-label="Date" class="text-muted">{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</td>
                                <td data-label="Method">
                                    <span class="badge-method">{{ $payment['method'] ?? 'Cash' }}</span>
                                </td>
                                <td data-label="Note" class="note-cell">{{ \App\Models\Payment::paymentNoteFor(data_get($payment, 'notes'), data_get($payment, 'membership_type'), data_get($payment, 'amount'), data_get($payment, 'payment_type')) ?: '—' }}</td>
                                <td data-label="Action">
                                    <form method="POST" action="{{ route('payments.destroy', $payment['id']) }}"
                                          onsubmit="return confirm('Delete this transaction?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-soft btn-sm btn-delete" aria-label="Delete transaction">🗑</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="empty-state">No gym fee transactions yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ INSTRUCTOR EARNINGS PANEL ══ --}}
<div id="panel-instructor" role="tabpanel" aria-labelledby="tab-instructor" style="display:none;">

    <div class="payments-stat-grid-3">
        <div class="stat-card blue">
            <div class="stat-card-left">
                <div class="stat-label">Total Coach Fees</div>
                <div class="stat-value accent-blue">₱{{ number_format($totalCoachFees, 0) }}</div>
                <div class="stat-sub">All-time instructor payouts</div>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-card-left">
                <div class="stat-label">This Month (Coach)</div>
                <div class="stat-value">₱{{ number_format($thisMonthCoachFees, 0) }}</div>
                <div class="stat-sub">{{ now()->format('F Y') }}</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-card-left">
                <div class="stat-label">Active Instructors Paid</div>
                <div class="stat-value">{{ $instructorsPaidCount }}</div>
                <div class="stat-sub">With at least one coach fee</div>
            </div>
        </div>
    </div>

    <div class="instructor-layout">

        {{-- Instructor leaderboard: each row opens that instructor's payments --}}
        <div class="record-payment-card">
            <div class="card-title">Instructor Earnings Breakdown</div>
            @forelse($instructorLeaderboard as $row)
            @php
                $instrPhoto = $row['instructor']['photo'] ?? null;
                $instrName  = $row['instructor']['name'] ?? '?';
            @endphp
            <a href="{{ route('payments.instructor-earnings', $row['instructor_id']) }}"
               class="leaderboard-row leaderboard-link"
               title="View {{ $instrName }}'s payments">
                <div class="leaderboard-left">
                    @if($instrPhoto)
                        <img src="{{ asset('storage/'.$instrPhoto) }}" class="leaderboard-avatar" alt="" loading="lazy">
                    @else
                        <div class="leaderboard-avatar-placeholder">
                            {{ strtoupper(substr($instrName, 0, 2)) }}
                        </div>
                    @endif
                    <div class="leaderboard-text">
                        <div class="leaderboard-name">{{ $instrName }}</div>
                        <div class="leaderboard-sub">{{ $row['txn_count'] }} transaction(s)</div>
                    </div>
                </div>
                <div class="leaderboard-right">
                    <div class="leaderboard-total">₱{{ number_format($row['total'], 0) }}</div>
                    <div class="leaderboard-label">total earned</div>
                </div>
                <span class="leaderboard-chevron" aria-hidden="true">›</span>
            </a>
            @empty
            <div class="empty-state">No instructor fees recorded yet.</div>
            @endforelse
        </div>

        {{-- Coach fee transaction log --}}
        <div>
            <div class="section-header">
                <div class="section-title">Coach Fee Transactions</div>
            </div>
            <div class="card table-card">
                <div class="table-responsive">
                    <table class="pay-table pay-table--narrow">
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
                                <td data-label="Receipt" class="receipt-number">{{ $cp['receipt_number'] ?? '—' }}</td>
                                <td data-label="Member → Instructor" class="coach-cell">
                                    <div style="font-size:13px;">
                                        <span style="font-weight:600;color:var(--text);">{{ $cp['member']['name'] ?? '—' }}</span>
                                        <span style="color:var(--muted);margin:0 6px;">→</span>
                                        <span class="accent-blue" style="font-weight:600;">{{ $cp['instructor']['name'] ?? '—' }}</span>
                                    </div>
                                    <div style="font-size:11px;color:var(--muted);">
                                        {{ $cp['fitness_plan'] ?? '' }} · {{ $cp['membership_type'] ?? '' }}
                                    </div>
                                </td>
                                <td data-label="Amount" class="amount-text-blue">₱{{ number_format($cp['amount'], 0) }}</td>
                                <td data-label="Date" class="text-muted" style="font-size:13px;">
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

<script>
(function () {
    'use strict';

    var PERIODS = ['Monthly', 'Quarterly', 'Semi-Annual', 'Annually'];

    /* ───────── Tabs ───────── */
    function showTab(tab) {
        var isAdmin = tab === 'admin';
        document.getElementById('panel-admin').style.display = isAdmin ? 'block' : 'none';
        document.getElementById('panel-instructor').style.display = isAdmin ? 'none' : 'block';

        ['admin', 'instructor'].forEach(function (name) {
            var btn = document.getElementById('tab-' + name);
            var active = name === tab;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        // Keep the URL in sync so refresh and the "back" link keep the same tab
        try {
            var url = new URL(window.location);
            url.searchParams.set('tab', tab);
            history.replaceState(null, '', url);
        } catch (e) { /* ignore */ }
    }

    document.querySelectorAll('.earn-tab').forEach(function (btn) {
        btn.addEventListener('click', function () { showTab(btn.getAttribute('data-tab')); });
    });

    var requestedTab = new URLSearchParams(window.location.search).get('tab');
    if (requestedTab === 'instructor' || requestedTab === 'admin') {
        showTab(requestedTab);
    }

    /* ───────── Date picker button (with fallback for browsers without showPicker) ───────── */
    var dateInput = document.getElementById('payment_date');
    var dateBtn = document.getElementById('payment-date-btn');
    if (dateInput && dateBtn) {
        dateBtn.addEventListener('click', function () {
            try {
                if (typeof dateInput.showPicker === 'function') {
                    dateInput.showPicker();
                } else {
                    dateInput.focus();
                    dateInput.click();
                }
            } catch (e) {
                dateInput.focus();
            }
        });
    }

    /* ───────── Rate data from the server ───────── */
    var defaultGymRates = @json($rates['gym'] ?? []);
    var defaultCoachRates = @json(\App\Models\Payment::defaultCoachRates());
    var instructorCoachRates = @json($instructorRateMap ?? []);

    function ratesFor(instructorId) {
        return (instructorId && instructorCoachRates[instructorId])
            ? instructorCoachRates[instructorId]
            : defaultCoachRates;
    }

    /* ───────── Subscription Rates form: preview the selected instructor's rates ───────── */
    var coachFieldMap = {
        'Monthly': 'coach_monthly',
        'Quarterly': 'coach_quarterly',
        'Semi-Annual': 'coach_semi_annual',
        'Annually': 'coach_annually'
    };

    function applyCoachRatePreview(instructorId) {
        var rates = ratesFor(instructorId);
        Object.keys(coachFieldMap).forEach(function (label) {
            var input = document.querySelector('input[name="' + coachFieldMap[label] + '"]');
            if (input) {
                input.value = (rates && rates[label] != null) ? rates[label] : 0;
            }
        });
    }

    var rateSelect = document.getElementById('instructor-rate-select');
    if (rateSelect) {
        rateSelect.addEventListener('change', function () { applyCoachRatePreview(this.value || ''); });
        applyCoachRatePreview(rateSelect.value || '');
    }

    /* ───────── Record Payment form: coach package + total ───────── */
    var gymSelect = document.getElementById('membership_type');
    var coachSelect = document.getElementById('coach_membership_type');
    var instructorSelect = document.getElementById('instructor_id');
    var coachGroup = document.getElementById('coach-package-group');
    var totalBox = document.getElementById('admin_total_amount');
    var hiddenAmount = document.getElementById('admin_amount_hidden');

    function refreshCoachPackageUI() {
        if (!coachSelect || !instructorSelect) return;

        var instructorId = instructorSelect.value || '';
        var activeRates = ratesFor(instructorId);
        var currentValue = coachSelect.value || '';

        coachSelect.innerHTML = '<option value="">— No coaching package —</option>' +
            PERIODS.map(function (period) {
                return '<option value="' + period + '"' + (currentValue === period ? ' selected' : '') + '>' +
                    period + ' · ₱' + Number(activeRates[period] || 0).toLocaleString('en-PH') + '</option>';
            }).join('');

        if (!instructorId) {
            coachSelect.disabled = true;
            coachSelect.value = '';
            if (coachGroup) coachGroup.style.display = 'none';
        } else {
            coachSelect.disabled = false;
            if (coachGroup) coachGroup.style.display = '';
            if (currentValue && activeRates[currentValue]) {
                coachSelect.value = currentValue;
            }
        }
    }

    function recalcAmount() {
        var gymType = gymSelect ? gymSelect.value : '';
        var coachType = coachSelect ? coachSelect.value : '';
        var activeCoachRates = ratesFor(instructorSelect ? instructorSelect.value : '');

        var gymTotal = (gymType && defaultGymRates[gymType]) ? Number(defaultGymRates[gymType]) : 0;
        var coachTotal = (coachType && activeCoachRates[coachType]) ? Number(activeCoachRates[coachType]) : 0;
        var total = gymTotal + coachTotal;

        if (totalBox) totalBox.value = '₱' + total.toLocaleString('en-PH');
        if (hiddenAmount) hiddenAmount.value = total;
    }

    [gymSelect, coachSelect].forEach(function (el) {
        if (!el) return;
        el.addEventListener('change', function () {
            if (instructorSelect && !instructorSelect.value && coachSelect) {
                coachSelect.value = '';
            }
            recalcAmount();
        });
    });

    if (instructorSelect) {
        instructorSelect.addEventListener('change', function () {
            refreshCoachPackageUI();
            recalcAmount();
        });
    }

    refreshCoachPackageUI();
    recalcAmount();
})();
</script>

@endsection