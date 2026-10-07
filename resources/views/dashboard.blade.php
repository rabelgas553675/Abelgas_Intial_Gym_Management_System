@extends('layouts.admin')
@section('title', 'Admin Dashboard – APEX FITNESS GYM')
@section('page_title', 'Dashboard')
@section('active', 'dashboard')

@section('content')

{{-- Page Header --}}
<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    Welcome back, <span style="color:var(--accent);">{{ explode(' ', auth()->user()->name)[0] }}</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">Here's what's happening at APEX FITNESS GYM today.</p>
</div>

{{-- Stat Cards --}}
<div class="stat-grid dash-stat-grid">

  <div class="stat-card green">
    <div class="stat-card-left">
      <div class="stat-label">Total Members</div>
      <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
      <div class="stat-sub stat-up">All time registrations</div>
    </div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
    </div>
  </div>

  <div class="stat-card orange">
    <div class="stat-card-left">
      <div class="stat-label">Active Members</div>
      <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
      <div class="stat-sub">Currently enrolled</div>
    </div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <circle cx="12" cy="12" r="8" stroke="currentColor" fill="none"/>
        <circle cx="12" cy="12" r="3" fill="currentColor" stroke="none"/>
      </svg>
    </div>
  </div>

  <div class="stat-card blue">
    <div class="stat-card-left">
      <div class="stat-label">Monthly Plans</div>
      <div class="stat-value">{{ $stats['monthly'] ?? 0 }}</div>
      <div class="stat-sub">Monthly subscribers</div>
    </div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
    </div>
  </div>

  <div class="stat-card gold">
    <div class="stat-card-left">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:28px;">₱{{ number_format($thisMonth ?? 0, 0) }}</div>
      <div class="stat-sub">Total: <span class="stat-up" style="font-weight:700;">₱{{ number_format($totalCollected ?? 0, 0) }}</span></div>
    </div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>

</div>

{{-- Recent Members --}}
<div class="dash-panel" style="margin-bottom:24px;">
  <div class="dash-panel-header">
    <div class="dash-panel-title">Recent Members</div>
    <a href="{{ route('members.index') }}" class="btn btn-secondary btn-sm">View All →</a>
  </div>

  <div class="table-scroll">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Plan</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentMembers ?? [] as $member)
          @php
            $planKey  = strtolower((string) data_get($member, 'membership_type', 'monthly'));
            $planKey  = str_replace(' ', '-', $planKey);
            $status   = data_get($member, 'status', 'Pending');
            $stKey    = match($status) {
                'Active'        => 'active',
                'Expiring Soon' => 'expiring',
                'Expired'       => 'expired',
                'Suspended'     => 'suspended',
                default         => 'pending',
            };
          @endphp
          <tr>
            <td>
              <div class="member-cell">
                @if(data_get($member, 'photo'))
                  <img src="{{ asset('storage/'.data_get($member, 'photo')) }}" class="row-avatar" alt=""/>
                @else
                  <div class="row-avatar-ph">{{ strtoupper(substr((string) data_get($member, 'name', ''), 0, 2)) }}</div>
                @endif
                <div style="min-width:0;">
                  <div class="row-name">{{ data_get($member, 'name', '') }}</div>
                  <div class="row-sub">{{ data_get($member, 'email', '') }}</div>
                </div>
              </div>
            </td>
                        <td style="color:var(--text-soft);">{{ data_get($member, 'email', '') }}</td>
            <td><span class="badge plain badge-{{ $planKey }}">{{ data_get($member, 'membership_type', '—') }}</span></td>
            <td><span class="badge badge-{{ $stKey }}">{{ $status }}</span></td>
            <td style="color:var(--muted);">{{ data_get($member, 'created_at')?->format('M d, Y') ?? '—' }}</td>
            <td><a href="{{ route('members.show', $member) }}" class="btn btn-secondary btn-sm">View</a></td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="padding:40px;text-align:center;color:var(--muted);">
              No members yet.
              @if(auth()->user()->isAdmin())
                <a href="{{ route('members.create') }}" style="color:var(--accent);margin-left:4px;">Add one →</a>
              @endif
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Bottom Row: Recent Payments + System Users --}}
<div class="dash-bottom-grid">

  {{-- Recent Payments --}}
  <div class="dash-panel">
    <div class="dash-panel-header">
      <div class="dash-panel-title">Recent Payments</div>
      @if(auth()->user()->isAdmin())
        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">View All →</a>
      @endif
    </div>

    @forelse(($recentPayments ?? collect())->take(6) as $pay)
      @php
        $payKey = match(strtolower($pay->status ?? 'paid')) {
            'paid'    => 'paid',
            'pending' => 'pending',
            default   => 'expired',
        };
      @endphp
      <div class="list-row">
        <div class="list-left">
          <div class="list-icon">
            <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          </div>
          <div style="min-width:0;">
            <div class="row-name" style="font-size:13px;">{{ $pay->member->name ?? 'Unknown' }}</div>
            <div class="row-sub" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              {{ $pay->fitness_plan }} · {{ $pay->membership_type }} · {{ $pay->payment_date->format('M d, Y') }}
            </div>
          </div>
        </div>
        <div class="list-right">
          <div class="pay-amount">₱{{ number_format($pay->amount, 0) }}</div>
          <span class="badge plain badge-{{ $payKey }}" style="margin-top:4px;">{{ $pay->status }}</span>
        </div>
      </div>
    @empty
      <div style="padding:40px;text-align:center;color:var(--muted);font-size:14px;">No payments recorded yet.</div>
    @endforelse
  </div>

  {{-- System Users --}}
  <div class="dash-panel">
    <div class="dash-panel-header">
      <div class="dash-panel-title">System Users</div>
      @if(auth()->user()->isAdmin())
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Manage →</a>
      @endif
    </div>

    @forelse(($recentUsers ?? collect())->take(6) as $u)
      @php $roleKey = strtolower($u->role); @endphp
      <div class="list-row">
        <div class="list-left">
          @if($u->photo)
            <img src="{{ asset('storage/'.$u->photo) }}" class="row-avatar" alt=""/>
          @else
            <div class="row-avatar-ph">{{ strtoupper(substr($u->name, 0, 2)) }}</div>
          @endif
          <div style="min-width:0;">
            <div class="row-name" style="font-size:13px;">{{ $u->name }}</div>
            <div class="row-sub" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $u->email }}</div>
          </div>
        </div>
        <div class="list-right" style="display:flex;align-items:center;gap:10px;">
          <span class="role-tag role-{{ $roleKey }}">{{ ucfirst($u->role) }}</span>
          <span class="row-sub" style="white-space:nowrap;">{{ $u->created_at->format('M d') }}</span>
        </div>
      </div>
    @empty
      <div style="padding:40px;text-align:center;color:var(--muted);font-size:14px;">No users found.</div>
    @endforelse
  </div>

</div>

<style>
  /* Uses the tokens from layouts/admin (--accent, --accent-2, --gold, --info,
     --success, --warning, --danger, --border, --muted, --text-soft, --bg-card…). */

  /* ── Stat cards (same look as the staff dashboard) ── */
  .dash-stat-grid { grid-template-columns: repeat(4, 1fr); gap: 18px; }

  .stat-card-left { flex: 1; min-width: 0; }
  .stat-card .stat-value { font-size: 32px; font-weight: 700; line-height: 1.1; margin-bottom: 0; }
  .stat-card .stat-sub { margin-top: 5px; }
  .stat-card.blue   .stat-value { color: var(--info); }
  .stat-card.orange .stat-value { color: var(--text-primary); }
  .stat-card.gold   .stat-value { color: var(--accent-2); }
  html:root[data-theme="light"] .stat-card.gold .stat-value { color: var(--gold); }

  .stat-icon {
    width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: rgba(var(--glow), 0.08);
    border: 1px solid rgba(var(--glow), 0.35);
    color: rgb(var(--glow));
  }
  .stat-icon svg { width: 20px; height: 20px; fill: none; stroke: currentColor; }
  html:root[data-theme="light"] .stat-icon {
    background: rgba(var(--glow), .10);
    border-color: rgba(var(--glow), .22);
  }

  /* ── Panels ── */
  .dash-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
  }
  html:root[data-theme="light"] .dash-panel { box-shadow: var(--shadow-card); }

  .dash-panel-header {
    padding: 18px 24px 14px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
  }
  .dash-panel-title { font-size: 16px; font-weight: 700; color: var(--accent); }

  .dash-panel td, .dash-panel th { padding-left: 18px; padding-right: 18px; }

  /* ── Badges ── */
  .badge { text-transform: uppercase; letter-spacing: 0.5px; font-size: 10px; }
  .badge.plain::before { display: none; }
  .badge-suspended { background: rgba(251,146,60,0.15); color: #fb923c; }
  .badge-semi-annual { background: rgba(224,169,59,0.15); color: var(--accent); }
  html:root[data-theme="light"] .badge-suspended { background: rgba(194,65,12,.10); color: #c2410c; }
  html:root[data-theme="light"] .badge-semi-annual { background: rgba(184,134,42,.14); color: var(--gold); }

  /* ── Row bits ── */
  .member-cell { display: flex; align-items: center; gap: 12px; }
  .row-avatar, .row-avatar-ph {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    border: 1px solid rgba(224,169,59,0.3);
  }
  .row-avatar { object-fit: cover; }
  .row-avatar-ph {
    background: var(--accent-soft);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: var(--accent);
  }
  html:root[data-theme="light"] .row-avatar,
  html:root[data-theme="light"] .row-avatar-ph { border-color: rgba(184,134,42,.4); }
  .row-name { font-size: 14px; font-weight: 600; color: var(--text-primary); }
  .row-sub  { font-size: 11px; color: var(--text-secondary); }

  /* ── Bottom lists ── */
  .dash-bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

  .list-row {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 13px 24px;
    border-bottom: 1px solid var(--border);
    transition: background .15s;
  }
  .list-row:last-child { border-bottom: none; }
  .list-row:hover { background: rgba(255,255,255,0.02); }
  html:root[data-theme="light"] .list-row:hover { background: rgba(184,134,42,.06); }

  .list-left { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
  .list-right { text-align: right; flex-shrink: 0; }

  .list-icon {
    width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
    background: var(--accent-soft);
    border: 1px solid rgba(224,169,59,0.3);
    display: flex; align-items: center; justify-content: center;
    color: var(--accent);
  }
  .list-icon svg { width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 2; }

  .pay-amount { font-size: 14px; font-weight: 700; color: var(--accent-2); }
  html:root[data-theme="light"] .pay-amount { color: var(--gold); }

  .role-tag {
    font-size: 10px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase;
    padding: 3px 10px; border-radius: 40px; white-space: nowrap; border: 1px solid transparent;
  }
  .role-admin      { background: var(--accent-soft); color: var(--accent); border-color: rgba(224,169,59,0.35); }
  .role-staff      { background: rgba(251,191,36,.15); color: var(--warning); border-color: rgba(251,191,36,.3); }
  .role-instructor { background: rgba(96,165,250,.15); color: var(--info); border-color: rgba(96,165,250,.3); }
  .role-member     { background: rgba(167,139,250,.15); color: #a78bfa; border-color: rgba(167,139,250,.3); }
  html:root[data-theme="light"] .role-staff  { background: rgba(180,83,9,.10); border-color: rgba(180,83,9,.25); }
  html:root[data-theme="light"] .role-instructor { background: rgba(37,99,235,.10); border-color: rgba(37,99,235,.25); }
  html:root[data-theme="light"] .role-member { background: rgba(109,79,216,.10); color: #6d4fd8; border-color: rgba(109,79,216,.25); }

  /* ── Responsive ── */
  @media (max-width: 1024px) {
    .dash-stat-grid { grid-template-columns: repeat(2, 1fr); }
    .dash-bottom-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 768px) {
    /* keep the icon on the right instead of stacking */
    .stat-card { flex-direction: row; align-items: center; gap: 10px; }
    .stat-card .stat-value { font-size: 26px; }
    .stat-icon { width: 36px; height: 36px; }
    .dash-panel-header { padding: 14px 16px 12px; }
    .list-row { padding: 11px 16px; }
  }
  @media (max-width: 400px) {
    .dash-stat-grid { grid-template-columns: 1fr; }
  }
</style>

@endsection