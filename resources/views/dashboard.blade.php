@extends('layouts.app')

@section('title', 'Dashboard – IRONFORGE')
@section('page_title', 'Dashboard')

@section('topbar_actions')
  @if(auth()->user()->isAdmin())
    <a href="{{ route('members.create') }}" class="btn btn-primary">+ Add Member</a>
  @endif
@endsection

@section('content')
<!-- Stat Cards -->
<div class="stat-grid" style="grid-template-columns:repeat(5,1fr);">
  <div class="stat-card">
    <div class="stat-label">Total Members</div>
    <div class="stat-value">{{ $stats['total'] }}</div>
    <div class="stat-sub stat-up">All time registrations</div>
  </div>
  <div class="stat-card orange">
    <div class="stat-label">Active Members</div>
    <div class="stat-value">{{ $stats['active'] }}</div>
    <div class="stat-sub">Currently enrolled</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-label">Monthly Plans</div>
    <div class="stat-value">{{ $stats['monthly'] }}</div>
    <div class="stat-sub">Monthly subscribers</div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">Yearly Plans</div>
    <div class="stat-value">{{ $stats['yearly'] }}</div>
    <div class="stat-sub">Annual members</div>
  </div>

  {{-- NEW: Total Payments Card --}}
  <div class="stat-card" style="--accent-color:var(--warning);" >
    <div style="position:absolute;top:0;left:0;width:3px;height:100%;background:var(--warning);"></div>
    <div class="stat-label">This Month</div>
    <div class="stat-value" style="font-size:28px;color:var(--warning);">
      ₱{{ number_format($thisMonth ?? 0, 0) }}
    </div>
    <div class="stat-sub">
      Total:
      <span style="color:var(--success);font-weight:600;">
        ₱{{ number_format($totalCollected ?? 0, 0) }}
      </span>
    </div>
  </div>
</div>

<!-- Recent Members -->
<div class="section-header">
  <div class="section-title">Recent Members</div>
  <a href="{{ route('members.index') }}" class="btn btn-secondary btn-sm">View All</a>
</div>
<div class="card">
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Plan</th>
        <th>Status</th>
        <th>Joined</th>
      </tr>
    </thead>
    <tbody>
      @forelse($recent as $member)
      <tr>
        <td><strong>{{ $member->name }}</strong></td>
        <td style="color:var(--muted)">{{ $member->email }}</td>
        <td>
          <span class="badge badge-{{ strtolower($member->membership_type) }}">
            {{ $member->membership_type }}
          </span>
        </td>
        <td>
          <span class="badge badge-{{ strtolower($member->status) }}">
            {{ $member->status }}
          </span>
        </td>
        <td style="color:var(--muted)">{{ $member->created_at->format('M d, Y') }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">
          No members yet.
          <a href="{{ route('members.create') }}" style="color:var(--accent)">Add one</a>.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection