@extends('layouts.member')
@section('title', 'Receipt – IRONFORGE')
@section('page_title', 'Payment Receipt')

@section('topbar_actions')
  <button onclick="window.print()" class="btn btn-primary">🖨 Print Receipt</button>
  <a href="{{ route('member.dashboard') }}" class="btn btn-secondary">← Dashboard</a>
@endsection

@section('content')
<div id="receipt-area" style="max-width:660px;margin:0 auto;">

  @if(session('success'))
    <div style="background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.3);
                border-radius:var(--radius);padding:12px 18px;margin-bottom:20px;color:var(--success);">
      ✓ {{ session('success') }}
    </div>
  @endif

  <div class="card" style="padding:0;overflow:hidden;">
    {{-- Header --}}
    <div style="background:var(--accent);padding:24px 32px;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="font-family:'Bebas Neue',sans-serif;font-size:28px;color:#111;letter-spacing:3px;">IRONFORGE</div>
          <div style="font-size:11px;color:#444;letter-spacing:2px;text-transform:uppercase;">Official Receipt</div>
        </div>
        <div style="text-align:right;">
          <div style="font-family:'Bebas Neue',sans-serif;font-size:20px;color:#111;letter-spacing:2px;">
            {{ $payment->receipt_number }}
          </div>
          <div style="font-size:12px;color:#555;">{{ $payment->payment_date->format('F d, Y') }}</div>
        </div>
      </div>
    </div>

    {{-- Scissor line --}}
    <div style="height:1px;border-top:2px dashed var(--border);margin:0;position:relative;">
      <span style="position:absolute;left:50%;transform:translateX(-50%) translateY(-50%);
                   background:var(--surface);padding:0 8px;color:var(--muted);font-size:14px;">✂</span>
    </div>

    {{-- Body --}}
    <div style="padding:28px 32px;">

      {{-- Member info --}}
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;
                  padding-bottom:20px;border-bottom:1px solid var(--border);">
        <div style="width:52px;height:52px;border-radius:50%;background:rgba(232,255,42,0.1);
                    border:2px solid var(--accent);display:flex;align-items:center;justify-content:center;
                    font-family:'Bebas Neue',sans-serif;font-size:20px;color:var(--accent);flex-shrink:0;">
          {{ strtoupper(substr($member->name ?? 'ME', 0, 2)) }}
        </div>
        <div>
          <div style="font-size:17px;font-weight:600;">{{ $member->name }}</div>
          <div style="font-size:13px;color:var(--muted);">{{ $member->email }}</div>
          <span style="background:rgba(74,222,128,0.15);color:var(--success);border:1px solid rgba(74,222,128,0.3);
                       font-size:11px;font-weight:600;padding:2px 10px;border-radius:5px;">✓ PAID</span>
        </div>
      </div>

      {{-- Details grid --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:10px;font-weight:600;">Plan Details</div>
          @foreach([
            ['Fitness Plan', $payment->fitness_plan],
            ['Duration', $payment->membership_type],
            ['Start Date', $member->start_date?->format('M d, Y')],
            ['End Date', $member->end_date?->format('M d, Y')],
          ] as [$label, $value])
          <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;">
            <span style="color:var(--muted);">{{ $label }}</span>
            <span>{{ $value ?? '—' }}</span>
          </div>
          @endforeach
        </div>
        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:10px;font-weight:600;">Payment Info</div>
          @foreach([
            ['Method', $payment->method],
            ['Status', $payment->status],
            ['Instructor', $member->instructor?->name ?? 'None'],
          ] as [$label, $value])
          <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;">
            <span style="color:var(--muted);">{{ $label }}</span>
            <span>{{ $value ?? '—' }}</span>
          </div>
          @endforeach
        </div>
      </div>

      {{-- Total --}}
      <div style="background:rgba(232,255,42,0.06);border:1px solid rgba(232,255,42,0.2);
                  border-radius:var(--radius);padding:18px 24px;display:flex;
                  align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Total Amount</div>
          <div style="font-size:13px;color:var(--muted);margin-top:2px;">{{ $payment->membership_type }} Plan</div>
        </div>
        <div style="font-family:'Bebas Neue',sans-serif;font-size:40px;color:var(--accent);letter-spacing:2px;">
          ₱{{ number_format($payment->amount, 2) }}
        </div>
      </div>

      {{-- Footer --}}
      <div style="border-top:1px dashed var(--border);padding-top:18px;text-align:center;">
        <div style="font-size:12px;color:var(--muted);">Thank you for choosing IRONFORGE! Keep this receipt for your records.</div>
        <div style="font-family:'Bebas Neue',sans-serif;font-size:14px;color:var(--accent);
                    letter-spacing:3px;margin-top:10px;opacity:0.6;">IRONFORGE GMS</div>
      </div>
    </div>
  </div>

  {{-- Buttons --}}
  <div style="display:flex;gap:10px;margin-top:16px;justify-content:center;">
    <button onclick="window.print()" class="btn btn-primary" style="padding:12px 28px;">🖨 Print</button>
    <a href="{{ route('member.payments') }}" class="btn btn-secondary" style="padding:12px 28px;">Payment History</a>
    <a href="{{ route('member.select-plan') }}" class="btn btn-secondary" style="padding:12px 28px;">New Plan</a>
  </div>
</div>

<style>
@media print {
  body * { visibility: hidden !important; }
  #receipt-area, #receipt-area * { visibility: visible !important; }
  #receipt-area {
    position: fixed !important; top: 0 !important; left: 0 !important;
    width: 100% !important; max-width: 100% !important; padding: 20px !important;
  }
  #receipt-area > div:last-child { display: none !important; }
  :root { --bg:#fff !important; --surface:#fff !important; --surface2:#f5f5f5 !important;
          --border:#ddd !important; --text:#111 !important; --muted:#555 !important; }
}
</style>
@endsection