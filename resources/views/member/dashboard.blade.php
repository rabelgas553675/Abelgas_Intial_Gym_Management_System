@extends('layouts.member')
@section('title', 'Dashboard – IRONFORGE')
@section('active', 'dashboard')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Welcome Header */
    .welcome-header {
        margin-bottom: 32px;
    }

    .welcome-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .welcome-header h1 span {
        color: var(--accent);
    }

    .welcome-header p {
        color: var(--muted);
        font-size: 14px;
    }

    /* Warning Banner */
    .warning-banner {
        background: rgba(251, 191, 36, 0.1);
        border: 1px solid rgba(251, 191, 36, 0.3);
        border-radius: 12px;
        padding: 14px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .warning-banner .icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .warning-banner .text strong {
        color: var(--warning);
    }

    .warning-banner .text .sub {
        font-size: 13px;
        color: var(--muted);
        margin-top: 2px;
    }

    .warning-banner .text a {
        color: var(--accent);
        margin-left: 6px;
        text-decoration: none;
    }

    .warning-banner .text a:hover {
        text-decoration: underline;
    }

    /* Top Row Grid */
    .top-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    /* Cards */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .card-header .title {
        font-size: 16px;
        font-weight: 700;
    }

    .card-header svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: var(--accent);
        stroke-width: 2;
        flex-shrink: 0;
    }

    /* Subscription Card */
    .sub-plan {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .sub-plan .icon-wrap {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(232, 255, 42, 0.15);
        color: var(--accent);
        border-radius: 14px;
        flex-shrink: 0;
    }

    .sub-plan .icon-wrap svg {
        width: 28px;
        height: 28px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .sub-plan .info .label {
        font-size: 12px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .sub-plan .info .value {
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
    }

    .sub-details {
        display: grid;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }

    .sub-details .item .label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 3px;
    }

    .sub-details .item .value {
        font-weight: 600;
    }

    .sub-actions {
        display: flex;
        gap: 10px;
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
        gap: 8px;
        font-size: 14px;
        min-height: 44px;
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

    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
        min-height: 36px;
    }

    /* Profile Card */
    .profile-avatar {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border);
    }

    .profile-avatar img {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--accent);
        flex-shrink: 0;
    }

    .profile-avatar .placeholder {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(232, 255, 42, 0.1);
        border: 2px solid var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 22px;
        color: var(--accent);
        flex-shrink: 0;
    }

    .profile-avatar .name {
        font-weight: 700;
        font-size: 15px;
    }

    .profile-avatar .email {
        font-size: 12px;
        color: var(--muted);
    }

    .profile-details {
        display: grid;
        gap: 14px;
        margin-bottom: 20px;
    }

    .profile-details .item .label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 3px;
    }

    .profile-details .item .value {
        font-weight: 600;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
    }

    .status-badge.inactive {
        background: rgba(248, 113, 113, 0.15);
        color: #f87171;
    }

    /* Banner */
    .explore-banner {
        background: var(--accent);
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .explore-banner .title {
        font-size: 18px;
        font-weight: 700;
        color: #111;
        margin-bottom: 4px;
    }

    .explore-banner .sub {
        font-size: 13px;
        color: rgba(0, 0, 0, 0.6);
    }

    .explore-banner .btn-dark {
        background: #111;
        color: var(--accent);
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        font-size: 14px;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        transition: opacity 0.2s;
    }

    .explore-banner .btn-dark:hover {
        opacity: 0.85;
    }

    /* Payments */
    .payment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid var(--border);
        gap: 12px;
    }

    .payment-item:last-child {
        border-bottom: none;
    }

    .payment-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
    }

    .payment-left svg {
        width: 16px;
        height: 16px;
        stroke: var(--muted);
        fill: none;
        stroke-width: 2;
        flex-shrink: 0;
    }

    .payment-info .plan {
        font-size: 14px;
        font-weight: 600;
    }

    .payment-info .date {
        font-size: 12px;
        color: var(--muted);
    }

    .payment-right {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-shrink: 0;
    }

    .payment-amount {
        font-weight: 700;
        color: var(--accent);
        text-align: right;
    }

    .payment-status {
        font-size: 12px;
        color: #4ade80;
        text-align: right;
    }

    .payment-receipt {
        color: var(--muted);
        text-decoration: none;
        transition: color 0.2s;
    }

    .payment-receipt:hover {
        color: var(--text);
    }

    .payment-receipt svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
    }

    .view-all-link {
        text-align: center;
        margin-top: 16px;
    }

    .view-all-link a {
        color: var(--accent);
        font-size: 14px;
        text-decoration: none;
        font-weight: 600;
    }

    .view-all-link a:hover {
        text-decoration: underline;
    }

    .empty-state {
        text-align: center;
        color: var(--muted);
        padding: 32px;
    }

    .empty-state .icon {
        font-size: 40px;
        margin-bottom: 12px;
    }

    .empty-state .title {
        font-weight: 600;
        margin-bottom: 6px;
    }

    .empty-state .sub {
        font-size: 13px;
        margin-bottom: 16px;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.75);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        width: 100%;
        max-width: 640px;
        padding: 32px;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        color: var(--muted);
        font-size: 20px;
        cursor: pointer;
        line-height: 1;
    }

    .modal-close:hover {
        color: var(--text);
    }

    .modal-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .modal-sub {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 28px;
    }

    .modal-section-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .modal-plan-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 28px;
    }

    .modal-plan-option {
        cursor: pointer;
        display: block;
    }

    .modal-plan-option input[type="radio"] {
        display: none;
    }

    .modal-plan-card {
        border-radius: 12px;
        padding: 12px 8px;
        text-align: center;
        transition: border-color 0.18s, background 0.18s;
        position: relative;
        background: var(--surface2);
        border: 1.5px solid var(--border);
        height: 100%;
        box-sizing: border-box;
    }

    .modal-plan-card:hover {
        border-color: rgba(232, 255, 42, 0.4);
    }

    .modal-plan-card .icon {
        width: 32px;
        height: 32px;
        margin: 0 auto 8px;
        color: rgba(255, 255, 255, 0.25);
    }

    .modal-plan-card .icon svg {
        width: 100%;
        height: 100%;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .modal-plan-card .name {
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        line-height: 1.3;
    }

    .modal-plan-card .dot {
        position: absolute;
        top: 7px;
        right: 7px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--accent);
        display: none;
    }

    .modal-plan-card.selected {
        border-color: var(--accent) !important;
        background: rgba(232, 255, 42, 0.06) !important;
    }

    .modal-plan-card.selected .icon {
        color: var(--accent);
    }

    .modal-plan-card.selected .dot {
        display: block;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
    }

    .modal-actions .btn {
        flex: 1;
        justify-content: center;
        padding: 13px;
    }

    .modal-actions .btn-secondary {
        flex: 0 1 auto;
        padding: 13px 20px;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .top-row {
            gap: 16px;
        }

        .modal-plan-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 900px) {
        .dashboard-container {
            padding: 0 12px;
        }

        .welcome-header h1 {
            font-size: 28px;
        }

        .top-row {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .explore-banner {
            flex-direction: column;
            text-align: center;
            padding: 24px 20px;
        }

        .explore-banner .btn-dark {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .welcome-header h1 {
            font-size: 24px;
        }

        .welcome-header p {
            font-size: 13px;
        }

        .card {
            padding: 20px 16px;
            border-radius: 12px;
        }

        .card-header .title {
            font-size: 15px;
        }

        .sub-plan {
            gap: 12px;
        }

        .sub-plan .icon-wrap {
            width: 44px;
            height: 44px;
        }

        .sub-plan .icon-wrap svg {
            width: 24px;
            height: 24px;
        }

        .sub-plan .info .value {
            font-size: 18px;
        }

        .profile-avatar img,
        .profile-avatar .placeholder {
            width: 48px;
            height: 48px;
            font-size: 18px;
        }

        .profile-avatar .name {
            font-size: 14px;
        }

        .profile-avatar .email {
            font-size: 11px;
        }

        .btn {
            font-size: 13px;
            padding: 8px 16px;
            min-height: 40px;
        }

        .btn-sm {
            font-size: 11px;
            padding: 5px 12px;
            min-height: 32px;
        }

        .warning-banner {
            padding: 12px 16px;
            flex-wrap: wrap;
        }

        .warning-banner .text .sub {
            font-size: 12px;
        }

        .payment-item {
            padding: 12px 0;
            flex-wrap: wrap;
        }

        .payment-left {
            flex: 1 1 100%;
        }

        .payment-right {
            flex: 1 1 100%;
            justify-content: flex-end;
        }

        .modal {
            padding: 24px 20px;
            max-width: 95vw;
        }

        .modal-plan-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .modal-actions {
            flex-direction: column;
        }

        .modal-actions .btn-secondary {
            flex: 1;
        }

        .explore-banner .title {
            font-size: 16px;
        }

        .explore-banner .sub {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .dashboard-container {
            padding: 0 8px;
        }

        .welcome-header h1 {
            font-size: 20px;
        }

        .welcome-header p {
            font-size: 12px;
        }

        .card {
            padding: 16px 12px;
            border-radius: 10px;
        }

        .card-header .title {
            font-size: 14px;
        }

        .card-header svg {
            width: 18px;
            height: 18px;
        }

        .sub-plan {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .sub-plan .icon-wrap {
            width: 40px;
            height: 40px;
        }

        .sub-plan .icon-wrap svg {
            width: 20px;
            height: 20px;
        }

        .sub-plan .info .value {
            font-size: 16px;
        }

        .sub-details {
            gap: 8px;
            padding-bottom: 14px;
        }

        .sub-actions {
            flex-direction: column;
        }

        .sub-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .profile-avatar {
            flex-wrap: wrap;
        }

        .profile-avatar img,
        .profile-avatar .placeholder {
            width: 42px;
            height: 42px;
            font-size: 16px;
        }

        .profile-avatar .name {
            font-size: 13px;
        }

        .profile-details {
            gap: 10px;
        }

        .btn {
            font-size: 12px;
            padding: 8px 14px;
            min-height: 38px;
        }

        .payment-info .plan {
            font-size: 13px;
        }

        .payment-amount {
            font-size: 14px;
        }

        .payment-status {
            font-size: 11px;
        }

        .modal {
            padding: 18px 14px;
        }

        .modal-title {
            font-size: 16px;
        }

        .modal-sub {
            font-size: 12px;
        }

        .modal-plan-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }

        .modal-plan-card {
            padding: 10px 6px;
        }

        .modal-plan-card .icon {
            width: 28px;
            height: 28px;
        }

        .modal-plan-card .name {
            font-size: 10px;
        }

        .modal-actions .btn {
            font-size: 13px;
            padding: 10px;
            min-height: 38px;
        }

        .explore-banner {
            padding: 18px 16px;
            border-radius: 12px;
        }

        .explore-banner .title {
            font-size: 15px;
        }

        .explore-banner .sub {
            font-size: 11px;
        }

        .explore-banner .btn-dark {
            font-size: 12px;
            padding: 10px 16px;
            min-height: 38px;
        }

        .warning-banner {
            padding: 10px 12px;
            gap: 8px;
        }

        .warning-banner .icon {
            font-size: 16px;
        }

        .warning-banner .text .sub {
            font-size: 11px;
        }

        .view-all-link a {
            font-size: 13px;
        }
    }

    @media (max-width: 360px) {
        .modal-plan-grid {
            grid-template-columns: 1fr 1fr;
        }

        .modal-plan-card .name {
            font-size: 9px;
        }

        .sub-plan .info .value {
            font-size: 15px;
        }

        .profile-avatar img,
        .profile-avatar .placeholder {
            width: 36px;
            height: 36px;
            font-size: 14px;
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

<div class="dashboard-container">

    {{-- Welcome Header --}}
    <div class="welcome-header">
        <h1>
            Welcome back, <span>{{ explode(' ', auth()->user()->name)[0] }}</span>
        </h1>
        <p>Track your fitness journey and manage your subscription</p>
    </div>

    {{-- Near-due warning --}}
    @if($nearDue && $member)
    <div class="warning-banner">
        <span class="icon">⚠️</span>
        <div class="text">
            <strong>Subscription Expiring Soon</strong>
            <div class="sub">
                Your {{ $member->membership_type }} plan expires on
                <strong>{{ $member->end_date->format('M d, Y') }}</strong>.
                <a href="{{ route('member.select-plan') }}">Renew now →</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Row: Subscription + Profile --}}
    <div class="top-row">

        {{-- Current Subscription Card --}}
        <div class="card">
            <div class="card-header">
                <div class="title">Current Subscription</div>
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>

            @if($member)
                @php
                    $plans = [
                        'Calisthenics'       => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><line x1="24" y1="11" x2="24" y2="24"/><line x1="24" y1="24" x2="14" y2="34"/><line x1="24" y1="24" x2="34" y2="34"/><line x1="24" y1="18" x2="14" y2="22"/><line x1="24" y1="18" x2="34" y2="22"/></svg>',
                        'Bodybuilding'       => '<svg viewBox="0 0 48 48"><path d="M14 28 Q10 24 14 20 Q18 16 22 20 L26 28 Q30 32 26 36 Q22 40 18 36 Z"/><path d="M26 28 Q30 24 34 20"/><path d="M6 22 L14 20"/><path d="M34 20 L42 18"/><path d="M6 26 L14 28"/><path d="M34 28 L42 26"/></svg>',
                        'Plyometrics'        => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><path d="M24 11 L18 22 L24 20 L20 34"/><path d="M24 20 L30 18 L26 30"/><path d="M16 38 L32 38"/></svg>',
                        'Powerlifting'       => '<svg viewBox="0 0 48 48"><rect x="4" y="18" width="6" height="12" rx="2"/><rect x="38" y="18" width="6" height="12" rx="2"/><rect x="8" y="20" width="6" height="8" rx="1"/><rect x="34" y="20" width="6" height="8" rx="1"/><line x1="14" y1="24" x2="34" y2="24"/><circle cx="24" cy="14" r="3"/></svg>',
                        'Endurance'          => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><path d="M20 12 Q16 18 18 24 L22 22 L20 34 L26 28 L28 34 L30 22 L34 24 Q36 18 32 12"/></svg>',
                        'Functional Training'=> '<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="14"/><path d="M24 10 L24 14"/><path d="M24 34 L24 38"/><path d="M10 24 L14 24"/><path d="M34 24 L38 24"/><circle cx="24" cy="24" r="4"/></svg>',
                        'Hybrid Training'    => '<svg viewBox="0 0 48 48"><polygon points="24,6 28,18 40,18 30,26 34,38 24,30 14,38 18,26 8,18 20,18"/></svg>',
                    ];
                    $icon = $plans[$member->fitness_plan] ?? '<circle cx="24" cy="24" r="20"/>';
                @endphp

                <div class="sub-plan">
                    <div class="icon-wrap">{!! $icon !!}</div>
                    <div class="info">
                        <div class="label">Fitness Plan</div>
                        <div class="value">{{ $member->fitness_plan }}</div>
                    </div>
                </div>

                <div class="sub-details">
                    <div class="item">
                        <div class="label">Duration</div>
                        <div class="value">{{ $member->membership_type }}</div>
                    </div>
                    <div class="item">
                        <div class="label">Instructor</div>
                        <div class="value">{{ $member->instructor->name ?? 'Not assigned' }}</div>
                    </div>
                    <div class="item">
                        <div class="label">Active Period</div>
                        <div class="value">
                            {{ $member->start_date?->format('M d, Y') }} – {{ $member->end_date?->format('M d, Y') }}
                        </div>
                    </div>
                </div>

                <div class="sub-actions">
                    <a href="{{ route('member.select-plan') }}" class="btn btn-primary" style="flex:1;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        {{ $member->isExpired() ? 'Renew Plan' : 'Change Plan' }}
                    </a>
                    <button type="button" class="btn btn-secondary"
                            onclick="document.getElementById('editSubModal').style.display='flex'"
                            style="padding:8px 12px;display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                </div>

            @else
                <div class="empty-state">
                    <div class="icon">🏋️</div>
                    <div class="title">No Active Plan</div>
                    <div class="sub">Choose a plan to get started</div>
                    <a href="{{ route('member.select-plan') }}" class="btn btn-primary">Choose a Plan</a>
                </div>
            @endif
        </div>

        {{-- Profile Card --}}
        <div class="card">
            <div class="card-header">
                <div class="title">Profile</div>
                <svg viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>

            <div class="profile-avatar">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt="">
                @else
                    <div class="placeholder">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
                @endif
                <div>
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="email">{{ auth()->user()->email }}</div>
                </div>
            </div>

            <div class="profile-details">
                <div class="item">
                    <div class="label">Phone</div>
                    <div class="value">{{ auth()->user()->phone ?? '—' }}</div>
                </div>
                <div class="item">
                    <div class="label">Member Since</div>
                    <div class="value">{{ auth()->user()->created_at->format('M d, Y') }}</div>
                </div>
                <div class="item">
                    <div class="label">Status</div>
                    <div>
                        <span class="status-badge {{ $member ? '' : 'inactive' }}">
                            {{ $member ? $member->status : 'No Plan' }}
                        </span>
                    </div>
                </div>
            </div>

            <a href="{{ route('member.profile') }}" class="btn btn-secondary" style="width:100%;justify-content:center;">
                Edit Profile
            </a>
        </div>

    </div>

    {{-- Explore Plans Banner --}}
    <div class="explore-banner">
        <div>
            <div class="title">Explore All Fitness Plans</div>
            <div class="sub">Choose from 7 specialized training programs designed to help you reach your goals</div>
        </div>
        <a href="{{ route('member.select-plan') }}" class="btn-dark">
            View All Plans →
        </a>
    </div>

    {{-- Recent Payments --}}
    <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
            <div class="title">Recent Payments</div>
            <svg viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>

        @forelse($payments->take(5) as $payment)
        <div class="payment-item">
            <div class="payment-left">
                <svg viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <div class="payment-info">
                    <div class="plan">{{ $payment->fitness_plan }} - {{ $payment->membership_type }}</div>
                    <div class="date">{{ $payment->payment_date->format('M d, Y') }}</div>
                </div>
            </div>
            <div class="payment-right">
                <div>
                    <div class="payment-amount">₱{{ number_format($payment->amount, 0) }}</div>
                    <div class="payment-status">{{ $payment->status }}</div>
                </div>
                <a href="{{ route('member.receipt', $payment) }}" class="payment-receipt" title="View Receipt">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state">No payments yet.</div>
        @endforelse

        @if($payments->count() > 0)
        <div class="view-all-link">
            <a href="{{ route('member.payments') }}">View All Payments →</a>
        </div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════
     Edit Subscription Modal
═══════════════════════════════════════════════ --}}
@if($member)
<div class="modal-overlay" id="editSubModal">
    <div class="modal">
        <button onclick="document.getElementById('editSubModal').style.display='none'" class="modal-close">✕</button>

        <div class="modal-title">Edit Subscription</div>
        <div class="modal-sub">No charge will be made.</div>

        <form method="POST" action="{{ route('member.subscription.update') }}">
            @csrf

            {{-- ── 1. FITNESS PLAN ── --}}
            <div class="modal-section-label">1. Fitness Plan</div>
            @php
                $svgPlans = [
                    'Calisthenics'       => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><line x1="24" y1="11" x2="24" y2="24"/><line x1="24" y1="24" x2="14" y2="34"/><line x1="24" y1="24" x2="34" y2="34"/><line x1="24" y1="18" x2="14" y2="22"/><line x1="24" y1="18" x2="34" y2="22"/></svg>',
                    'Bodybuilding'       => '<svg viewBox="0 0 48 48"><path d="M14 28 Q10 24 14 20 Q18 16 22 20 L26 28 Q30 32 26 36 Q22 40 18 36 Z"/><path d="M26 28 Q30 24 34 20"/><path d="M6 22 L14 20"/><path d="M34 20 L42 18"/><path d="M6 26 L14 28"/><path d="M34 28 L42 26"/></svg>',
                    'Plyometrics'        => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><path d="M24 11 L18 22 L24 20 L20 34"/><path d="M24 20 L30 18 L26 30"/><path d="M16 38 L32 38"/></svg>',
                    'Powerlifting'       => '<svg viewBox="0 0 48 48"><rect x="4" y="18" width="6" height="12" rx="2"/><rect x="38" y="18" width="6" height="12" rx="2"/><rect x="8" y="20" width="6" height="8" rx="1"/><rect x="34" y="20" width="6" height="8" rx="1"/><line x1="14" y1="24" x2="34" y2="24"/><circle cx="24" cy="14" r="3"/></svg>',
                    'Endurance'          => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><path d="M20 12 Q16 18 18 24 L22 22 L20 34 L26 28 L28 34 L30 22 L34 24 Q36 18 32 12"/></svg>',
                    'Functional Training'=> '<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="14"/><path d="M24 10 L24 14"/><path d="M24 34 L24 38"/><path d="M10 24 L14 24"/><path d="M34 24 L38 24"/><circle cx="24" cy="24" r="4"/></svg>',
                    'Hybrid Training'    => '<svg viewBox="0 0 48 48"><polygon points="24,6 28,18 40,18 30,26 34,38 24,30 14,38 18,26 8,18 20,18"/></svg>',
                ];
            @endphp
            <div class="modal-plan-grid">
                @foreach($svgPlans as $planName => $planSvg)
                    @php $isPlan = $member->fitness_plan === $planName; @endphp
                    <label class="modal-plan-option">
                        <input type="radio" name="fitness_plan" value="{{ $planName }}" {{ $isPlan ? 'checked' : '' }}/>
                        <div class="modal-plan-card {{ $isPlan ? 'selected' : '' }}">
                            <div class="icon">{!! $planSvg !!}</div>
                            <div class="name">{{ $planName }}</div>
                            <div class="dot"></div>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- ── ACTIONS ── --}}
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">✓ Save Changes</button>
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editSubModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        // Radio → card selection handler
        document.querySelectorAll('.modal-plan-option input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var parent = this.closest('.modal-plan-grid');
                parent.querySelectorAll('.modal-plan-card').forEach(function(card) {
                    card.classList.remove('selected');
                });
                if (this.checked) {
                    var card = this.closest('.modal-plan-option').querySelector('.modal-plan-card');
                    card.classList.add('selected');
                }
            });
        });

        // Close modal on backdrop click
        var modal = document.getElementById('editSubModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) modal.style.display = 'none';
            });
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('editSubModal');
                if (modal && modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            }
        });
    })();
</script>
@endif

@endsection