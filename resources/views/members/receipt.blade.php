@extends('layouts.app')
@section('title', 'Member Receipt – IRONFORGE')
@section('page_title', 'Registration Receipt')

@section('topbar_actions')
  <button onclick="printReceipt()" class="btn btn-primary">
    🖨 Print Receipt
  </button>
  <a href="{{ route('members.index') }}" class="btn btn-secondary">
    ← Back to Members
  </a>
@endsection

@section('content')

{{-- Success alert --}}
@if(session('success'))
  <div class="alert alert-success" style="margin-bottom:20px;">
    ✓ {{ session('success') }}
  </div>
@endif

{{-- Receipt wrapper — this gets printed --}}
<div id="receipt-area" style="max-width:680px;margin:0 auto;">

  {{-- Receipt Card --}}
  <div class="card" style="padding:0;overflow:visible;">

    {{-- Header --}}
    <div style="background:var(--accent);padding:28px 36px;border-radius:var(--radius) var(--radius) 0 0;">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <div style="font-family:'Bebas Neue',sans-serif;font-size:32px;color:#111;letter-spacing:3px;line-height:1;">
            IRONFORGE
          </div>
          <div style="font-size:11px;color:#333;letter-spacing:2px;text-transform:uppercase;margin-top:2px;">
            Gym Management System
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:11px;color:#333;letter-spacing:1px;text-transform:uppercase;">
            Official Receipt
          </div>
          <div style="font-family:'Bebas Neue',sans-serif;font-size:22px;color:#111;letter-spacing:2px;">
            #{{ str_pad($member->id, 6, '0', STR_PAD_LEFT) }}
          </div>
          <div style="font-size:12px;color:#444;margin-top:2px;">
            {{ now()->format('F d, Y h:i A') }}
          </div>
        </div>
      </div>
    </div>

    {{-- Divider with scissor --}}
    <div style="position:relative;height:24px;background:var(--surface2);display:flex;align-items:center;justify-content:center;">
      <div style="position:absolute;left:0;right:0;top:50%;border-top:2px dashed var(--border);"></div>
      <div style="background:var(--surface2);padding:0 12px;position:relative;z-index:1;font-size:14px;">✂</div>
    </div>

    {{-- Body --}}
    <div style="padding:28px 36px;">

      {{-- Member Info Header --}}
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border);">
        @if($member->photo)
          <img src="{{ asset('storage/' . $member->photo) }}"
               style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--accent);"
               alt="{{ $member->name }}"/>
        @else
          <div style="width:60px;height:60px;border-radius:50%;background:rgba(232,255,42,0.1);
                      border:2px solid var(--accent);display:flex;align-items:center;justify-content:center;
                      font-family:'Bebas Neue',sans-serif;font-size:22px;color:var(--accent);flex-shrink:0;">
            {{ strtoupper(substr($member->name, 0, 2)) }}
          </div>
        @endif
        <div>
          <div style="font-size:18px;font-weight:600;color:var(--text);">{{ $member->name }}</div>
          <div style="font-size:13px;color:var(--muted);">Member #{{ str_pad($member->id, 6, '0', STR_PAD_LEFT) }}</div>
          <div style="margin-top:4px;">
            <span style="background:rgba(74,222,128,0.15);color:var(--success);border:1px solid rgba(74,222,128,0.3);
                         font-size:11px;font-weight:600;padding:2px 10px;border-radius:5px;">
              ✓ ACTIVE
            </span>
          </div>
        </div>
      </div>

      {{-- Two column details --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

        {{-- Left: Personal Info --}}
        <div>
          <div style="font-size:10px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;font-weight:600;">
            Personal Information
          </div>
          <table style="width:100%;border-collapse:collapse;">
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Email</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">{{ $member->email }}</td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Phone</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">{{ $member->phone ?? '—' }}</td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Gender</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">{{ $member->gender ?? '—' }}</td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Birthdate</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">
                {{ $member->birthdate ? \Carbon\Carbon::parse($member->birthdate)->format('M d, Y') : '—' }}
              </td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Address</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">{{ $member->address ?? '—' }}</td>
            </tr>
          </table>
        </div>

        {{-- Right: Membership Info --}}
        <div>
          <div style="font-size:10px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;font-weight:600;">
            Membership Details
          </div>
          <table style="width:100%;border-collapse:collapse;">
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Plan</td>
              <td style="font-size:12px;padding:5px 0;border:none;text-align:right;">
                <span style="
                  @if($member->membership_type=='Monthly')     background:rgba(96,165,250,0.15);color:#60a5fa;
                  @elseif($member->membership_type=='Quarterly') background:rgba(34,211,238,0.15);color:#22d3ee;
                  @elseif($member->membership_type=='Semi-Annual') background:rgba(251,191,36,0.15);color:#fbbf24;
                  @elseif($member->membership_type=='Annual')  background:rgba(74,222,128,0.15);color:#4ade80;
                  @endif
                  padding:2px 10px;border-radius:5px;font-size:11px;font-weight:600;">
                  {{ $member->membership_type }}
                </span>
              </td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Start Date</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">
                {{ \Carbon\Carbon::parse($member->start_date)->format('M d, Y') }}
              </td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">End Date</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">
                {{ \Carbon\Carbon::parse($member->end_date)->format('M d, Y') }}
              </td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Duration</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">
                {{ \Carbon\Carbon::parse($member->start_date)->diffInDays(\Carbon\Carbon::parse($member->end_date)) }} days
              </td>
            </tr>
            <tr>
              <td style="font-size:12px;color:var(--muted);padding:5px 0;border:none;">Registered By</td>
              <td style="font-size:12px;color:var(--text);padding:5px 0;border:none;text-align:right;">
                {{ auth()->user()->name }}
              </td>
            </tr>
          </table>
        </div>
      </div>

      {{-- Payment Total Box --}}
      <div style="background:rgba(232,255,42,0.06);border:1px solid rgba(232,255,42,0.2);
                  border-radius:var(--radius);padding:20px 24px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <div>
            <div style="font-size:11px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-bottom:4px;">
              Total Amount Due
            </div>
            <div style="font-size:13px;color:var(--muted);">
              {{ $member->membership_type }} Membership Plan
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-family:'Bebas Neue',sans-serif;font-size:40px;color:var(--accent);letter-spacing:2px;line-height:1;">
              ₱{{ number_format($member->fee, 2) }}
            </div>
          </div>
        </div>
      </div>

      {{-- Divider --}}
      <div style="border-top:1px dashed var(--border);margin-bottom:20px;"></div>

      {{-- Footer note --}}
      <div style="text-align:center;">
        <div style="font-size:12px;color:var(--muted);margin-bottom:6px;">
          Thank you for joining IRONFORGE! This serves as your official membership receipt.
        </div>
        <div style="font-size:11px;color:var(--muted);">
          Keep this receipt for your records. For concerns, contact the front desk.
        </div>
        <div style="margin-top:16px;font-family:'Bebas Neue',sans-serif;font-size:16px;
                    color:var(--accent);letter-spacing:3px;opacity:0.6;">
          IRONFORGE GMS
        </div>
      </div>

    </div>

  </div>

  {{-- Action buttons below receipt --}}
  <div style="display:flex;gap:12px;margin-top:20px;justify-content:center;">
    <button onclick="printReceipt()" class="btn btn-primary" style="padding:12px 32px;">
      🖨 Print Receipt
    </button>
    <a href="{{ route('members.create') }}" class="btn btn-secondary" style="padding:12px 32px;">
      + Add Another Member
    </a>
    <a href="{{ route('members.show', $member) }}" class="btn btn-secondary" style="padding:12px 32px;">
      View Member Profile
    </a>
  </div>

</div>

{{-- Print CSS --}}
<style>
@media print {
  /* Hide everything except the receipt */
  body * { visibility: hidden !important; }
  #receipt-area, #receipt-area * { visibility: visible !important; }
  #receipt-area {
    position: fixed !important;
    top: 0 !important; left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 20px !important;
  }
  /* Hide action buttons when printing */
  #receipt-area > div:last-child { display: none !important; }
  /* Override dark theme for print */
  :root {
    --bg: #ffffff !important;
    --surface: #ffffff !important;
    --surface2: #f5f5f5 !important;
    --border: #dddddd !important;
    --text: #111111 !important;
    --muted: #555555 !important;
    --accent: #c8ff00 !important;
    --success: #16a34a !important;
    --radius: 10px !important;
  }
  body {
    background: #ffffff !important;
    color: #111 !important;
  }
  .card {
    border: 1px solid #ddd !important;
    box-shadow: none !important;
  }
}
</style>

<script>
function printReceipt() {
  window.print();
}

// Auto-trigger print dialog after 1 second if coming from registration
@if(session('success'))
  setTimeout(() => {
    if (confirm('Print this receipt now?')) {
      window.print();
    }
  }, 800);
@endif
</script>

@endsection