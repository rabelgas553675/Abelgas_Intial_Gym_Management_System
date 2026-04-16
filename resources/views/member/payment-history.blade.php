@extends('layouts.member')
@section('title', 'Payment History – IRONFORGE')
@section('active', 'payments')

@section('content')

<div style="margin-bottom:32px;">
  <h1 style="font-size:32px;font-weight:700;margin-bottom:6px;">Payment History</h1>
  <p style="color:var(--muted);font-size:14px;">View and download your payment receipts</p>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
    <div style="font-size:13px;color:var(--muted);margin-bottom:8px;">Total Payments</div>
    <div style="font-size:36px;font-weight:700;">{{ $payments->count() }}</div>
  </div>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
    <div style="font-size:13px;color:var(--muted);margin-bottom:8px;">Total Paid</div>
    <div style="font-size:36px;font-weight:700;color:var(--accent);">
      ₱{{ number_format($payments->sum(function($p){
        return $p->amount + ($p->coach_fee ?? 0);
      }), 0) }}
    </div>
  </div>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
    <div style="font-size:13px;color:var(--muted);margin-bottom:8px;">Last Payment</div>
    @if($payments->first())
      @php
        $last = $payments->first();
        $lastTotal = $last->amount + ($last->coach_fee ?? 0);
      @endphp
      <div style="font-size:13px;color:var(--muted);margin-bottom:4px;">
        {{ $last->payment_date->format('M d, Y') }}
      </div>
      <div style="font-size:28px;font-weight:700;">
        ₱{{ number_format($lastTotal, 0) }}
      </div>
    @else
      <div style="font-size:28px;font-weight:700;color:var(--muted);">—</div>
    @endif
  </div>
</div>

{{-- Payments Table --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:20px;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:rgba(255,255,255,0.02);">
        <th style="padding:14px 20px;text-align:left;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Receipt ID</th>
        <th style="padding:14px 20px;text-align:left;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Date</th>
        <th style="padding:14px 20px;text-align:left;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Plan</th>
        <th style="padding:14px 20px;text-align:left;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Duration</th>
        <th style="padding:14px 20px;text-align:left;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Amount</th>
        <th style="padding:14px 20px;text-align:left;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Status</th>
        <th style="padding:14px 20px;text-align:left;font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($payments as $p)
      @php
        $total = $p->amount + ($p->coach_fee ?? 0);
      @endphp
      <tr style="border-top:1px solid var(--border);">
        <td style="padding:16px 20px;">
          <div style="display:flex;align-items:center;gap:8px;">
            <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span style="font-family:monospace;font-size:12px;color:var(--muted);">
              {{ $p->receipt_number }}
            </span>
          </div>
        </td>

        <td style="padding:16px 20px;font-size:14px;">
          {{ $p->payment_date->format('M d, Y') }}
        </td>

        <td style="padding:16px 20px;font-size:14px;font-weight:600;">
          {{ $p->fitness_plan }}
        </td>

        <td style="padding:16px 20px;font-size:14px;">
          {{ $p->membership_type }}
        </td>

        <td style="padding:16px 20px;font-size:14px;font-weight:700;color:var(--accent);">
          ₱{{ number_format($total, 0) }}
        </td>

        <td style="padding:16px 20px;">
          <span style="display:inline-block;padding:4px 12px;border-radius:6px;font-size:12px;font-weight:600;
                       background:rgba(74,222,128,0.15);color:#4ade80;">
            {{ strtoupper($p->status ?? 'Paid') }}
          </span>
        </td>

        <td style="padding:16px 20px;">
          <a href="{{ route('member.receipt', $p->id) }}"
             style="color:var(--muted);text-decoration:none;margin-right:12px;" title="View Receipt">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
          </a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" style="padding:48px;text-align:center;color:var(--muted);">
          No payments recorded yet.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Receipt Info --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
  <div style="font-size:14px;font-weight:600;margin-bottom:12px;">Receipt Information</div>
  <ul style="list-style:none;display:grid;gap:8px;">
    <li style="font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px;">
      <span style="color:var(--accent);">•</span> Click the download icon to save a copy of your receipt.
    </li>
    <li style="font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px;">
      <span style="color:var(--accent);">•</span> Amounts shown include both Gym and Coaching fees where applicable.
    </li>
    <li style="font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px;">
      <span style="color:var(--accent);">•</span> Keep your Receipt IDs for any support inquiries.
    </li>
  </ul>
</div>

@endsection