@extends('layouts.instructor')
@section('title', 'Member Detail – APEX')
@section('active', 'dashboard')

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
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-header-left h1 {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text);
    }
    .page-header-left h1 span {
        color: var(--accent);
    }
    .page-header-left p {
        color: var(--muted);
        font-size: clamp(0.8rem, 1.2vw, 1rem);
    }
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: var(--radius);
        background: var(--surface2);
        border: 1px solid var(--border);
        color: var(--muted);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .back-btn:hover {
        color: var(--text);
        border-color: var(--accent);
        background: rgba(255,0,0,0.05);
    }

    /* Main Grid */
    .detail-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
        align-items: start;
    }

    /* Profile Card */
    .profile-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 28px;
        text-align: center;
        transition: border-color 0.3s ease;
    }
    .profile-card:hover {
        border-color: var(--accent);
    }
    .profile-avatar {
        margin: 0 auto 16px;
        width: 88px;
        height: 88px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid var(--accent);
        background: rgba(255,0,0,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 30px rgba(255,0,0,0.15);
        transition: all 0.3s ease;
    }
    .profile-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 0 40px rgba(255,0,0,0.25);
    }
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-avatar-placeholder {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 30px;
        color: var(--accent);
    }
    .profile-name {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 4px;
        color: var(--text);
    }
    .profile-email {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 14px;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 16px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    /* Info Card */
    .info-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 22px;
        transition: border-color 0.3s ease;
    }
    .info-card:hover {
        border-color: var(--accent);
    }
    .info-title {
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 16px;
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        background: var(--surface2);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        transition: border-color 0.3s ease;
        margin-bottom: 10px;
    }
    .info-item:last-child {
        margin-bottom: 0;
    }
    .info-item:hover {
        border-color: var(--accent);
    }
    .info-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .info-icon.phone { background: rgba(255,68,68,0.12); }
    .info-icon.gender { background: rgba(255,107,53,0.12); }
    .info-icon.birth { background: rgba(255,107,53,0.12); }
    .info-icon.address { background: rgba(255,68,68,0.12); }
    .info-icon svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        fill: none;
    }
    .info-icon.phone svg { stroke: #ff4444; }
    .info-icon.gender svg { stroke: #ff6b35; }
    .info-icon.birth svg { stroke: #ff6b35; }
    .info-icon.address svg { stroke: #ff4444; }
    .info-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    .info-value {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
    }

    /* Right Column */
    .right-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Membership Card */
    .membership-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 26px;
        transition: border-color 0.3s ease;
    }
    .membership-card:hover {
        border-color: var(--accent);
    }
    .membership-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 16px;
    }
    .membership-item {
        padding: 16px;
        background: var(--surface2);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        transition: border-color 0.3s ease;
    }
    .membership-item:hover {
        border-color: var(--accent);
    }
    .membership-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }
    .membership-value {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
    }
    .membership-value.accent {
        color: var(--accent);
    }
    .membership-value.danger {
        color: var(--danger);
    }

    /* Alert Messages */
    .alert {
        padding: 12px 16px;
        border-radius: var(--radius);
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .alert-danger {
        background: rgba(255,0,0,0.08);
        border: 1px solid rgba(255,0,0,0.2);
        color: var(--danger);
    }
    .alert-warning {
        background: rgba(255,107,53,0.08);
        border: 1px solid rgba(255,107,53,0.2);
        color: var(--warning);
    }
    .alert svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
    }

    /* Payments Card */
    .payments-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        transition: border-color 0.3s ease;
    }
    .payments-card:hover {
        border-color: var(--accent);
    }
    .payments-header {
        padding: 20px 24px 16px;
        border-bottom: 2px solid var(--accent);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }
    .payments-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--accent);
    }
    .payments-count {
        font-size: 12px;
        color: var(--muted);
    }
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }
    thead {
        background: var(--surface2);
        border-bottom: 1px solid var(--border);
    }
    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
        white-space: nowrap;
    }
    td {
        padding: 14px 20px;
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
        font-size: 11px;
        color: var(--muted);
    }
    .payment-amount {
        font-size: 14px;
        font-weight: 700;
        color: var(--accent);
    }
    .payment-date {
        font-size: 13px;
        color: var(--muted);
    }
    .payment-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(255,68,68,0.15);
        color: var(--success);
        border: 1px solid rgba(255,68,68,0.2);
    }
    .status-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--success);
        display: inline-block;
    }
    .empty-state {
        padding: 40px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
    }

    /* ===== RESPONSIVE ===== */

    @media (max-width: 1024px) {
        .detail-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .profile-card {
            max-width: 400px;
            margin: 0 auto;
        }
        .membership-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .back-btn {
            justify-content: center;
        }
        .membership-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .profile-card {
            max-width: 100%;
            padding: 20px;
        }
        .profile-avatar {
            width: 72px;
            height: 72px;
        }
        .payments-header {
            padding: 16px 18px 12px;
        }
        th, td {
            padding: 10px 14px;
            font-size: 12px;
        }
        table {
            min-width: 450px;
        }
    }

    @media (max-width: 480px) {
        .detail-grid {
            gap: 12px;
        }
        .profile-card {
            padding: 16px;
        }
        .profile-avatar {
            width: 64px;
            height: 64px;
        }
        .profile-avatar-placeholder {
            font-size: 24px;
        }
        .profile-name {
            font-size: 18px;
        }
        .info-card {
            padding: 16px;
        }
        .info-item {
            padding: 9px 12px;
        }
        .membership-card {
            padding: 18px;
        }
        .membership-grid {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .membership-item {
            padding: 12px;
        }
        .membership-value {
            font-size: 14px;
        }
        .payments-header {
            padding: 14px 14px 10px;
        }
        .payments-title {
            font-size: 14px;
        }
        th, td {
            padding: 8px 10px;
            font-size: 11px;
        }
        table {
            min-width: 380px;
        }
        .receipt-id {
            font-size: 10px;
        }
        .payment-amount {
            font-size: 13px;
        }
        .payment-status {
            font-size: 10px;
            padding: 2px 8px;
        }
        .alert {
            font-size: 12px;
            padding: 10px 12px;
        }
    }

    @media (max-width: 360px) {
        .membership-grid {
            grid-template-columns: 1fr;
        }
        .profile-avatar {
            width: 56px;
            height: 56px;
        }
        table {
            min-width: 320px;
        }
        th, td {
            padding: 6px 8px;
            font-size: 10px;
        }
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
    .profile-card {
        animation: fadeInUp 0.4s ease forwards;
    }
    .info-card {
        animation: fadeInUp 0.4s ease 0.05s forwards;
        opacity: 0;
    }
    .membership-card {
        animation: fadeInUp 0.4s ease 0.1s forwards;
        opacity: 0;
    }
    .payments-card {
        animation: fadeInUp 0.4s ease 0.15s forwards;
        opacity: 0;
    }
</style>

{{-- Page Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>Member <span>Detail</span></h1>
        <p>Viewing full profile and payment history</p>
    </div>
    <a href="{{ route('instructor.dashboard') }}" class="back-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Back to Dashboard
    </a>
</div>

@php
    $memberPhoto = $member->user?->photo ?? $member->photo ?? null;
    $isExpired   = $member->isExpired();
    $isExpiring  = $member->isDueWithinDays(7) && !$isExpired;
    $statusLabel = $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active');
    $statusColor = $isExpired ? '#ff3333' : ($isExpiring ? '#ff6b35' : '#ff4444');
    $statusBg    = $isExpired ? 'rgba(255,51,51,0.15)' : ($isExpiring ? 'rgba(255,107,53,0.15)' : 'rgba(255,68,68,0.15)');
@endphp

{{-- Main Grid --}}
<div class="detail-grid">

    {{-- LEFT: Profile Card --}}
    <div>
        {{-- Avatar + Name --}}
        <div class="profile-card">
            <div class="profile-avatar">
                @if($memberPhoto)
                    <img src="{{ asset('storage/'.$memberPhoto) }}" alt="{{ $member->name }}"/>
                @else
                    <span class="profile-avatar-placeholder">
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    </span>
                @endif
            </div>

            <div class="profile-name">{{ $member->name }}</div>
            <div class="profile-email">{{ $member->email }}</div>

            <span class="status-badge" style="background:{{ $statusBg }};color:{{ $statusColor }};border-color:{{ $statusColor }}44;">
                <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};display:inline-block;"></span>
                {{ $statusLabel }}
            </span>
        </div>

        {{-- Personal Info --}}
        <div class="info-card">
            <div class="info-title">Personal Information</div>

            <div class="info-item">
                <div class="info-icon phone">
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div>
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $member->phone ?? 'Not set' }}</div>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon gender">
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <div class="info-label">Gender</div>
                    <div class="info-value">{{ $member->gender ?? 'Not set' }}</div>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon birth">
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <div class="info-label">Birthdate</div>
                    <div class="info-value">{{ $member->birthdate?->format('M d, Y') ?? 'Not set' }}</div>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon address">
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $member->address ?? 'Not set' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Membership + Payments --}}
    <div class="right-column">

        {{-- Membership Details --}}
        <div class="membership-card">
            <div class="info-title">Membership Details</div>

            <div class="membership-grid">
                <div class="membership-item">
                    <div class="membership-label">Fitness Plan</div>
                    <div class="membership-value accent">{{ $member->fitness_plan ?? 'Not set' }}</div>
                </div>
                <div class="membership-item">
                    <div class="membership-label">Duration</div>
                    <div class="membership-value">{{ $member->membership_type ?? 'Not set' }}</div>
                </div>
                <div class="membership-item">
                    <div class="membership-label">Fee</div>
                    <div class="membership-value accent">₱{{ number_format($member->fee ?? 0, 2) }}</div>
                </div>
                <div class="membership-item">
                    <div class="membership-label">Start Date</div>
                    <div class="membership-value">{{ $member->start_date?->format('M d, Y') ?? 'Not set' }}</div>
                </div>
                <div class="membership-item">
                    <div class="membership-label">End Date</div>
                    <div class="membership-value {{ $isExpired ? 'danger' : '' }}">
                        {{ $member->end_date?->format('M d, Y') ?? 'Not set' }}
                    </div>
                </div>
                <div class="membership-item">
                    <div class="membership-label">Status</div>
                    <span style="display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:700;color:{{ $statusColor }};">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};display:inline-block;"></span>
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            @if($isExpired)
                <div class="alert alert-danger">
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    Membership expired on {{ $member->end_date->format('M d, Y') }}.
                </div>
            @elseif($isExpiring)
                <div class="alert alert-warning">
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    Membership expiring in {{ (int) now()->diffInDays($member->end_date) }} days.
                </div>
            @endif
        </div>

        {{-- Payment History --}}
        <div class="payments-card">
            <div class="payments-header">
                <div class="payments-title">Payment History</div>
                <div class="payments-count">{{ $payments->count() }} transaction(s)</div>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td class="receipt-id">{{ $p->receipt_number }}</td>
                            <td>{{ $p->fitness_plan }} / {{ $p->membership_type }}</td>
                            <td class="payment-amount">₱{{ number_format($p->amount, 2) }}</td>
                            <td class="payment-date">{{ $p->payment_date->format('M d, Y') }}</td>
                            <td>
                                <span class="payment-status">
                                    <span class="status-dot"></span>
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">No payments on record.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection