@extends('layouts.instructor')
@section('title', 'Payments – IRONFORGE')
@section('active', 'payments')

@section('content')

<div style="margin-bottom:32px;">
  <h1 style="font-size:30px;font-weight:700;margin-bottom:6px;">Payments</h1>
  <p style="color:var(--muted);font-size:14px;">Track payments from your assigned members</p>
</div>

{{-- Summary Cards --}}
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">Total Payments</div>
      <div class="stat-value">{{ $payments->count() }}</div>
      <div class="stat-sub">All transactions</div>
    </div>
    <div class="stat-icon icon-green">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <rect x="1" y="4" width="22" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="1" y1="10" x2="23" y2="10" stroke-linecap="round"/>
      </svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">Total Collected</div>
      <div class="stat-value" style="font-size:28px;color:var(--accent);">
        ₱{{ number_format($payments->sum('amount'), 0) }}
      </div>
      <div class="stat-sub">From all members</div>
    </div>
    <div class="stat-icon icon-orange">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:28px;color:var(--success);">
        ₱{{ number_format($payments->where('payment_date', '>=', now()->startOfMonth())->sum('amount'), 0) }}
      </div>
      <div class="stat-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-icon icon-yellow">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
    </div>
  </div>

</div>

{{-- Payments Table --}}
<div class="section-header">
  <div class="section-title">Payment History</div>
</div>

<div class="card">
  <table>
    <thead>
      <tr>
        <th>Member</th>
        <th>Receipt #</th>
        <th>Plan</th>
        <th>Duration</th>
        <th>Amount</th>
        <th>Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($payments as $payment)
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            @if($payment->member && $payment->member->photo)
              <img src="{{ asset('storage/'.$payment->member->photo) }}"
                   style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid var(--border);"/>
            @else
              <div style="width:32px;height:32px;border-radius:50%;background:rgba(200,255,0,0.08);
                          border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                          justify-content:center;font-size:11px;font-weight:700;color:var(--accent);flex-shrink:0;">
                {{ strtoupper(substr($payment->member->name ?? 'ME', 0, 2)) }}
              </div>
            @endif
            <div>
              <div style="font-weight:600;font-size:14px;">{{ $payment->member->name ?? '—' }}</div>
              <div style="font-size:12px;color:var(--muted);">{{ $payment->member->email ?? '' }}</div>
            </div>
          </div>
        </td>
        <td style="font-family:monospace;font-size:11px;color:var(--muted);">
          {{ $payment->receipt_number }}
        </td>
        <td style="font-size:14px;">{{ $payment->fitness_plan ?? '—' }}</td>
        <td>
          <span class="badge badge-{{ strtolower($payment->membership_type ?? 'monthly') }}">
            {{ $payment->membership_type ?? '—' }}
          </span>
        </td>
        <td style="font-weight:700;color:var(--accent);font-size:15px;">
          ₱{{ number_format($payment->amount, 0) }}
        </td>
        <td style="color:var(--muted);font-size:13px;">
          {{ $payment->payment_date->format('M d, Y') }}
        </td>
        <td>
          <span class="badge badge-{{ strtolower($payment->status) }}">
            {{ $payment->status }}
          </span>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="text-align:center;color:var(--muted);padding:56px;">
          No payments from your members yet.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection