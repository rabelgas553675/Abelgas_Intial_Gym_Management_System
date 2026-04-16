@extends('layouts.member')
@section('title', 'My Earnings – IRONFORGE')
@section('active', 'payments')

@section('content')

<div style="margin-bottom:32px;">
  <h1 style="font-size:32px;font-weight:700;margin-bottom:6px;">
    My <span style="color:var(--accent);">Earnings</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">
    Coach subscription fees automatically allocated from member subscriptions
  </p>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;">
    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">This Month</div>
    <div style="font-size:32px;font-weight:800;color:var(--accent);">₱{{ number_format($thisMonthTotal, 0) }}</div>
    <div style="font-size:12px;color:var(--muted);margin-top:4px;">{{ now()->format('F Y') }}</div>
  </div>
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;">
    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Total Earned</div>
    <div style="font-size:32px;font-weight:800;color:#4ade80;">₱{{ number_format($totalEarned, 0) }}</div>
    <div style="font-size:12px;color:var(--muted);margin-top:4px;">All time</div>
  </div>
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;">
    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Transactions</div>
    <div style="font-size:32px;font-weight:800;">{{ $payments->total() }}</div>
    <div style="font-size:12px;color:var(--muted);margin-top:4px;">Total coach fee payments</div>
  </div>
</div>

{{-- Info banner --}}
<div style="background:rgba(232,255,42,0.05);border:1px solid rgba(232,255,42,0.2);
            border-radius:12px;padding:14px 20px;margin-bottom:24px;
            display:flex;align-items:center;gap:12px;">
  <svg width="18" height="18" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
    <line x1="12" y1="16" x2="12.01" y2="16"/>
  </svg>
  <div style="font-size:13px;color:var(--muted);">
    These earnings are automatically recorded when members subscribe with you as their instructor.
    Coach fees are separate from the gym's membership fee.
  </div>
</div>

{{-- Transactions table --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
  <div style="padding:20px 24px;border-bottom:1px solid var(--border);
              display:flex;align-items:center;justify-content:space-between;">
    <div style="font-size:16px;font-weight:700;">Coach Fee Transactions</div>
    <svg width="20" height="20" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
      <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
    </svg>
  </div>
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:rgba(255,255,255,0.03);">
          <th style="padding:12px 20px;text-align:left;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Receipt</th>
          <th style="padding:12px 20px;text-align:left;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Member</th>
          <th style="padding:12px 20px;text-align:left;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Plan</th>
          <th style="padding:12px 20px;text-align:left;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Duration</th>
          <th style="padding:12px 20px;text-align:right;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Coach Fee</th>
          <th style="padding:12px 20px;text-align:left;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Date</th>
          <th style="padding:12px 20px;text-align:left;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;font-weight:600;">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $payment)
        <tr style="border-top:1px solid var(--border);">
          <td style="padding:16px 20px;font-family:monospace;font-size:12px;color:var(--muted);">
            {{ $payment->receipt_number }}
          </td>
          <td style="padding:16px 20px;">
            @php $memberName = $payment->member?->name ?? '—'; @endphp
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:32px;height:32px;border-radius:50%;background:rgba(232,255,42,0.08);
                          border:1px solid rgba(232,255,42,0.2);display:flex;align-items:center;
                          justify-content:center;font-size:12px;font-weight:700;color:var(--accent);flex-shrink:0;">
                {{ strtoupper(substr($memberName,0,1)) }}
              </div>
              <span style="font-weight:600;font-size:14px;">{{ $memberName }}</span>
            </div>
          </td>
          <td style="padding:16px 20px;font-size:14px;">{{ $payment->fitness_plan ?? '—' }}</td>
          <td style="padding:16px 20px;">
            <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;
                         font-weight:600;background:rgba(96,165,250,.1);color:#60a5fa;">
              {{ $payment->membership_type ?? '—' }}
            </span>
          </td>
          <td style="padding:16px 20px;text-align:right;font-weight:700;color:var(--accent);font-size:16px;">
            ₱{{ number_format($payment->amount, 0) }}
          </td>
          <td style="padding:16px 20px;color:var(--muted);font-size:13px;">
            {{ $payment->payment_date->format('M d, Y') }}
          </td>
          <td style="padding:16px 20px;">
            <span style="display:inline-block;padding:4px 12px;border-radius:6px;font-size:12px;
                         font-weight:600;background:rgba(74,222,128,0.15);color:#4ade80;">
              {{ $payment->status }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="padding:60px 24px;text-align:center;color:var(--muted);">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5"
                 viewBox="0 0 24 24" style="display:block;margin:0 auto 12px;opacity:.3;">
              <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            No coach fee payments received yet.<br>
            <span style="font-size:12px;margin-top:8px;display:inline-block;">
              Members who select you as their instructor will automatically generate earnings here.
            </span>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($payments->hasPages())
  <div style="padding:16px 24px;border-top:1px solid var(--border);">
    {{ $payments->links() }}
  </div>
  @endif
</div>

@endsection