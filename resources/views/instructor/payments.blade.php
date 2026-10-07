@extends('layouts.instructor')
<<<<<<< HEAD
@section('title', 'My Earnings – IRONFORGE')
=======
@section('title', 'My Earnings – APEX')
>>>>>>> 77ebe7c4ad72fe3a31873f5e62749429642c4d98
@section('active', 'payments')

@section('content')

<<<<<<< HEAD
<div style="margin-bottom:32px;">
  <h1 style="font-size:32px;font-weight:700;margin-bottom:6px;">
    My <span style="color:var(--accent);">Earnings</span>`
=======
{{-- Page Header --}}
<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    My <span style="color:var(--accent);">Earnings</span>
>>>>>>> 77ebe7c4ad72fe3a31873f5e62749429642c4d98
  </h1>
  <p style="color:var(--muted);font-size:14px;">Coach subscription fees automatically allocated from member subscriptions</p>
</div>

{{-- Stats --}}
<div class="stat-grid">
  <div class="stat-card orange">
    <div class="stat-card-left">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:28px;">₱{{ number_format($thisMonthTotal, 0) }}</div>
      <div class="stat-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-icon icon-orange">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
    </div>
  </div>

  <div class="stat-card gold">
    <div class="stat-card-left">
      <div class="stat-label">Total Earned</div>
      <div class="stat-value" style="font-size:28px;color:var(--accent-2);">₱{{ number_format($totalEarned, 0) }}</div>
      <div class="stat-sub">All time</div>
    </div>
    <div class="stat-icon icon-yellow">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>

  <div class="stat-card green">
    <div class="stat-card-left">
      <div class="stat-label">Transactions</div>
      <div class="stat-value">{{ $payments->total() }}</div>
      <div class="stat-sub stat-up">Total coach fee payments</div>
    </div>
    <div class="stat-icon icon-green">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <rect x="1" y="4" width="22" height="16" rx="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
    </div>
  </div>
</div>

{{-- Info banner --}}
<div class="info-banner">
  <svg viewBox="0 0 24 24" stroke-width="2">
    <circle cx="12" cy="12" r="10"/>
    <line x1="12" y1="8" x2="12" y2="12"/>
    <line x1="12" y1="16" x2="12.01" y2="16"/>
  </svg>
  <p>
    These earnings are automatically recorded when members subscribe with you as their instructor.
    Coach fees are separate from the gym's membership fee.
  </p>
</div>

{{-- Transactions table --}}
<div class="transactions-card">
  <div class="transactions-header">
    <div class="transactions-title">Coach Fee Transactions</div>
    <svg class="transactions-icon" viewBox="0 0 24 24" stroke-width="2">
      <rect x="1" y="4" width="22" height="16" rx="2"/>
      <line x1="1" y1="10" x2="23" y2="10"/>
    </svg>
  </div>

  <div class="table-scroll">
    <table>
      <thead>
        <tr>
          <th>Receipt</th>
          <th>Member</th>
          <th>Plan</th>
          <th>Duration</th>
          <th style="text-align:right;">Coach Fee</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
        @php
          $pPhoto     = $payment->member?->user?->photo ?? $payment->member?->photo ?? null;
          $memberName = $payment->member?->name ?? '—';
        @endphp
        <tr>
          <td class="receipt-id">{{ $payment->receipt_number }}</td>
          <td>
            <div class="member-cell">
              @if($pPhoto)
                <img src="{{ asset('storage/'.$pPhoto) }}" class="member-avatar"/>
              @else
                <div class="member-avatar-placeholder">{{ strtoupper(substr($memberName, 0, 2)) }}</div>
              @endif
              <span class="member-name">{{ $memberName }}</span>
            </div>
          </td>
          <td>{{ $payment->fitness_plan ?? '—' }}</td>
          <td><span class="duration-badge">{{ $payment->membership_type ?? '—' }}</span></td>
          <td class="fee-amount">₱{{ number_format($payment->amount, 0) }}</td>
          <td class="payment-date">{{ $payment->payment_date->format('M d, Y') }}</td>
          <td><span class="status-badge">{{ $payment->status }}</span></td>
        </tr>
        @empty
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <svg width="40" height="40" stroke-width="1.5" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
              </svg>
              No coach fee payments received yet.
              <br>
              <span class="empty-sub">Members who select you as their instructor will automatically generate earnings here.</span>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($payments->hasPages())
  <div class="pagination-wrap">{{ $payments->links() }}</div>
  @endif
</div>

<style>
  /* Charcoal & gold — colours come from the tokens in layouts/instructor.blade.php */

  .stat-grid { grid-template-columns: repeat(3, 1fr); }

  .info-banner {
    background: var(--accent-soft); border: 1px solid rgba(224,169,59,0.3);
    border-radius: 10px; padding: 14px 20px; margin-bottom: 24px;
    display: flex; align-items: center; gap: 12px;
  }
  .info-banner svg { width: 18px; height: 18px; stroke: var(--accent); fill: none; flex-shrink: 0; }
  .info-banner p { font-size: 13px; color: var(--text-soft); margin: 0; }

  .transactions-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
  html:root[data-theme="light"] .transactions-card { box-shadow: var(--shadow-card); }
  .transactions-header {
    padding: 18px 24px 14px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
  }
  .transactions-title { font-size: 16px; font-weight: 700; color: var(--accent); }
  .transactions-icon { width: 20px; height: 20px; stroke: var(--accent); fill: none; }

  table { min-width: 700px; font-size: 13px; }
  th { padding: 14px 16px; border-bottom: 1px solid var(--border); }
  td { padding: 14px 16px; color: var(--text-soft); }

  .receipt-id { font-family: monospace; font-size: 11px; color: var(--muted); }
  .member-cell { display: flex; align-items: center; gap: 10px; }
  .member-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(224,169,59,0.3); flex-shrink: 0; }
  .member-avatar-placeholder {
    width: 32px; height: 32px; border-radius: 50%; background: var(--accent-soft);
    border: 1px solid rgba(224,169,59,0.3); display: flex; align-items: center;
    justify-content: center; font-size: 11px; font-weight: 700; color: var(--accent); flex-shrink: 0;
  }
  .member-name { font-weight: 600; font-size: 13px; color: var(--text); }
  .duration-badge {
    display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;
    background: rgba(96,165,250,0.15); color: var(--info);
  }
  .fee-amount { text-align: right; font-weight: 700; color: var(--accent-2); font-size: 15px; }
  html:root[data-theme="light"] .fee-amount { color: var(--accent); }
  .payment-date { color: var(--muted); white-space: nowrap; }
  .status-badge {
    display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 6px;
    font-size: 11px; font-weight: 700; background: color-mix(in srgb, var(--success) 15%, transparent);
    color: var(--success); border: 1px solid color-mix(in srgb, var(--success) 25%, transparent);
  }

  .empty-state { padding: 60px 24px; text-align: center; color: var(--muted); }
  .empty-state svg { display: block; margin: 0 auto 12px; opacity: .3; stroke: currentColor; fill: none; }
  .empty-sub { font-size: 12px; margin-top: 8px; display: inline-block; }
  .pagination-wrap { padding: 16px 24px; border-top: 1px solid var(--border); }

  @media (max-width: 1024px) { table { min-width: 600px; } }
  @media (max-width: 768px) {
    .stat-grid { grid-template-columns: 1fr 1fr; }
    .transactions-header { padding: 14px 18px; }
    th, td { padding: 10px 14px; font-size: 12px; }
    table { min-width: 500px; }
    .info-banner { padding: 12px 16px; }
  }
  @media (max-width: 480px) {
    .stat-grid { grid-template-columns: 1fr; }
    .transactions-header { padding: 12px 14px; }
    table { min-width: 400px; }
    th, td { padding: 8px 10px; font-size: 11px; }
    .member-avatar, .member-avatar-placeholder { width: 26px; height: 26px; font-size: 10px; }
    .empty-state { padding: 40px 16px; }
    .pagination-wrap { padding: 12px 14px; }
  }
</style>

@endsection