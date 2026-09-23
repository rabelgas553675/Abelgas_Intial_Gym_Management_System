@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', $member->name . ' – APEX')
@section('page_title', 'Member Detail')
@section('active_nav', 'members')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .detail-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        margin-bottom: 24px;
        color: var(--muted);
        flex-wrap: wrap;
    }

    .breadcrumb a {
        color: var(--muted);
        text-decoration: none;
        transition: .15s;
    }

    .breadcrumb a:hover {
        color: var(--accent);
    }

    .breadcrumb .current {
        color: var(--text);
    }

    /* Two Column Layout */
    .detail-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
        align-items: start;
    }

    .detail-left {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .detail-right {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Cards */
    .card {
        background: var(--surface1);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
        overflow: hidden;
    }

    .card-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--muted);
        margin-bottom: 20px;
    }

    /* Profile Card */
    .profile-card {
        text-align: center;
        padding: 32px 24px;
    }

    .profile-avatar {
        margin-bottom: 20px;
    }

    .profile-avatar img {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(200, 255, 0, 0.35);
        margin: 0 auto;
        display: block;
    }

    .profile-avatar .placeholder {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        margin: 0 auto;
        background: rgba(200, 255, 0, 0.08);
        border: 3px solid rgba(200, 255, 0, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 800;
        color: var(--accent);
        letter-spacing: 1px;
    }

    .profile-name {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 6px;
        letter-spacing: .3px;
    }

    .profile-email {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 18px;
        word-break: break-all;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 20px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 700;
    }

    .status-badge .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 6px currentColor;
        flex-shrink: 0;
    }

    /* Info Items */
    .info-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        background: var(--surface2);
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .info-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .info-icon svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke-width: 2;
    }

    .info-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted);
        margin-bottom: 3px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        word-break: break-word;
    }

    /* Action Buttons */
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none;
        transition: .15s;
        border: none;
        cursor: pointer;
        min-height: 48px;
        width: 100%;
    }

    .action-btn-primary {
        background: var(--accent);
        color: #000;
    }

    .action-btn-primary:hover {
        opacity: .88;
    }

    .action-btn-secondary {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
    }

    .action-btn-secondary:hover {
        border-color: rgba(255, 255, 255, .2);
    }

    /* Membership Grid */
    .membership-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .membership-item {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 18px 20px;
    }

    .membership-item .label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted);
        margin-bottom: 10px;
    }

    .membership-item .value {
        font-size: 18px;
        font-weight: 700;
    }

    .membership-item .value.accent {
        color: var(--accent);
    }

    .membership-item .value.danger {
        color: #f87171;
    }

    .membership-item .value.warning {
        color: #fbbf24;
    }

    /* Progress Bar */
    .progress-wrap {
        padding: 16px 20px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 4px;
    }

    .progress-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }

    .progress-days {
        font-size: 13px;
        font-weight: 700;
    }

    .progress-track {
        background: var(--border);
        border-radius: 999px;
        height: 6px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    /* Alert */
    .alert-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
    }

    .alert-bar svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
    }

    .alert-warning {
        background: rgba(251, 191, 36, 0.08);
        border: 1px solid rgba(251, 191, 36, 0.2);
        color: #fbbf24;
    }

    .alert-danger {
        background: rgba(248, 113, 113, 0.08);
        border: 1px solid rgba(248, 113, 113, 0.2);
        color: #f87171;
    }

    /* Payment Table */
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    .payment-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    .payment-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted);
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .payment-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .payment-table tr:last-child td {
        border-bottom: none;
    }

    .payment-table tr {
        transition: .15s;
    }

    .payment-table tr:hover {
        background: rgba(255, 255, 255, 0.015);
    }

    .receipt-number {
        font-family: monospace;
        font-size: 12px;
        color: var(--muted);
    }

    .payment-amount {
        font-size: 14px;
        font-weight: 700;
        color: var(--accent);
    }

    .payment-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .payment-status .dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }

    .payment-status.paid {
        background: rgba(74, 222, 128, 0.1);
        color: #4ade80;
    }

    .payment-status.pending {
        background: rgba(251, 191, 36, 0.1);
        color: #fbbf24;
    }

    .payment-status.overdue {
        background: rgba(248, 113, 113, 0.1);
        color: #f87171;
    }

    .empty-state {
        padding: 48px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .card-header .title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
    }

    .card-header .count {
        font-size: 13px;
        color: var(--muted);
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .detail-grid {
            grid-template-columns: 280px 1fr;
            gap: 16px;
        }
    }

    @media (max-width: 900px) {
        .detail-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .detail-left {
            max-width: 400px;
            margin: 0 auto;
            width: 100%;
        }

        .detail-right {
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .detail-container {
            padding: 0 12px;
        }

        .breadcrumb {
            font-size: 12px;
            gap: 6px;
        }

        .card {
            padding: 20px 16px;
            border-radius: 12px;
        }

        .card-title {
            font-size: 9px;
            margin-bottom: 16px;
        }

        .profile-card {
            padding: 24px 16px;
        }

        .profile-avatar img,
        .profile-avatar .placeholder {
            width: 80px;
            height: 80px;
            font-size: 22px;
        }

        .profile-name {
            font-size: 18px;
        }

        .profile-email {
            font-size: 12px;
        }

        .status-badge {
            font-size: 12px;
            padding: 6px 16px;
        }

        .membership-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .membership-item {
            padding: 14px 16px;
        }

        .membership-item .value {
            font-size: 16px;
        }

        .info-item {
            padding: 12px;
            gap: 12px;
        }

        .info-icon {
            width: 32px;
            height: 32px;
        }

        .info-icon svg {
            width: 14px;
            height: 14px;
        }

        .info-value {
            font-size: 13px;
        }

        .action-btn {
            font-size: 12px;
            padding: 10px;
            min-height: 44px;
        }

        .payment-table {
            min-width: 500px;
        }

        .payment-table th,
        .payment-table td {
            padding: 10px 12px;
            font-size: 12px;
        }

        .payment-amount {
            font-size: 13px;
        }

        .payment-status {
            font-size: 10px;
            padding: 4px 10px;
        }

        .receipt-number {
            font-size: 11px;
        }

        .empty-state {
            padding: 32px 16px;
            font-size: 12px;
        }

        .card-header .title {
            font-size: 16px;
        }

        .card-header .count {
            font-size: 12px;
        }

        .alert-bar {
            font-size: 13px;
            padding: 14px 16px;
        }

        .alert-bar svg {
            width: 16px;
            height: 16px;
        }

        .progress-wrap {
            padding: 14px 16px;
        }

        .progress-days {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .detail-container {
            padding: 0 8px;
        }

        .breadcrumb {
            font-size: 11px;
        }

        .card {
            padding: 16px 12px;
            border-radius: 10px;
        }

        .profile-card {
            padding: 20px 12px;
        }

        .profile-avatar img,
        .profile-avatar .placeholder {
            width: 70px;
            height: 70px;
            font-size: 20px;
        }

        .profile-name {
            font-size: 16px;
        }

        .profile-email {
            font-size: 11px;
            margin-bottom: 14px;
        }

        .status-badge {
            font-size: 11px;
            padding: 5px 14px;
            gap: 5px;
        }

        .status-badge .dot {
            width: 6px;
            height: 6px;
        }

        .membership-item {
            padding: 12px 14px;
        }

        .membership-item .label {
            font-size: 9px;
            margin-bottom: 6px;
        }

        .membership-item .value {
            font-size: 15px;
        }

        .info-item {
            padding: 10px 12px;
            gap: 10px;
            border-radius: 8px;
        }

        .info-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
        }

        .info-icon svg {
            width: 13px;
            height: 13px;
        }

        .info-label {
            font-size: 9px;
        }

        .info-value {
            font-size: 12px;
        }

        .action-btn {
            font-size: 11px;
            padding: 8px;
            min-height: 40px;
            border-radius: 8px;
        }

        .payment-table {
            min-width: 420px;
        }

        .payment-table th,
        .payment-table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .payment-amount {
            font-size: 12px;
        }

        .payment-status {
            font-size: 9px;
            padding: 3px 8px;
            gap: 4px;
        }

        .payment-status .dot {
            width: 4px;
            height: 4px;
        }

        .receipt-number {
            font-size: 10px;
        }

        .empty-state {
            padding: 24px 12px;
            font-size: 11px;
        }

        .card-header .title {
            font-size: 14px;
        }

        .card-header .count {
            font-size: 11px;
        }

        .alert-bar {
            font-size: 12px;
            padding: 12px 14px;
            gap: 10px;
        }

        .alert-bar svg {
            width: 14px;
            height: 14px;
        }

        .progress-wrap {
            padding: 12px 14px;
        }

        .progress-days {
            font-size: 11px;
        }
    }

    @media (max-width: 360px) {
        .payment-table {
            min-width: 360px;
        }

        .payment-table th,
        .payment-table td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .payment-amount {
            font-size: 11px;
        }

        .profile-avatar img,
        .profile-avatar .placeholder {
            width: 60px;
            height: 60px;
            font-size: 18px;
        }

        .profile-name {
            font-size: 14px;
        }

        .membership-item .value {
            font-size: 14px;
        }

        .info-value {
            font-size: 11px;
        }

        .action-btn {
            font-size: 10px;
            min-height: 36px;
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

<div class="detail-container">

    {{-- Breadcrumb --}}
    <div class="breadcrumb">
        <a href="{{ route('members.index') }}">Members</a>
        <span>/</span>
        <span class="current">{{ $member->name }}</span>
    </div>

    {{-- TWO-COLUMN LAYOUT --}}
    <div class="detail-grid">

        {{-- ── LEFT COLUMN ──────────────────────────────────── --}}
        <div class="detail-left">

            {{-- Profile Card --}}
            <div class="card profile-card">

                {{-- Avatar --}}
                <div class="profile-avatar">
                    @if($member->photo)
                        <img src="{{ asset('storage/'.$member->photo) }}" alt="{{ $member->name }}">
                    @else
                        <div class="placeholder">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                    @endif
                </div>

                {{-- Name + Email --}}
                <div class="profile-name">{{ $member->name }}</div>
                <div class="profile-email">{{ $member->email }}</div>

                {{-- Status Badge --}}
                @php
                    $dynamicStatus = $member->status;
                    $end = $member->end_date;
                    $daysLeft = $end ? (int) now()->diffInDays($end, false) : null;

                    match ($dynamicStatus) {
                        'Active' => [
                            $badgeLabel  = 'Active',
                            $badgeColor  = '#4ade80',
                            $badgeBg     = 'rgba(74,222,128,0.12)',
                            $badgeBorder = 'rgba(74,222,128,0.25)',
                        ],
                        'Expiring Soon' => [
                            $badgeLabel  = 'Expiring Soon',
                            $badgeColor  = '#fbbf24',
                            $badgeBg     = 'rgba(251,191,36,0.12)',
                            $badgeBorder = 'rgba(251,191,36,0.25)',
                        ],
                        'Expired' => [
                            $badgeLabel  = 'Expired',
                            $badgeColor  = '#f87171',
                            $badgeBg     = 'rgba(248,113,113,0.12)',
                            $badgeBorder = 'rgba(248,113,113,0.25)',
                        ],
                        'Suspended' => [
                            $badgeLabel  = 'Suspended',
                            $badgeColor  = '#fb923c',
                            $badgeBg     = 'rgba(251,146,60,0.12)',
                            $badgeBorder = 'rgba(251,146,60,0.25)',
                        ],
                        default => [
                            $badgeLabel  = $dynamicStatus,
                            $badgeColor  = 'var(--muted)',
                            $badgeBg     = 'rgba(255,255,255,0.05)',
                            $badgeBorder = 'var(--border)',
                        ],
                    };
                @endphp
                <span class="status-badge" style="background:{{ $badgeBg }};color:{{ $badgeColor }};border:1px solid {{ $badgeBorder }};">
                    <span class="dot"></span>
                    {{ $badgeLabel }}
                </span>
            </div>

            {{-- Personal Info Card --}}
            <div class="card">
                <div class="card-title">Personal Info</div>

                {{-- Phone --}}
                <div class="info-item">
                    <div class="info-icon" style="background:rgba(96,165,250,0.12);">
                        <svg stroke="#60a5fa" viewBox="0 0 24 24">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07
                                     A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .99h3a2 2 0 012 1.72
                                     c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27
                                     a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $member->phone ?? '—' }}</div>
                    </div>
                </div>

                {{-- Gender --}}
                <div class="info-item">
                    <div class="info-icon" style="background:rgba(248,113,113,0.12);">
                        <svg stroke="#f87171" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label">Gender</div>
                        <div class="info-value">{{ $member->gender ?? '—' }}</div>
                    </div>
                </div>

                {{-- Birthdate --}}
                <div class="info-item">
                    <div class="info-icon" style="background:rgba(251,191,36,0.12);">
                        <svg stroke="#fbbf24" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label">Birthdate</div>
                        <div class="info-value">{{ $member->birthdate?->format('M d, Y') ?? '—' }}</div>
                    </div>
                </div>

                {{-- Address --}}
                <div class="info-item">
                    <div class="info-icon" style="background:rgba(74,222,128,0.12);">
                        <svg stroke="#4ade80" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label">Address</div>
                        <div class="info-value">{{ $member->address ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Back / Edit Buttons --}}
            <div style="display:flex;flex-direction:column;gap:8px;">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('members.edit', $member->id) }}" class="action-btn action-btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit Member
                    </a>
                @endif
                <a href="{{ route('members.index') }}" class="action-btn action-btn-secondary">
                    ← Back to Members
                </a>
            </div>
        </div>

        {{-- ── RIGHT COLUMN ─────────────────────────────────── --}}
        <div class="detail-right">

            {{-- Membership Details Card --}}
            <div class="card">
                <div class="card-title">Membership Details</div>

                {{-- Top row: Fitness Plan / Duration / Fee --}}
                <div class="membership-grid">
                    <div class="membership-item">
                        <div class="label">Fitness Plan</div>
                        <div class="value accent">{{ $member->fitness_plan ?? $member->membership_type ?? '—' }}</div>
                    </div>

                    <div class="membership-item">
                        <div class="label">Duration</div>
                        <div class="value">{{ $member->membership_type ?? '—' }}</div>
                    </div>

                    <div class="membership-item">
                        <div class="label">Fee</div>
                        <div class="value accent">₱{{ number_format($member->fee ?? 0, 2) }}</div>
                    </div>
                </div>

                {{-- Bottom row: Start Date / End Date / Status --}}
                <div class="membership-grid" style="margin-bottom:20px;">
                    <div class="membership-item">
                        <div class="label">Start Date</div>
                        <div class="value">{{ $member->start_date?->format('M d, Y') ?? '—' }}</div>
                    </div>

                    <div class="membership-item">
                        <div class="label">End Date</div>
                        @php
                            $endDateColor = match(true) {
                                $end && $end->isPast() => '#f87171',
                                $end && now()->diffInDays($end) <= 7 => '#fbbf24',
                                default => 'var(--text)',
                            };
                        @endphp
                        <div class="value" style="color:{{ $endDateColor }};">
                            {{ $end?->format('M d, Y') ?? '—' }}
                        </div>
                    </div>

                    <div class="membership-item">
                        <div class="label">Status</div>
                        <div style="display:inline-flex;align-items:center;gap:7px;font-size:16px;font-weight:700;color:{{ $badgeColor }};">
                            <span style="width:7px;height:7px;border-radius:50%;background:currentColor;box-shadow:0 0 6px currentColor;flex-shrink:0;"></span>
                            {{ $badgeLabel }}
                        </div>
                    </div>
                </div>

                {{-- Days remaining progress bar --}}
                @if($end && !$end->isPast())
                    @php
                        $daysRemaining = (int) now()->diffInDays($end);
                        $totalDays = $member->start_date ? (int) $member->start_date->diffInDays($end) : 30;
                        $progressPct = $totalDays > 0 ? min(100, round(($daysRemaining / $totalDays) * 100)) : 0;
                        $barColor = $daysRemaining <= 7 ? '#fbbf24' : '#4ade80';
                    @endphp
                    <div class="progress-wrap">
                        <div class="progress-header">
                            <div class="progress-label">Days Remaining</div>
                            <div class="progress-days" style="color:{{ $barColor }};">
                                {{ $daysRemaining }} day{{ $daysRemaining !== 1 ? 's' : '' }} left
                            </div>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar" style="width:{{ $progressPct }}%;background:{{ $barColor }};"></div>
                        </div>
                    </div>
                @endif

                {{-- Alert Bar --}}
                @if($dynamicStatus === 'Expiring Soon')
                    <div class="alert-bar alert-warning">
                        <svg viewBox="0 0 24 24">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3 L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        Membership expiring in {{ (int) now()->diffInDays($end) }} day{{ now()->diffInDays($end) !== 1 ? 's' : '' }}.
                    </div>
                @elseif($dynamicStatus === 'Expired')
                    <div class="alert-bar alert-danger">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Membership has expired. Please renew to continue access.
                    </div>
                @endif
            </div>

            {{-- Payment History Card --}}
            <div class="card">
                <div class="card-header">
                    <div class="title">Payment History</div>
                    <div class="count">{{ ($member->payments ?? collect())->count() }} transaction(s)</div>
                </div>

                <div class="table-wrap">
                    <table class="payment-table">
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
                            @forelse($member->payments ?? [] as $payment)
                                <tr>
                                    <td>
                                        <span class="receipt-number">
                                            {{ $payment->receipt_number ?? ('RCP-'.strtoupper(substr(md5($payment->id ?? uniqid()), 0, 12))) }}
                                        </span>
                                    </td>
                                    <td style="font-size:13px;color:var(--text);">
                                        {{ $payment->fitness_plan ?? $member->fitness_plan ?? '—' }}
                                        / {{ $payment->membership_type ?? $member->membership_type ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="payment-amount">₱{{ number_format($payment->amount, 2) }}</span>
                                    </td>
                                    <td style="font-size:13px;color:var(--muted);">
                                        {{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @php
                                            $pStatus = $payment->status ?? 'Paid';
                                            $pClass = match($pStatus) {
                                                'Paid' => 'paid',
                                                'Pending' => 'pending',
                                                default => 'overdue',
                                            };
                                        @endphp
                                        <span class="payment-status {{ $pClass }}">
                                            <span class="dot"></span>
                                            {{ $pStatus }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        No payment records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- end right column --}}
    </div>{{-- end grid --}}

</div>

@endsection