@extends('layouts.admin')
@section('title', 'Attendance Log – APEX')
@section('active', 'attendance')

@section('content')

{{-- Custom Dropdown & Date Styling --}}
<style>
  .form-select-custom {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='rgba(255,255,255,0.5)'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 14px !important;
    padding-right: 36px !important;
    cursor: pointer;
  }
  .form-select-custom:hover { border-color: var(--accent) !important; }

  /* Hide native browser calendar & clock icons */
  input[type="date"]::-webkit-calendar-picker-indicator,
  input[type="time"]::-webkit-calendar-picker-indicator { opacity:0;width:0;padding:0;margin:0; }
  input[type="date"],
  input[type="time"] { color-scheme: dark; }

  .date-picker-wrap,
  .time-picker-wrap { position:relative;display:flex; }
  .date-picker-wrap { width:160px; }
  .date-picker-wrap input[type="date"],
  .time-picker-wrap input[type="time"] { flex:1;padding-right:44px; }
  .date-picker-btn,
  .time-picker-btn {
    position:absolute;right:0;top:0;bottom:0;width:40px;
    background:rgba(200,255,0,0.10);
    border:none;border-left:1px solid var(--border);
    border-radius:0 8px 8px 0;
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:background .15s;
  }
  .date-picker-btn:hover,
  .time-picker-btn:hover { background:rgba(200,255,0,0.22); }

  /* ===== RESPONSIVE STYLES ===== */
  .attendance-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 16px;
  }

  .attendance-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .attendance-header-left h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .attendance-header-left p {
    color: var(--muted);
    font-size: 14px;
  }

  .attendance-header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  /* Stat Cards Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 24px;
  }

  .stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    min-width: 0;
  }

  .stat-card-label {
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
  }

  .stat-card-value {
    font-size: 28px;
    font-weight: 800;
  }

  /* Filters */
  .filters-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
  }

  .filters-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: flex-end;
  }

  .filter-group {
    flex: 0 0 auto;
  }

  .filter-group label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
  }

  .filter-group .form-control {
    width: 100%;
    min-width: 120px;
  }

  .filter-group .form-control[type="text"] {
    min-width: 140px;
  }

  .filter-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  /* Role Summary Pills */
  .role-pills {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .role-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 100px;
  }

  .role-pill-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
  }

  /* Table Responsive */
  .table-wrapper {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .attendance-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
  }

  .attendance-table th {
    padding: 12px 18px;
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 2px;
    background: var(--surface2);
    white-space: nowrap;
  }

  .attendance-table td {
    padding: 13px 18px;
    border-top: 1px solid var(--border);
    vertical-align: middle;
  }

  .attendance-table tr:first-child td {
    border-top: none;
  }

  .user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
  }

  .user-avatar-placeholder {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
  }

  .user-name {
    font-size: 13px;
    font-weight: 600;
    color: #fff;
  }

  .user-sub {
    font-size: 11px;
    color: var(--muted);
  }

  .role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
  }

  .role-badge-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    display: inline-block;
  }

  .status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
  }

  .status-inside {
    background: rgba(74, 222, 128, 0.15);
    color: #4ade80;
  }

  .status-done {
    background: var(--surface2);
    color: var(--muted);
  }

  .method-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
  }

  .method-manual {
    background: rgba(251, 191, 36, 0.15);
    color: #fbbf24;
  }

  .method-qr {
    background: var(--surface2);
    color: var(--muted);
  }

  .action-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  .btn-sm {
    padding: 6px 12px;
    font-size: 11px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    white-space: nowrap;
    min-height: 32px;
  }

  .btn-primary {
    background: var(--accent);
    color: #111;
  }

  .btn-primary:hover {
    opacity: 0.9;
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

  .btn-danger {
    background: rgba(248, 113, 113, 0.1);
    color: #f87171;
    border: 1px solid rgba(248, 113, 113, 0.2);
  }

  .btn-danger:hover {
    background: rgba(248, 113, 113, 0.2);
  }

  .btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 6px; }

  /* Modal */
  .modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }

  .modal-overlay.active {
    display: flex;
  }

  .modal-content {
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
    font-size: 20px;
    cursor: pointer;
  }

  .modal-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .modal-subtitle {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 24px;
  }

  /* Pagination */
  .pagination-wrapper {
    margin-top: 16px;
  }

  .pagination-wrapper .pagination {
    flex-wrap: wrap;
  }

  /* ===== RESPONSIVE BREAKPOINTS ===== */

  /* Tablets and small laptops */
  @media (max-width: 1024px) {
    .stats-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  /* Mobile */
  @media (max-width: 768px) {
    .attendance-container {
      padding: 0 12px;
    }

    .attendance-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .attendance-header-left h1 {
      font-size: 24px;
    }

    .attendance-header-left p {
      font-size: 13px;
    }

    .attendance-header-actions {
      width: 100%;
    }

    .attendance-header-actions .btn {
      flex: 1;
      justify-content: center;
      min-width: 80px;
    }

    .stats-grid {
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
    }

    .stat-card {
      padding: 12px;
    }

    .stat-card-value {
      font-size: 22px;
    }

    .stat-card-label {
      font-size: 9px;
    }

    .filters-container {
      padding: 16px;
    }

    .filters-form {
      flex-direction: column;
      gap: 10px;
    }

    .filter-group {
      width: 100%;
    }

    .filter-group .form-control {
      width: 100%;
      min-width: unset;
    }

    .date-picker-wrap {
      width: 100%;
    }

    .filter-actions {
      width: 100%;
    }

    .filter-actions .btn {
      flex: 1;
      justify-content: center;
    }

    .role-pills {
      gap: 6px;
    }

    .role-pill {
      padding: 6px 12px;
      font-size: 12px;
    }

    .table-wrapper {
      border-radius: 10px;
    }

    .attendance-table th,
    .attendance-table td {
      padding: 10px 12px;
      font-size: 12px;
    }

    .user-avatar,
    .user-avatar-placeholder {
      width: 28px;
      height: 28px;
      font-size: 10px;
    }

    .user-name {
      font-size: 12px;
    }

    .user-sub {
      font-size: 10px;
    }

    .modal-content {
      padding: 24px 16px;
      margin: 12px;
    }

    .modal-content form .form-control {
      width: 100%;
    }

    .time-picker-wrap {
      width: 100%;
    }

    .btn-sm {
      padding: 5px 10px;
      font-size: 10px;
      min-height: 28px;
    }
  }

  /* Small phones */
  @media (max-width: 480px) {
    .attendance-container {
      padding: 0 8px;
    }

    .attendance-header-left h1 {
      font-size: 20px;
    }

    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 6px;
    }

    .stat-card {
      padding: 10px 8px;
      border-radius: 8px;
    }

    .stat-card-value {
      font-size: 18px;
    }

    .stat-card-label {
      font-size: 8px;
      letter-spacing: 1px;
      margin-bottom: 4px;
    }

    .filters-container {
      padding: 12px;
      border-radius: 8px;
    }

    .attendance-table th,
    .attendance-table td {
      padding: 8px 10px;
      font-size: 11px;
    }

    .attendance-table {
      min-width: 750px;
    }

    .action-buttons {
      flex-direction: column;
      gap: 4px;
    }

    .action-buttons .btn-sm {
      width: 100%;
      justify-content: center;
    }

    .role-badge {
      font-size: 10px;
      padding: 3px 8px;
    }

    .status-badge,
    .method-badge {
      font-size: 10px;
      padding: 2px 6px;
    }

    .modal-content {
      padding: 20px 12px;
    }

    .modal-title {
      font-size: 16px;
    }

    .modal-subtitle {
      font-size: 12px;
    }
  }

  /* Extra small phones */
  @media (max-width: 360px) {
    .stats-grid {
      grid-template-columns: 1fr 1fr;
      gap: 4px;
    }

    .stat-card {
      padding: 8px 4px;
    }

    .stat-card-value {
      font-size: 16px;
    }

    .attendance-table {
      min-width: 650px;
    }

    .attendance-table th,
    .attendance-table td {
      padding: 6px 8px;
      font-size: 10px;
    }

    .user-avatar,
    .user-avatar-placeholder {
      width: 24px;
      height: 24px;
    }

    .user-name {
      font-size: 11px;
    }
  }
</style>

<div class="attendance-container">
  {{-- Header --}}
  <div class="attendance-header">
    <div class="attendance-header-left">
      <h1>Attendance Log</h1>
      <p>Full attendance records with filters</p>
    </div>
    <div class="attendance-header-actions">
      <button onclick="document.getElementById('addManualModal').style.display='flex'"
              class="btn btn-primary btn-sm">+ Add Manual</button>
      <a href="{{ route('attendance.scan') }}" class="btn btn-secondary btn-sm">Scanner</a>
      <a href="{{ route('attendance.qr-list') }}" class="btn btn-secondary btn-sm">QR Codes</a>
    </div>
  </div>

  {{-- Stat Cards --}}
  <div class="stats-grid">
    @foreach([
      ['Total Visits',  $stats->total_visits ?? 0,  'var(--accent)'],
      ['Inside Now',    $stats->inside_now ?? 0,     '#4ade80'],
      ['Completed',     $stats->completed ?? 0,      '#60a5fa'],
      ['Avg Duration',  ($stats->avg_duration ?? 0).'m', '#fbbf24'],
      ['QR Scans',      $stats->qr_count ?? 0,       'var(--accent)'],
      ['Manual',        $stats->manual_count ?? 0,   '#f87171'],
    ] as [$label, $val, $color])
    <div class="stat-card">
      <div class="stat-card-label">{{ $label }}</div>
      <div class="stat-card-value" style="color:{{ $color }};">{{ $val }}</div>
    </div>
    @endforeach
  </div>

  {{-- Filters --}}
  <div class="filters-container">
    <form method="GET" class="filters-form">
      {{-- Date --}}
      <div class="filter-group">
        <label>Date</label>
        <div class="date-picker-wrap">
          <input type="date" name="date" id="filter_date"
                 value="{{ $filterDate }}" class="form-control"/>
          <button type="button" class="date-picker-btn"
                  onclick="document.getElementById('filter_date').showPicker()"
                  title="Open calendar">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                 stroke="var(--accent)" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8"  y1="2" x2="8"  y2="6"/>
              <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
          </button>
        </div>
      </div>

      {{-- Member --}}
      <div class="filter-group" style="flex:1;min-width:120px;">
        <label>Member</label>
        <input type="text" name="member" value="{{ $filterMember }}" class="form-control" placeholder="Search name..."/>
      </div>

      {{-- Role --}}
      <div class="filter-group">
        <label>Role</label>
        <select name="role" class="form-control form-select-custom">
          <option value="">All Roles</option>
          <option value="member"     {{ ($filterRole??'')==='member'     ?'selected':'' }}>🏋️ Members</option>
          <option value="staff"      {{ ($filterRole??'')==='staff'      ?'selected':'' }}>👤 Staff</option>
          <option value="instructor" {{ ($filterRole??'')==='instructor' ?'selected':'' }}>💪 Instructors</option>
          <option value="admin"      {{ ($filterRole??'')==='admin'      ?'selected':'' }}>🛡️ Admins</option>
        </select>
      </div>

      {{-- Status --}}
      <div class="filter-group">
        <label>Status</label>
        <select name="status" class="form-control form-select-custom">
          <option value="">All Status</option>
          <option value="inside" {{ $filterStatus==='inside'?'selected':'' }}>Inside Now</option>
          <option value="done"   {{ $filterStatus==='done'  ?'selected':'' }}>Completed</option>
        </select>
      </div>

      {{-- Method --}}
      <div class="filter-group">
        <label>Method</label>
        <select name="method" class="form-control form-select-custom">
          <option value="">All Methods</option>
          <option value="qr_scan" {{ $filterMethod==='qr_scan'?'selected':'' }}>QR Scan</option>
          <option value="manual"  {{ $filterMethod==='manual' ?'selected':'' }}>Manual</option>
        </select>
      </div>

      {{-- Actions --}}
      <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm">Reset</a>
      </div>
    </form>
  </div>

  {{-- Role Summary Pills --}}
  @if(!($filterRole ?? ''))
  <div class="role-pills">
    @php
      $memberCount = $logs->getCollection()->whereNotNull('member_id')->count();
      $staffCount  = $logs->getCollection()->whereNull('member_id')->count();
    @endphp
    <div class="role-pill">
      <span class="role-pill-dot" style="background:#4ade80;"></span>
      <span style="font-size:13px;font-weight:600;">Members:</span>
      <span style="font-size:13px;color:var(--accent);font-weight:700;">{{ $memberCount }}</span>
    </div>
    <div class="role-pill">
      <span class="role-pill-dot" style="background:#fbbf24;"></span>
      <span style="font-size:13px;font-weight:600;">Staff / Instructors:</span>
      <span style="font-size:13px;color:var(--accent);font-weight:700;">{{ $staffCount }}</span>
    </div>
  </div>
  @endif

  {{-- Table --}}
  <div class="table-wrapper">
    <table class="attendance-table">
      <thead>
        <tr>
          <th>Member / User</th>
          <th>Role</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Duration</th>
          <th>Method</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
        @php
          $isStaff = !$log->member_id && $log->staff_user_id;

          $rowRole      = 'Member';
          $rowRoleColor = '#4ade80';
          $rowRoleBg    = 'rgba(74,222,128,0.15)';
          $displayName  = $log->member?->name;
          $displayPhoto = $log->member?->user?->photo ?? $log->member?->photo ?? null;
          $subText      = $log->member?->email ?? $log->member?->membership_type ?? '—';

          if ($isStaff && $log->user) {
            $rowRole      = ucfirst($log->user->role);
            $displayName  = $log->user->name;
            $displayPhoto = $log->user->photo;
            $subText      = $log->user->email ?? 'Staff User';

            $roleColorMap = [
              'admin'      => ['#c8ff00', 'rgba(200,255,0,0.12)'],
              'staff'      => ['#fbbf24', 'rgba(251,191,36,0.12)'],
              'instructor' => ['#ff6b35', 'rgba(255,107,53,0.12)'],
            ];
            [$rowRoleColor, $rowRoleBg] = $roleColorMap[strtolower($log->user->role)] ?? ['#60a5fa','rgba(96,165,250,0.12)'];
          }

          $displayName = $displayName ?? 'Unknown';
        @endphp
        <tr>
          <td>
            <div class="user-cell">
              @if($displayPhoto)
                <img src="{{ asset('storage/'.$displayPhoto) }}" class="user-avatar" alt=""/>
              @else
                <div class="user-avatar-placeholder" style="background:{{ $rowRoleBg }};border:1px solid {{ $rowRoleColor }}44;color:{{ $rowRoleColor }};">
                  {{ strtoupper(substr($displayName,0,2)) }}
                </div>
              @endif
              <div>
                <div class="user-name">{{ $displayName }}</div>
                <div class="user-sub">{{ $subText }}</div>
              </div>
            </div>
          </td>

          <td>
            <span class="role-badge" style="background:{{ $rowRoleBg }};color:{{ $rowRoleColor }};">
              <span class="role-badge-dot" style="background:{{ $rowRoleColor }};"></span>
              {{ $rowRole }}
            </span>
          </td>

          <td>{{ $log->time_in?->format('h:i A') ?? '—' }}</td>
          <td>
            @if($log->time_out)
              {{ $log->time_out->format('h:i A') }}
            @else
              <span class="status-badge status-inside">Inside</span>
            @endif
          </td>
          <td style="color:var(--muted);">{{ $log->duration_formatted }}</td>
          <td>
            <span class="method-badge {{ $log->entry_method === 'manual' ? 'method-manual' : 'method-qr' }}">
              {{ $log->entry_method === 'manual' ? 'Manual' : 'QR Scan' }}
            </span>
          </td>
          <td>
            @if($log->time_out)
              <span class="status-badge status-done">Done</span>
            @else
              <span class="status-badge status-inside">Inside</span>
            @endif
          </td>
          <td>
            <div class="action-buttons">
              @if(!$log->time_out)
                <form method="POST" action="{{ route('attendance.timeout') }}">
                  @csrf
                  <input type="hidden" name="timeout_id" value="{{ $log->id }}"/>
                  <button type="submit" class="btn btn-secondary btn-sm">Time Out</button>
                </form>
              @endif
              <form method="POST" action="{{ route('attendance.destroy') }}"
                    onsubmit="return confirm('Delete this record?')">
                @csrf
                <input type="hidden" name="delete_id" value="{{ $log->id }}"/>
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="padding:48px;text-align:center;color:var(--muted);">
            No attendance records for this date.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="pagination-wrapper">{{ $logs->links() }}</div>

  {{-- Add Manual Modal --}}
  <div id="addManualModal" class="modal-overlay">
    <div class="modal-content">
      <button onclick="document.getElementById('addManualModal').style.display='none'" class="modal-close">✕</button>
      <div class="modal-title">Add Manual Entry</div>
      <div class="modal-subtitle">Record attendance manually</div>
      <form method="POST" action="{{ route('attendance.add-manual') }}">
        @csrf
        <div style="margin-bottom:16px;">
          <label class="form-label">Member</label>
          <select name="manual_member_id" class="form-control" required>
            <option value="">— Select Member —</option>
            @foreach($allMembers as $m)
              <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->membership_type }})</option>
            @endforeach
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
          <div>
            <label class="form-label">Time In *</label>
            <div class="time-picker-wrap">
              <input type="time" name="manual_time_in" id="manual_time_in" class="form-control"
                     value="{{ now()->format('H:i') }}" required/>
              <button type="button" class="time-picker-btn"
                      onclick="document.getElementById('manual_time_in').showPicker()"
                      title="Pick time">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                     stroke="var(--accent)" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
              </button>
            </div>
          </div>
          <div>
            <label class="form-label">Time Out (optional)</label>
            <div class="time-picker-wrap">
              <input type="time" name="manual_time_out" id="manual_time_out" class="form-control"/>
              <button type="button" class="time-picker-btn"
                      onclick="document.getElementById('manual_time_out').showPicker()"
                      title="Pick time">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"
                     stroke="var(--accent)" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
            Save Entry
          </button>
          <button type="button" class="btn btn-secondary"
                  onclick="document.getElementById('addManualModal').style.display='none'"
                  style="padding:8px 20px;">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('addManualModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
</script>

@endsection