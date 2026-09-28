{{-- Staff users get the staff layout (charcoal & gold nav + tokens); everyone else keeps the admin layout. --}}
@extends(auth()->user()?->role === 'staff' ? 'layouts.staff' : 'layouts.admin')
@section('title', 'Attendance Log – APEX')
@section('page_title', 'Attendance Log')
@section('active', 'attendance')

@section('content')

<style>
  /* ===== CHARCOAL & GOLD THEME =====
     Uses the same variables as the staff dashboard
     (--accent, --accent-2, --accent-dark, --accent-soft, --surface, --surface2,
      --surface3, --border, --text, --text-soft, --muted, --success, --warning,
      --danger, --info) defined in layouts/staff.blade.php.
     No hardcoded text/background colours, so dark and light mode both work. */

  @if(auth()->user()?->role !== 'staff')
  /* Admin layout fallback: force the gold palette for this page's content (dark mode only) */
  html:not([data-theme="light"]) .attendance-container {
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

  .attendance-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 16px;
  }

  /* ===== HEADER ===== */
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
    color: var(--text);
  }

  .attendance-header-left h1 span { color: var(--accent); }

  .attendance-header-left p {
    color: var(--muted);
    font-size: 14px;
  }

  .attendance-header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  /* ===== BUTTONS (scoped so they don't collide with the layout) ===== */
  .attendance-container .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 9px 18px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .attendance-container .btn-sm {
    padding: 6px 14px;
    font-size: 12px;
    border-radius: 8px;
    min-height: 34px;
  }

  /* Gold gradient — same as the dashboard's "View Full Profile" */
  .attendance-container .btn-primary {
    background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
    color: #1a1a1a;
    font-weight: 800;
  }

  .attendance-container .btn-primary:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(224,169,59,0.25);
  }

  /* Near-black button → fixed light text so it stays visible in light mode */
  .attendance-container .btn-secondary {
    background: #0e0e10;
    color: #ffffff;
    border-color: var(--border);
    font-weight: 600;
  }

  .attendance-container .btn-secondary:hover {
    color: var(--accent-2);
    border-color: rgba(224,169,59,0.45);
  }

  html:root[data-theme="light"] .attendance-container .btn-secondary {
    background: #111111;
    color: #ffffff;
    border-color: #111111;
  }

  html:root[data-theme="light"] .attendance-container .btn-secondary:hover {
    color: var(--accent-2);
    border-color: var(--accent-2);
  }

  .attendance-container .btn-danger {
    background: color-mix(in srgb, var(--danger) 10%, transparent);
    color: var(--danger);
    border-color: color-mix(in srgb, var(--danger) 25%, transparent);
    font-weight: 600;
  }

  .attendance-container .btn-danger:hover {
    background: color-mix(in srgb, var(--danger) 20%, transparent);
  }

  /* ===== STAT CARDS (same glow language as the dashboard) ===== */
  .att-stats {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
    margin-bottom: 24px;
  }

  .att-stat {
    --glow: 224,169,59;
    background: linear-gradient(145deg, rgba(var(--glow),0.10), rgba(26,27,31,0.95) 70%);
    border: 1px solid rgba(var(--glow),0.45);
    border-radius: 14px;
    padding: 18px;
    text-align: center;
    min-width: 0;
    box-shadow: 0 0 22px rgba(var(--glow),0.16), inset 0 0 18px rgba(var(--glow),0.04);
  }

  .att-stat.green { --glow: 74,222,128; }
  .att-stat.blue  { --glow: 96,165,250; }
  .att-stat.amber { --glow: 245,158,11; }
  .att-stat.gold  { --glow: 224,169,59; }

  html:root[data-theme="light"] .att-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-card, none);
  }

  .att-stat-label {
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 8px;
  }

  .att-stat-value {
    font-size: 28px;
    font-weight: 800;
    line-height: 1.1;
  }

  /* ===== FILTERS ===== */
  .filters-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-card, none);
  }

  .filters-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: flex-end;
  }

  .filter-group { flex: 0 0 auto; }

  .filter-group label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 8px;
  }

  .filter-group .form-control {
    width: 100%;
    min-width: 120px;
  }

  .filter-group .form-control[type="text"] { min-width: 140px; }

  .filter-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
  }

  /* Custom dropdown arrow (gold) */
  .form-select-custom {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23e0a93b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 14px !important;
    padding-right: 36px !important;
    cursor: pointer;
  }

  .form-select-custom:hover { border-color: var(--accent) !important; }

  /* Hide native browser calendar & clock icons */
  input[type="date"]::-webkit-calendar-picker-indicator,
  input[type="time"]::-webkit-calendar-picker-indicator { opacity:0; width:0; padding:0; margin:0; }
  input[type="date"],
  input[type="time"] { color-scheme: dark; }
  html:root[data-theme="light"] input[type="date"],
  html:root[data-theme="light"] input[type="time"] { color-scheme: light !important; }

  .date-picker-wrap,
  .time-picker-wrap { position: relative; display: flex; }
  .date-picker-wrap { width: 160px; }
  .date-picker-wrap input[type="date"],
  .time-picker-wrap input[type="time"] { flex: 1; padding-right: 44px; }

  .date-picker-btn,
  .time-picker-btn {
    position: absolute;
    right: 0; top: 0; bottom: 0;
    width: 40px;
    background: var(--accent-soft);
    border: none;
    border-left: 1px solid var(--border);
    border-radius: 0 8px 8px 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
  }

  .date-picker-btn:hover,
  .time-picker-btn:hover { background: rgba(224,169,59,0.25); }

  /* ===== ROLE SUMMARY PILLS ===== */
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
    color: var(--text);
  }

  .role-pill-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
  }

  /* ===== TABLE ===== */
  .table-wrapper {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    box-shadow: var(--shadow-card, none);
  }

  .attendance-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
  }

  .attendance-table th {
    padding: 14px 18px;
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
    color: var(--text-soft);
  }

  .attendance-table tr:first-child td { border-top: none; }

  .user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .att-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid rgba(224,169,59,0.3);
  }

  .att-avatar-ph {
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
    color: var(--text);
  }

  .user-sub {
    font-size: 11px;
    color: var(--muted);
  }

  .role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 40px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    border: 1px solid transparent;
  }

  .role-badge-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    display: inline-block;
  }

  .status-badge,
  .method-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
    border: 1px solid transparent;
  }

  .status-inside {
    background: color-mix(in srgb, var(--success) 15%, transparent);
    color: var(--success);
    border-color: color-mix(in srgb, var(--success) 25%, transparent);
  }

  .status-done {
    background: var(--surface2);
    color: var(--muted);
    border-color: var(--border);
  }

  .method-manual {
    background: color-mix(in srgb, var(--warning) 15%, transparent);
    color: var(--warning);
    border-color: color-mix(in srgb, var(--warning) 25%, transparent);
  }

  .method-qr {
    background: var(--surface2);
    color: var(--muted);
    border-color: var(--border);
  }

  .action-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  /* ===== MODAL ===== */
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

  .modal-overlay.active { display: flex; }

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
    color: var(--text);
    background-image: linear-gradient(135deg, rgba(224,169,59,0.08), transparent 120px);
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
    transition: color 0.2s;
  }

  .modal-close:hover { color: var(--accent); }

  .modal-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
    color: var(--accent);
  }

  .modal-subtitle {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 24px;
  }

  /* Pagination */
  .pagination-wrapper { margin-top: 16px; }
  .pagination-wrapper .pagination { flex-wrap: wrap; }

  /* ===== RESPONSIVE BREAKPOINTS ===== */

  @media (max-width: 1024px) {
    .att-stats { grid-template-columns: repeat(3, 1fr); }
  }

  @media (max-width: 768px) {
    .attendance-container { padding: 0 12px; }

    .attendance-header { flex-direction: column; align-items: flex-start; }
    .attendance-header-left h1 { font-size: 24px; }
    .attendance-header-left p { font-size: 13px; }
    .attendance-header-actions { width: 100%; }
    .attendance-header-actions .btn { flex: 1; min-width: 80px; }

    .att-stats { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .att-stat { padding: 12px; }
    .att-stat-value { font-size: 22px; }
    .att-stat-label { font-size: 9px; }

    .filters-container { padding: 16px; border-radius: 12px; }
    .filters-form { flex-direction: column; align-items: stretch; gap: 10px; }
    .filter-group { width: 100%; }
    .filter-group .form-control { width: 100%; min-width: unset; }
    .date-picker-wrap { width: 100%; }
    .filter-actions { width: 100%; }
    .filter-actions .btn { flex: 1; }

    .role-pills { gap: 6px; }
    .role-pill { padding: 6px 12px; font-size: 12px; }

    .table-wrapper { border-radius: 12px; }
    .attendance-table th,
    .attendance-table td { padding: 10px 12px; font-size: 12px; }

    .att-avatar,
    .att-avatar-ph { width: 28px; height: 28px; font-size: 10px; }
    .user-name { font-size: 12px; }
    .user-sub { font-size: 10px; }

    .modal-content { padding: 24px 16px; margin: 12px; }
    .modal-content form .form-control { width: 100%; }
    .time-picker-wrap { width: 100%; }
    .attendance-container .btn-sm { padding: 5px 10px; font-size: 11px; min-height: 30px; }
  }

  @media (max-width: 480px) {
    .attendance-container { padding: 0 8px; }
    .attendance-header-left h1 { font-size: 20px; }

    .att-stats { grid-template-columns: repeat(2, 1fr); gap: 6px; }
    .att-stat { padding: 10px 8px; border-radius: 10px; }
    .att-stat-value { font-size: 18px; }
    .att-stat-label { font-size: 8px; letter-spacing: 1px; margin-bottom: 4px; }

    .filters-container { padding: 12px; border-radius: 10px; }

    .attendance-table { min-width: 750px; }
    .attendance-table th,
    .attendance-table td { padding: 8px 10px; font-size: 11px; }

    .action-buttons { flex-direction: column; gap: 4px; }
    .action-buttons .btn-sm { width: 100%; }

    .role-badge { font-size: 9px; padding: 3px 8px; }
    .status-badge,
    .method-badge { font-size: 10px; padding: 2px 6px; }

    .modal-content { padding: 20px 12px; }
    .modal-title { font-size: 16px; }
    .modal-subtitle { font-size: 12px; }
  }

  @media (max-width: 360px) {
    .att-stats { gap: 4px; }
    .att-stat { padding: 8px 4px; }
    .att-stat-value { font-size: 16px; }

    .attendance-table { min-width: 650px; }
    .attendance-table th,
    .attendance-table td { padding: 6px 8px; font-size: 10px; }

    .att-avatar,
    .att-avatar-ph { width: 24px; height: 24px; }
    .user-name { font-size: 11px; }
  }
</style>

<div class="attendance-container">
  {{-- Header --}}
  <div class="attendance-header">
    <div class="attendance-header-left">
      <h1>Attendance <span>Log</span></h1>
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
  <div class="att-stats">
    @foreach([
      ['Total Visits',  $stats->total_visits ?? 0,       'var(--accent)',   'gold'],
      ['Inside Now',    $stats->inside_now ?? 0,          'var(--success)',  'green'],
      ['Completed',     $stats->completed ?? 0,           'var(--info)',     'blue'],
      ['Avg Duration',  ($stats->avg_duration ?? 0).'m',  'var(--warning)',  'amber'],
      ['QR Scans',      $stats->qr_count ?? 0,            'var(--accent)',   'gold'],
      ['Manual',        $stats->manual_count ?? 0,        'var(--accent-2)', 'gold'],
    ] as [$label, $val, $color, $glow])
    <div class="att-stat {{ $glow }}">
      <div class="att-stat-label">{{ $label }}</div>
      <div class="att-stat-value" style="color:{{ $color }};">{{ $val }}</div>
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
      <span class="role-pill-dot" style="background:var(--success);"></span>
      <span style="font-size:13px;font-weight:600;">Members:</span>
      <span style="font-size:13px;color:var(--accent);font-weight:700;">{{ $memberCount }}</span>
    </div>
    <div class="role-pill">
      <span class="role-pill-dot" style="background:var(--warning);"></span>
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
          $rowRoleColor = 'var(--success)';
          $displayName  = $log->member?->name;
          $displayPhoto = $log->member?->user?->photo ?? $log->member?->photo ?? null;
          $subText      = $log->member?->email ?? $log->member?->membership_type ?? '—';

          if ($isStaff && $log->user) {
            $rowRole      = ucfirst($log->user->role);
            $displayName  = $log->user->name;
            $displayPhoto = $log->user->photo;
            $subText      = $log->user->email ?? 'Staff User';

            // Theme variables so the colours adapt to dark / light mode
            $roleColorMap = [
              'admin'      => 'var(--accent)',
              'staff'      => 'var(--warning)',
              'instructor' => 'var(--info)',
            ];
            $rowRoleColor = $roleColorMap[strtolower($log->user->role)] ?? 'var(--info)';
          }

          $rowRoleBg     = 'color-mix(in srgb, '.$rowRoleColor.' 15%, transparent)';
          $rowRoleBorder = 'color-mix(in srgb, '.$rowRoleColor.' 35%, transparent)';

          $displayName = $displayName ?? 'Unknown';
        @endphp
        <tr>
          <td>
            <div class="user-cell">
              @if($displayPhoto)
                <img src="{{ asset('storage/'.$displayPhoto) }}" class="att-avatar" alt=""/>
              @else
                <div class="att-avatar-ph" style="background:{{ $rowRoleBg }};border:1px solid {{ $rowRoleBorder }};color:{{ $rowRoleColor }};">
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
            <span class="role-badge" style="background:{{ $rowRoleBg }};color:{{ $rowRoleColor }};border-color:{{ $rowRoleBorder }};">
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
          <button type="submit" class="btn btn-primary" style="flex:1;">
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