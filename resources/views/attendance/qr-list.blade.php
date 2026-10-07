{{-- Staff users get the staff layout (charcoal & gold nav + tokens); everyone else keeps the admin layout. --}}
@extends(auth()->user()?->role === 'staff' ? 'layouts.staff' : 'layouts.admin')
@section('title', 'QR Codes – APEX')
@section('page_title', 'QR Codes')

@section('content')
<style>
    /* ===== CHARCOAL & GOLD THEME =====
       Uses the same variables as the staff dashboard
       (--accent, --accent-2, --accent-dark, --accent-soft, --surface, --surface2,
        --surface3, --border, --text, --text-soft, --muted, --success, --warning,
        --danger, --info, --radius) defined in layouts/staff.blade.php.
       Change the palette there, not here. */

    @if(auth()->user()?->role !== 'staff')
    /* Admin layout fallback: force the gold palette for this page's content (dark mode only) */
    html:not([data-theme="light"]) .qr-container {
        --accent: #e0a93b;
        --accent-2: #f3c866;
        --accent-dark: #b8862a;
        --accent-soft: rgba(224,169,59,0.12);
        --text-soft: #d2d3d8;
        --success: #4ade80;
        --warning: #fbbf24;
        --danger: #f87171;
        --info: #60a5fa;
    }
    @endif

    .qr-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* ===== HEADER ===== */
    .qr-header {
        margin-bottom: 28px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }

    .qr-header-left h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text);
    }

    .qr-header-left h1 span { color: var(--accent); }

    .qr-header-left p {
        color: var(--muted);
        font-size: 14px;
    }

    .qr-header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* ===== FILTER PANEL ===== */
    .filter-section {
        background: var(--surface);
        border: 1px solid var(--border);
        padding: 22px 24px;
        border-radius: 16px;
        margin-bottom: 28px;
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
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .filter-group-select { min-width: 200px; }

    .filter-group-search {
        flex: 1;
        min-width: 200px;
    }

    .filter-group-search input {
        background: var(--surface2);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 10px 14px;
        border-radius: 8px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        height: 42px;
        font-family: inherit;
        font-size: 14px;
    }

    .filter-group-search input::placeholder { color: var(--muted); }

    .filter-group-search input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--accent-soft);
    }

    .filter-divider { display: none; }

    select.group-filter {
        background-color: var(--surface2) !important;
        color: var(--text) !important;
        border: 1px solid var(--border) !important;
        padding: 10px 35px 10px 15px;
        border-radius: 8px;
        appearance: none;
        width: 100%;
        height: 42px;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23e0a93b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    select.group-filter:focus {
        border-color: var(--accent) !important;
        box-shadow: 0 0 0 3px var(--accent-soft);
        outline: none;
    }

    select.group-filter option {
        background-color: var(--surface2);
        color: var(--text);
    }

    /* ===== BUTTONS ===== */
    .btn {
        padding: 10px 20px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 42px;
        white-space: nowrap;
    }

    /* Gold gradient — same as the dashboard's "View Full Profile" button */
    .btn-primary-neon,
    .btn-print {
        background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
        color: #1a1a1a;
        font-weight: 800;
    }

    .btn-primary-neon:hover,
    .btn-print:hover {
        filter: brightness(1.08);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(224,169,59,0.25);
    }

    /* Dark outlined — same as the dashboard's "View All →" button */
    /* The background is always near-black, so the text must be a fixed light
       colour — var(--text) turns dark in light mode and made these buttons vanish. */
    .btn-dark,
    .btn-secondary {
        background: #0e0e10;
        color: #ffffff;
        border-color: var(--border);
        font-weight: 600;
    }

    .btn-dark:hover,
    .btn-secondary:hover {
        color: var(--accent-2);
        border-color: rgba(224,169,59,0.45);
    }

    /* Light mode: keep the black buttons black with gold hover */
    html:root[data-theme="light"] .btn-dark {
        background: #111111;
        color: #ffffff;
        border-color: #111111;
    }

    html:root[data-theme="light"] .btn-dark:hover {
        color: var(--accent-2);
        border-color: var(--accent-2);
    }

    /* ===== QR GRID ===== */
    .qr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 22px;
        margin-top: 0;
    }

    .qr-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        text-align: center;
        padding: 24px;
        color: var(--text);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .qr-card:hover {
        transform: translateY(-4px);
        border-color: rgba(224,169,59,0.5);
        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
    }

    .qr-image-wrapper {
        width: 160px;
        height: 160px;
        margin: 0 auto 15px auto;
        background: #ffffff;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid var(--accent);
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
        color: var(--text);
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }

    .qr-card-email {
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 8px;
        word-break: break-all;
    }

    /* Role / plan pills — same pill language as the dashboard's .status-pill */
    .badge-role {
        font-size: 10px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 40px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: inline-block;
        margin-top: 8px;
        border: 1px solid transparent;
    }

    .badge-member {
        background: color-mix(in srgb, var(--success) 15%, transparent);
        color: var(--success);
        border-color: color-mix(in srgb, var(--success) 30%, transparent);
    }

    .badge-instructor {
        background: color-mix(in srgb, var(--info) 15%, transparent);
        color: var(--info);
        border-color: color-mix(in srgb, var(--info) 30%, transparent);
    }

    .badge-admin {
        background: var(--accent-soft);
        color: var(--accent);
        border-color: rgba(224,169,59,0.35);
    }

    .badge-staff {
        background: color-mix(in srgb, var(--warning) 15%, transparent);
        color: var(--warning);
        border-color: color-mix(in srgb, var(--warning) 30%, transparent);
    }

    .scanner-id-tag {
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px dashed var(--border);
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .id-label {
        font-size: 9px;
        color: var(--muted);
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1.5px;
        margin-bottom: 4px;
    }

    .id-number {
        font-family: 'Courier New', monospace;
        background: var(--accent-soft);
        border: 1px solid rgba(224,169,59,0.3);
        color: var(--accent);
        padding: 2px 12px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 800;
    }

    .qr-token-hash {
        font-size: 8px;
        color: var(--muted);
        margin-top: 10px;
        font-family: monospace;
        word-break: break-all;
        opacity: 0.6;
        max-width: 100%;
    }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 100px 20px;
        color: var(--muted);
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
    }

    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.25;
    }

    /* ===== PRINT ===== */
    @media print {
        .no-print { display: none !important; }
        .qr-grid { display: block; }
        .qr-card {
            break-inside: avoid;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            box-shadow: none;
            page-break-inside: avoid;
            background: #ffffff;
            color: #111111;
        }
        .qr-card-name,
        .qr-card-email,
        .id-label { color: #111111 !important; }
        .qr-image-wrapper { border-color: #111111; }
        .id-number {
            background: #111;
            border-color: #111;
            color: #e0a93b;
        }
        .qr-container { padding: 0; }
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
        }
        .qr-image-wrapper { width: 140px; height: 140px; }
    }

    @media (max-width: 768px) {
        .qr-container { padding: 0 12px; }

        .qr-header {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 22px;
        }

        .qr-header-left h1 { font-size: 24px; }
        .qr-header-left p { font-size: 13px; }
        .qr-header-actions { width: 100%; }

        .qr-header-actions .btn {
            flex: 1;
            min-width: 80px;
            font-size: 13px;
            padding: 8px 16px;
        }

        .filter-section { padding: 16px; border-radius: 12px; }
        .filter-form { flex-direction: column; align-items: stretch; gap: 10px; }
        .filter-group,
        .filter-group-select,
        .filter-group-search { width: 100%; min-width: unset; }
        .filter-form .btn { width: 100%; }

        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
        }

        .qr-card { padding: 16px; border-radius: 12px; }
        .qr-image-wrapper { width: 120px; height: 120px; }
        .qr-card-name { font-size: 16px; }
        .qr-card-email { font-size: 11px; }
        .id-number { font-size: 12px; }
        .badge-role { font-size: 9px; padding: 3px 10px; }
        .qr-token-hash { font-size: 7px; }
        .empty-state { padding: 60px 16px; }
        .empty-state-icon { font-size: 36px; }
    }

    @media (max-width: 480px) {
        .qr-container { padding: 0 8px; }
        .qr-header-left h1 { font-size: 20px; }
        .qr-header-left p { font-size: 12px; }

        .qr-header-actions .btn {
            font-size: 12px;
            padding: 6px 12px;
            height: 36px;
            min-width: 60px;
        }

        .filter-section { padding: 12px; border-radius: 10px; }
        .filter-group label { font-size: 9px; }

        select.group-filter { padding: 8px 30px 8px 12px; font-size: 13px; height: 38px; }
        .filter-group-search input { height: 38px; font-size: 13px; padding: 8px 12px; }
        .filter-form .btn { height: 38px; font-size: 13px; padding: 6px 14px; }

        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }

        .qr-card { padding: 12px; border-radius: 10px; }
        .qr-image-wrapper { width: 100px; height: 100px; margin-bottom: 10px; }
        .qr-card-name { font-size: 14px; }
        .qr-card-email { font-size: 10px; margin-bottom: 4px; }
        .badge-role { font-size: 8px; padding: 2px 8px; margin-top: 4px; }
        .scanner-id-tag { margin-top: 10px; padding-top: 8px; }
        .id-label { font-size: 8px; }
        .id-number { font-size: 11px; padding: 1px 8px; }
        .qr-token-hash { font-size: 6px; margin-top: 6px; }
        .empty-state { padding: 40px 12px; }
        .empty-state-icon { font-size: 28px; }
    }

    @media (max-width: 360px) {
        .qr-grid {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 8px;
        }
        .qr-card { padding: 10px; }
        .qr-image-wrapper { width: 80px; height: 80px; }
        .qr-card-name { font-size: 12px; }
        .qr-card-email { font-size: 9px; }
        .id-number { font-size: 10px; }
    }
</style>

<div class="qr-container">
    {{-- Header --}}
    <div class="qr-header">
        <div class="qr-header-left">
            <h1>QR <span>Codes</span></h1>
            <p>View and print QR codes for members and staff</p>
        </div>
        <div class="qr-header-actions no-print">
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

            <button type="submit" class="btn btn-dark">
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
                $badgeClass = 'badge-member';
                $label = 'Active Member';

                // Staff/Admin styling
                if($isStaffGroup) {
                    $rawRole = strtolower($item->role ?? $item->user->role ?? 'staff');
                    if($rawRole == 'instructor') {
                        $badgeClass = 'badge-instructor';
                        $label = 'Instructor';
                    } elseif($rawRole == 'admin') {
                        $badgeClass = 'badge-admin';
                        $label = 'Admin';
                    } else {
                        $badgeClass = 'badge-staff';
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
                        <img src="{{ asset('storage/' . $item->qr_code_path) }}" alt="QR Code for {{ $name }}" loading="lazy" style="background:#fff;padding:10px;border-radius:8px"/>
                    @else
                        <div style="color:#6f6a5e; font-size:12px; text-align:center; padding:10px;">
                            No QR<br>Generated
                        </div>
                    @endif
                </div>

                <div class="qr-card-name" title="{{ $name }}">{{ $name }}</div>
                <div class="qr-card-email">{{ $email }}</div>

                <span class="badge-role {{ $badgeClass }}">
                    {{ $label }}
                </span>

                <div class="scanner-id-tag">
                    <span class="id-label">Manual Scanner ID</span>
                    <span class="id-number">{{ $memberId }}</span>
                </div>

                @if($item->qr_token)
                    <div class="qr-token-hash">{{ substr($item->qr_token, 0, 24) }}...</div>
                @endif

                @php
                    $printRoute = $isStaffGroup ? route('users.qr.print', $item) : route('members.qr.print', $item);
                @endphp
                <a href="{{ $printRoute }}" class="btn btn-print" style="margin-top:14px;width:100%;justify-content:center;">🖨️ Print QR</a>
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