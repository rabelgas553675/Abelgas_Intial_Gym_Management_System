@extends('layouts.instructor')
@section('title', 'Instructor Dashboard – APEX')
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

    /* Dashboard Container */
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--accent);
    }
    .page-header h1 {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 700;
        margin-bottom: 6px;
        color: var(--text);
    }
    .page-header h1 span {
        color: var(--accent);
    }
    .page-header p {
        color: var(--muted);
        font-size: clamp(0.8rem, 1.2vw, 1rem);
    }

    /* Stat Grid */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    .stat-card:hover {
        transform: translateY(-3px);
        border-color: var(--accent);
        box-shadow: 0 8px 30px rgba(255,0,0,0.12);
    }
    .stat-card-left {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .stat-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
    }
    .stat-value {
        font-size: clamp(1.5rem, 3vw, 2.4rem);
        font-weight: 800;
        color: var(--text);
        line-height: 1;
    }
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        fill: none;
    }
    .icon-green { background: rgba(255,68,68,0.12); color: var(--success); }
    .icon-orange { background: rgba(255,107,53,0.12); color: var(--warning); }
    .icon-yellow { background: rgba(255,107,53,0.12); color: var(--warning); }

    /* Split Panel */
    .split-panel {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 24px;
        margin-bottom: 28px;
    }

    /* Members Panel */
    .members-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 600px;
        transition: border-color 0.3s ease;
    }
    .members-panel:hover {
        border-color: var(--accent);
    }
    .members-panel-header {
        padding: 16px 20px;
        border-bottom: 2px solid var(--accent);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .members-panel-title {
        font-weight: 700;
        font-size: 16px;
        color: var(--accent);
    }
    .members-search {
        display: flex;
        align-items: center;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 40px;
        padding: 4px 14px 4px 10px;
        gap: 6px;
        flex: 1 1 180px;
        min-width: 120px;
        transition: border-color 0.3s ease;
    }
    .members-search:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 20px rgba(255,0,0,0.1);
    }
    .members-search svg {
        width: 16px;
        height: 16px;
        stroke: var(--muted);
        flex-shrink: 0;
    }
    .members-search input {
        background: transparent;
        border: none;
        padding: 8px 0;
        font-size: 13px;
        color: var(--text);
        width: 100%;
        outline: none;
    }
    .members-search input::placeholder { color: var(--muted); }

    .members-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
    }
    .members-list::-webkit-scrollbar {
        width: 6px;
    }
    .members-list::-webkit-scrollbar-track {
        background: var(--surface);
    }
    .members-list::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 3px;
    }

    .member-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
        gap: 10px;
    }
    .member-item:hover {
        background: var(--surface2);
        border-left-color: var(--accent);
    }
    .member-item.active-item {
        background: rgba(255,0,0,0.06);
        border-left-color: var(--accent);
    }
    .member-item-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }
    .member-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        border: 2px solid var(--border);
    }
    .member-avatar-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,0,0,0.12);
        border: 2px solid rgba(255,0,0,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--accent);
        flex-shrink: 0;
    }
    .member-item-info {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
        min-width: 0;
    }
    .member-item-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .member-item-email {
        font-size: 11px;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .status-pill {
        font-size: 10px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 40px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .pill-active { 
        background: rgba(255,68,68,0.15); 
        color: var(--success); 
        border: 1px solid rgba(255,68,68,0.25);
    }
    .pill-expiring { 
        background: rgba(255,107,53,0.15); 
        color: var(--warning); 
        border: 1px solid rgba(255,107,53,0.25);
    }
    .pill-expired { 
        background: rgba(255,0,0,0.15); 
        color: var(--danger); 
        border: 1px solid rgba(255,0,0,0.25);
    }

    /* Details Panel */
    .details-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        min-height: 400px;
        transition: border-color 0.3s ease;
    }
    .details-panel:hover {
        border-color: var(--accent);
    }
    .details-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: 320px;
        color: var(--muted);
        padding: 20px;
        text-align: center;
    }
    .details-empty svg {
        width: 48px;
        height: 48px;
        stroke: var(--border);
        margin-bottom: 8px;
    }
    .details-content {
        display: none;
        flex-direction: column;
    }
    .details-content.visible {
        display: flex;
    }

    /* Details Hero */
    .details-hero {
        padding: 28px 28px 24px;
        border-bottom: 2px solid var(--accent);
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        background: linear-gradient(135deg, rgba(255,0,0,0.03), transparent);
    }
    .details-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        flex-shrink: 0;
        background: rgba(255,0,0,0.12);
        border: 3px solid var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 26px;
        color: var(--accent);
        overflow: hidden;
        box-shadow: 0 0 30px rgba(255,0,0,0.15);
    }
    .details-name {
        font-size: clamp(1.1rem, 2vw, 1.5rem);
        font-weight: 800;
        margin-bottom: 8px;
        color: var(--text);
    }

    /* Details Body */
    .details-body {
        padding: 24px 28px;
    }
    .section-label {
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 14px;
    }
    .contact-grid {
        display: grid;
        gap: 12px;
        margin-bottom: 24px;
    }
    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: var(--surface2);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        flex-wrap: wrap;
        transition: border-color 0.3s ease;
    }
    .contact-item:hover {
        border-color: var(--accent);
    }
    .contact-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .email-icon { background: rgba(255,68,68,0.12); }
    .phone-icon { background: rgba(255,68,68,0.12); }
    .contact-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    .contact-value {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        word-break: break-word;
    }

    /* Subscription Grid */
    .sub-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 24px;
    }
    .sub-item {
        padding: 14px;
        background: var(--surface2);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        transition: border-color 0.3s ease;
    }
    .sub-item:hover {
        border-color: var(--accent);
    }
    .sub-item.full-width {
        grid-column: span 2;
    }
    .sub-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }
    .sub-value {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
    }
    .sub-value.accent {
        color: var(--accent);
    }

    /* Days Remaining */
    .days-container {
        padding: 14px;
        background: var(--surface2);
        border-radius: var(--radius);
        border: 1px solid var(--border);
        margin-bottom: 24px;
        transition: border-color 0.3s ease;
    }
    .days-container:hover {
        border-color: var(--accent);
    }
    .days-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
        gap: 4px;
    }
    .days-label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .days-value {
        font-size: 13px;
        font-weight: 700;
        color: var(--accent);
    }
    .progress-bar {
        background: var(--border);
        border-radius: 999px;
        height: 6px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
        width: 0%;
        background: var(--accent);
    }

    /* View Profile Button */
    .view-profile-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px;
        background: var(--accent);
        color: #000;
        font-size: 14px;
        font-weight: 800;
        border-radius: var(--radius);
        text-decoration: none;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        box-shadow: 0 4px 20px rgba(255,0,0,0.3);
    }
    .view-profile-btn:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(255,0,0,0.4);
    }

    /* ===== RESPONSIVE ===== */

    @media (max-width: 1024px) {
        .stat-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .split-panel {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .members-panel {
            max-height: 420px;
        }
        .details-panel {
            min-height: 320px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0 16px;
        }
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .stat-card {
            padding: 16px 18px;
        }
        .stat-value {
            font-size: clamp(1.2rem, 2.5vw, 1.8rem);
        }
        .stat-icon {
            width: 38px;
            height: 38px;
        }
        .stat-icon svg {
            width: 20px;
            height: 20px;
        }
        .members-panel-header {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        .members-search {
            flex: 1;
        }
        .member-item {
            padding: 10px 14px;
            flex-wrap: wrap;
            gap: 6px;
        }
        .member-item-left {
            flex: 1;
            min-width: 120px;
        }
        .status-pill {
            font-size: 9px;
            padding: 3px 10px;
        }
        .details-hero {
            padding: 20px !important;
            gap: 16px;
        }
        .details-avatar {
            width: 60px;
            height: 60px;
            font-size: 22px;
        }
        .details-body {
            padding: 20px !important;
        }
        .sub-grid {
            grid-template-columns: 1fr !important;
        }
        .sub-item.full-width {
            grid-column: span 1 !important;
        }
        .contact-item {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 480px) {
        .dashboard-container {
            padding: 0 12px;
        }
        .stat-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .stat-card {
            padding: 12px 14px;
        }
        .stat-value {
            font-size: 1.2rem;
        }
        .stat-label {
            font-size: 9px;
        }
        .stat-icon {
            width: 32px;
            height: 32px;
        }
        .stat-icon svg {
            width: 17px;
            height: 17px;
        }
        .members-panel {
            max-height: 380px;
        }
        .member-item {
            padding: 8px 12px;
        }
        .member-item-name {
            font-size: 13px;
        }
        .member-item-email {
            font-size: 10px;
        }
        .member-avatar,
        .member-avatar-placeholder {
            width: 30px;
            height: 30px;
            font-size: 10px;
        }
        .details-hero {
            padding: 16px !important;
            gap: 12px;
        }
        .details-avatar {
            width: 50px;
            height: 50px;
            font-size: 18px;
        }
        .details-name {
            font-size: 17px;
        }
        .details-body {
            padding: 16px !important;
        }
        .view-profile-btn {
            padding: 12px;
            font-size: 13px;
        }
        .sub-value {
            font-size: 13px;
        }
    }

    @media (max-width: 360px) {
        .stat-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .stat-card {
            padding: 12px 14px;
        }
        .members-panel {
            max-height: 320px;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .stat-card {
        animation: fadeInUp 0.5s ease forwards;
    }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.1s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .members-panel {
        animation: fadeInUp 0.5s ease 0.1s forwards;
        opacity: 0;
    }
    .details-panel {
        animation: fadeInUp 0.5s ease 0.2s forwards;
        opacity: 0;
    }
</style>

<div class="dashboard-container">

    {{-- Page Header --}}
    <div class="page-header">
        <h1>Instructor <span>Dashboard</span></h1>
        <p>Manage and monitor your assigned members</p>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card-left">
                <div class="stat-label">Total Members</div>
                <div class="stat-value">{{ count($members) }}</div>
            </div>
            <div class="stat-icon icon-green">
                <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-left">
                <div class="stat-label">Active</div>
                <div class="stat-value">{{ $active }}</div>
            </div>
            <div class="stat-icon icon-orange">
                <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <circle cx="12" cy="12" r="8" stroke="var(--success)"/>
                    <circle cx="12" cy="12" r="3" fill="var(--success)" stroke="none"/>
                </svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-left">
                <div class="stat-label">Expiring Soon</div>
                <div class="stat-value" style="color:var(--warning);">{{ $nearDue }}</div>
            </div>
            <div class="stat-icon icon-yellow">
                <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Split Panel --}}
    <div class="split-panel">

        {{-- LEFT: Members List --}}
        <div class="members-panel">
            <div class="members-panel-header">
                <div class="members-panel-title">Members List</div>
                <div class="members-search">
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" id="memberSearch" placeholder="Search members..."
                           oninput="filterMembers(this.value)"/>
                </div>
            </div>

            <div class="members-list" id="membersList">
                @forelse($members as $member)
                    @php
                        $isExpiring  = $member->isDueWithinDays(7) && !$member->isExpired();
                        $isExpired   = $member->isExpired();
                        $pillClass   = $isExpired ? 'pill-expired' : ($isExpiring ? 'pill-expiring' : 'pill-active');
                        $pillLabel   = $isExpired ? 'Expired'      : ($isExpiring ? 'Expiring'      : 'Active');
                        $memberPhoto = $member->user?->photo ?? $member->photo ?? null;
                    @endphp
                    <div class="member-item"
                         data-name="{{ strtolower($member->name) }}"
                         data-email="{{ strtolower($member->email) }}"
                         onclick="showMemberDetail({{ $member->id }}, this)"
                         id="item-{{ $member->id }}">

                        <div class="member-item-left">
                            @if($memberPhoto)
                                <img src="{{ asset('storage/'.$memberPhoto) }}" class="member-avatar"/>
                            @else
                                <div class="member-avatar-placeholder">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                            @endif
                            <div class="member-item-info">
                                <span class="member-item-name">{{ $member->name }}</span>
                                <span class="member-item-email">{{ $member->email }}</span>
                            </div>
                        </div>

                        <span class="status-pill {{ $pillClass }}">{{ $pillLabel }}</span>
                    </div>
                @empty
                    <div style="padding:40px;text-align:center;color:var(--muted);font-size:14px;">
                        No members assigned yet.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT: Member Details Panel --}}
        <div class="details-panel" id="detailsPanel">

            <div class="details-empty" id="detailsEmpty">
                <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                             M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                             m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <div style="font-size:14px;margin-top:4px;">Select a member to view details</div>
            </div>

            <div class="details-content" id="detailsContent" style="padding:0;">

                <div class="details-hero">
                    <div class="details-avatar" id="detailsAvatar"></div>
                    <div>
                        <div class="details-name" id="detailsName"></div>
                        <div id="detailsBadge"></div>
                    </div>
                </div>

                <div class="details-body">

                    <div class="section-label">Contact Information</div>

                    <div class="contact-grid">
                        <div class="contact-item">
                            <div class="contact-icon email-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                     stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="contact-label">Email</div>
                                <div class="contact-value" id="detailsEmail"></div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon phone-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                     stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="contact-label">Phone</div>
                                <div class="contact-value" id="detailsPhone"></div>
                            </div>
                        </div>
                    </div>

                    <div class="section-label">Subscription</div>

                    <div class="sub-grid">
                        <div class="sub-item">
                            <div class="sub-label">Plan</div>
                            <div class="sub-value accent" id="detailsPlan"></div>
                        </div>
                        <div class="sub-item">
                            <div class="sub-label">Duration</div>
                            <div class="sub-value" id="detailsDuration"></div>
                        </div>
                        <div class="sub-item full-width">
                            <div class="sub-label">Active Period</div>
                            <div class="sub-value" id="detailsPeriod"></div>
                        </div>
                    </div>

                    {{-- Days Remaining --}}
                    <div class="days-container">
                        <div class="days-header">
                            <div class="days-label">Days Remaining</div>
                            <div class="days-value" id="daysRemainingLabel"></div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="daysRemainingBar"></div>
                        </div>
                    </div>

                    <a id="detailsViewBtn" href="#" class="view-profile-btn">
                        View Full Profile
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="#000" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden member data for JS --}}
<div id="memberData" style="display:none;">
    @foreach($members as $member)
        @php
            $isExpiring     = $member->isDueWithinDays(7) && !$member->isExpired();
            $isExpired      = $member->isExpired();
            $statusLabel    = $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active');
            $statusColor    = $isExpired ? '#ff3333' : ($isExpiring ? '#ff6b35' : '#ff4444');
            $statusBg       = $isExpired ? 'rgba(255,51,51,0.15)' : ($isExpiring ? 'rgba(255,107,53,0.15)' : 'rgba(255,68,68,0.15)');
            $daysRemaining  = $isExpired ? 0 : (int) now()->diffInDays($member->end_date);
            $totalDays      = ($member->start_date && $member->end_date)
                                ? (int) $member->start_date->diffInDays($member->end_date) : 30;
            $progressPct    = $totalDays > 0 ? min(100, round(($daysRemaining / $totalDays) * 100)) : 0;
            $barColor       = $isExpired ? '#ff3333' : ($isExpiring ? '#ff6b35' : '#ff4444');
            $memberPhotoUrl = ($member->user?->photo ?? $member->photo ?? null)
                                ? asset('storage/' . ($member->user?->photo ?? $member->photo))
                                : '';
        @endphp
        <div class="md"
             data-id="{{ $member->id }}"
             data-name="{{ $member->name }}"
             data-email="{{ $member->email }}"
             data-phone="{{ $member->phone ?? '—' }}"
             data-plan="{{ $member->fitness_plan ?? '—' }}"
             data-duration="{{ $member->membership_type ?? '—' }}"
             data-start="{{ $member->start_date?->format('M d, Y') ?? '—' }}"
             data-end="{{ $member->end_date?->format('M d, Y') ?? '—' }}"
             data-status="{{ $statusLabel }}"
             data-status-color="{{ $statusColor }}"
             data-status-bg="{{ $statusBg }}"
             data-days-remaining="{{ $daysRemaining }}"
             data-progress-pct="{{ $progressPct }}"
             data-bar-color="{{ $barColor }}"
             data-photo="{{ $memberPhotoUrl }}"
             data-url="{{ route('instructor.member.show', $member) }}">
        </div>
    @endforeach
</div>

<script>
function showMemberDetail(id, el) {
    document.querySelectorAll('.member-item').forEach(i => i.classList.remove('active-item'));
    el.classList.add('active-item');

    const md = document.querySelector(`.md[data-id="${id}"]`);
    if (!md) return;

    const avatar = document.getElementById('detailsAvatar');
    const initials = md.dataset.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
    if (md.dataset.photo) {
        avatar.innerHTML = `<img src="${md.dataset.photo}" style="width:100%;height:100%;object-fit:cover;"/>`;
        avatar.style.background = 'transparent';
    } else {
        avatar.innerHTML = initials;
        avatar.style.background = 'rgba(255,0,0,0.12)';
    }

    document.getElementById('detailsName').textContent = md.dataset.name;

    document.getElementById('detailsBadge').innerHTML =
        `<span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;
                      border-radius:100px;font-size:12px;font-weight:700;
                      background:${md.dataset.statusBg};color:${md.dataset.statusColor};
                      border:1px solid ${md.dataset.statusColor}44;">
             <span style="width:6px;height:6px;border-radius:50%;
                          background:${md.dataset.statusColor};display:inline-block;"></span>
             ${md.dataset.status}
         </span>`;

    document.getElementById('detailsEmail').textContent = md.dataset.email;
    document.getElementById('detailsPhone').textContent = md.dataset.phone;
    document.getElementById('detailsPlan').textContent = md.dataset.plan;
    document.getElementById('detailsDuration').textContent = md.dataset.duration;
    document.getElementById('detailsPeriod').textContent = `${md.dataset.start} – ${md.dataset.end}`;

    const days = parseInt(md.dataset.daysRemaining) || 0;
    const pct = parseInt(md.dataset.progressPct) || 0;
    const barColor = md.dataset.barColor;

    document.getElementById('daysRemainingLabel').textContent =
        days === 0 ? 'Expired' : `${days} day${days !== 1 ? 's' : ''} left`;
    document.getElementById('daysRemainingLabel').style.color = barColor;
    document.getElementById('daysRemainingBar').style.width = pct + '%';
    document.getElementById('daysRemainingBar').style.background = barColor;

    document.getElementById('detailsViewBtn').href = md.dataset.url;

    document.getElementById('detailsEmpty').style.display = 'none';
    document.getElementById('detailsContent').classList.add('visible');
}

function filterMembers(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('.member-item').forEach(item => {
        const match = (item.dataset.name || '').includes(q)
                   || (item.dataset.email || '').includes(q);
        item.style.display = match ? '' : 'none';
    });
}

// Auto-select first member on load
document.addEventListener('DOMContentLoaded', function() {
    const first = document.querySelector('.member-item');
    if (first) {
        first.click();
    }
});
</script>

@endsection