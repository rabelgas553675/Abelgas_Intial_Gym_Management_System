@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')

@section('title', 'Members – IRONFORGE')
@section('page_title', 'Members')
@section('active_nav', 'members')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .members-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Toolbar */
    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .toolbar-filters {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
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
        flex: 1;
        min-width: 180px;
        max-width: 280px;
    }

    .search-wrapper svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        opacity: 0.5;
    }

    .search-wrapper input {
        width: 100%;
        padding: 10px 14px 10px 36px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 13px;
        outline: none;
        transition: border-color .15s;
        min-height: 44px;
    }

    .search-wrapper input:focus {
        border-color: var(--accent);
    }

    .filter-select {
        padding: 10px 36px 10px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 13px;
        outline: none;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23aaaaaa' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 11px;
        min-height: 44px;
        min-width: 120px;
    }

    .filter-select option {
        background: var(--surface1);
        color: var(--text);
    }

    .filter-select option[value=""] {
        color: var(--muted);
    }

    .btn-filter {
        padding: 10px 18px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: .15s;
        min-height: 44px;
        white-space: nowrap;
    }

    .btn-filter:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .btn-clear {
        padding: 10px 14px;
        color: var(--muted);
        font-size: 12px;
        text-decoration: none;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: .15s;
        white-space: nowrap;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
    }

    .btn-clear:hover {
        color: var(--text);
    }

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
        gap: 8px;
        white-space: nowrap;
        transition: .15s;
        border: none;
        min-height: 44px;
        flex-shrink: 0;
    }

    .btn-add:hover {
        opacity: .88;
    }

    /* Results info */
    .results-info {
        margin-bottom: 14px;
        font-size: 13px;
        color: var(--muted);
    }

    .results-info strong {
        color: var(--text);
    }

    .results-info .highlight {
        color: var(--accent);
    }

    /* Table */
    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--surface1);
    }

    .members-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        min-width: 900px;
    }

    .members-table th {
        padding: 14px 16px;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .members-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    .members-table tr:last-child td {
        border-bottom: none;
    }

    .members-table tr {
        transition: .15s;
    }

    .members-table tr:hover {
        background: rgba(255, 255, 255, 0.015);
    }

    .col-index {
        width: 44px;
        color: var(--muted);
        font-size: 13px;
    }

    .col-name {
        min-width: 200px;
    }

    .col-phone {
        white-space: nowrap;
        color: var(--muted);
        font-size: 13px;
    }

    .col-plan {
        width: 110px;
    }

    .col-role {
        width: 100px;
    }

    .col-status {
        width: 100px;
    }

    .col-start {
        width: 110px;
        white-space: nowrap;
        color: var(--muted);
        font-size: 13px;
    }

    .col-due {
        width: 110px;
        white-space: nowrap;
    }

    .col-actions {
        width: 180px;
        text-align: right;
        white-space: nowrap;
    }

    /* User cell */
    .user-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }

    .user-avatar-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        flex-shrink: 0;
        background: rgba(200, 255, 0, 0.1);
        border: 1px solid rgba(200, 255, 0, 0.2);
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
        font-size: 14px;
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

    /* Badges */
    .badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        display: inline-block;
    }

    .badge-plan {
        background: rgba(96, 165, 250, 0.1);
        color: #60a5fa;
        border: 1px solid rgba(96, 165, 250, 0.15);
    }

    .badge-role-staff {
        background: rgba(167, 139, 250, 0.1);
        color: #a78bfa;
        border: 1px solid rgba(167, 139, 250, 0.2);
    }

    .badge-role-instructor {
        background: rgba(251, 146, 60, 0.1);
        color: #fb923c;
        border: 1px solid rgba(251, 146, 60, 0.2);
    }

    .badge-role-member {
        background: rgba(74, 222, 128, 0.1);
        color: #4ade80;
        border: 1px solid rgba(74, 222, 128, 0.2);
    }

    .badge-role-default {
        background: rgba(255, 255, 255, 0.04);
        color: var(--muted);
        border: 1px solid var(--border);
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .badge-status .dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .badge-status.active {
        background: rgba(74, 222, 128, 0.1);
        color: #4ade80;
    }

    .badge-status.active .dot {
        background: #4ade80;
        box-shadow: 0 0 6px #4ade80;
    }

    .badge-status.expired {
        background: rgba(248, 113, 113, 0.1);
        color: #f87171;
    }

    .badge-status.expired .dot {
        background: #f87171;
    }

    .badge-status.pending {
        background: rgba(250, 204, 21, 0.1);
        color: #facc15;
    }

    .badge-status.pending .dot {
        background: #facc15;
    }

    /* Due date */
    .due-date {
        font-weight: 700;
        font-size: 13px;
    }

    .due-date.danger {
        color: #f87171;
    }

    .due-date.warning {
        color: #facc15;
    }

    .due-date.success {
        color: var(--accent);
    }

    /* Action buttons */
    .action-group {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-pill {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: .15s;
        display: inline-block;
        white-space: nowrap;
        min-height: 32px;
        line-height: 1;
    }

    .btn-pill:hover {
        background: var(--surface2);
        border-color: rgba(255, 255, 255, .15);
    }

    .btn-pill-danger {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: .15s;
        display: inline-block;
        white-space: nowrap;
        min-height: 32px;
        line-height: 1;
        font-family: inherit;
        background: transparent;
    }

    .btn-pill-danger:hover {
        color: #f87171;
        border-color: #f87171;
        background: rgba(248, 113, 113, .06);
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        font-size: 12px;
        color: var(--muted);
    }

    .pagination-wrapper .pagination {
        flex-wrap: wrap;
    }

    /* Empty state */
    .empty-state {
        padding: 80px;
        text-align: center;
        color: var(--muted);
    }

    .empty-state svg {
        display: block;
        margin: 0 auto 12px;
        opacity: .3;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .members-container {
            padding: 0 12px;
        }

        .toolbar-filters form {
            gap: 8px;
        }

        .filter-select {
            min-width: 100px;
            font-size: 12px;
            padding: 8px 32px 8px 12px;
        }

        .search-wrapper {
            max-width: 220px;
            min-width: 150px;
        }

        .search-wrapper input {
            font-size: 12px;
            padding: 8px 12px 8px 32px;
        }

        .btn-filter {
            font-size: 12px;
            padding: 8px 14px;
        }

        .btn-add {
            font-size: 12px;
            padding: 8px 16px;
        }
    }

    @media (max-width: 768px) {
        .members-container {
            padding: 0 8px;
        }

        .toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }

        .toolbar-filters {
            width: 100%;
        }

        .toolbar-filters form {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            width: 100%;
        }

        .search-wrapper {
            max-width: 100%;
            min-width: unset;
        }

        .search-wrapper input {
            font-size: 14px;
            padding: 10px 14px 10px 36px;
            min-height: 44px;
        }

        .filter-select {
            width: 100%;
            min-width: unset;
            font-size: 14px;
            padding: 10px 36px 10px 14px;
            min-height: 44px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-actions .btn-filter {
            flex: 1;
            justify-content: center;
            min-width: 80px;
            min-height: 44px;
        }

        .filter-actions .btn-clear {
            flex: 0 1 auto;
            min-height: 44px;
        }

        .btn-add {
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }

        .results-info {
            font-size: 12px;
        }

        .members-table {
            min-width: 750px;
        }

        .members-table th,
        .members-table td {
            padding: 10px 12px;
            font-size: 12px;
        }

        .members-table .col-phone {
            display: none;
        }

        .user-avatar,
        .user-avatar-placeholder {
            width: 30px;
            height: 30px;
            font-size: 10px;
        }

        .user-name {
            font-size: 13px;
        }

        .user-email {
            font-size: 10px;
        }

        .badge {
            font-size: 10px;
            padding: 3px 8px;
        }

        .badge-status {
            font-size: 10px;
            padding: 3px 8px;
        }

        .due-date {
            font-size: 12px;
        }

        .btn-pill,
        .btn-pill-danger {
            font-size: 11px;
            padding: 4px 10px;
            min-height: 28px;
        }

        .pagination-wrapper {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 12px 16px;
        }

        .pagination-info {
            font-size: 11px;
        }

        .empty-state {
            padding: 48px 20px;
        }
    }

    @media (max-width: 480px) {
        .members-container {
            padding: 0 6px;
        }

        .members-table {
            min-width: 600px;
        }

        .members-table th,
        .members-table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .members-table .col-index {
            width: 32px;
            font-size: 11px;
        }

        .members-table .col-start,
        .members-table .col-due {
            display: none;
        }

        .user-avatar,
        .user-avatar-placeholder {
            width: 26px;
            height: 26px;
            font-size: 9px;
        }

        .user-name {
            font-size: 12px;
        }

        .user-email {
            font-size: 9px;
        }

        .badge {
            font-size: 9px;
            padding: 2px 6px;
        }

        .badge-status {
            font-size: 9px;
            padding: 2px 6px;
        }

        .due-date {
            font-size: 11px;
        }

        .btn-pill,
        .btn-pill-danger {
            font-size: 10px;
            padding: 3px 8px;
            min-height: 24px;
        }

        .action-group {
            gap: 4px;
        }

        .filter-actions .btn-filter {
            font-size: 12px;
            padding: 8px 12px;
            min-height: 38px;
        }

        .filter-actions .btn-clear {
            font-size: 11px;
            min-height: 38px;
        }

        .search-wrapper input {
            font-size: 13px;
            min-height: 38px;
            padding: 8px 12px 8px 32px;
        }

        .filter-select {
            font-size: 13px;
            min-height: 38px;
            padding: 8px 32px 8px 12px;
        }

        .btn-add {
            font-size: 12px;
            min-height: 38px;
            padding: 8px 14px;
        }

        .empty-state {
            padding: 32px 16px;
            font-size: 13px;
        }

        .empty-state svg {
            width: 32px;
            height: 32px;
        }
    }

    @media (max-width: 360px) {
        .members-table {
            min-width: 500px;
        }

        .members-table th,
        .members-table td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .members-table .col-role {
            display: none;
        }

        .user-avatar,
        .user-avatar-placeholder {
            width: 22px;
            height: 22px;
            font-size: 8px;
        }

        .user-name {
            font-size: 11px;
        }

        .badge {
            font-size: 8px;
            padding: 2px 5px;
        }

        .btn-pill,
        .btn-pill-danger {
            font-size: 9px;
            padding: 2px 6px;
            min-height: 20px;
        }

        .search-wrapper input {
            font-size: 12px;
            min-height: 34px;
            padding: 6px 10px 6px 28px;
        }

        .search-wrapper svg {
            width: 12px;
            height: 12px;
            left: 10px;
        }

        .filter-select {
            font-size: 12px;
            min-height: 34px;
            padding: 6px 28px 6px 10px;
            background-size: 10px;
        }

        .btn-add {
            font-size: 11px;
            min-height: 34px;
            padding: 6px 12px;
        }

        .filter-actions .btn-filter {
            font-size: 11px;
            min-height: 34px;
            padding: 6px 10px;
        }

        .filter-actions .btn-clear {
            font-size: 10px;
            min-height: 34px;
            padding: 6px 10px;
        }
    }
</style>

<div class="members-container">

    {{-- ══ Toolbar ══════════════════════════════════════ --}}
    <div class="toolbar">

        {{-- Search + Filters --}}
        <div class="toolbar-filters">
            <form method="GET" action="{{ route('members.index') }}">

                <div class="search-wrapper">
                    <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search members..."
                           onfocus="this.style.borderColor='var(--accent)'"
                           onblur="this.style.borderColor='var(--border)'">
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
                </select>

                <select name="role" class="filter-select">
                    <option value="">All Roles</option>
                    <option value="Staff"      {{ request('role')=='Staff'      ? 'selected' : '' }}>Staff</option>
                    <option value="Instructor" {{ request('role')=='Instructor' ? 'selected' : '' }}>Instructor</option>
                </select>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">Filter</button>

                    @if(request('search') || request('plan') || request('status') || request('role'))
                        <a href="{{ route('members.index') }}" class="btn-clear">
                            ✕ Clear
                        </a>
                    @endif
                </div>
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
                    <th class="col-name">Name</th>
                    <th class="col-phone">Phone</th>
                    <th class="col-plan">Plan</th>
                    <th class="col-role">Role</th>
                    <th class="col-status">Status</th>
                    <th class="col-start">Start Date</th>
                    <th class="col-due">Due Date</th>
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
                            <div>
                                <div class="user-name">{{ $member->name ?? trim(($member->first_name ?? '').' '.($member->last_name ?? '')) }}</div>
                                <div class="user-email">{{ $member->email }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="col-phone">{{ $member->phone ?? '—' }}</td>

                    <td>
                        <span class="badge badge-plan">{{ $member->membership_type ?? '—' }}</span>
                    </td>

                    <td>
                        <span class="badge" style="background:{{ $roleBg }}; color:{{ $roleColor }}; border:1px solid {{ $roleBorder }};">
                            {{ $role ?? '—' }}
                        </span>
                    </td>

                    <td>
                        @php
                            $isActive  = ($member->status ?? '') === 'Active';
                            $isExpired = ($member->status ?? '') === 'Expired';
                            $statusClass = $isActive ? 'active' : ($isExpired ? 'expired' : 'pending');
                        @endphp
                        <span class="badge-status {{ $statusClass }}">
                            <span class="dot"></span>
                            {{ $member->status ?? '—' }}
                        </span>
                    </td>

                    <td class="col-start">{{ isset($member->start_date) && $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('Y-m-d') : '—' }}</td>

                    <td class="col-due">
                        @if(isset($member->end_date) && $member->end_date)
                            @php $due = \Carbon\Carbon::parse($member->end_date); @endphp
                            <span class="due-date {{ $due->isPast() ? 'danger' : ($due->diffInDays(now()) <= 7 ? 'warning' : 'success') }}">
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
                                          onsubmit="return confirm('Delete this member?')" style="display:inline;">
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
                <tr>
                    <td colspan="9" class="empty-state">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        No members found.
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

@endsection