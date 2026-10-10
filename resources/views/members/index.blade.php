@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')

@section('title', 'Members – APEX')
@section('page_title', in_array(request('role'), ['Staff', 'Instructor']) ? request('role') : 'Members')
@section('active_nav', 'members')

@section('content')

@php
    // Label changes with the Role filter: Member (default) / Staff / Instructor
    $roleFilter = in_array(request('role'), ['Staff', 'Instructor']) ? request('role') : null;
    $roleLabel  = $roleFilter ?? 'Member';
    $rolePlural = $roleFilter === 'Staff' ? 'staff'
                : ($roleFilter === 'Instructor' ? 'instructors' : 'members');
@endphp

<style>
    /* ═══════════════════════════════════════════════════════════
       MEMBERS PAGE — no horizontal scroll at any width
       • ≥1024px : fixed-layout table, percentage column widths
       • <1024px : each row becomes a labelled card (all data kept)
       ═══════════════════════════════════════════════════════════ */

    .members-container {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0;
    }

    /* ───────── Toolbar ───────── */
    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .toolbar-filters {
        display: flex;
        align-items: center;
        flex: 1;
        min-width: 0;
    }

    .toolbar-filters form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        width: 100%;
    }

    .search-wrapper {
        position: relative;
        flex: 1 1 200px;
        min-width: 160px;
        max-width: 300px;
    }

    .search-wrapper svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        opacity: .5;
    }

    .search-wrapper input {
        width: 100%;
        padding: 10px 14px 10px 36px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 13px;
        font-family: inherit;
        outline: none;
        transition: border-color .15s;
        min-height: 42px;
    }

    .search-wrapper input:focus { border-color: var(--accent); }

    .filter-select {
        padding: 10px 34px 10px 14px;
        background-color: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 13px;
        font-family: inherit;
        outline: none;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23aaaaaa' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 11px;
        min-height: 42px;
        min-width: 120px;
    }

    .filter-select:focus { border-color: var(--accent); }
    .filter-select option { background: var(--surface2); color: var(--text); }

    .filter-actions { display: flex; align-items: center; gap: 8px; }

    .btn-filter {
        padding: 10px 18px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        transition: .15s;
        min-height: 42px;
        white-space: nowrap;
    }

    .btn-filter:hover { border-color: var(--accent); color: var(--accent); }

    .btn-clear {
        padding: 10px 12px;
        color: var(--muted);
        font-size: 12px;
        text-decoration: none;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: .15s;
        white-space: nowrap;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
    }

    .btn-clear:hover { color: var(--text); }

    .btn-add {
        padding: 10px 22px;
        background: var(--accent);
        color: #000;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
        transition: .15s;
        border: none;
        min-height: 42px;
        flex-shrink: 0;
    }

    .btn-add:hover { opacity: .88; }

    /* ───────── Results info ───────── */
    .results-info { margin-bottom: 14px; font-size: 13px; color: var(--muted); }
    .results-info strong { color: var(--text); }
    .results-info .highlight { color: var(--accent); }

    /* ───────── Table shell ───────── */
    .table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow: hidden;              /* nothing may push the page sideways */
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--surface);
    }

    .members-table {
        width: 100%;
        min-width: 0;                  /* cancels the global table min-width */
        table-layout: fixed;           /* columns obey the widths below */
        border-collapse: collapse;
        text-align: left;
    }

    .members-table th {
        padding: 13px 10px;
        color: var(--muted);
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        line-height: 1.25;
        background: rgba(255, 255, 255, .02);
        border-bottom: 1px solid var(--border);
        white-space: normal;
        vertical-align: middle;
    }

    .members-table td {
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        font-size: 13px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .members-table tr:last-child td { border-bottom: none; }
    .members-table tbody tr { transition: background .15s; }

    /* Column widths — total 100% */
    .col-index   { width: 4%;  padding-left: 14px !important; padding-right: 0 !important; color: var(--muted); font-size: 12px; }
    .col-name    { width: 21%; }
    .col-phone   { width: 11%; color: var(--muted); font-size: 12.5px; overflow-wrap: anywhere; }
    .col-plan    { width: 10%; }
    .col-role    { width: 9%;  }
    .col-status  { width: 9%;  }
    .col-start   { width: 10%; color: var(--muted); font-size: 12.5px; white-space: nowrap; }
    .col-due     { width: 10%; white-space: nowrap; }
    .col-actions { width: 16%; text-align: right; padding-right: 14px !important; overflow: visible !important; }

    /* ───────── Member cell ───────── */
    .user-cell { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .user-info { min-width: 0; flex: 1; }

    .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }

    .user-avatar-placeholder {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        flex-shrink: 0;
        background: var(--accent-soft);
        border: 1px solid rgba(224, 169, 59, .3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--accent);
    }

    .user-name {
        font-weight: 600;
        color: var(--text);
        font-size: 13.5px;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-email {
        font-size: 11px;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ───────── Badges ───────── */
    .members-table .badge {
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        letter-spacing: 0;
        text-transform: none;
    }

    .badge-plan {
        background: rgba(96, 165, 250, .1);
        color: #60a5fa;
        border: 1px solid rgba(96, 165, 250, .15);
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-status .dot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }

    .badge-status.active  { background: rgba(74, 222, 128, .1);  color: #4ade80; }
    .badge-status.active .dot { background: #4ade80; box-shadow: 0 0 6px #4ade80; }
    .badge-status.expired { background: rgba(248, 113, 113, .1); color: #f87171; }
    .badge-status.expired .dot { background: #f87171; }
    .badge-status.pending { background: rgba(250, 204, 21, .1);  color: #facc15; }
    .badge-status.pending .dot { background: #facc15; }
    .badge-status.suspended { background: rgba(251, 146, 60, .12); color: #fb923c; }
    .badge-status.suspended .dot { background: #fb923c; }
    .badge-status.inactive  { background: rgba(148, 163, 184, .12); color: #94a3b8; }
    .badge-status.inactive .dot { background: #94a3b8; }
    .badge-status.expiring { background: rgba(251, 191, 36, .12); color: #fbbf24; }
    .badge-status.expiring .dot { background: #fbbf24; box-shadow: 0 0 6px #fbbf24; }

    /* "N days left" line under the status badge */
    .days-left { margin-top: 4px; font-size: 11px; font-weight: 600; color: var(--muted, #888); }
    .days-left.active   { color: #4ade80; }
    .days-left.expiring { color: #fbbf24; }
    .days-left.expired  { color: #f87171; }

    /* ───────── Due date ───────── */
    .due-date { font-weight: 700; font-size: 12.5px; }
    .due-date.danger  { color: #f87171; }
    .due-date.warning { color: #facc15; }
    .due-date.success { color: var(--accent); }

    /* ───────── Actions ───────── */
    .action-group {
        display: inline-flex;
        flex-wrap: nowrap;
        gap: 6px;
        align-items: center;
        justify-content: flex-end;
    }

    .action-group form { display: inline-flex; margin: 0; }

    .btn-pill,
    .btn-pill-danger {
        background: rgba(255, 255, 255, .03);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 6px 12px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        font-family: inherit;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        transition: .15s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        min-height: 30px;
        line-height: 1;
    }

    .btn-pill:hover {
        background: var(--surface2);
        border-color: rgba(255, 255, 255, .15);
    }

    .btn-pill-danger { background: transparent; }

    .btn-pill-danger:hover {
        color: #f87171;
        border-color: #f87171;
        background: rgba(248, 113, 113, .06);
    }

    /* ───────── Pagination ───────── */
    .pagination-wrapper {
        padding: 14px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info { font-size: 12px; color: var(--muted); }
    .pagination-wrapper .pagination { flex-wrap: wrap; }
    .pagination-wrapper nav { max-width: 100%; }

    /* ───────── Empty state ───────── */
    .empty-state { padding: 70px 20px !important; text-align: center; color: var(--muted); }
    .empty-state svg { display: block; margin: 0 auto 12px; opacity: .3; }

    /* ═══════════════════════════════════════════
       COMPACT DESKTOP / SMALL LAPTOP (1024–1200)
       ═══════════════════════════════════════════ */
    @media (max-width: 1200px) {
        .members-table th { padding: 12px 8px; font-size: 10px; letter-spacing: .5px; }
        .members-table td { padding: 11px 8px; font-size: 12px; }
        .col-index   { padding-left: 12px !important; }
        .col-actions { padding-right: 12px !important; }
        .col-phone, .col-start { font-size: 12px; }
        .due-date { font-size: 12px; }
        .user-cell { gap: 8px; }
        .user-avatar, .user-avatar-placeholder { width: 30px; height: 30px; font-size: 11px; }
        .user-name { font-size: 13px; }
        .user-email { font-size: 10.5px; }
        .members-table .badge, .badge-status { font-size: 10px; padding: 3px 7px; }
        .action-group { gap: 4px; }
        .btn-pill, .btn-pill-danger { padding: 6px 9px; font-size: 11px; min-height: 28px; }
    }

    /* ═══════════════════════════════════════════
       TABLET & MOBILE (<1024): ROWS → CARDS
       Every field stays visible, labelled.
       ═══════════════════════════════════════════ */
    @media (max-width: 1023px) {
        .toolbar { flex-direction: column; align-items: stretch; gap: 10px; }
        .toolbar-filters { width: 100%; }
        .search-wrapper { max-width: none; flex: 1 1 100%; }
        .btn-add { width: 100%; }

        /* visually hide header row (kept for screen readers) */
        .members-table thead {
            position: absolute;
            width: 1px; height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
        }

        .members-table,
        .members-table tbody { display: block; width: 100%; }

        .members-table tbody tr {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px 16px;
            padding: 16px;
            border-bottom: 1px solid var(--border);
            align-items: start;
        }

        .members-table tbody tr:last-child { border-bottom: none; }
        .members-table tbody tr:hover td { background: transparent; }
        .members-table tbody tr:hover { background: rgba(255, 255, 255, .02); }

        .members-table td {
            display: block;
            width: auto;
            padding: 0 !important;
            border: none;
            font-size: 13px;
            white-space: normal;
            overflow: visible;
            text-align: left;
            min-width: 0;
        }

        /* small caption above each value */
        .members-table td[data-label]::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--muted);
        }

        .col-index { display: none; }          /* row number only; every data field stays */

        .col-name   { grid-column: 1 / span 2; grid-row: 1; }
        .col-status { grid-column: 3; grid-row: 1; justify-self: end; }
        .col-status::before { display: none !important; }

        .col-phone, .col-start { font-size: 13px; overflow-wrap: anywhere; }
        .due-date { font-size: 13px; }

        .user-avatar, .user-avatar-placeholder { width: 38px; height: 38px; font-size: 12px; }
        .user-name  { font-size: 14px; }
        .user-email { font-size: 11.5px; }

        .col-actions {
            grid-column: 1 / -1;
            padding-top: 4px !important;
            border-top: 1px dashed var(--border);
            margin-top: 2px;
        }

        .action-group { display: flex; width: 100%; gap: 8px; padding-top: 12px; }
        .action-group > a,
        .action-group > form { flex: 1; }
        .action-group form { display: flex; }
        .action-group .btn-pill,
        .action-group .btn-pill-danger { width: 100%; min-height: 38px; font-size: 12.5px; }

        .empty-row { display: block !important; padding: 0 !important; }
        .empty-row td { display: block; }
        .empty-state { padding: 48px 20px !important; }

        .pagination-wrapper { flex-direction: column; align-items: center; text-align: center; padding: 14px 16px; }
    }

    /* Filters on tablet/large phone */
    @media (max-width: 768px) {
        .toolbar-filters form {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }
        .search-wrapper { grid-column: 1 / -1; }
        .filter-select  { width: 100%; min-width: 0; }
        .filter-actions { grid-column: 1 / -1; }
        .filter-actions .btn-filter { flex: 1; }
        .search-wrapper input,
        .filter-select,
        .btn-filter,
        .btn-add { min-height: 44px; font-size: 14px; }
    }

    /* Phones */
    @media (max-width: 560px) {
        .toolbar-filters form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .filter-select[name="role"] { grid-column: 1 / -1; }

        .members-table tbody tr {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: 14px;
            gap: 12px 14px;
        }

        .col-name   { grid-column: 1 / -1; grid-row: 1; }
        .col-status { grid-column: 1 / -1; grid-row: auto; justify-self: start; order: 5; }
        .col-status::before { display: block !important; }

        /* order: phone, plan, role, start, due, status(kept together) */
        .col-phone   { order: 1; }
        .col-plan    { order: 2; }
        .col-role    { order: 3; }
        .col-status  { order: 4; grid-column: auto; }
        .col-start   { order: 5; }
        .col-due     { order: 6; }
        .col-actions { order: 7; }
    }

    @media (max-width: 360px) {
        .action-group { gap: 6px; }
        .action-group .btn-pill,
        .action-group .btn-pill-danger { font-size: 12px; padding: 6px 4px; }
    }
</style>

<div class="members-container">

    {{-- ══ Toolbar ══════════════════════════════════════ --}}
    <div class="toolbar">

        {{-- Search + Filters --}}
        <div class="toolbar-filters">
            <form method="GET" action="{{ route('members.index') }}" id="memberFilterForm">

                <div class="search-wrapper">
                    <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search {{ $rolePlural }}...">
                </div>

                <select name="plan" class="filter-select">
                    <option value="">All Plans</option>
                    @foreach(['Monthly','Quarterly','Semi-Annual','Annual'] as $p)
                        <option value="{{ $p }}" {{ request('plan')==$p?'selected':'' }}>{{ $p }}</option>
                    @endforeach
                </select>

                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Active"  {{ request('status')=='Active' ?'selected':'' }}>Active</option>
                    <option value="Expired" {{ request('status')=='Expired'?'selected':'' }}>Expired</option>
                    <option value="Inactive" {{ request('status')=='Inactive'?'selected':'' }}>Inactive</option>
                    <option value="Suspended" {{ request('status')=='Suspended'?'selected':'' }}>Suspended</option>
                </select>

                <select name="role" class="filter-select">
                    <option value="">Member</option>
                    <option value="Staff"      {{ request('role')=='Staff'      ? 'selected' : '' }}>Staff</option>
                    <option value="Instructor" {{ request('role')=='Instructor' ? 'selected' : '' }}>Instructor</option>
                </select>

                {{-- Filters apply automatically (see script at the bottom). The button only
                     shows if JavaScript is disabled. --}}
                <noscript><button type="submit" class="btn-filter">Filter</button></noscript>

                @if(request('search') || request('plan') || request('status') || request('role'))
                    <div class="filter-actions">
                        <a href="{{ route('members.index') }}" class="btn-clear">
                            ✕ Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('members.create') }}" class="btn-add">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Member
            </a>
        @endif
    </div>

    {{-- Results info --}}
    @if(request('search') || request('plan') || request('status') || request('role'))
    <div class="results-info">
        Showing <strong>{{ $members->total() }}</strong> result(s)
        @if(request('search')) for "<strong class="highlight">{{ request('search') }}</strong>"@endif
        @if(request('plan')) · Plan: <strong class="highlight">{{ request('plan') }}</strong>@endif
        @if(request('status')) · Status: <strong class="highlight">{{ request('status') }}</strong>@endif
        @if(request('role')) · Role: <strong class="highlight">{{ request('role') }}</strong>@endif
    </div>
    @endif

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="members-table">
            <thead>
                <tr>
                    <th class="col-index">#</th>
                    <th class="col-name">{{ $roleLabel }}</th>
                    <th class="col-phone">Contact</th>
                    <th class="col-plan">Plan</th>
                    <th class="col-role">Role</th>
                    <th class="col-status">Status</th>
                    <th class="col-start">Start Date</th>
                    <th class="col-due">Expiry Date</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                @php
                    $memberId  = is_object($member) && isset($member->id) ? $member->id : (is_array($member) ? $member['id'] : null);
                    $isUserRow = in_array(strtolower($member->role ?? ''), ['staff', 'instructor']);
                    $role = $member->role ?? null;

                    $roleColor  = match($role) {
                        'Staff'      => '#a78bfa',
                        'Instructor' => '#fb923c',
                        'Member'     => '#4ade80',
                        default      => 'var(--muted)',
                    };
                    $roleBg     = match($role) {
                        'Staff'      => 'rgba(167,139,250,0.1)',
                        'Instructor' => 'rgba(251,146,60,0.1)',
                        'Member'     => 'rgba(74,222,128,0.1)',
                        default      => 'rgba(255,255,255,0.04)',
                    };
                    $roleBorder = match($role) {
                        'Staff'      => 'rgba(167,139,250,0.2)',
                        'Instructor' => 'rgba(251,146,60,0.2)',
                        'Member'     => 'rgba(74,222,128,0.2)',
                        default      => 'var(--border)',
                    };

                    $statusClass = match($member->status ?? '') {
                        'Active'    => 'active',
                        'Expired'   => 'expired',
                        'Suspended' => 'suspended',
                        'Inactive'  => 'inactive',
                        'Expiring Soon' => 'expiring',
                        default     => 'pending',
                    };

                    // Same calculation as the member page (App\Services\MembershipExpiration).
                    // Rows without an end date (e.g. staff accounts) keep their original badge.
                    $expRow = null;
                    try {
                        if (!empty($member->end_date)) {
                            $expRow = \App\Services\MembershipExpiration::calculate(
                                !empty($member->start_date) ? \Carbon\Carbon::parse($member->start_date) : null,
                                \Carbon\Carbon::parse($member->end_date),
                                $member->status ?? null
                            );
                            $statusClass = match ($expRow->state) {
                                'active'    => 'active',
                                'expiring'  => 'expiring',
                                'expired'   => 'expired',
                                'suspended' => 'suspended',
                                'inactive'  => 'inactive',
                                default     => 'pending',
                            };
                        }
                    } catch (\Throwable $e) {
                        $expRow = null;
                    }
                @endphp
                <tr>
                    <td class="col-index">{{ $members->firstItem() + $loop->index }}</td>

                    <td class="col-name">
                        <div class="user-cell">
                            @if($member->photo)
                                <img src="{{ asset('storage/'.$member->photo) }}" class="user-avatar" alt="">
                            @else
                                <div class="user-avatar-placeholder">
                                    {{ strtoupper(substr($member->name ?? ($member->first_name ?? '?'), 0, 2)) }}
                                </div>
                            @endif
                            <div class="user-info">
                                <div class="user-name">{{ $member->name ?? trim(($member->first_name ?? '').' '.($member->last_name ?? '')) }}</div>
                                <div class="user-email">{{ $member->email }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="col-phone" data-label="Contact">{{ $member->phone ?? '—' }}</td>

                    <td class="col-plan" data-label="Plan">
                        <span class="badge badge-plan">{{ $member->membership_type ?? '—' }}</span>
                    </td>

                    <td class="col-role" data-label="Role">
                        <span class="badge" style="background:{{ $roleBg }}; color:{{ $roleColor }}; border:1px solid {{ $roleBorder }};">
                            {{ $role ?? '—' }}
                        </span>
                    </td>

                    <td class="col-status" data-label="Status">
                        <span class="badge-status {{ $statusClass }}">
                            <span class="dot"></span>
                            {{ $expRow?->status ?? $member->status ?? '—' }}
                        </span>
                    </td>

                    <td class="col-start" data-label="Start Date">{{ isset($member->start_date) && $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('Y-m-d') : '—' }}</td>

                    <td class="col-due" data-label="Expiry Date">
                        @if(isset($member->end_date) && $member->end_date)
                            @php
                                $due = $expRow?->endDate ?? \Carbon\Carbon::parse($member->end_date);
                                $dueClass = $expRow
                                    ? ($expRow->dateExpired ? 'danger' : ($expRow->isExpiringSoon() ? 'warning' : 'success'))
                                    : 'success';
                            @endphp
                            <span class="due-date {{ $dueClass }}">
                                {{ $due->format('Y-m-d') }}
                            </span>
                        @else
                            <span style="color:var(--muted);">—</span>
                        @endif
                    </td>

                    <td class="col-actions">
                        <div class="action-group">
                            @if(!$isUserRow)
                                <a href="{{ route('members.show', $memberId) }}" class="btn-pill">View</a>
                                @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                    <a href="{{ route('members.edit', $memberId) }}" class="btn-pill">Edit</a>
                                    <form method="POST" action="{{ route('members.destroy', $memberId) }}"
                                          onsubmit="return confirm('Delete this member?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-pill-danger">Delete</button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('users.show', $memberId) }}" class="btn-pill">View</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="empty-row">
                    <td colspan="9" class="empty-state">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        No {{ $rolePlural }} found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($members->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $members->firstItem() }}–{{ $members->lastItem() }} of {{ $members->total() }}
            </div>
            {{ $members->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>

<script>
    // Auto-apply filters: dropdowns submit as soon as they change, and the search
    // box submits shortly after the user stops typing (or immediately on Enter).
    // The server-side filtering in MemberController@index is unchanged.
    (function () {
        var form   = document.getElementById('memberFilterForm');
        if (!form) return;

        var search = form.querySelector('input[name="search"]');
        var KEY    = 'membersSearchFocus';
        var DELAY  = 450;                       // ms of silence before the search runs
        var timer  = null;
        var composing = false;                  // true while an IME (e.g. CJK) is mid-word
        var applied = search ? search.value.trim() : '';

        // Dropdowns: apply right away
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () { form.submit(); });
        });

        if (!search) return;

        function submitSearch() {
            clearTimeout(timer);
            try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
            form.submit();
        }

        function schedule() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (search.value.trim() !== applied) submitSearch();   // skip if nothing changed
            }, DELAY);
        }

        search.addEventListener('compositionstart', function () { composing = true; });
        search.addEventListener('compositionend',   function () { composing = false; schedule(); });
        search.addEventListener('input', function () { if (!composing) schedule(); });
        search.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); submitSearch(); }
        });

        // The page reloads after each search, so put the cursor back where the user was typing
        try {
            if (sessionStorage.getItem(KEY)) {
                sessionStorage.removeItem(KEY);
                search.focus();
                var end = search.value.length;
                search.setSelectionRange(end, end);
            }
        } catch (e) {}
    })();
</script>

@endsection