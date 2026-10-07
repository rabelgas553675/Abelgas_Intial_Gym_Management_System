@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', $user->name . ' – APEX')
@section('page_title', 'User Profile')
@section('active_nav', 'members')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .profile-container {
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

    /* Grid */
    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
        align-items: start;
    }

    .profile-left {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .profile-right {
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
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(200, 255, 0, 0.35);
        margin: 0 auto 20px;
        display: block;
    }

    .profile-avatar-placeholder {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        margin: 0 auto 20px;
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
    }

    .profile-email {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 18px;
        word-break: break-all;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 20px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 700;
    }

    .role-badge .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 6px currentColor;
        flex-shrink: 0;
    }

    .specialization-badge {
        margin-top: 14px;
        padding: 10px 14px;
        background: rgba(251, 146, 60, 0.06);
        border: 1px solid rgba(251, 146, 60, 0.15);
        border-radius: 10px;
        font-size: 12px;
        color: #fb923c;
        font-weight: 600;
    }

    .experience-text {
        margin-top: 8px;
        font-size: 12px;
        color: var(--muted);
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

    /* Quick Stats Grid */
    .stats-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .stat-item {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
        text-align: center;
    }

    .stat-item .value {
        font-size: 24px;
        font-weight: 800;
        color: var(--accent);
    }

    .stat-item .value.green {
        color: #4ade80;
    }

    .stat-item .value.orange {
        color: #fb923c;
    }

    .stat-item .label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 4px;
    }

    /* Account Details Grid */
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .detail-item {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 18px 20px;
    }

    .detail-item .label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted);
        margin-bottom: 10px;
    }

    .detail-item .value {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        word-break: break-all;
    }

    .detail-item .value-sm {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        word-break: break-all;
    }

    .role-tag {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
        display: inline-block;
    }

    .status-active {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
        background: rgba(74, 222, 128, 0.1);
        color: #4ade80;
    }

    .status-active .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 6px currentColor;
    }

    /* Back Button */
    .btn-back {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        transition: .15s;
        min-height: 48px;
    }

    .btn-back:hover {
        border-color: rgba(255, 255, 255, .2);
    }

    /* Tables */
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
        border: 1px solid var(--border);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 560px;
    }

    table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted);
        white-space: nowrap;
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid var(--border);
    }

    table td {
        padding: 14px 16px;
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

    .member-avatar-sm {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        flex-shrink: 0;
        background: rgba(200, 255, 0, 0.1);
        border: 1px solid rgba(200, 255, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
    }

    .member-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .member-cell .name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
    }

    .badge-intensity {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        display: inline-block;
    }

    .badge-category {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(96, 165, 250, 0.1);
        color: #60a5fa;
        border: 1px solid rgba(96, 165, 250, 0.15);
        white-space: nowrap;
        display: inline-block;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge .dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }

    .status-badge.completed {
        background: rgba(74, 222, 128, 0.1);
        color: #4ade80;
    }

    .status-badge.upcoming {
        background: rgba(96, 165, 250, 0.1);
        color: #60a5fa;
    }

    .status-badge.upcoming .dot {
        box-shadow: 0 0 6px currentColor;
    }

    .status-badge.missed {
        background: rgba(248, 113, 113, 0.1);
        color: #f87171;
    }

    .fee-amount {
        font-size: 15px;
        font-weight: 700;
        color: var(--accent);
        white-space: nowrap;
    }

    .receipt-number {
        font-family: monospace;
        font-size: 11px;
        color: var(--muted);
        white-space: nowrap;
    }

    .empty-state {
        padding: 52px;
        text-align: center;
        color: var(--muted);
        font-size: 13px;
    }

    .empty-state svg {
        display: block;
        margin: 0 auto 10px;
        opacity: .3;
    }

    .empty-state .sub {
        font-size: 12px;
        margin-top: 6px;
        display: inline-block;
    }

    /* Section Header */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .section-header .title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }

    .section-header .sub {
        font-size: 12px;
        color: var(--muted);
    }

    .section-header .count-badge {
        padding: 5px 14px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .count-badge.orange {
        background: rgba(251, 146, 60, 0.1);
        color: #fb923c;
        border: 1px solid rgba(251, 146, 60, 0.2);
    }

    .count-badge.green {
        background: rgba(74, 222, 128, 0.1);
        color: #4ade80;
        border: 1px solid rgba(74, 222, 128, 0.2);
    }

    /* Info Notice */
    .info-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 20px;
        background: rgba(96, 165, 250, 0.06);
        border: 1px solid rgba(96, 165, 250, 0.15);
        border-radius: 12px;
        color: #60a5fa;
        font-size: 13px;
        line-height: 1.6;
    }

    .info-notice svg {
        flex-shrink: 0;
        margin-top: 1px;
    }

    .info-notice a {
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
    }

    .info-notice a:hover {
        text-decoration: underline;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .profile-grid {
            grid-template-columns: 280px 1fr;
            gap: 16px;
        }

        .details-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 900px) {
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .profile-left {
            max-width: 400px;
            margin: 0 auto;
            width: 100%;
        }

        .profile-right {
            max-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .profile-container {
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

        .profile-avatar,
        .profile-avatar-placeholder {
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

        .role-badge {
            font-size: 12px;
            padding: 6px 16px;
        }

        .details-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .detail-item {
            padding: 14px 16px;
        }

        .detail-item .value {
            font-size: 15px;
        }

        .stats-grid-2 {
            gap: 8px;
        }

        .stat-item .value {
            font-size: 20px;
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

        .btn-back {
            font-size: 12px;
            padding: 10px;
            min-height: 44px;
        }

        table {
            min-width: 500px;
        }

        table th,
        table td {
            padding: 10px 12px;
            font-size: 12px;
        }

        table th {
            font-size: 9px;
            letter-spacing: 1px;
        }

        .member-avatar-sm {
            width: 24px;
            height: 24px;
            font-size: 9px;
        }

        .member-cell .name {
            font-size: 12px;
        }

        .badge-intensity,
        .badge-category {
            font-size: 10px;
            padding: 3px 8px;
        }

        .status-badge {
            font-size: 10px;
            padding: 3px 8px;
        }

        .fee-amount {
            font-size: 14px;
        }

        .receipt-number {
            font-size: 10px;
        }

        .empty-state {
            padding: 32px 16px;
            font-size: 12px;
        }

        .section-header .title {
            font-size: 15px;
        }

        .section-header .sub {
            font-size: 11px;
        }

        .section-header .count-badge {
            font-size: 10px;
            padding: 4px 10px;
        }

        .info-notice {
            font-size: 12px;
            padding: 16px;
        }

        .specialization-badge {
            font-size: 11px;
            padding: 8px 12px;
        }

        .experience-text {
            font-size: 11px;
        }
    }

    @media (max-width: 480px) {
        .profile-container {
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

        .profile-avatar,
        .profile-avatar-placeholder {
            width: 70px;
            height: 70px;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .profile-name {
            font-size: 16px;
        }

        .profile-email {
            font-size: 11px;
            margin-bottom: 14px;
        }

        .role-badge {
            font-size: 11px;
            padding: 5px 14px;
            gap: 5px;
        }

        .role-badge .dot {
            width: 6px;
            height: 6px;
        }

        .details-grid {
            gap: 8px;
        }

        .detail-item {
            padding: 12px 14px;
        }

        .detail-item .label {
            font-size: 9px;
            margin-bottom: 6px;
        }

        .detail-item .value,
        .detail-item .value-sm {
            font-size: 14px;
        }

        .role-tag {
            font-size: 12px;
            padding: 3px 10px;
        }

        .status-active {
            font-size: 12px;
            padding: 3px 10px;
        }

        .stats-grid-2 {
            gap: 6px;
        }

        .stat-item {
            padding: 10px;
        }

        .stat-item .value {
            font-size: 18px;
        }

        .stat-item .label {
            font-size: 9px;
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

        .btn-back {
            font-size: 11px;
            padding: 8px;
            min-height: 40px;
            border-radius: 8px;
        }

        table {
            min-width: 420px;
        }

        table th,
        table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .member-avatar-sm {
            width: 22px;
            height: 22px;
            font-size: 8px;
        }

        .member-cell .name {
            font-size: 11px;
        }

        .badge-intensity,
        .badge-category {
            font-size: 9px;
            padding: 2px 7px;
        }

        .status-badge {
            font-size: 9px;
            padding: 2px 7px;
            gap: 4px;
        }

        .status-badge .dot {
            width: 4px;
            height: 4px;
        }

        .fee-amount {
            font-size: 13px;
        }

        .receipt-number {
            font-size: 9px;
        }

        .empty-state {
            padding: 24px 12px;
            font-size: 11px;
        }

        .empty-state svg {
            width: 28px;
            height: 28px;
        }

        .section-header .title {
            font-size: 14px;
        }

        .section-header .sub {
            font-size: 10px;
        }

        .section-header .count-badge {
            font-size: 9px;
            padding: 3px 8px;
        }

        .info-notice {
            font-size: 11px;
            padding: 12px 14px;
            gap: 10px;
        }

        .info-notice svg {
            width: 16px;
            height: 16px;
        }

        .specialization-badge {
            font-size: 10px;
            padding: 6px 10px;
        }
    }

    @media (max-width: 360px) {
        table {
            min-width: 360px;
        }

        table th,
        table td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .profile-avatar,
        .profile-avatar-placeholder {
            width: 60px;
            height: 60px;
            font-size: 18px;
        }

        .profile-name {
            font-size: 14px;
        }

        .detail-item .value,
        .detail-item .value-sm {
            font-size: 13px;
        }

        .stat-item .value {
            font-size: 16px;
        }

        .btn-back {
            font-size: 10px;
            min-height: 36px;
        }

        .section-header .title {
            font-size: 13px;
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

<div class="profile-container">

    {{-- Breadcrumb --}}
    <div class="breadcrumb">
        <a href="{{ route('members.index') }}">Members</a>
        <span>/</span>
        <span class="current">{{ $user->name }}</span>
    </div>

    <div class="profile-grid">

        {{-- ══ LEFT COLUMN ══════════════════════════════════════════════════════════ --}}
        <div class="profile-left">

            {{-- Profile Card --}}
            <div class="card profile-card">

                @if($user->photo)
                    <img src="{{ asset('storage/'.$user->photo) }}" class="profile-avatar" alt="{{ $user->name }}">
                @else
                    <div class="profile-avatar-placeholder">
                        {{ strtoupper(substr($user->name ?? '?', 0, 2)) }}
                    </div>
                @endif

                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-email">{{ $user->email }}</div>

                @php
                    $roleKey    = strtolower($user->role ?? '');
                    $roleColor  = match($roleKey) {
                        'staff'      => '#a78bfa',
                        'instructor' => '#fb923c',
                        'admin'      => '#f87171',
                        default      => 'var(--muted)',
                    };
                    $roleBg     = match($roleKey) {
                        'staff'      => 'rgba(167,139,250,0.12)',
                        'instructor' => 'rgba(251,146,60,0.12)',
                        'admin'      => 'rgba(248,113,113,0.12)',
                        default      => 'rgba(255,255,255,0.05)',
                    };
                    $roleBorder = match($roleKey) {
                        'staff'      => 'rgba(167,139,250,0.25)',
                        'instructor' => 'rgba(251,146,60,0.25)',
                        'admin'      => 'rgba(248,113,113,0.25)',
                        default      => 'var(--border)',
                    };
                @endphp

                <span class="role-badge" style="background:{{ $roleBg }};color:{{ $roleColor }};border:1px solid {{ $roleBorder }};">
                    <span class="dot"></span>
                    {{ ucfirst($user->role) }}
                </span>

                {{-- Instructor specialization --}}
                @if($user->isInstructor())
                    @if($user->specialization)
                        <div class="specialization-badge">{{ $user->specialization }}</div>
                    @endif
                    @if($user->experience_years)
                        <div class="experience-text">{{ $user->experience_years }} yr{{ $user->experience_years != 1 ? 's' : '' }} experience</div>
                    @endif
                @endif
            </div>

            {{-- Personal Info --}}
            <div class="card">
                <div class="card-title">Personal Info</div>

                {{-- Phone --}}
                <div class="info-item">
                    <div class="info-icon" style="background:rgba(96,165,250,0.12);">
                        <svg stroke="#60a5fa" viewBox="0 0 24 24">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .99h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $user->phone ?? '—' }}</div>
                    </div>
                </div>

                {{-- Joined --}}
                <div class="info-item">
                    <div class="info-icon" style="background:rgba(74,222,128,0.12);">
                        <svg stroke="#4ade80" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-label">Joined</div>
                        <div class="info-value">{{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Instructor Quick Stats --}}
            @if($user->isInstructor())
            <div class="card">
                <div class="card-title">Quick Stats</div>
                <div class="stats-grid-2">
                    <div class="stat-item">
                        <div class="value">{{ $workoutPlans->count() }}</div>
                        <div class="label">Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="value green">{{ $workoutPlans->where('is_completed', true)->count() }}</div>
                        <div class="label">Done</div>
                    </div>
                    <div class="stat-item">
                        <div class="value orange">{{ $instructorFees->count() }}</div>
                        <div class="label">Payments</div>
                    </div>
                    <div class="stat-item">
                        <div class="value">₱{{ number_format($instructorFees->where('status','Paid')->sum('amount'), 0) }}</div>
                        <div class="label">Earned</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Back Button --}}
            <a href="{{ route('members.index') }}" class="btn-back">
                ← Back to Members
            </a>
        </div>

        {{-- ══ RIGHT COLUMN ═════════════════════════════════════════════════════════ --}}
        <div class="profile-right">

            {{-- Account Details Card --}}
            <div class="card">
                <div class="card-title">Account Details</div>
                <div class="details-grid">

                    <div class="detail-item">
                        <div class="label">Full Name</div>
                        <div class="value">{{ $user->name }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="label">Email</div>
                        <div class="value-sm">{{ $user->email }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="label">Role</div>
                        <span class="role-tag" style="background:{{ $roleBg }};color:{{ $roleColor }};border:1px solid {{ $roleBorder }};">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>

                    <div class="detail-item">
                        <div class="label">Account Status</div>
                        <span class="status-active">
                            <span class="dot"></span>
                            Active
                        </span>
                    </div>

                </div>
            </div>

            {{-- ══ INSTRUCTOR-ONLY SECTIONS ══════════════════════════════════════════ --}}
            @if($user->isInstructor())

            {{-- Member Schedule --}}
            <div class="card">
                <div class="section-header">
                    <div>
                        <div class="title">Member Schedule</div>
                        <div class="sub">All assigned workout sessions for this instructor</div>
                    </div>
                    <span class="count-badge orange">{{ $workoutPlans->count() }} session(s)</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Session</th>
                                <th>Category</th>
                                <th>Intensity</th>
                                <th>Scheduled Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workoutPlans as $plan)
                            @php
                                $isPast      = $plan->scheduled_date && $plan->scheduled_date->isPast();
                                $isCompleted = $plan->is_completed;
                                $isUpcoming  = !$isPast && !$isCompleted;

                                $iColor = match(strtolower($plan->intensity ?? '')) {
                                    'high'   => '#f87171',
                                    'medium' => '#facc15',
                                    'low'    => '#4ade80',
                                    default  => 'var(--muted)'
                                };
                                $iBg = match(strtolower($plan->intensity ?? '')) {
                                    'high'   => 'rgba(248,113,113,0.1)',
                                    'medium' => 'rgba(250,204,21,0.1)',
                                    'low'    => 'rgba(74,222,128,0.1)',
                                    default  => 'rgba(255,255,255,0.04)'
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="member-cell">
                                        <div class="member-avatar-sm">
                                            {{ strtoupper(substr($plan->member->name ?? '?', 0, 2)) }}
                                        </div>
                                        <span class="name">{{ $plan->member->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td style="font-size:13px;color:var(--text);font-weight:500;">{{ $plan->title ?? '—' }}</td>
                                <td>
                                    <span class="badge-category">{{ $plan->category ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge-intensity" style="background:{{ $iBg }};color:{{ $iColor }};">
                                        {{ ucfirst($plan->intensity ?? '—') }}
                                    </span>
                                </td>
                                <td style="font-size:13px;color:var(--muted);white-space:nowrap;">
                                    {{ $plan->scheduled_date ? $plan->scheduled_date->format('M d, Y') : '—' }}
                                </td>
                                <td>
                                    @if($isCompleted)
                                        <span class="status-badge completed">
                                            <span class="dot"></span>Completed
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="status-badge upcoming">
                                            <span class="dot"></span>Upcoming
                                        </span>
                                    @else
                                        <span class="status-badge missed">
                                            <span class="dot"></span>Missed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    No sessions scheduled yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Coach Fee History --}}
            <div class="card">
                <div class="section-header">
                    <div>
                        <div class="title">Coach Fee History</div>
                        <div class="sub">Payments received from member subscriptions</div>
                    </div>
                    <span class="count-badge green">Total: ₱{{ number_format($instructorFees->where('status','Paid')->sum('amount'), 0) }}</span>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Member</th>
                                <th>Plan</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($instructorFees as $fee)
                            @php
                                $fColor = match($fee->status) {
                                    'Paid'      => '#4ade80',
                                    'Pending'   => '#facc15',
                                    'Cancelled' => '#f87171',
                                    default     => 'var(--muted)'
                                };
                                $fBg = match($fee->status) {
                                    'Paid'      => 'rgba(74,222,128,0.1)',
                                    'Pending'   => 'rgba(250,204,21,0.1)',
                                    'Cancelled' => 'rgba(248,113,113,0.1)',
                                    default     => 'rgba(255,255,255,0.04)'
                                };
                            @endphp
                            <tr>
                                <td class="receipt-number">{{ Str::limit($fee->receipt_number ?? '—', 18) }}</td>
                                <td>
                                    <div class="member-cell">
                                        <div class="member-avatar-sm">
                                            {{ strtoupper(substr($fee->member->name ?? '?', 0, 2)) }}
                                        </div>
                                        <span class="name">{{ $fee->member->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td style="font-size:13px;color:var(--text);">{{ $fee->fitness_plan ?? '—' }}</td>
                                <td>
                                    <span class="badge-category">{{ $fee->membership_type ?? '—' }}</span>
                                </td>
                                <td class="fee-amount">₱{{ number_format($fee->amount, 0) }}</td>
                                <td style="font-size:13px;color:var(--muted);white-space:nowrap;">
                                    {{ $fee->payment_date ? $fee->payment_date->format('M d, Y') : '—' }}
                                </td>
                                <td>
                                    <span class="status-badge" style="background:{{ $fBg }};color:{{ $fColor }};">
                                        <span class="dot"></span>
                                        {{ $fee->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <rect x="1" y="4" width="22" height="16" rx="2"/>
                                        <line x1="1" y1="10" x2="23" y2="10"/>
                                    </svg>
                                    No coach fee payments received yet.
                                    <span class="sub">Coach fees appear here automatically when a member subscribes with this instructor.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @else
            {{-- Non-instructor info notice --}}
            <div class="info-notice">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>
                    This is a <strong>{{ ucfirst($user->role) }}</strong> user account — not a gym member.
                    Staff accounts do not have membership plans, schedules, or payment records.
                    @if(auth()->user()->isAdmin())
                        To manage this user's system role, visit
                        <a href="{{ route('users.index') }}">Manage Users</a>.
                    @endif
                </span>
            </div>
            @endif

        </div>
    </div>

</div>

@endsection