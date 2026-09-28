@extends('layouts.admin')
@section('title', 'Payment Transactions – APEX')
@section('page_title', 'Payments')
@section('active_nav', 'payments')

@section('content')

<style>
    /* Colours, cards, buttons, forms, tables and alerts come from layouts/admin.blade.php.
       This block only holds layout rules specific to the payments page. */

    .stat-card-left { min-width: 0; }
    .payments-stat-grid-4,
    .payments-stat-grid-3 { display: grid; gap: 14px; margin-bottom: 24px; }
    .payments-stat-grid-4 { grid-template-columns: repeat(4, 1fr); }
    .payments-stat-grid-3 { grid-template-columns: repeat(3, 1fr); }
    .payments-stat-grid-4 .stat-value,
    .payments-stat-grid-3 .stat-value { font-size: 28px; }

    /* ── Page header ── */
    .pay-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
    }
    .pay-head h1 { font-size: 28px; font-weight: 700; margin-bottom: 4px; }
    .pay-head p { color: var(--muted); font-size: 14px; }

    .payments-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .earn-tab {
        padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
        cursor: pointer; border: 1px solid var(--border); background: transparent;
        color: var(--muted); transition: .15s; white-space: nowrap; min-height: 40px;
        font-family: 'DM Sans', sans-serif;
    }
    .earn-tab.active {
        background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
        color: #1a1a1a; border-color: transparent;
    }
    .earn-tab:hover:not(.active) { border-color: rgba(224,169,59,0.45); color: var(--accent); }

    /* ── Two-column layouts ── */
    .payments-form-layout { display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start; }
    .instructor-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }

    .record-payment-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 14px; padding: 24px;
    }
    html:root[data-theme="light"] .record-payment-card { box-shadow: var(--shadow-card); }
    .record-payment-card .card-title {
        font-size: 15px; font-weight: 700; color: var(--accent);
        margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border);
    }

    /* ── Form controls ── */
    select.form-control {
        appearance: none; -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23e0a93b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;
        padding-right: 40px; cursor: pointer; min-height: 44px;
    }
    .field-error { color: var(--danger); font-size: 12px; margin-top: 4px; }

    input[type="date"]::-webkit-calendar-picker-indicator { opacity: 0; width: 0; padding: 0; margin: 0; }
    html:root[data-theme="dark"] input[type="date"] { color-scheme: dark; }

    .date-field-wrap { position: relative; display: flex; }
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

    /* ── Table cells ── */
    table th { border-bottom: 1px solid var(--border); }
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
    .receipt-number { font-family: monospace; font-size: 11px; color: var(--muted); white-space: nowrap; }
    .amount-text { font-weight: 700; color: var(--accent-2); white-space: nowrap; }
    html:root[data-theme="light"] .amount-text { color: var(--accent); }
    .amount-text-blue { font-weight: 700; color: var(--info); white-space: nowrap; }
    .text-muted { color: var(--muted); }
    .accent-blue { color: var(--info); }

    /* ── Leaderboard ── */
    .leaderboard-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 0; border-bottom: 1px solid var(--border); gap: 10px;
    }
    .leaderboard-row:last-child { border-bottom: none; }
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
    .leaderboard-name { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .leaderboard-sub { font-size: 12px; color: var(--muted); }
    .leaderboard-right { text-align: right; flex-shrink: 0; }
    .leaderboard-total { font-size: 16px; font-weight: 700; color: var(--accent-2); }
    html:root[data-theme="light"] .leaderboard-total { color: var(--accent); }
    .leaderboard-label { font-size: 11px; color: var(--muted); }

    .empty-state { text-align: center; color: var(--muted); padding: 48px; }

    /* ── Responsive ── */
    @media (max-width: 1024px) {
        .payments-stat-grid-4, .payments-stat-grid-3 { grid-template-columns: repeat(2, 1fr); }
        .payments-form-layout { grid-template-columns: 300px 1fr; gap: 16px; }
    }

    @media (max-width: 860px) {
        .pay-head h1 { font-size: 24px; }
        .payments-form-layout, .instructor-layout { grid-template-columns: 1fr; }
        .payments-stat-grid-4, .payments-stat-grid-3 { gap: 10px; }
        .payments-stat-grid-4 .stat-value,
        .payments-stat-grid-3 .stat-value { font-size: 22px; }
        .record-payment-card { padding: 18px; }
        table { min-width: 600px; }
        table th, table td { padding: 10px 14px; font-size: 12px; }
        .section-title { font-size: 15px; }
        .section-title .sub { display: block; margin-left: 0; font-size: 11px; }
        .payments-tabs { width: 100%; }
        .payments-tabs .earn-tab { flex: 1; text-align: center; }
    }

    @media (max-width: 480px) {
        .pay-head h1 { font-size: 20px; }
        .pay-head p { font-size: 12px; }
        .payments-stat-grid-4, .payments-stat-grid-3 { grid-template-columns: 1fr 1fr; gap: 8px; }
        .payments-stat-grid-4 .stat-value,
        .payments-stat-grid-3 .stat-value { font-size: 18px; }
        .record-payment-card { padding: 14px 12px; border-radius: 10px; }
        .form-control { font-size: 16px; }
        select.form-control { padding-right: 34px; background-size: 14px; background-position: right 10px center; }
        table { min-width: 500px; }
        table th, table td { padding: 8px 10px; font-size: 11px; }
        .member-cell-avatar, .member-cell-placeholder { width: 26px; height: 26px; font-size: 9px; }
        .leaderboard-avatar, .leaderboard-avatar-placeholder { width: 28px; height: 28px; font-size: 10px; }
        .leaderboard-name { font-size: 12px; }
        .leaderboard-total { font-size: 14px; }
        .empty-state { padding: 24px 12px; font-size: 12px; }
        .date-picker-btn { width: 36px; }
    }

    @media (prefers-reduced-motion: reduce) {
        * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
    }
</style>

{{-- Page header --}}
<div class="pay-head">
    <div>
        <h1>Payment Transactions</h1>
        <p>Track gym fees, coach fees, and earnings per role.</p>
    </div>
    <div class="payments-tabs">
        <button class="earn-tab active" onclick="showTab('admin')" id="tab-admin">Admin Earnings</button>
        <button class="earn-tab" onclick="showTab('instructor')" id="tab-instructor">Instructor Earnings</button>
    </div>
</div>

{{-- ══ ADMIN EARNINGS PANEL ══ --}}
<div id="panel-admin">

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
                    @error('member_id')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Amount (₱)</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0"
                           placeholder="0.00" value="{{ old('amount') }}" required/>
                    @error('amount')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <div class="date-field-wrap">
                        <input type="date" name="payment_date" id="payment_date" class="form-control"
                               value="{{ old('payment_date', date('Y-m-d')) }}" required
                               style="padding-right:48px;flex:1;"/>
                        <button type="button" class="date-picker-btn"
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
                    @error('payment_date')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Method</label>
                    <select name="method" class="form-control" required>
                        <option value="" disabled selected>— Select Method —</option>
                        @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
                            <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                    @error('method')<div class="field-error">{{ $message }}</div>@enderror
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
                                <td><span class="badge-plan">{{ $payment['membership_type'] ?? '—' }}</span></td>
                                <td class="amount-text">₱{{ number_format($payment['amount'], 0) }}</td>
                                <td class="text-muted">{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</td>
                                <td><span class="badge-method">{{ $payment['method'] ?? 'Cash' }}</span></td>
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

{{-- ══ INSTRUCTOR EARNINGS PANEL ══ --}}
<div id="panel-instructor" style="display:none;">

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
                                        <span style="font-weight:600;color:var(--text);">{{ $cp['member']['name'] ?? '—' }}</span>
                                        <span style="color:var(--muted);margin:0 6px;">→</span>
                                        <span class="accent-blue" style="font-weight:600;">{{ $cp['instructor']['name'] ?? '—' }}</span>
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

<script>
    function showTab(tab) {
        document.getElementById('panel-admin').style.display = tab === 'admin' ? 'block' : 'none';
        document.getElementById('panel-instructor').style.display = tab === 'instructor' ? 'block' : 'none';
        document.getElementById('tab-admin').classList.toggle('active', tab === 'admin');
        document.getElementById('tab-instructor').classList.toggle('active', tab === 'instructor');
    }
</script>

@endsection