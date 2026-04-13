@extends('layouts.admin')
@section('title', 'Payment Transactions – IRONFORGE')
@section('page_title', 'Payment Transactions')
@section('active_nav', 'staff.payments')

@section('content')

<div style="margin-bottom:28px;">
  <h1 style="font-size:30px;font-weight:700;margin-bottom:4px;">Payment Transactions</h1>
  <p style="color:var(--muted);font-size:14px;">View all member payment records.</p>
</div>

{{-- Summary Cards --}}
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
  <div class="stat-card">
    <div class="stat-label">Total Transactions</div>
    <div class="stat-value">{{ $totalCount }}</div>
    <div class="stat-sub">All time</div>
  </div>
  <div class="stat-card green">
    <div class="stat-label">This Month</div>
    <div class="stat-value" style="font-size:26px;color:var(--warning);">₱{{ number_format($thisMonth, 0) }}</div>
    <div class="stat-sub">Current month collections</div>
  </div>
  <div class="stat-card blue">
    <div class="stat-label">Total Collected</div>
    <div class="stat-value" style="font-size:26px;color:var(--success);">₱{{ number_format($totalCollected, 0) }}</div>
    <div class="stat-sub">All time revenue</div>
  </div>
</div>

{{-- Transactions Table --}}
<div class="section-header">
  <div class="section-title">All Transactions</div>
</div>

<div class="card">
  <table>
    <thead>
      <tr>
        <th>Transaction ID</th>
        <th>Member</th>
        <th>Plan</th>
        <th>Duration</th>
        <th>Amount</th>
        <th>Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse($payments as $p)
      <tr>
        <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--muted);">
          {{ $p->receipt_number }}
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:30px;height:30px;border-radius:50%;background:rgba(200,255,0,0.08);
                        border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                        justify-content:center;font-size:10px;font-weight:700;color:var(--accent);flex-shrink:0;">
              {{ strtoupper(substr($p->member->name ?? '?', 0, 2)) }}
            </div>
            <span style="font-weight:600;">{{ $p->member->name ?? '—' }}</span>
          </div>
        </td>
        <td>{{ $p->fitness_plan }}</td>
        <td>
          <span class="badge badge-{{ strtolower($p->membership_type) }}">{{ $p->membership_type }}</span>
        </td>
        <td style="font-weight:700;color:var(--accent);">₱{{ number_format($p->amount, 0) }}</td>
        <td style="color:var(--muted);">{{ $p->payment_date->format('M d, Y') }}</td>
        <td>
          <span class="badge badge-{{ $p->status === 'Paid' ? 'paid' : 'expired' }}">{{ $p->status }}</span>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="text-align:center;color:var(--muted);padding:48px;">No transactions yet.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Pagination --}}
  @if($payments->hasPages())
  <div style="padding:16px 18px;border-top:1px solid var(--border);">
    {{ $payments->links() }}
  </div>
  @endif
</div>

@endsection