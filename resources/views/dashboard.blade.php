@extends('layouts.admin')
@section('title', 'Admin Dashboard – IRONFORGE')
@section('active', 'dashboard')

@section('content')

{{-- Welcome --}}
<div style="margin-bottom:32px;">
  <h1 style="font-size:30px;font-weight:700;margin-bottom:6px;">
    Welcome back, <span style="color:var(--accent);">{{ explode(' ', auth()->user()->name)[0] }}</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">Here's what's happening at IRONFORGE today.</p>
</div>

{{-- Stat Cards --}}
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px;">

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">Total Members</div>
      <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
      <div class="stat-sub stat-up">All time registrations</div>
    </div>
    <div class="stat-icon icon-green">
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
    <div class="stat-icon icon-orange">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <circle cx="12" cy="12" r="8" stroke="var(--success)"/>
        <circle cx="12" cy="12" r="3" fill="var(--success)" stroke="none"/>
      </svg>
    </div>
  </div>

  <div class="stat-card blue">
    <div class="stat-card-left">
      <div class="stat-label">Monthly Plans</div>
      <div class="stat-value">{{ $stats['monthly'] ?? 0 }}</div>
      <div class="stat-sub">Monthly subscribers</div>
    </div>
    <div class="stat-icon icon-blue">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
    </div>
  </div>

  <div class="stat-card yellow">
    <div class="stat-card-left">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:26px;color:var(--warning);">
        ₱{{ number_format($thisMonth ?? 0, 0) }}
      </div>
      <div class="stat-sub">
        Total: <span style="color:var(--success);font-weight:700;">₱{{ number_format($totalCollected ?? 0, 0) }}</span>
      </div>
    </div>
    <div class="stat-icon icon-yellow">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>

</div>

{{-- Recent Members --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
  <div style="font-size:17px;font-weight:700;">Recent Members</div>
  <a href="{{ route('members.index') }}" class="btn btn-secondary btn-sm">View All</a>
</div>

<div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;
            overflow:hidden;margin-bottom:24px;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:var(--surface2);">
        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Name</th>
        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Email</th>
        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Plan</th>
        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Status</th>
        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Joined</th>
        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Action</th>
      </tr>
    </thead>
    <tbody>
      @forelse($recentMembers ?? [] as $member)
      <tr style="border-top:1px solid var(--border);">
        <td style="padding:14px 20px;">
          <div style="display:flex;align-items:center;gap:10px;">
            @if($member->photo)
              <img src="{{ asset('storage/'.$member->photo) }}"
                   style="width:34px;height:34px;border-radius:50%;object-fit:cover;
                          flex-shrink:0;border:1px solid var(--border);"/>
            @else
              <div style="width:34px;height:34px;border-radius:50%;background:rgba(200,255,0,0.08);
                          border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                          justify-content:center;font-size:12px;font-weight:700;
                          color:var(--accent);flex-shrink:0;">
                {{ strtoupper(substr($member->name,0,2)) }}
              </div>
            @endif
            <div>
              <div style="font-size:14px;font-weight:600;">{{ $member->name }}</div>
              <div style="font-size:11px;color:var(--muted);">{{ $member->email }}</div>
            </div>
          </div>
        </td>
        <td style="padding:14px 20px;font-size:13px;color:var(--muted);">{{ $member->email }}</td>
        <td style="padding:14px 20px;">
          <span class="badge badge-{{ strtolower($member->membership_type ?? 'monthly') }}">
            {{ $member->membership_type ?? '—' }}
          </span>
        </td>
        <td style="padding:14px 20px;">
          <span class="badge badge-{{ strtolower($member->status) }}">
            {{ $member->status }}
          </span>
        </td>
        <td style="padding:14px 20px;font-size:13px;color:var(--muted);">
          {{ $member->created_at->format('M d, Y') }}
        </td>
        <td style="padding:14px 20px;">
          <a href="{{ route('members.show', $member) }}"
             style="font-size:12px;font-weight:600;padding:5px 12px;background:transparent;
                    border:1px solid var(--border);border-radius:6px;color:var(--text);
                    text-decoration:none;transition:all 0.15s;"
             onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
             onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
            View
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" style="padding:48px;text-align:center;color:var(--muted);font-size:14px;">
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

{{-- Bottom Row: Recent Payments + System Users --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  {{-- Recent Payments --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
    <div style="padding:18px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;">
      <div style="font-size:15px;font-weight:700;">Recent Payments</div>
      @if(auth()->user()->isAdmin())
        <a href="{{ route('payments.index') }}"
           style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600;">
          View All →
        </a>
      @endif
    </div>
    @forelse(($recentPayments ?? collect())->take(6) as $pay)
      <div style="display:flex;align-items:center;justify-content:space-between;
                  padding:13px 20px;border-bottom:1px solid var(--border);transition:background 0.1s;"
           onmouseover="this.style.background='rgba(255,255,255,0.015)'"
           onmouseout="this.style.background='transparent'">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:32px;height:32px;border-radius:8px;background:var(--surface2);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24">
              <rect x="1" y="4" width="22" height="16" rx="2"/>
              <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
          </div>
          <div>
            <div style="font-size:13px;font-weight:600;">{{ $pay->member->name ?? 'Unknown' }}</div>
            <div style="font-size:11px;color:var(--muted);">
              {{ $pay->fitness_plan }} · {{ $pay->membership_type }} · {{ $pay->payment_date->format('M d, Y') }}
            </div>
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:14px;font-weight:700;color:var(--accent);">₱{{ number_format($pay->amount, 0) }}</div>
          <div style="font-size:11px;color:#4ade80;">{{ $pay->status }}</div>
        </div>
      </div>
    @empty
      <div style="padding:36px;text-align:center;color:var(--muted);font-size:13px;">
        No payments recorded yet.
      </div>
    @endforelse
  </div>

  {{-- System Users --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
    <div style="padding:18px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;">
      <div style="font-size:15px;font-weight:700;">System Users</div>
      @if(auth()->user()->isAdmin())
        <a href="{{ route('users.index') }}"
           style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600;">
          Manage →
        </a>
      @endif
    </div>
    @forelse(($recentUsers ?? collect())->take(6) as $u)
      @php
        $roleColor = match($u->role) {
          'admin'      => ['bg'=>'rgba(200,255,0,0.1)',  'color'=>'var(--accent)'],
          'staff'      => ['bg'=>'rgba(251,191,36,0.1)', 'color'=>'var(--warning)'],
          'instructor' => ['bg'=>'rgba(96,165,250,0.1)', 'color'=>'#60a5fa'],
          default      => ['bg'=>'rgba(167,139,250,0.1)','color'=>'#a78bfa'],
        };
      @endphp
      <div style="display:flex;align-items:center;justify-content:space-between;
                  padding:13px 20px;border-bottom:1px solid var(--border);transition:background 0.1s;"
           onmouseover="this.style.background='rgba(255,255,255,0.015)'"
           onmouseout="this.style.background='transparent'">
        <div style="display:flex;align-items:center;gap:10px;">
          @if($u->photo)
            <img src="{{ asset('storage/'.$u->photo) }}"
                 style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid var(--border);"/>
          @else
            <div style="width:34px;height:34px;border-radius:50%;background:rgba(200,255,0,0.08);
                        border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                        justify-content:center;font-size:12px;font-weight:700;
                        color:var(--accent);flex-shrink:0;">
              {{ strtoupper(substr($u->name,0,2)) }}
            </div>
          @endif
          <div>
            <div style="font-size:13px;font-weight:600;">{{ $u->name }}</div>
            <div style="font-size:11px;color:var(--muted);">{{ $u->email }}</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
          <span style="font-size:10px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;
                       padding:3px 9px;border-radius:5px;
                       background:{{ $roleColor['bg'] }};color:{{ $roleColor['color'] }};">
            {{ ucfirst($u->role) }}
          </span>
          <span style="font-size:11px;color:var(--muted);">{{ $u->created_at->format('M d') }}</span>
        </div>
      </div>
    @empty
      <div style="padding:36px;text-align:center;color:var(--muted);font-size:13px;">
        No users found.
      </div>
    @endforelse
  </div>

</div>

@endsection