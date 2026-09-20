@extends('layouts.admin')
@section('title', 'QR Codes – IRONFORGE')

@section('content')
<style>
    :root {
        --primary-neon: #c8ff00;
        --card-bg: #ffffff;
        --text-dark: #111111;
        --text-muted: #666666;
    }

    /* ===== RESPONSIVE STYLES ===== */
    .qr-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Dropdown Styling Fix */
    select.group-filter {
        background-color: #1a1a2e !important;
        color: #ffffff !important;
        border: 1px solid #3f3f5f !important;
        padding: 10px 35px 10px 15px;
        border-radius: 8px;
        appearance: none;
        width: 100%;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
    }

    select.group-filter option {
        background-color: #1a1a2e;
        color: #ffffff;
    }

    /* QR Grid */
    .qr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 24px;
        margin-top: 30px;
    }

    .qr-card {
        background: var(--card-bg);
        border-radius: 16px;
        overflow: hidden;
        text-align: center;
        padding: 24px;
        color: var(--text-dark);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #eee;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .qr-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }

    .qr-image-wrapper {
        width: 160px;
        height: 160px;
        margin: 0 auto 15px auto;
        background: #f8f8f8;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #eee;
        flex-shrink: 0;
    }

    .qr-image-wrapper img {
        width: 90%;
        height: 90%;
        object-fit: contain;
    }

    .qr-card-name {
        font-weight: 800;
        font-size: 18px;
        color: #111;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .qr-card-email {
        font-size: 12px;
        color: #666;
        margin-bottom: 8px;
        word-break: break-all;
    }

    .badge-role {
        font-size: 10px;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-top: 8px;
    }

    .scanner-id-tag {
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px dashed #eee;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .id-label {
        font-size: 9px;
        color: #999;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .id-number {
        font-family: 'Courier New', monospace;
        background: #111;
        color: var(--primary-neon);
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 800;
    }

    .qr-token-hash {
        font-size: 8px;
        color: #bbb;
        margin-top: 10px;
        font-family: monospace;
        word-break: break-all;
        opacity: 0.6;
        max-width: 100%;
    }

    /* Header */
    .qr-header {
        margin-bottom: 32px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }

    .qr-header-left h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .qr-header-left p {
        color: #888;
        font-size: 14px;
    }

    .qr-header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Filter Section */
    .filter-section {
        background: rgba(255,255,255,0.03);
        padding: 24px;
        border-radius: 16px;
        margin-bottom: 32px;
    }

    .filter-form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filter-group label {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group-select {
        min-width: 200px;
    }

    .filter-group-search {
        flex: 1;
        min-width: 200px;
    }

    .filter-group-search input {
        background: #111;
        border: 1px solid #333;
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
        height: 42px;
    }

    .filter-group-search input:focus {
        border-color: var(--primary-neon);
    }

    .filter-divider {
        width: 1px;
        height: 30px;
        background: rgba(255,255,255,0.1);
        margin: 0 4px;
        display: none;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 42px;
        white-space: nowrap;
    }

    .btn-primary-neon {
        background: var(--primary-neon);
        color: #000;
    }

    .btn-primary-neon:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }

    .btn-dark {
        background: #222;
        color: #fff;
    }

    .btn-dark:hover {
        background: #333;
    }

    .btn-print {
        background: var(--primary-neon);
        color: #000;
        font-weight: bold;
    }

    .btn-print:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.2);
    }

    .btn-secondary:hover {
        background: rgba(255,255,255,0.2);
    }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 100px 20px;
        color: #888;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.2;
    }

    /* Print Styles */
    @media print {
        .no-print { display: none !important; }
        .qr-grid { display: block; }
        .qr-card {
            break-inside: avoid;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            box-shadow: none;
            page-break-inside: avoid;
        }
        .qr-container {
            padding: 0;
        }
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    /* Tablets */
    @media (max-width: 1024px) {
        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
        }

        .qr-image-wrapper {
            width: 140px;
            height: 140px;
        }
    }

    /* Mobile */
    @media (max-width: 768px) {
        .qr-container {
            padding: 0 12px;
        }

        .qr-header {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 24px;
        }

        .qr-header-left h1 {
            font-size: 26px;
        }

        .qr-header-left p {
            font-size: 13px;
        }

        .qr-header-actions {
            width: 100%;
        }

        .qr-header-actions .btn {
            flex: 1;
            justify-content: center;
            min-width: 80px;
            font-size: 13px;
            padding: 8px 16px;
        }

        .filter-section {
            padding: 16px;
            border-radius: 12px;
        }

        .filter-form {
            flex-direction: column;
            gap: 10px;
        }

        .filter-group {
            width: 100%;
        }

        .filter-group-select {
            min-width: unset;
            width: 100%;
        }

        .filter-group-search {
            min-width: unset;
            width: 100%;
        }

        .filter-group-search input {
            height: 40px;
            font-size: 14px;
        }

        .filter-divider {
            display: none;
        }

        .filter-form .btn {
            width: 100%;
            justify-content: center;
        }

        .filter-form .btn-dark {
            width: 100%;
        }

        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .qr-card {
            padding: 16px;
            border-radius: 12px;
        }

        .qr-image-wrapper {
            width: 120px;
            height: 120px;
        }

        .qr-card-name {
            font-size: 16px;
        }

        .qr-card-email {
            font-size: 11px;
        }

        .id-number {
            font-size: 12px;
        }

        .badge-role {
            font-size: 9px;
            padding: 3px 10px;
        }

        .qr-token-hash {
            font-size: 7px;
        }

        .empty-state {
            padding: 60px 16px;
        }

        .empty-state-icon {
            font-size: 36px;
        }
    }

    /* Small phones */
    @media (max-width: 480px) {
        .qr-container {
            padding: 0 8px;
        }

        .qr-header-left h1 {
            font-size: 22px;
        }

        .qr-header-left p {
            font-size: 12px;
        }

        .qr-header-actions .btn {
            font-size: 12px;
            padding: 6px 12px;
            height: 36px;
            min-width: 60px;
        }

        .filter-section {
            padding: 12px;
            border-radius: 10px;
        }

        .filter-group label {
            font-size: 10px;
        }

        select.group-filter {
            padding: 8px 30px 8px 12px;
            font-size: 13px;
            height: 38px;
        }

        .filter-group-search input {
            height: 38px;
            font-size: 13px;
            padding: 8px;
        }

        .filter-form .btn {
            height: 38px;
            font-size: 13px;
            padding: 6px 14px;
        }

        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }

        .qr-card {
            padding: 12px;
            border-radius: 10px;
        }

        .qr-image-wrapper {
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
        }

        .qr-card-name {
            font-size: 14px;
        }

        .qr-card-email {
            font-size: 10px;
            margin-bottom: 4px;
        }

        .badge-role {
            font-size: 8px;
            padding: 2px 8px;
            margin-top: 4px;
        }

        .scanner-id-tag {
            margin-top: 10px;
            padding-top: 8px;
        }

        .id-label {
            font-size: 8px;
        }

        .id-number {
            font-size: 11px;
            padding: 1px 8px;
        }

        .qr-token-hash {
            font-size: 6px;
            margin-top: 6px;
        }

        .empty-state {
            padding: 40px 12px;
        }

        .empty-state-icon {
            font-size: 28px;
        }
    }

    /* Extra small phones */
    @media (max-width: 360px) {
        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 8px;
        }

        .qr-card {
            padding: 10px;
        }

        .qr-image-wrapper {
            width: 80px;
            height: 80px;
        }

        .qr-card-name {
            font-size: 12px;
        }

        .qr-card-email {
            font-size: 9px;
        }

        .id-number {
            font-size: 10px;
        }
    }
</style>

<div class="qr-container">
    {{-- Header --}}
    <div class="qr-header">
        <div class="qr-header-left">
            <h1>QR Codes</h1>
            <p>View and print QR codes for members and staff</p>
        </div>
        <div class="qr-header-actions no-print">
            <button onclick="window.print()" class="btn btn-print">
                🖨️ Print All
            </button>
            <a href="{{ route('attendance.scan') }}" class="btn btn-secondary">Scanner</a>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="filter-section no-print">
        <form action="{{ route('attendance.qr-list') }}" method="GET" class="filter-form">
            <div class="filter-group filter-group-select">
                <label>Group</label>
                <select name="group" class="group-filter" onchange="this.form.submit()">
                    <option value="members" {{ request('group') == 'members' ? 'selected' : '' }}>Members</option>
                    <option value="staff" {{ request('group') == 'staff' ? 'selected' : '' }}>Admin / Staff / Instructors</option>
                </select>
            </div>

            <button type="submit" class="btn btn-dark" style="display:flex; align-items:center; gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"></path>
                </svg>
                <span>Filter</span>
            </button>

            <div class="filter-divider"></div>

            <div class="filter-group filter-group-search">
                <label>Search Name</label>
                <input name="q" type="text" value="{{ request('q') }}" placeholder="Type a name..." />
            </div>

            <button type="submit" class="btn btn-primary-neon">Search</button>
            <a href="{{ route('attendance.qr-list') }}" class="btn btn-dark">Reset</a>
        </form>
    </div>

    {{-- QR Grid --}}
    @php
        $isStaffGroup = (request('group') == 'staff');
        $items = $isStaffGroup ? $staffList : $members;
    @endphp

    <div class="qr-grid">
        @forelse($items as $item)
            @php
                $name = $item->name ?? $item->user->name ?? 'N/A';
                $email = $item->email ?? $item->user->email ?? '';
                $memberId = $item->id;

                // Default styling for Members
                $badgeColor = '#e6fffa';
                $textColor = '#38b2ac';
                $label = 'Active Member';

                // Staff/Admin styling
                if($isStaffGroup) {
                    $rawRole = strtolower($item->role ?? $item->user->role ?? 'staff');
                    if($rawRole == 'instructor') {
                        $badgeColor = '#ebf8ff';
                        $textColor = '#2b6cb0';
                        $label = 'Instructor';
                    } elseif($rawRole == 'admin') {
                        $badgeColor = '#f0fff4';
                        $textColor = '#276749';
                        $label = 'Admin';
                    } else {
                        $badgeColor = '#fff5f5';
                        $textColor = '#e53e3e';
                        $label = 'Staff';
                    }
                } else {
                    // For members, display membership type if available
                    if(isset($item->membership_type)) {
                        $label = strtoupper($item->membership_type);
                    }
                }
            @endphp

            <div class="qr-card">
                <div class="qr-image-wrapper">
                    @if($item->qr_code_path)
                        <img src="{{ asset('storage/' . $item->qr_code_path) }}" alt="QR Code for {{ $name }}" loading="lazy"/>
                    @else
                        <div style="color:#aaa; font-size:12px; text-align:center; padding:10px;">
                            No QR<br>Generated
                        </div>
                    @endif
                </div>

                <div class="qr-card-name" title="{{ $name }}">{{ $name }}</div>
                <div class="qr-card-email">{{ $email }}</div>

                <span class="badge-role" style="background:{{ $badgeColor }}; color:{{ $textColor }};">
                    {{ $label }}
                </span>

                <div class="scanner-id-tag">
                    <span class="id-label">Manual Scanner ID</span>
                    <span class="id-number">{{ $memberId }}</span>
                </div>

                @if($item->qr_token)
                    <div class="qr-token-hash">{{ substr($item->qr_token, 0, 24) }}...</div>
                @endif
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                No records found for "{{ request('group', 'members') }}".
                @if(request('q'))
                    <div style="margin-top:8px; font-size:13px;">Try adjusting your search terms.</div>
                @endif
            </div>
        @endforelse
    </div>
</div>

@endsection