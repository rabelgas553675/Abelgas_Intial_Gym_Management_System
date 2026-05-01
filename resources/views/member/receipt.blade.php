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
                border-radius:12px;padding:12px 18px;margin-bottom:20px;color:var(--success);">
      ✓ {{ session('success') }}
    </div>
  @endif

  <div class="card" style="padding:0;overflow:hidden;background:var(--surface);border:1px solid var(--border);">

    {{-- Header --}}
    <div style="background:var(--accent);padding:24px 32px;">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="font-family:'Bebas Neue',sans-serif;font-size:32px;color:#111;letter-spacing:3px;line-height:1;">IRONFORGE</div>
          <div style="font-size:11px;color:#444;letter-spacing:2px;text-transform:uppercase;font-weight:700;">Official Receipt</div>
        </div>
        <div style="text-align:right;">
          <div style="font-family:'Bebas Neue',sans-serif;font-size:20px;color:#111;letter-spacing:2px;">
            {{ $payment->receipt_number }}
          </div>
          <div style="font-size:12px;color:#333;font-weight:600;">{{ $payment->payment_date->format('F d, Y') }}</div>
        </div>
      </div>
    </div>

    {{-- Scissor Cut Line --}}
    <div style="height:1px;border-top:2px dashed var(--border);position:relative;margin:0;">
      <span style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);
                   background:var(--surface);padding:0 12px;color:var(--muted);font-size:16px;">✂</span>
    </div>

    {{-- Body --}}
    <div style="padding:28px 32px;">

      {{-- Member info --}}
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px;
                  padding-bottom:20px;border-bottom:1px solid var(--border);">
        @if(isset($member->photo) && $member->photo)
          <img src="{{ asset('storage/'.$member->photo) }}"
               style="width:52px;height:52px;border-radius:50%;object-fit:cover;
                      border:2px solid var(--accent);flex-shrink:0;"/>
        @else
          <div style="width:52px;height:52px;border-radius:50%;background:rgba(232,255,42,0.1);
                      border:2px solid var(--accent);display:flex;align-items:center;justify-content:center;
                      font-family:'Bebas Neue',sans-serif;font-size:20px;color:var(--accent);flex-shrink:0;">
            {{ strtoupper(substr($member->name ?? 'ME', 0, 2)) }}
          </div>
        @endif
        <div style="flex-grow:1;">
          <div style="font-size:17px;font-weight:700;color:#fff;">{{ $member->name }}</div>
          <div style="font-size:13px;color:var(--muted);margin-bottom:6px;">{{ $member->email }}</div>
          <span style="background:rgba(74,222,128,0.15);color:#4ade80;
                       border:1px solid rgba(74,222,128,0.3);font-size:11px;font-weight:600;
                       padding:2px 10px;border-radius:5px;">✓ PAID</span>
        </div>
      </div>

      {{-- Details grid --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:2px;margin-bottom:12px;font-weight:700;">Plan Details</div>
          @foreach([
            ['Fitness Plan', $payment->fitness_plan   ?? $member->fitness_plan],
            ['Duration',     $payment->membership_type ?? $member->membership_type],
            ['Start Date',   $member->start_date?->format('M d, Y')],
            ['End Date',     $member->end_date?->format('M d, Y')],
          ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;
                        border-bottom:1px solid rgba(255,255,255,0.04);">
              <span style="color:var(--muted);">{{ $label }}</span>
              <span style="font-weight:600;color:#fff;">{{ $value ?? '—' }}</span>
            </div>
          @endforeach
        </div>
        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:2px;margin-bottom:12px;font-weight:700;">Payment Info</div>
          @foreach([
            ['Receipt No.',  $payment->receipt_number],
            ['Method',       $payment->method ?? 'Cash'],
            ['Type',         'Gym Membership Fee'],
            ['Instructor',   $member->instructor?->name ?? 'None'],
          ] as [$label, $value])
            <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;
                        border-bottom:1px solid rgba(255,255,255,0.04);">
              <span style="color:var(--muted);">{{ $label }}</span>
              <span style="font-weight:600;color:#fff;">{{ $value ?? '—' }}</span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- ── Price calculation (consistent with select-plan prices) ── --}}
      @php
        $gymPrices = [
          'Monthly'   => 800,
          'Quarterly' => 2100,
          'Annually'  => 7500,
        ];
        $coachPrices = [
          'Monthly'   => 300,
          'Quarterly' => 1200,
          'Annually'  => 3600,
        ];

        $gymType   = $payment->membership_type   ?? $member->membership_type   ?? 'Monthly';
        $coachType = $payment->coach_membership_type
                      ?? $member->coach_membership_type
                      ?? null;

        $gymFee   = $gymPrices[$gymType]     ?? 0;
        $coachFee = 0;
        $hasCoach = $member->instructor_id && $coachType;

        if ($hasCoach) {
          $coachFee = $coachPrices[$coachType] ?? 0;
        }

        $totalPaid = $gymFee + $coachFee;
      @endphp

      {{-- Coach info banner (only if coach exists) --}}
      @if($hasCoach)
      <div style="background:rgba(96,165,250,0.06);border:1px solid rgba(96,165,250,0.2);
                  border-radius:10px;padding:12px 18px;margin-bottom:24px;
                  display:flex;align-items:center;gap:10px;">
        <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div style="font-size:12px;color:var(--muted);line-height:1.6;">
          A separate <strong style="color:#60a5fa;">coach subscription fee</strong> of
          <strong style="color:#60a5fa;">₱{{ number_format($coachFee, 0) }}</strong>
          has been allocated to your instructor
          <strong style="color:#fff;">{{ $member->instructor->name }}</strong>
          ({{ $coachType }} plan).
        </div>
      </div>
      @endif

      {{-- Breakdown box --}}
      <div style="background:rgba(232,255,42,0.04);border:1px solid var(--border);
                  border-radius:12px;padding:20px 24px;margin-bottom:24px;">

        {{-- Gym row --}}
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:14px;">
          <div>
            <div style="font-weight:600;color:#fff;">Gym Membership</div>
            <div style="font-size:12px;color:var(--muted);">{{ $gymType }} · {{ ['Monthly'=>'30 days','Quarterly'=>'90 days','Annually'=>'365 days'][$gymType] ?? '' }}</div>
          </div>
          <div style="font-weight:700;color:#fff;font-size:15px;">₱{{ number_format($gymFee, 2) }}</div>
        </div>

        {{-- Coach row (only if applicable) --}}
        @if($hasCoach)
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:14px;">
          <div>
            <div style="font-weight:600;color:#fff;">Coach Subscription</div>
            <div style="font-size:12px;color:var(--muted);">{{ $coachType }} · {{ $member->instructor->name }}</div>
          </div>
          <div style="font-weight:700;color:#fff;font-size:15px;">₱{{ number_format($coachFee, 2) }}</div>
        </div>
        @endif

        {{-- Grand total --}}
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:18px 0 4px;margin-top:4px;">
          <div>
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;
                        letter-spacing:1px;font-weight:700;">Total Fee Paid</div>
            <div style="font-size:12px;color:var(--muted);">Inclusive of all services</div>
          </div>
          <div style="font-family:'Bebas Neue',sans-serif;font-size:44px;
                      color:var(--accent);letter-spacing:2px;line-height:1;">
            ₱{{ number_format($totalPaid, 2) }}
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div style="border-top:1px dashed var(--border);padding-top:18px;text-align:center;">
        <div style="font-size:12px;color:var(--muted);">
          Thank you for choosing IRONFORGE! Keep this receipt for your records.
        </div>
        <div style="font-family:'Bebas Neue',sans-serif;font-size:14px;color:var(--accent);
                    letter-spacing:3px;margin-top:10px;opacity:0.6;">IRONFORGE GMS</div>
      </div>
    </div>
  </div>

  {{-- Action buttons --}}
  <div class="no-print" style="display:flex;gap:10px;margin-top:16px;justify-content:center;">
    <button onclick="window.print()" class="btn btn-primary" style="padding:12px 28px;">🖨 Print</button>
    <a href="{{ route('member.payments') }}" class="btn btn-secondary" style="padding:12px 28px;">Payment History</a>
    <a href="{{ route('member.dashboard') }}" class="btn btn-secondary" style="padding:12px 28px;">Dashboard</a>
  </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap');

@media print {
  body * { visibility: hidden !important; }
  #receipt-area, #receipt-area * { visibility: visible !important; }
  #receipt-area {
    position: fixed !important; top: 0 !important; left: 0 !important;
    width: 100% !important; max-width: 100% !important; padding: 20px !important;
  }
  .no-print { display: none !important; }
  :root {
    --bg: #fff !important; --surface: #fff !important;
    --border: #ddd !important; --text: #111 !important; --muted: #555 !important;
  }
}
</style>
@endsection