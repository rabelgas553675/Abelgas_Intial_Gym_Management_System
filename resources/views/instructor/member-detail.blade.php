@extends('layouts.instructor')
@section('title', 'Member Detail – APEX')
@section('active', 'dashboard')

@section('content')

@php
  $memberPhoto = $member->user?->photo ?? $member->photo ?? null;
  $isExpired   = $member->isExpired();
  $isExpiring  = $member->isDueWithinDays(7) && !$isExpired;
  $statusLabel = $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active');
  $statusColor = $isExpired ? 'var(--danger)' : ($isExpiring ? 'var(--warning)' : 'var(--success)');
  $statusBg    = 'color-mix(in srgb, ' . $statusColor . ' 15%, transparent)';
  $statusBorder = 'color-mix(in srgb, ' . $statusColor . ' 27%, transparent)';
@endphp

{{-- Page Header --}}
<div class="md-head">
  <div>
    <h1>Member <span>Detail</span></h1>
    <p>Viewing full profile and payment history</p>
  </div>
  <a href="{{ route('instructor.dashboard') }}" class="btn btn-secondary">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 5l-7 7 7 7"/>
    </svg>
    Back to Dashboard
  </a>
</div>

<div class="detail-grid">

  {{-- LEFT: Profile + Personal Info --}}
  <div class="left-column">

    <div class="profile-card">
      <div class="profile-avatar">
        @if($memberPhoto)
          <img src="{{ asset('storage/'.$memberPhoto) }}" alt="{{ $member->name }}"/>
        @else
          <span class="profile-avatar-placeholder">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
        @endif
      </div>

      <div class="profile-name">{{ $member->name }}</div>
      <div class="profile-email">{{ $member->email }}</div>

      <span class="status-badge" style="background:{{ $statusBg }};color:{{ $statusColor }};border-color:{{ $statusBorder }};">
        <span class="dot" style="background:{{ $statusColor }};"></span>
        {{ $statusLabel }}
      </span>
    </div>

    <div class="info-card">
      <div class="info-title">Personal Information</div>

      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
        </div>
        <div>
          <div class="info-label">Phone</div>
          <div class="info-value">{{ $member->phone ?? 'Not set' }}</div>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <div class="info-label">Gender</div>
          <div class="info-value">{{ $member->gender ?? 'Not set' }}</div>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <div>
          <div class="info-label">Birthdate</div>
          <div class="info-value">{{ $member->birthdate?->format('M d, Y') ?? 'Not set' }}</div>
        </div>
      </div>

      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div>
          <div class="info-label">Address</div>
          <div class="info-value">{{ $member->address ?? 'Not set' }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- RIGHT: Membership + Payments --}}
  <div class="right-column">

    <div class="membership-card">
      <div class="info-title">Membership Details</div>

      <div class="membership-grid">
        <div class="membership-item">
          <div class="membership-label">Fitness Plan</div>
          <div class="membership-value accent">{{ $member->fitness_plan ?? 'Not set' }}</div>
        </div>
        <div class="membership-item">
          <div class="membership-label">Duration</div>
          <div class="membership-value">{{ $member->membership_type ?? 'Not set' }}</div>
        </div>
        <div class="membership-item">
          <div class="membership-label">Fee</div>
          <div class="membership-value accent">₱{{ number_format($member->fee ?? 0, 2) }}</div>
        </div>
        <div class="membership-item">
          <div class="membership-label">Start Date</div>
          <div class="membership-value">{{ $member->start_date?->format('M d, Y') ?? 'Not set' }}</div>
        </div>
        <div class="membership-item">
          <div class="membership-label">End Date</div>
          <div class="membership-value {{ $isExpired ? 'danger' : '' }}">
            {{ $member->end_date?->format('M d, Y') ?? 'Not set' }}
          </div>
        </div>
        <div class="membership-item">
          <div class="membership-label">Status</div>
          <span class="status-text" style="color:{{ $statusColor }};">
            <span class="dot" style="background:{{ $statusColor }};"></span>
            {{ $statusLabel }}
          </span>
        </div>
      </div>

      @if($isExpired)
        <div class="notice notice-danger">
          <svg viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          Membership expired on {{ $member->end_date->format('M d, Y') }}.
        </div>
      @elseif($isExpiring)
        <div class="notice notice-warning">
          <svg viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          Membership expiring in {{ (int) now()->diffInDays($member->end_date) }} days.
        </div>
      @endif
    </div>

    <div class="payments-card">
      <div class="payments-header">
        <div class="payments-title">Payment History</div>
        <div class="payments-count">{{ $payments->count() }} transaction(s)</div>
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Receipt</th>
              <th>Plan</th>
              <th>Amount</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $p)
            <tr>
              <td class="receipt-id">{{ $p->receipt_number }}</td>
              <td>{{ $p->fitness_plan }} / {{ $p->membership_type }}</td>
              <td class="payment-amount">₱{{ number_format($p->amount, 2) }}</td>
              <td class="payment-date">{{ $p->payment_date->format('M d, Y') }}</td>
              <td>
                <span class="payment-status"><span class="status-dot"></span>{{ $p->status }}</span>
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="empty-state">No payments on record.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<style>
  /* Charcoal & gold — colours come from the tokens in layouts/instructor.blade.php */

  .md-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
  }
  .md-head h1 { font-size: 28px; font-weight: 700; margin-bottom: 4px; }
  .md-head h1 span { color: var(--accent); }
  .md-head p { color: var(--muted); font-size: 14px; }

  .detail-grid { display: grid; grid-template-columns: 300px 1fr; gap: 20px; align-items: start; }
  .left-column, .right-column { display: flex; flex-direction: column; gap: 20px; }

  .profile-card, .info-card, .membership-card, .payments-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
  }
  html:root[data-theme="light"] :is(.profile-card,.info-card,.membership-card,.payments-card) { box-shadow: var(--shadow-card); }

  .profile-card { padding: 28px; text-align: center; }
  .profile-avatar {
    margin: 0 auto 16px; width: 88px; height: 88px; border-radius: 50%; overflow: hidden;
    border: 2px solid var(--accent); background: var(--accent-soft);
    display: flex; align-items: center; justify-content: center;
  }
  .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
  .profile-avatar-placeholder { font-size: 28px; font-weight: 700; color: var(--accent); }
  .profile-name { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
  .profile-email { font-size: 13px; color: var(--muted); margin-bottom: 14px; word-break: break-word; }
  .status-badge {
    display: inline-flex; align-items: center; gap: 6px; padding: 5px 16px;
    border-radius: 100px; font-size: 12px; font-weight: 700; border: 1px solid transparent;
  }
  .dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
  .status-text { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; font-weight: 700; }

  .info-card { padding: 22px; }
  .info-title { font-size: 10px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; }
  .info-item {
    display: flex; align-items: center; gap: 12px; padding: 11px 14px; margin-bottom: 10px;
    background: var(--surface2); border: 1px solid var(--border); border-radius: 10px;
    transition: border-color .3s ease;
  }
  .info-item:last-child { margin-bottom: 0; }
  .info-item:hover { border-color: rgba(224,169,59,0.5); }
  .info-icon {
    width: 30px; height: 30px; border-radius: 8px; background: var(--accent-soft); color: var(--accent);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .info-icon svg { width: 14px; height: 14px; stroke: currentColor; fill: none; }
  .info-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
  .info-value { font-size: 13px; font-weight: 600; color: var(--text); word-break: break-word; }

  .membership-card { padding: 24px; }
  .membership-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 16px; }
  .membership-item {
    padding: 16px; background: var(--surface2); border: 1px solid var(--border); border-radius: 10px;
    transition: border-color .3s ease;
  }
  .membership-item:hover { border-color: rgba(224,169,59,0.5); }
  .membership-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
  .membership-value { font-size: 15px; font-weight: 700; color: var(--text); }
  .membership-value.accent { color: var(--accent); }
  .membership-value.danger { color: var(--danger); }

  .notice {
    padding: 12px 16px; border-radius: 10px; font-size: 13px;
    display: flex; align-items: center; gap: 8px;
  }
  .notice svg { width: 16px; height: 16px; flex-shrink: 0; stroke: currentColor; fill: none; }
  .notice-danger { background: color-mix(in srgb, var(--danger) 10%, transparent); border: 1px solid color-mix(in srgb, var(--danger) 25%, transparent); color: var(--danger); }
  .notice-warning { background: color-mix(in srgb, var(--warning) 10%, transparent); border: 1px solid color-mix(in srgb, var(--warning) 25%, transparent); color: var(--warning); }

  .payments-card { overflow: hidden; }
  .payments-header {
    padding: 18px 24px 14px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
  }
  .payments-title { font-size: 16px; font-weight: 700; color: var(--accent); }
  .payments-count { font-size: 12px; color: var(--muted); }
  table { min-width: 500px; font-size: 13px; }
  th { padding: 12px 20px; border-bottom: 1px solid var(--border); }
  td { padding: 14px 20px; color: var(--text-soft); }
  .receipt-id { font-family: monospace; font-size: 11px; color: var(--muted); }
  .payment-amount { font-size: 14px; font-weight: 700; color: var(--accent-2); }
  html:root[data-theme="light"] .payment-amount { color: var(--accent); }
  .payment-date { color: var(--muted); }
  .payment-status {
    display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 6px;
    font-size: 11px; font-weight: 700; background: color-mix(in srgb, var(--success) 15%, transparent);
    color: var(--success); border: 1px solid color-mix(in srgb, var(--success) 25%, transparent);
  }
  .status-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--success); display: inline-block; }
  .empty-state { padding: 40px; text-align: center; color: var(--muted); font-size: 14px; }

  @media (max-width: 1024px) {
    .detail-grid { grid-template-columns: 1fr; gap: 16px; }
    .profile-card { max-width: 400px; width: 100%; margin: 0 auto; }
    .left-column { align-items: stretch; }
  }
  @media (max-width: 768px) {
    .md-head { flex-direction: column; align-items: stretch; }
    .md-head .btn { justify-content: center; }
    .membership-grid { grid-template-columns: repeat(2, 1fr); }
    .profile-card { max-width: 100%; padding: 20px; }
    th, td { padding: 10px 14px; font-size: 12px; }
    table { min-width: 450px; }
  }
  @media (max-width: 480px) {
    .md-head h1 { font-size: 22px; }
    .membership-card, .info-card { padding: 16px; }
    .membership-grid { gap: 10px; }
    .membership-item { padding: 12px; }
    .membership-value { font-size: 14px; }
    .payments-header { padding: 14px; }
    table { min-width: 380px; }
    th, td { padding: 8px 10px; font-size: 11px; }
  }
</style>

@endsection