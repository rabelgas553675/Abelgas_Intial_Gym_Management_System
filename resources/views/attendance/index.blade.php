@extends('layouts.admin')
@section('title', 'Attendance Log – IRONFORGE')
@section('active', 'attendance')

@section('content')

{{-- Custom Dropdown Styling --}}
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
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
  <div>
    <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">Attendance Log</h1>
    <p style="color:var(--muted);font-size:14px;">Full attendance records with filters</p>
  </div>
  <div style="display:flex;gap:10px;">
    <button onclick="document.getElementById('addManualModal').style.display='flex'"
            class="btn btn-primary btn-sm">+ Add Manual</button>
    <a href="{{ route('attendance.scan') }}" class="btn btn-secondary btn-sm">Scanner</a>
    <a href="{{ route('attendance.qr-list') }}" class="btn btn-secondary btn-sm">QR Codes</a>
  </div>
</div>

{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:24px;">
  @foreach([
    ['Total Visits',  $stats->total_visits ?? 0,  'var(--accent)'],
    ['Inside Now',    $stats->inside_now ?? 0,     '#4ade80'],
    ['Completed',     $stats->completed ?? 0,      '#60a5fa'],
    ['Avg Duration',  ($stats->avg_duration ?? 0).'m', '#fbbf24'],
    ['QR Scans',      $stats->qr_count ?? 0,       'var(--accent)'],
    ['Manual',        $stats->manual_count ?? 0,   '#f87171'],
  ] as [$label, $val, $color])
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px;text-align:center;">
    <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;">{{ $label }}</div>
    <div style="font-size:28px;font-weight:800;color:{{ $color }};">{{ $val }}</div>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px; padding:20px;margin-bottom:20px;">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div>
      <label style="display:block;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Date</label>
      <input type="date" name="date" value="{{ $filterDate }}" class="form-control" style="width:160px;"/>
    </div>
    <div>
      <label style="display:block;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Member</label>
      <input type="text" name="member" value="{{ $filterMember }}" class="form-control" style="width:160px;" placeholder="Search name..."/>
    </div>
    <div>
      <label style="display:block;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Role</label>
      <select name="role" class="form-control form-select-custom" style="width:140px;">
        <option value="">All Roles</option>
        <option value="member"     {{ ($filterRole??'')==='member'     ?'selected':'' }}>🏋️ Members</option>
        <option value="staff"      {{ ($filterRole??'')==='staff'      ?'selected':'' }}>👤 Staff</option>
        <option value="instructor" {{ ($filterRole??'')==='instructor' ?'selected':'' }}>💪 Instructors</option>
        <option value="admin"      {{ ($filterRole??'')==='admin'      ?'selected':'' }}>🛡️ Admins</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Status</label>
      <select name="status" class="form-control form-select-custom" style="width:140px;">
        <option value="">All Status</option>
        <option value="inside" {{ $filterStatus==='inside'?'selected':'' }}>Inside Now</option>
        <option value="done"   {{ $filterStatus==='done'  ?'selected':'' }}>Completed</option>
      </select>
    </div>
    <div>
      <label style="display:block;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Method</label>
      <select name="method" class="form-control form-select-custom" style="width:140px;">
        <option value="">All Methods</option>
        <option value="qr_scan" {{ $filterMethod==='qr_scan'?'selected':'' }}>QR Scan</option>
        <option value="manual"  {{ $filterMethod==='manual' ?'selected':'' }}>Manual</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">Reset</a>
  </form>
</div>

{{-- Role Summary Pills --}}
@if(!($filterRole ?? ''))
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
  @php
    $memberCount     = $logs->getCollection()->whereNotNull('member_id')->count();
    $staffCount      = $logs->getCollection()->whereNull('member_id')->count();
  @endphp
  <div style="display:flex;align-items:center;gap:8px;padding:8px 16px; background:var(--surface);border:1px solid var(--border);border-radius:100px;">
    <span style="width:8px;height:8px;border-radius:50%;background:#4ade80;display:inline-block;"></span>
    <span style="font-size:13px;font-weight:600;">Members:</span>
    <span style="font-size:13px;color:var(--accent);font-weight:700;">{{ $memberCount }}</span>
  </div>
  <div style="display:flex;align-items:center;gap:8px;padding:8px 16px; background:var(--surface);border:1px solid var(--border);border-radius:100px;">
    <span style="width:8px;height:8px;border-radius:50%;background:#fbbf24;display:inline-block;"></span>
    <span style="font-size:13px;font-weight:600;">Staff / Instructors:</span>
    <span style="font-size:13px;color:var(--accent);font-weight:700;">{{ $staffCount }}</span>
  </div>
</div>
@endif

{{-- Table --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:var(--surface2);">
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Member / User</th>
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Role</th>
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Time In</th>
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Time Out</th>
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Duration</th>
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Method</th>
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Status</th>
        <th style="padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $log)
      @php
        // LOGIC FIX: Check direct relationships loaded from controller
        $isStaff = !$log->member_id && $log->staff_user_id;
        
        // Defaults (Member)
        $rowRole = 'Member';
        $rowRoleColor = '#4ade80';
        $rowRoleBg    = 'rgba(74,222,128,0.15)';
        $displayName  = $log->member?->name;
        $displayPhoto = $log->member?->photo;
        $subText      = $log->member?->email ?? $log->member?->membership_type ?? '—';

        // Override for Staff
        if ($isStaff && $log->user) {
          $rowRole     = ucfirst($log->user->role);
          $displayName = $log->user->name;
          $displayPhoto = $log->user->photo;
          $subText     = $log->user->email ?? 'Staff User';

          $roleColorMap = [
            'admin'      => ['#c8ff00', 'rgba(200,255,0,0.12)'],
            'staff'      => ['#fbbf24', 'rgba(251,191,36,0.12)'],
            'instructor' => ['#ff6b35', 'rgba(255,107,53,0.12)'],
          ];
          [$rowRoleColor, $rowRoleBg] = $roleColorMap[strtolower($log->user->role)] ?? ['#60a5fa','rgba(96,165,250,0.12)'];
        }
        
        $displayName = $displayName ?? 'Unknown';
      @endphp
      <tr style="border-top:1px solid var(--border);">
        <td style="padding:13px 18px;">
          <div style="display:flex;align-items:center;gap:10px;">
            @if($displayPhoto)
              <img src="{{ asset('storage/'.$displayPhoto) }}"
                  style="width:34px;height:34px;border-radius:50%;object-fit:cover;"/>
            @else
              <div style="width:34px;height:34px;border-radius:50%;
                          background:{{ $rowRoleBg }};border:1px solid {{ $rowRoleColor }}44;
                          display:flex;align-items:center;justify-content:center;
                          font-size:12px;font-weight:700;color:{{ $rowRoleColor }};">
                {{ strtoupper(substr($displayName,0,2)) }}
              </div>
            @endif
            <div>
              <div style="font-size:13px;font-weight:600;color:#fff;">{{ $displayName }}</div>
              <div style="font-size:11px;color:var(--muted);">{{ $subText }}</div>
            </div>
          </div>
        </td>

        <td style="padding:13px 18px;">
          <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                      border-radius:6px;font-size:11px;font-weight:700;
                      background:{{ $rowRoleBg }};color:{{ $rowRoleColor }};">
            <span style="width:5px;height:5px;border-radius:50%;
                        background:{{ $rowRoleColor }};display:inline-block;"></span>
            {{ $rowRole }}
          </span>
        </td>

        <td style="padding:13px 18px;font-size:13px;">{{ $log->time_in?->format('h:i A') ?? '—' }}</td>
        <td style="padding:13px 18px;font-size:13px;">
          @if($log->time_out)
            {{ $log->time_out->format('h:i A') }}
          @else
            <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;
                        font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span>
          @endif
        </td>
        <td style="padding:13px 18px;font-size:13px;color:var(--muted);">{{ $log->duration_formatted }}</td>
        <td style="padding:13px 18px;">
          <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;
                      {{ $log->entry_method === 'manual'
                          ? 'background:rgba(251,191,36,0.15);color:#fbbf24;'
                          : 'background:var(--surface2);color:var(--muted);' }}">
            {{ $log->entry_method === 'manual' ? 'Manual' : 'QR Scan' }}
          </span>
        </td>
        <td style="padding:13px 18px;">
          @if($log->time_out)
            <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;
                        font-weight:700;background:var(--surface2);color:var(--muted);">Done</span>
          @else
            <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;
                        font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span>
          @endif
        </td>
        <td style="padding:13px 18px;">
          <div style="display:flex;gap:6px;">
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
              <button type="submit" class="btn btn-sm"
                      style="background:rgba(248,113,113,0.1);color:#f87171;
                            border:1px solid rgba(248,113,113,0.2);">Delete</button>
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
<div style="margin-top:16px;">{{ $logs->links() }}</div>

{{-- Add Manual Modal --}}
<div id="addManualModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);
            z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              width:100%;max-width:460px;padding:32px;position:relative;">
    <button onclick="document.getElementById('addManualModal').style.display='none'"
            style="position:absolute;top:16px;right:16px;background:none;border:none;
                  color:var(--muted);font-size:20px;cursor:pointer;">✕</button>
    <div style="font-size:18px;font-weight:700;margin-bottom:4px;">Add Manual Entry</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:24px;">Record attendance manually</div>
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
          <input type="time" name="manual_time_in" class="form-control"
                value="{{ now()->format('H:i') }}" required/>
        </div>
        <div>
          <label class="form-label">Time Out (optional)</label>
          <input type="time" name="manual_time_out" class="form-control"/>
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

<script>
document.getElementById('addManualModal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>

@endsection 