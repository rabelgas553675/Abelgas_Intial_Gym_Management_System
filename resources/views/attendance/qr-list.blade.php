@extends('layouts.admin')
@section('title', 'QR Codes – IRONFORGE')
@section('active', 'attendance')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
  <div>
    <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">QR Codes</h1>
    <p style="color:var(--muted);font-size:14px;">View and print QR codes for members and staff</p>
  </div>
  <div style="display:flex;gap:10px;">
    <button onclick="window.print()"
            class="btn btn-primary btn-sm">🖨 Print All</button>
    @if(auth()->user()->isAdmin())
      <a href="{{ route('attendance.generate-tokens') }}" class="btn btn-secondary btn-sm">
        Generate Tokens
      </a>
    @endif
    <a href="{{ route('attendance.scan') }}" class="btn btn-secondary btn-sm">Scanner</a>
  </div>
</div>

{{-- Filters --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;
            padding:20px;margin-bottom:24px;" class="no-print">
  <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
    <div>
      <label class="form-label">Group</label>
      <select name="group" class="form-control" style="width:200px;" onchange="this.form.submit()">
        <option value="members" {{ $group==='members'?'selected':'' }}>Members</option>
        <option value="staff"   {{ $group==='staff'  ?'selected':'' }}>Admin / Staff / Instructors</option>
      </select>
    </div>
    <div>
      <label class="form-label">Search</label>
      <input type="text" name="q" value="{{ $search }}" class="form-control"
             style="width:200px;" placeholder="Search name..."/>
    </div>
    <input type="hidden" name="group" value="{{ $group }}"/>
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="{{ route('attendance.qr-list', ['group' => $group]) }}" class="btn btn-secondary">Reset</a>
  </form>
</div>

{{-- QR Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;">

  @if($group === 'members')
    @forelse($members as $member)
      @php
        $payload = urlencode("IRONFORGE|MBR|{$member->id}|{$member->qr_token}");
        $qrUrl   = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={$payload}";
      @endphp
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;
                  padding:20px 16px;text-align:center;page-break-inside:avoid;">
        <img src="{{ $qrUrl }}" alt="QR" style="width:130px;height:130px;"/>
        <div style="font-weight:700;font-size:14px;color:#111;margin-top:10px;margin-bottom:2px;">
          {{ $member->name }}
        </div>
        <div style="font-size:12px;color:#6b7280;">{{ $member->email }}</div>
        <span style="display:inline-block;padding:2px 10px;border-radius:100px;font-size:10px;
                     font-weight:700;text-transform:uppercase;margin-top:6px;
                     {{ $member->status === 'Active' ? 'background:rgba(74,222,128,0.15);color:#4ade80;' : 'background:rgba(248,113,113,0.15);color:#f87171;' }}">
          {{ $member->status }}
        </span>
        <div style="font-family:monospace;font-size:9px;color:#9ca3af;word-break:break-all;margin-top:6px;">
          {{ $member->qr_token }}
        </div>
      </div>
    @empty
      <div style="grid-column:1/-1;padding:48px;text-align:center;color:var(--muted);">
        No members with QR tokens yet.
        @if(auth()->user()->isAdmin())
          <a href="{{ route('attendance.generate-tokens') }}" style="color:var(--accent);">Generate tokens →</a>
        @endif
      </div>
    @endforelse

  @else
    @forelse($staffList as $staff)
      @php
        $rolePrefix = strtoupper($staff->role[0]);
        $payload    = urlencode("IRONFORGE|{$rolePrefix}SR|{$staff->user_id}|{$staff->qr_token}");
        $qrUrl      = "https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={$payload}";
        $roleColors = ['admin'=>'#c8ff00','staff'=>'#fbbf24','instructor'=>'#ff6b35'];
        $rc         = $roleColors[$staff->role] ?? '#60a5fa';
      @endphp
      <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;
                  padding:20px 16px;text-align:center;page-break-inside:avoid;">
        <img src="{{ $qrUrl }}" alt="QR" style="width:130px;height:130px;"/>
        <div style="font-weight:700;font-size:14px;color:#111;margin-top:10px;margin-bottom:2px;">
          {{ $staff->name }}
        </div>
        <span style="display:inline-block;padding:2px 10px;border-radius:100px;font-size:10px;
                     font-weight:700;text-transform:uppercase;margin-top:6px;
                     background:{{ $rc }}22;color:{{ $rc }};">
          {{ ucfirst($staff->role) }}
        </span>
        <div style="font-family:monospace;font-size:9px;color:#9ca3af;word-break:break-all;margin-top:6px;">
          {{ $staff->qr_token }}
        </div>
      </div>
    @empty
      <div style="grid-column:1/-1;padding:48px;text-align:center;color:var(--muted);">
        No staff/admin QR tokens yet.
        @if(auth()->user()->isAdmin())
          <a href="{{ route('attendance.generate-tokens') }}" style="color:var(--accent);">Generate tokens →</a>
        @endif
      </div>
    @endforelse
  @endif

</div>

<style>
@media print {
  .no-print { display:none !important; }
  nav.navbar, .page-content > div:first-child { display:none !important; }
}
</style>

@endsection 