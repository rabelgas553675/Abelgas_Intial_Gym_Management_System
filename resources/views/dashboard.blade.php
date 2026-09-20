@extends('layouts.admin')
@section('title', 'Admin Dashboard – APEX FITNESS GYM')
@section('active', 'dashboard')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .welcome-section {
        margin-bottom: 32px;
    }

    .welcome-section h1 {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .welcome-section h1 span {
        color: var(--accent);
    }

    .welcome-section p {
        color: var(--muted);
        font-size: 14px;
    }

    /* Stat Cards */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
        min-height: 100px;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card-left {
        flex: 1;
        min-width: 0;
    }

    .stat-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }

    .stat-sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 2px;
    }

    .stat-up {
        color: #4ade80;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-left: 12px;
    }

    .stat-icon svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .icon-green {
        background: rgba(74, 222, 128, 0.12);
        color: #4ade80;
    }

    .icon-orange {
        background: rgba(251, 191, 36, 0.12);
        color: #fbbf24;
    }

    .icon-blue {
        background: rgba(96, 165, 250, 0.12);
        color: #60a5fa;
    }

    .icon-yellow {
        background: rgba(200, 255, 0, 0.12);
        color: var(--accent);
    }

    .stat-card.orange .stat-value {
        color: #fbbf24;
    }

    .stat-card.blue .stat-value {
        color: #60a5fa;
    }

    .stat-card.yellow .stat-value {
        color: var(--warning);
    }

    /* Section Headers */
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

    /* Tables */
    .table-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .dashboard-table th {
        padding: 12px 20px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 2px;
        background: var(--surface2);
        white-space: nowrap;
    }

    .dashboard-table td {
        padding: 14px 20px;
        border-top: 1px solid var(--border);
        vertical-align: middle;
    }

    .dashboard-table tr:first-child td {
        border-top: none;
    }

    .dashboard-table .user-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dashboard-table .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid var(--border);
    }

    .dashboard-table .user-avatar-placeholder {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
        background: rgba(255, 43, 61, 0.08);
        border: 1px solid rgba(255, 43, 61, 0.15);
        color: var(--accent);
    }

    .dashboard-table .user-name {
        font-size: 14px;
        font-weight: 600;
    }

    .dashboard-table .user-email {
        font-size: 11px;
        color: var(--muted);
    }

    .dashboard-table .text-muted {
        color: var(--muted);
        font-size: 13px;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .badge-monthly {
        background: rgba(96, 165, 250, 0.15);
        color: #60a5fa;
    }

    .badge-weekly {
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
    }

    .badge-daily {
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
    }

    .badge-active {
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
    }

    .badge-expired {
        background: rgba(248, 113, 113, 0.15);
        color: #f87171;
    }

    .badge-suspended {
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
    }

    .badge-pending {
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
    }

    .badge-paid {
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
    }

    /* View button */
    .view-btn {
        font-size: 12px;
        font-weight: 600;
        padding: 5px 12px;
        background: transparent;
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text);
        text-decoration: none;
        transition: all 0.15s;
        display: inline-block;
    }

    .view-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .view-link {
        font-size: 12px;
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
    }

    .view-link:hover {
        text-decoration: underline;
    }

    /* Empty State */
    .empty-state {
        padding: 48px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
    }

    .empty-state a {
        color: var(--accent);
        margin-left: 4px;
    }

    /* Bottom Grid */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    /* Payment Item */
    .payment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 20px;
        border-bottom: 1px solid var(--border);
        transition: background 0.1s;
    }

    .payment-item:hover {
        background: rgba(255, 255, 255, 0.015);
    }

    .payment-item:last-child {
        border-bottom: none;
    }

    .payment-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .payment-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--surface2);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .payment-icon svg {
        width: 14px;
        height: 14px;
        stroke: var(--muted);
        fill: none;
        stroke-width: 2;
    }

    .payment-info {
        min-width: 0;
    }

    .payment-name {
        font-size: 13px;
        font-weight: 600;
    }

    .payment-details {
        font-size: 11px;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .payment-right {
        text-align: right;
        flex-shrink: 0;
        margin-left: 12px;
    }

    .payment-amount {
        font-size: 14px;
        font-weight: 700;
        color: var(--accent);
    }

    .payment-status {
        font-size: 11px;
        color: #4ade80;
    }

    /* User Item */
    .user-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 20px;
        border-bottom: 1px solid var(--border);
        transition: background 0.1s;
    }

    .user-item:hover {
        background: rgba(255, 255, 255, 0.015);
    }

    .user-item:last-child {
        border-bottom: none;
    }

    .user-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }

    .user-avatar-sm {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid var(--border);
    }

    .user-avatar-placeholder-sm {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
        background: rgba(255, 43, 61, 0.08);
        border: 1px solid rgba(255, 43, 61, 0.15);
        color: var(--accent);
    }

    .user-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        margin-left: 12px;
    }

    .user-role-badge {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 3px 9px;
        border-radius: 5px;
        white-space: nowrap;
    }

    .user-joined {
        font-size: 11px;
        color: var(--muted);
        white-space: nowrap;
    }

    /* Responsive */

    @media (max-width: 1024px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .bottom-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0 12px;
        }

        .welcome-section h1 {
            font-size: 24px;
        }

        .welcome-section p {
            font-size: 13px;
        }

        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .stat-card {
            padding: 16px;
            min-height: 80px;
            border-radius: 12px;
        }

        .stat-value {
            font-size: 22px;
        }

        .stat-label {
            font-size: 10px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
        }

        .stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .section-title {
            font-size: 15px;
        }

        .dashboard-table {
            min-width: 600px;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 10px 14px;
            font-size: 12px;
        }

        .dashboard-table .user-name {
            font-size: 13px;
        }

        .dashboard-table .user-avatar,
        .dashboard-table .user-avatar-placeholder {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }

        .payment-item,
        .user-item {
            padding: 10px 14px;
        }

        .payment-name {
            font-size: 12px;
        }

        .payment-details {
            font-size: 10px;
        }

        .payment-amount {
            font-size: 13px;
        }

        .user-avatar-sm,
        .user-avatar-placeholder-sm {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }

        .user-role-badge {
            font-size: 9px;
            padding: 2px 7px;
        }

        .user-joined {
            font-size: 10px;
        }

        .empty-state {
            padding: 32px 16px;
            font-size: 13px;
        }

        .view-btn {
            font-size: 11px;
            padding: 4px 10px;
        }
    }

    @media (max-width: 480px) {
        .dashboard-container {
            padding: 0 8px;
        }

        .welcome-section h1 {
            font-size: 20px;
        }

        .welcome-section p {
            font-size: 12px;
        }

        .stat-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .stat-card {
            padding: 12px;
            min-height: 70px;
            border-radius: 10px;
        }

        .stat-value {
            font-size: 18px;
        }

        .stat-label {
            font-size: 9px;
            letter-spacing: 1px;
        }

        .stat-sub {
            font-size: 10px;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }

        .stat-icon svg {
            width: 16px;
            height: 16px;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .section-title {
            font-size: 14px;
        }

        .table-card {
            border-radius: 10px;
            margin-bottom: 16px;
        }

        .dashboard-table {
            min-width: 500px;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .dashboard-table .user-name {
            font-size: 12px;
        }

        .dashboard-table .user-email {
            font-size: 10px;
        }

        .dashboard-table .user-avatar,
        .dashboard-table .user-avatar-placeholder {
            width: 24px;
            height: 24px;
            font-size: 9px;
        }

        .badge {
            font-size: 9px;
            padding: 2px 8px;
        }

        .view-btn {
            font-size: 10px;
            padding: 3px 8px;
        }

        .bottom-grid {
            gap: 12px;
        }

        .payment-item {
            padding: 8px 10px;
            flex-wrap: wrap;
        }

        .payment-left {
            min-width: 0;
        }

        .payment-icon {
            width: 28px;
            height: 28px;
        }

        .payment-icon svg {
            width: 12px;
            height: 12px;
        }

        .payment-name {
            font-size: 11px;
        }

        .payment-details {
            font-size: 9px;
        }

        .payment-amount {
            font-size: 12px;
        }

        .payment-right {
            margin-left: 8px;
        }

        .user-item {
            padding: 8px 10px;
            flex-wrap: wrap;
        }

        .user-avatar-sm,
        .user-avatar-placeholder-sm {
            width: 24px;
            height: 24px;
            font-size: 9px;
        }

        .user-item .user-name {
            font-size: 12px;
        }

        .user-item .user-email {
            font-size: 10px;
        }

        .user-role-badge {
            font-size: 8px;
            padding: 2px 6px;
        }

        .user-joined {
            font-size: 9px;
        }

        .empty-state {
            padding: 24px 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 360px) {
        .stat-grid {
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .stat-card {
            padding: 10px 8px;
            min-height: 60px;
            border-radius: 8px;
        }

        .stat-value {
            font-size: 16px;
        }

        .stat-label {
            font-size: 8px;
        }

        .stat-icon {
            width: 28px;
            height: 28px;
        }

        .stat-icon svg {
            width: 14px;
            height: 14px;
        }

        .dashboard-table {
            min-width: 400px;
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .dashboard-table .user-name {
            font-size: 11px;
        }
    }
</style>

<div class="dashboard-container">

    {{-- Welcome --}}
    <div class="welcome-section">
        <h1>
            Welcome back, <span>{{ explode(' ', auth()->user()->name)[0] }}</span>
        </h1>
        <p>Here's what's happening at APEX FITNESS GYM today.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card-left">
                <div class="stat-label">Total Members</div>
                <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-sub stat-up">All time registrations</div>
            </div>
            <div class="stat-icon icon-green">
                <svg viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-card-left">
                <div class="stat-label">Active Members</div>
                <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
                <div class="stat-sub">Currently enrolled</div>
            </div>
            <div class="stat-icon icon-orange">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8"/>
                    <circle cx="12" cy="12" r="3" fill="currentColor" stroke="none"/>
                </svg>
            </div>
        </div>

        <div class="stat-card blue">
            <div class="stat-card-left">
                <div class="stat-label">Monthly Plans</div>
                <div class="stat-value">{{ $stats['monthly'] ?? 0 }}</div>
                <div class="stat-sub">Monthly subscribers</div>
            </div>
            <div class="stat-icon icon-blue">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
        </div>

        <div class="stat-card yellow">
            <div class="stat-card-left">
                <div class="stat-label">This Month</div>
                <div class="stat-value" style="font-size:26px;">
                    ₱{{ number_format($thisMonth ?? 0, 0) }}
                </div>
                <div class="stat-sub">
                    Total: <span style="color:var(--success);font-weight:700;">₱{{ number_format($totalCollected ?? 0, 0) }}</span>
                </div>
            </div>
            <div class="stat-icon icon-yellow">
                <svg viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Recent Members --}}
    <div class="section-header">
        <div class="section-title">Recent Members</div>
        <a href="{{ route('members.index') }}" class="view-btn">View All</a>
    </div>

    <div class="table-card">
        <div class="table-wrapper">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentMembers ?? [] as $member)
                    <tr>
                        <td>
                            <div class="user-cell">
                                @if($member->photo)
                                    <img src="{{ asset('storage/'.$member->photo) }}" class="user-avatar" alt=""/>
                                @else
                                    <div class="user-avatar-placeholder">
                                        {{ strtoupper(substr($member->name,0,2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="user-name">{{ $member->name }}</div>
                                    <div class="user-email">{{ $member->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $member->email }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower($member->membership_type ?? 'monthly') }}">
                                {{ $member->membership_type ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ strtolower($member->status) }}">
                                {{ $member->status }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $member->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('members.show', $member) }}" class="view-btn">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            No members yet.
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('members.create') }}">Add one →</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bottom Row: Recent Payments + System Users --}}
    <div class="bottom-grid">

        {{-- Recent Payments --}}
        <div class="table-card" style="margin-bottom:0;">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div class="section-title" style="font-size:15px;">Recent Payments</div>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('payments.index') }}" class="view-link">View All →</a>
                @endif
            </div>
            @forelse(($recentPayments ?? collect())->take(6) as $pay)
                <div class="payment-item">
                    <div class="payment-left">
                        <div class="payment-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="1" y="4" width="22" height="16" rx="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                        </div>
                        <div class="payment-info">
                            <div class="payment-name">{{ $pay->member->name ?? 'Unknown' }}</div>
                            <div class="payment-details">
                                {{ $pay->fitness_plan }} · {{ $pay->membership_type }} · {{ $pay->payment_date->format('M d, Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="payment-right">
                        <div class="payment-amount">₱{{ number_format($pay->amount, 0) }}</div>
                        <div class="payment-status">{{ $pay->status }}</div>
                    </div>
                </div>
            @empty
                <div class="empty-state">No payments recorded yet.</div>
            @endforelse
        </div>

        {{-- System Users --}}
        <div class="table-card" style="margin-bottom:0;">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div class="section-title" style="font-size:15px;">System Users</div>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="view-link">Manage →</a>
                @endif
            </div>
            @forelse(($recentUsers ?? collect())->take(6) as $u)
                @php
                    $roleColor = match($u->role) {
                        'admin'      => ['bg'=>'rgba(255,43,61,0.1)',  'color'=>'var(--accent)'],
                        'staff'      => ['bg'=>'rgba(251,191,36,0.1)', 'color'=>'var(--warning)'],
                        'instructor' => ['bg'=>'rgba(96,165,250,0.1)', 'color'=>'#60a5fa'],
                        default      => ['bg'=>'rgba(167,139,250,0.1)','color'=>'#a78bfa'],
                    };
                @endphp
                <div class="user-item">
                    <div class="user-left">
                        @if($u->photo)
                            <img src="{{ asset('storage/'.$u->photo) }}" class="user-avatar-sm" alt=""/>
                        @else
                            <div class="user-avatar-placeholder-sm">
                                {{ strtoupper(substr($u->name,0,2)) }}
                            </div>
                        @endif
                        <div>
                            <div class="user-name" style="font-size:13px;font-weight:600;">{{ $u->name }}</div>
                            <div class="user-email" style="font-size:11px;color:var(--muted);">{{ $u->email }}</div>
                        </div>
                    </div>
                    <div class="user-right">
                        <span class="user-role-badge" style="background:{{ $roleColor['bg'] }};color:{{ $roleColor['color'] }};">
                            {{ ucfirst($u->role) }}
                        </span>
                        <span class="user-joined">{{ $u->created_at->format('M d') }}</span>
                    </div>
                </div>
            @empty
                <div class="empty-state">No users found.</div>
            @endforelse
        </div>

    </div>

</div>

@endsection