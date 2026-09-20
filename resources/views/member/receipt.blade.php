@extends('layouts.member')
@section('title', 'Receipt – IRONFORGE')
@section('page_title', 'Payment Receipt')

@section('topbar_actions')
  <button onclick="window.print()" class="btn btn-primary">🖨 Print Receipt</button>
  <a href="{{ route('member.dashboard') }}" class="btn btn-secondary">← Dashboard</a>
@endsection

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .receipt-container {
        max-width: 660px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .alert-success {
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.3);
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 20px;
        color: var(--success);
    }

    .receipt-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.2s;
    }

    /* Receipt Header */
    .receipt-header {
        background: var(--accent);
        padding: 24px 32px;
    }

    .receipt-header .brand {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .receipt-header .brand-left .name {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 32px;
        color: #111;
        letter-spacing: 3px;
        line-height: 1;
    }

    .receipt-header .brand-left .sub {
        font-size: 11px;
        color: #444;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 700;
    }

    .receipt-header .brand-right {
        text-align: right;
    }

    .receipt-header .brand-right .receipt-no {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 20px;
        color: #111;
        letter-spacing: 2px;
    }

    .receipt-header .brand-right .receipt-date {
        font-size: 12px;
        color: #333;
        font-weight: 600;
    }

    /* Scissor Cut Line */
    .cut-line {
        height: 1px;
        border-top: 2px dashed var(--border);
        position: relative;
        margin: 0;
    }

    .cut-line .scissor {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background: var(--surface);
        padding: 0 12px;
        color: var(--muted);
        font-size: 16px;
    }

    /* Receipt Body */
    .receipt-body {
        padding: 28px 32px;
    }

    /* Member Info */
    .member-info {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }

    .member-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--accent);
        flex-shrink: 0;
    }

    .member-avatar-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(232, 255, 42, 0.1);
        border: 2px solid var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 20px;
        color: var(--accent);
        flex-shrink: 0;
    }

    .member-details {
        flex: 1;
        min-width: 0;
    }

    .member-details .name {
        font-size: 17px;
        font-weight: 700;
        color: var(--text);
    }

    .member-details .email {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 6px;
        word-break: break-all;
    }

    .paid-badge {
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
        border: 1px solid rgba(74, 222, 128, 0.3);
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 5px;
        display: inline-block;
    }

    /* Detail Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .detail-section .section-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 12px;
        font-weight: 700;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 7px 0;
        font-size: 13px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        gap: 8px;
    }

    .detail-row .label {
        color: var(--muted);
        flex-shrink: 0;
    }

    .detail-row .value {
        font-weight: 600;
        color: var(--text);
        text-align: right;
        word-break: break-all;
    }

    /* Coach Info Banner */
    .coach-banner {
        background: rgba(96, 165, 250, 0.06);
        border: 1px solid rgba(96, 165, 250, 0.2);
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .coach-banner svg {
        width: 16px;
        height: 16px;
        stroke: #60a5fa;
        fill: none;
        stroke-width: 2;
        flex-shrink: 0;
    }

    .coach-banner .text {
        font-size: 12px;
        color: var(--muted);
        line-height: 1.6;
    }

    .coach-banner .text strong {
        color: #60a5fa;
    }

    .coach-banner .text .highlight {
        color: #fff;
    }

    /* Breakdown Box */
    .breakdown-box {
        background: rgba(232, 255, 42, 0.04);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .breakdown-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        font-size: 14px;
        gap: 12px;
    }

    .breakdown-row:last-child {
        border-bottom: none;
    }

    .breakdown-row .left .title {
        font-weight: 600;
        color: var(--text);
    }

    .breakdown-row .left .sub {
        font-size: 12px;
        color: var(--muted);
    }

    .breakdown-row .amount {
        font-weight: 700;
        color: var(--text);
        font-size: 15px;
        white-space: nowrap;
    }

    .breakdown-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0 4px;
        margin-top: 4px;
        gap: 12px;
    }

    .breakdown-total .left .label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }

    .breakdown-total .left .sub {
        font-size: 12px;
        color: var(--muted);
    }

    .breakdown-total .total-amount {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 44px;
        color: var(--accent);
        letter-spacing: 2px;
        line-height: 1;
        white-space: nowrap;
    }

    /* Footer */
    .receipt-footer {
        border-top: 1px dashed var(--border);
        padding-top: 18px;
        text-align: center;
    }

    .receipt-footer .thanks {
        font-size: 12px;
        color: var(--muted);
    }

    .receipt-footer .brand {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 14px;
        color: var(--accent);
        letter-spacing: 3px;
        margin-top: 10px;
        opacity: 0.6;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        padding: 12px 28px;
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
        min-height: 48px;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-primary {
        background: var(--accent);
        color: #000;
    }

    .btn-primary:hover {
        opacity: .88;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--border);
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 768px) {
        .receipt-container {
            padding: 0 12px;
        }

        .receipt-header {
            padding: 20px 20px;
        }

        .receipt-header .brand-left .name {
            font-size: 26px;
        }

        .receipt-header .brand-right .receipt-no {
            font-size: 17px;
        }

        .receipt-body {
            padding: 20px 20px;
        }

        .member-info {
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 16px;
        }

        .member-avatar,
        .member-avatar-placeholder {
            width: 44px;
            height: 44px;
            font-size: 17px;
        }

        .member-details .name {
            font-size: 15px;
        }

        .member-details .email {
            font-size: 12px;
        }

        .detail-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .detail-row {
            font-size: 12px;
            padding: 6px 0;
        }

        .breakdown-box {
            padding: 16px 18px;
        }

        .breakdown-row {
            font-size: 13px;
            padding: 8px 0;
        }

        .breakdown-row .amount {
            font-size: 14px;
        }

        .breakdown-total .total-amount {
            font-size: 36px;
        }

        .coach-banner {
            padding: 10px 14px;
        }

        .coach-banner .text {
            font-size: 11px;
        }

        .receipt-footer .thanks {
            font-size: 11px;
        }

        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .action-buttons .btn {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
            min-height: 44px;
            font-size: 13px;
        }

        .cut-line .scissor {
            font-size: 14px;
            padding: 0 8px;
        }
    }

    @media (max-width: 480px) {
        .receipt-container {
            padding: 0 8px;
        }

        .receipt-header {
            padding: 16px 14px;
        }

        .receipt-header .brand-left .name {
            font-size: 22px;
            letter-spacing: 2px;
        }

        .receipt-header .brand-left .sub {
            font-size: 9px;
            letter-spacing: 1px;
        }

        .receipt-header .brand-right .receipt-no {
            font-size: 15px;
        }

        .receipt-header .brand-right .receipt-date {
            font-size: 11px;
        }

        .receipt-body {
            padding: 16px 14px;
        }

        .member-avatar,
        .member-avatar-placeholder {
            width: 38px;
            height: 38px;
            font-size: 15px;
        }

        .member-details .name {
            font-size: 14px;
        }

        .member-details .email {
            font-size: 11px;
        }

        .detail-row {
            font-size: 11px;
            padding: 5px 0;
        }

        .detail-section .section-label {
            font-size: 9px;
            letter-spacing: 1.5px;
        }

        .breakdown-box {
            padding: 14px 12px;
            border-radius: 10px;
        }

        .breakdown-row {
            font-size: 12px;
            padding: 6px 0;
        }

        .breakdown-row .amount {
            font-size: 13px;
        }

        .breakdown-row .left .sub {
            font-size: 10px;
        }

        .breakdown-total {
            padding: 12px 0 4px;
        }

        .breakdown-total .total-amount {
            font-size: 30px;
        }

        .breakdown-total .left .label {
            font-size: 10px;
        }

        .breakdown-total .left .sub {
            font-size: 10px;
        }

        .coach-banner {
            padding: 8px 10px;
            gap: 8px;
        }

        .coach-banner svg {
            width: 14px;
            height: 14px;
        }

        .coach-banner .text {
            font-size: 10px;
        }

        .receipt-footer .thanks {
            font-size: 10px;
        }

        .receipt-footer .brand {
            font-size: 12px;
            margin-top: 8px;
        }

        .action-buttons .btn {
            font-size: 12px;
            padding: 10px 16px;
            min-height: 40px;
        }

        .cut-line .scissor {
            font-size: 12px;
            padding: 0 6px;
        }

        .paid-badge {
            font-size: 10px;
            padding: 1px 8px;
        }
    }

    @media (max-width: 360px) {
        .receipt-header .brand-left .name {
            font-size: 18px;
        }

        .receipt-header .brand-right .receipt-no {
            font-size: 13px;
        }

        .member-avatar,
        .member-avatar-placeholder {
            width: 32px;
            height: 32px;
            font-size: 13px;
        }

        .member-details .name {
            font-size: 13px;
        }

        .breakdown-total .total-amount {
            font-size: 26px;
        }

        .receipt-body {
            padding: 12px 10px;
        }

        .detail-row {
            font-size: 10px;
        }
    }

    /* Print Styles */
    @media print {
        body * {
            visibility: hidden !important;
        }
        #receipt-area,
        #receipt-area * {
            visibility: visible !important;
        }
        #receipt-area {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            padding: 20px !important;
        }
        .no-print {
            display: none !important;
        }
        :root {
            --bg: #fff !important;
            --surface: #fff !important;
            --border: #ddd !important;
            --text: #111 !important;
            --muted: #555 !important;
        }
        .receipt-card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        .receipt-header {
            background: #f5f5f5 !important;
        }
        .receipt-header .brand-left .name {
            color: #111 !important;
        }
        .receipt-header .brand-right .receipt-no {
            color: #111 !important;
        }
        .receipt-header .brand-right .receipt-date {
            color: #333 !important;
        }
        .member-details .name {
            color: #111 !important;
        }
        .detail-row .value {
            color: #111 !important;
        }
        .breakdown-row .amount {
            color: #111 !important;
        }
        .breakdown-total .total-amount {
            color: #111 !important;
        }
        .breakdown-box {
            border-color: #ddd !important;
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

<div class="receipt-container">
    <div id="receipt-area">

        @if(session('success'))
            <div class="alert-success">✓ {{ session('success') }}</div>
        @endif

        <div class="receipt-card">

            {{-- Header --}}
            <div class="receipt-header">
                <div class="brand">
                    <div class="brand-left">
                        <div class="name">IRONFORGE</div>
                        <div class="sub">Official Receipt</div>
                    </div>
                    <div class="brand-right">
                        <div class="receipt-no">{{ $payment->receipt_number }}</div>
                        <div class="receipt-date">{{ $payment->payment_date->format('F d, Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Scissor Cut Line --}}
            <div class="cut-line">
                <span class="scissor">✂</span>
            </div>

            {{-- Body --}}
            <div class="receipt-body">

                {{-- Member info --}}
                <div class="member-info">
                    @if(isset($member->photo) && $member->photo)
                        <img src="{{ asset('storage/'.$member->photo) }}" class="member-avatar" alt="">
                    @else
                        <div class="member-avatar-placeholder">
                            {{ strtoupper(substr($member->name ?? 'ME', 0, 2)) }}
                        </div>
                    @endif
                    <div class="member-details">
                        <div class="name">{{ $member->name }}</div>
                        <div class="email">{{ $member->email }}</div>
                        <span class="paid-badge">✓ PAID</span>
                    </div>
                </div>

                {{-- Details grid --}}
                <div class="detail-grid">
                    <div class="detail-section">
                        <div class="section-label">Plan Details</div>
                        @foreach([
                            ['Fitness Plan', $payment->fitness_plan ?? $member->fitness_plan],
                            ['Duration', $payment->membership_type ?? $member->membership_type],
                            ['Start Date', $member->start_date?->format('M d, Y')],
                            ['End Date', $member->end_date?->format('M d, Y')],
                        ] as [$label, $value])
                            <div class="detail-row">
                                <span class="label">{{ $label }}</span>
                                <span class="value">{{ $value ?? '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="detail-section">
                        <div class="section-label">Payment Info</div>
                        @foreach([
                            ['Receipt No.', $payment->receipt_number],
                            ['Method', $payment->method ?? 'Cash'],
                            ['Type', 'Gym Membership Fee'],
                            ['Instructor', $member->instructor?->name ?? 'None'],
                        ] as [$label, $value])
                            <div class="detail-row">
                                <span class="label">{{ $label }}</span>
                                <span class="value">{{ $value ?? '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── Price calculation ── --}}
                @php
                    $gymPrices = [
                        'Monthly' => 800,
                        'Quarterly' => 2100,
                        'Annually' => 7500,
                    ];
                    $coachPrices = [
                        'Monthly' => 300,
                        'Quarterly' => 1200,
                        'Annually' => 3600,
                    ];

                    $gymType = $payment->membership_type ?? $member->membership_type ?? 'Monthly';
                    $coachType = $payment->coach_membership_type ?? $member->coach_membership_type ?? null;

                    $gymFee = $gymPrices[$gymType] ?? 0;
                    $coachFee = 0;
                    $hasCoach = $member->instructor_id && $coachType;

                    if ($hasCoach) {
                        $coachFee = $coachPrices[$coachType] ?? 0;
                    }

                    $totalPaid = $gymFee + $coachFee;
                @endphp

                {{-- Coach info banner --}}
                @if($hasCoach)
                    <div class="coach-banner">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div class="text">
                            A separate <strong>coach subscription fee</strong> of
                            <strong>₱{{ number_format($coachFee, 0) }}</strong>
                            has been allocated to your instructor
                            <strong class="highlight">{{ $member->instructor->name }}</strong>
                            ({{ $coachType }} plan).
                        </div>
                    </div>
                @endif

                {{-- Breakdown box --}}
                <div class="breakdown-box">

                    {{-- Gym row --}}
                    <div class="breakdown-row">
                        <div class="left">
                            <div class="title">Gym Membership</div>
                            <div class="sub">{{ $gymType }} · {{ ['Monthly'=>'30 days','Quarterly'=>'90 days','Annually'=>'365 days'][$gymType] ?? '' }}</div>
                        </div>
                        <div class="amount">₱{{ number_format($gymFee, 2) }}</div>
                    </div>

                    {{-- Coach row --}}
                    @if($hasCoach)
                        <div class="breakdown-row">
                            <div class="left">
                                <div class="title">Coach Subscription</div>
                                <div class="sub">{{ $coachType }} · {{ $member->instructor->name }}</div>
                            </div>
                            <div class="amount">₱{{ number_format($coachFee, 2) }}</div>
                        </div>
                    @endif

                    {{-- Grand total --}}
                    <div class="breakdown-total">
                        <div class="left">
                            <div class="label">Total Fee Paid</div>
                            <div class="sub">Inclusive of all services</div>
                        </div>
                        <div class="total-amount">₱{{ number_format($totalPaid, 2) }}</div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="receipt-footer">
                    <div class="thanks">Thank you for choosing IRONFORGE! Keep this receipt for your records.</div>
                    <div class="brand">IRONFORGE GMS</div>
                </div>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="action-buttons no-print">
            <button onclick="window.print()" class="btn btn-primary">🖨 Print</button>
            <a href="{{ route('member.payments') }}" class="btn btn-secondary">Payment History</a>
            <a href="{{ route('member.dashboard') }}" class="btn btn-secondary">Dashboard</a>
        </div>

    </div>
</div>

@endsection