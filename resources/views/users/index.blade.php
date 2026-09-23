@extends('layouts.admin')
@section('title', 'Manage Users – APEX')
@section('page_title', 'Manage Users')
@section('active_nav', 'users')

@section('topbar_actions')
  <button onclick="document.getElementById('add-user-modal').style.display='flex'"
          class="btn btn-primary">+ Add User</button>
@endsection

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .users-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 28px;
    }

    .page-header h1 {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .page-header p {
        color: var(--muted);
        font-size: 14px;
    }

    /* Tabs */
    .tabs-wrapper {
        display: flex;
        gap: 4px;
        margin-bottom: 24px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 6px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tab-btn {
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        gap: 8px;
        background: transparent;
        color: var(--muted);
        white-space: nowrap;
        min-height: 40px;
        flex-shrink: 0;
    }

    .tab-btn.active {
        background: var(--surface2);
        color: var(--text);
    }

    .tab-btn .count {
        background: rgba(255, 255, 255, 0.08);
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
    }

    .tab-btn.active .count {
        background: rgba(255, 255, 255, 0.15);
    }

    .tab-btn:hover:not(.active) {
        color: var(--text);
    }

    /* Section Header */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 17px;
        font-weight: 700;
    }

    .section-title .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    /* Card */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }

    /* Table */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    table th {
        padding: 12px 18px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 2px;
        background: var(--surface2);
        white-space: nowrap;
        border-bottom: 1px solid var(--border);
    }

    table td {
        padding: 13px 18px;
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

    .col-index {
        color: var(--muted);
        width: 40px;
    }

    .col-actions {
        white-space: nowrap;
    }

    .user-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }

    .user-avatar-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .user-name {
        font-weight: 600;
    }

    .user-email {
        color: var(--muted);
    }

    .you-badge {
        color: var(--muted);
        font-size: 11px;
    }

    .action-group {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 6px 14px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 13px;
        min-height: 36px;
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

    .btn-danger-soft {
        background: rgba(248, 113, 113, 0.1);
        color: #f87171;
        border: 1px solid rgba(248, 113, 113, 0.2);
        padding: 6px 12px;
        min-height: 32px;
        font-size: 14px;
    }

    .btn-danger-soft:hover {
        background: rgba(248, 113, 113, 0.2);
    }

    .btn-sm {
        padding: 5px 12px;
        font-size: 12px;
        min-height: 32px;
    }

    /* Alert */
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .alert-success {
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.2);
        color: #4ade80;
    }

    .alert-danger {
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.2);
        color: #f87171;
    }

    .empty-state {
        text-align: center;
        color: var(--muted);
        padding: 40px;
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
        max-width: 460px;
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
        font-size: 22px;
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
        margin-bottom: 24px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 14px;
        outline: none;
        transition: border-color .15s;
        box-sizing: border-box;
        font-family: inherit;
        min-height: 44px;
    }

    .form-control:focus {
        border-color: var(--accent);
    }

    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 40px !important;
        cursor: pointer;
    }

    select.form-control option {
        background-color: var(--surface);
        color: white;
        padding: 8px;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 8px;
    }

    .modal-actions .btn {
        flex: 1;
        justify-content: center;
        padding: 12px;
        min-height: 48px;
    }

    .modal-actions .btn-secondary {
        flex: 0 1 auto;
        padding: 12px 20px;
    }

    /* ── RESPONSIVE BREAKPOINTS ── */

    @media (max-width: 1024px) {
        .users-container {
            padding: 0 12px;
        }

        .page-header h1 {
            font-size: 26px;
        }
    }

    @media (max-width: 768px) {
        .users-container {
            padding: 0 12px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .page-header p {
            font-size: 13px;
        }

        .tabs-wrapper {
            gap: 3px;
            padding: 4px;
            border-radius: 10px;
        }

        .tab-btn {
            padding: 6px 12px;
            font-size: 12px;
            min-height: 36px;
        }

        .tab-btn .count {
            font-size: 10px;
            padding: 1px 6px;
        }

        .section-title {
            font-size: 15px;
        }

        table {
            min-width: 500px;
        }

        table th,
        table td {
            padding: 10px 14px;
            font-size: 12px;
        }

        table th {
            font-size: 9px;
            letter-spacing: 1px;
        }

        .user-avatar,
        .user-avatar-placeholder {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }

        .user-name {
            font-size: 13px;
        }

        .user-email {
            font-size: 11px;
        }

        .btn {
            font-size: 12px;
            padding: 5px 12px;
            min-height: 32px;
        }

        .btn-sm {
            font-size: 11px;
            padding: 4px 10px;
            min-height: 28px;
        }

        .empty-state {
            padding: 32px 16px;
            font-size: 13px;
        }

        .modal {
            padding: 24px 20px;
            max-width: 95vw;
        }

        .modal-title {
            font-size: 16px;
        }

        .form-control {
            font-size: 13px;
            padding: 8px 12px;
            min-height: 40px;
        }

        .modal-actions .btn {
            min-height: 44px;
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .users-container {
            padding: 0 8px;
        }

        .page-header h1 {
            font-size: 20px;
        }

        .page-header p {
            font-size: 12px;
        }

        .tabs-wrapper {
            gap: 2px;
            padding: 3px;
            border-radius: 8px;
        }

        .tab-btn {
            padding: 5px 10px;
            font-size: 11px;
            min-height: 32px;
            gap: 4px;
        }

        .tab-btn .count {
            font-size: 9px;
            padding: 1px 5px;
        }

        .section-title {
            font-size: 14px;
        }

        table {
            min-width: 420px;
        }

        table th,
        table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        table th {
            font-size: 8px;
            letter-spacing: 0.5px;
        }

        .col-index {
            width: 30px;
        }

        .user-avatar,
        .user-avatar-placeholder {
            width: 24px;
            height: 24px;
            font-size: 9px;
        }

        .user-name {
            font-size: 12px;
        }

        .user-email {
            font-size: 10px;
        }

        .you-badge {
            font-size: 10px;
        }

        .action-group {
            gap: 4px;
        }

        .btn {
            font-size: 11px;
            padding: 4px 10px;
            min-height: 28px;
            border-radius: 6px;
        }

        .btn-sm {
            font-size: 10px;
            padding: 3px 8px;
            min-height: 24px;
        }

        .btn-danger-soft {
            font-size: 12px;
            padding: 4px 8px;
            min-height: 24px;
        }

        .empty-state {
            padding: 24px 12px;
            font-size: 12px;
        }

        .modal {
            padding: 20px 16px;
            border-radius: 12px;
        }

        .modal-title {
            font-size: 15px;
        }

        .modal-sub {
            font-size: 12px;
        }

        .form-label {
            font-size: 10px;
        }

        .form-control {
            font-size: 13px;
            padding: 8px 10px;
            min-height: 38px;
        }

        select.form-control {
            padding-right: 34px !important;
            background-size: 14px;
            background-position: right 10px center;
        }

        .modal-actions {
            flex-direction: column;
        }

        .modal-actions .btn {
            min-height: 40px;
            font-size: 13px;
            width: 100%;
        }

        .modal-actions .btn-secondary {
            flex: 1;
            padding: 10px;
        }

        .alert {
            font-size: 12px;
            padding: 10px 12px;
        }
    }

    @media (max-width: 360px) {
        .tabs-wrapper {
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .tab-btn {
            font-size: 10px;
            padding: 4px 8px;
            min-height: 28px;
        }

        table {
            min-width: 360px;
        }

        table th,
        table td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .user-avatar,
        .user-avatar-placeholder {
            width: 20px;
            height: 20px;
            font-size: 8px;
        }

        .user-name {
            font-size: 11px;
        }

        .btn {
            font-size: 10px;
            padding: 3px 8px;
            min-height: 24px;
        }

        .btn-sm {
            font-size: 9px;
            padding: 2px 6px;
            min-height: 20px;
        }

        .modal {
            padding: 16px 12px;
        }

        .form-control {
            font-size: 12px;
            padding: 6px 8px;
            min-height: 34px;
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

<div class="users-container">

    {{-- Page Header --}}
    <div class="page-header">
        <h1>Manage Users</h1>
        <p>Manage all system accounts by role.</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">✕ {{ session('error') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="tabs-wrapper">
        @foreach([
            ['id'=>'instructors','label'=>'Instructors','count'=>$instructors->count(),'color'=>'var(--accent2)'],
            ['id'=>'staff',      'label'=>'Staff',       'count'=>$staff->count(),       'color'=>'var(--warning)'],
            ['id'=>'members',    'label'=>'Members',     'count'=>$members->count(),     'color'=>'var(--success)'],
            ['id'=>'admins',     'label'=>'Admins',      'count'=>$admins->count(),      'color'=>'var(--accent)'],
        ] as $tab)
        <button onclick="switchTab('{{ $tab['id'] }}')" id="tab-{{ $tab['id'] }}"
                class="tab-btn">
            {{ $tab['label'] }}
            <span class="count">{{ $tab['count'] }}</span>
        </button>
        @endforeach
    </div>

    {{-- Instructors Tab --}}
    <div id="tab-content-instructors" class="tab-content">
        <div class="section-header">
            <div class="section-title">
                <span class="dot" style="background:var(--accent2);"></span>
                Instructors
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="col-index">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instructors as $i => $user)
                        <tr>
                            <td class="col-index">{{ $i + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    @if($user->photo)
                                        <img src="{{ asset('storage/'.$user->photo) }}" class="user-avatar" alt="">
                                    @else
                                        <div class="user-avatar-placeholder" style="background:rgba(255,107,53,0.12);border:1px solid rgba(255,107,53,0.25);color:var(--accent2);">
                                            {{ strtoupper(substr($user->name,0,2)) }}
                                        </div>
                                    @endif
                                    <span class="user-name">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="user-email">{{ $user->email }}</td>
                            <td class="user-email">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="col-actions">
                                <div class="action-group">
                                    <form method="POST" action="{{ route('users.promote', $user) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-secondary btn-sm">↑ Make Admin</button>
                                    </form>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">No instructors found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Staff Tab --}}
    <div id="tab-content-staff" class="tab-content" style="display:none;">
        <div class="section-header">
            <div class="section-title">
                <span class="dot" style="background:var(--warning);"></span>
                Staff
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="col-index">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $i => $user)
                        <tr>
                            <td class="col-index">{{ $i + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    @if($user->photo)
                                        <img src="{{ asset('storage/'.$user->photo) }}" class="user-avatar" alt="">
                                    @else
                                        <div class="user-avatar-placeholder" style="background:rgba(251,191,36,0.12);border:1px solid rgba(251,191,36,0.25);color:var(--warning);">
                                            {{ strtoupper(substr($user->name,0,2)) }}
                                        </div>
                                    @endif
                                    <span class="user-name">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="user-email">{{ $user->email }}</td>
                            <td class="user-email">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="col-actions">
                                <div class="action-group">
                                    <form method="POST" action="{{ route('users.promote', $user) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-secondary btn-sm">↑ Make Admin</button>
                                    </form>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">No staff found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Members Tab --}}
    <div id="tab-content-members" class="tab-content" style="display:none;">
        <div class="section-header">
            <div class="section-title">
                <span class="dot" style="background:var(--success);"></span>
                Members
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="col-index">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $i => $user)
                        <tr>
                            <td class="col-index">{{ $i + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    @if($user->photo)
                                        <img src="{{ asset('storage/'.$user->photo) }}" class="user-avatar" alt="">
                                    @else
                                        <div class="user-avatar-placeholder" style="background:rgba(74,222,128,0.12);border:1px solid rgba(74,222,128,0.25);color:var(--success);">
                                            {{ strtoupper(substr($user->name,0,2)) }}
                                        </div>
                                    @endif
                                    <span class="user-name">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="user-email">{{ $user->email }}</td>
                            <td class="user-email">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="col-actions">
                                <div class="action-group">
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">No members found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Admins Tab --}}
    <div id="tab-content-admins" class="tab-content" style="display:none;">
        <div class="section-header">
            <div class="section-title">
                <span class="dot" style="background:var(--accent);"></span>
                Admins
            </div>
        </div>
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="col-index">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th class="col-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $i => $user)
                        <tr>
                            <td class="col-index">{{ $i + 1 }}</td>
                            <td>
                                <div class="user-cell">
                                    @if($user->photo)
                                        <img src="{{ asset('storage/'.$user->photo) }}" class="user-avatar" alt="">
                                    @else
                                        <div class="user-avatar-placeholder" style="background:rgba(200,255,0,0.1);border:1px solid rgba(200,255,0,0.2);color:var(--accent);">
                                            {{ strtoupper(substr($user->name,0,2)) }}
                                        </div>
                                    @endif
                                    <span class="user-name">{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="you-badge">(you)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="user-email">{{ $user->email }}</td>
                            <td class="user-email">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="col-actions">
                                @if($user->id === auth()->id())
                                    <span class="you-badge">Current user</span>
                                @else
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete {{ $user->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="empty-state">No admins found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add User Modal --}}
    <div class="modal-overlay" id="add-user-modal">
        <div class="modal">
            <button onclick="document.getElementById('add-user-modal').style.display='none'" class="modal-close">✕</button>
            <div class="modal-title">Add New User</div>
            <div class="modal-sub">Create a new system account.</div>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Juan Dela Cruz" required/>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="email@gym.com" required/>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required/>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="staff">Staff</option>
                        <option value="instructor">Instructor</option>
                        <option value="member">Member</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Add User</button>
                    <button type="button"
                            onclick="document.getElementById('add-user-modal').style.display='none'"
                            class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function switchTab(id) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-content-' + id).style.display = 'block';
        document.getElementById('tab-' + id).classList.add('active');
    }

    // Default active tab
    switchTab('instructors');

    // Close modal on backdrop click
    document.getElementById('add-user-modal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('add-user-modal').style.display = 'none';
        }
    });
</script>

@endsection