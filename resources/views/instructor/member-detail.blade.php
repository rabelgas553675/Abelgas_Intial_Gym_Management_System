@extends('layouts.instructor')
@section('title', 'Member Detail – IRONFORGE')
@section('active', 'dashboard')

@section('content')

{{-- Page Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
  <div>
    <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">Member Detail</h1>
    <p style="color:var(--muted);font-size:14px;">Viewing full profile and payment history</p>
  </div>
  <a href="{{ route('instructor.dashboard') }}"
     style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;
            border-radius:10px;background:var(--surface2);border:1px solid var(--border);
            color:var(--muted);font-size:13px;font-weight:600;text-decoration:none;transition:all 0.15s;"
     onmouseover="this.style.color='var(--text)';this.style.borderColor='var(--accent)'"
     onmouseout="this.style.color='var(--muted)';this.style.borderColor='var(--border)'">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 5l-7 7 7 7"/>
    </svg>
    Back to Dashboard
  </a>
</div>

{{-- Main Grid --}}
<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

  {{-- LEFT: Profile Card --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Avatar + Name --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;
                padding:28px;text-align:center;">

      {{-- Avatar --}}
      <div style="margin:0 auto 16px;width:88px;height:88px;border-radius:50%;overflow:hidden;
                  border:3px solid rgba(200,255,0,0.3);background:rgba(200,255,0,0.08);
                  display:flex;align-items:center;justify-content:center;">
        @if($member->photo)
          <img src="{{ asset('storage/'.$member->photo) }}"
               style="width:100%;height:100%;object-fit:cover;"/>
        @else
          <span style="font-family:'Bebas Neue',sans-serif;font-size:30px;color:var(--accent);">
            {{ strtoupper(substr($member->name,0,2)) }}
          </span>
        @endif
      </div>

      <div style="font-size:20px;font-weight:800;margin-bottom:4px;">{{ $member->name }}</div>
      <div style="font-size:13px;color:var(--muted);margin-bottom:14px;">{{ $member->email }}</div>

      {{-- Status badge --}}
      @php
        $isExpired  = $member->isExpired();
        $isExpiring = $member->isDueWithinDays(7) && !$isExpired;
        $statusLabel = $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active');
        $statusColor = $isExpired ? '#f87171' : ($isExpiring ? '#fbbf24' : '#4ade80');
        $statusBg    = $isExpired ? 'rgba(248,113,113,0.15)' : ($isExpiring ? 'rgba(251,191,36,0.15)' : 'rgba(74,222,128,0.15)');
      @endphp
      <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 16px;
                   border-radius:100px;font-size:12px;font-weight:700;
                   background:{{ $statusBg }};color:{{ $statusColor }};
                   border:1px solid {{ $statusColor }}33;">
        <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};display:inline-block;"></span>
        {{ $statusLabel }}
      </span>
    </div>

    {{-- Personal Info --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;">
      <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;
                  letter-spacing:2px;margin-bottom:16px;">Personal Info</div>

      <div style="display:flex;flex-direction:column;gap:10px;">

        <div style="display:flex;align-items:center;gap:12px;padding:11px 14px;
                    background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="width:30px;height:30px;border-radius:8px;background:rgba(96,165,250,0.1);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="14" height="14" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Phone</div>
            <div style="font-size:13px;font-weight:600;">{{ $member->phone ?? '—' }}</div>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;padding:11px 14px;
                    background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="width:30px;height:30px;border-radius:8px;background:rgba(255,107,53,0.1);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="14" height="14" fill="none" stroke="#ff6b35" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Gender</div>
            <div style="font-size:13px;font-weight:600;">{{ $member->gender ?? '—' }}</div>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;padding:11px 14px;
                    background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="width:30px;height:30px;border-radius:8px;background:rgba(251,191,36,0.1);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="14" height="14" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24">
              <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
              <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Birthdate</div>
            <div style="font-size:13px;font-weight:600;">{{ $member->birthdate?->format('M d, Y') ?? '—' }}</div>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;padding:11px 14px;
                    background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="width:30px;height:30px;border-radius:8px;background:rgba(74,222,128,0.1);
                      display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="14" height="14" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div>
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Address</div>
            <div style="font-size:13px;font-weight:600;">{{ $member->address ?? '—' }}</div>
          </div>
        </div>

      </div>
    </div>

  </div>

  {{-- RIGHT: Membership + Payments --}}
  <div style="display:flex;flex-direction:column;gap:20px;">

    {{-- Membership Details --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:26px;">
      <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;
                  letter-spacing:2px;margin-bottom:20px;">Membership Details</div>

      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:16px;">
        <div style="padding:16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Fitness Plan</div>
          <div style="font-size:15px;font-weight:700;color:var(--accent);">{{ $member->fitness_plan ?? '—' }}</div>
        </div>
        <div style="padding:16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Duration</div>
          <div style="font-size:15px;font-weight:700;">{{ $member->membership_type ?? '—' }}</div>
        </div>
        <div style="padding:16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Fee</div>
          <div style="font-size:15px;font-weight:700;color:var(--accent);">₱{{ number_format($member->fee ?? 0, 2) }}</div>
        </div>
        <div style="padding:16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Start Date</div>
          <div style="font-size:15px;font-weight:700;">{{ $member->start_date?->format('M d, Y') ?? '—' }}</div>
        </div>
        <div style="padding:16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">End Date</div>
          <div style="font-size:15px;font-weight:700;color:{{ $isExpired ? 'var(--danger)' : 'var(--text)' }};">
            {{ $member->end_date?->format('M d, Y') ?? '—' }}
          </div>
        </div>
        <div style="padding:16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Status</div>
          <span style="display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:700;color:{{ $statusColor }};">
            <span style="width:6px;height:6px;border-radius:50%;background:{{ $statusColor }};display:inline-block;"></span>
            {{ $statusLabel }}
          </span>
        </div>
      </div>

      {{-- Expiry warning --}}
      @if($isExpired)
        <div style="padding:12px 16px;background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);
                    border-radius:10px;font-size:13px;color:var(--danger);display:flex;align-items:center;gap:8px;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          Membership expired on {{ $member->end_date->format('M d, Y') }}.
        </div>
      @elseif($isExpiring)
        <div style="padding:12px 16px;background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.2);
                    border-radius:10px;font-size:13px;color:var(--warning);display:flex;align-items:center;gap:8px;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          Membership expiring in {{ (int) now()->diffInDays($member->end_date) }} days.
        </div>
      @endif
    </div>

    {{-- Payment History --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);
                  display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:16px;font-weight:700;">Payment History</div>
        <span style="font-size:12px;color:var(--muted);">{{ $payments->count() }} transaction(s)</span>
      </div>
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--surface2);">
            <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Receipt</th>
            <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Plan</th>
            <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Amount</th>
            <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Date</th>
            <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $p)
          <tr style="border-top:1px solid var(--border);">
            <td style="padding:14px 20px;font-family:monospace;font-size:11px;color:var(--muted);">
              {{ $p->receipt_number }}
            </td>
            <td style="padding:14px 20px;font-size:13px;font-weight:500;">
              {{ $p->fitness_plan }} / {{ $p->membership_type }}
            </td>
            <td style="padding:14px 20px;font-size:14px;font-weight:700;color:var(--accent);">
              ₱{{ number_format($p->amount, 2) }}
            </td>
            <td style="padding:14px 20px;font-size:13px;color:var(--muted);">
              {{ $p->payment_date->format('M d, Y') }}
            </td>
            <td style="padding:14px 20px;">
              <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;
                           border-radius:6px;font-size:11px;font-weight:700;
                           background:rgba(74,222,128,0.15);color:#4ade80;">
                <span style="width:5px;height:5px;border-radius:50%;background:#4ade80;display:inline-block;"></span>
                {{ $p->status }}
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="padding:40px;text-align:center;color:var(--muted);font-size:14px;">
              No payments on record.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

@endsection