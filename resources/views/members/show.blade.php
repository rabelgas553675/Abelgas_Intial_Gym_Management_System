@extends('layouts.app')
@section('title', $member->name . ' – IRONFORGE')
@section('page_title', 'Member Details')

@section('content')

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;font-size:14px;margin-bottom:20px;">
  <a href="{{ route('members.index') }}"
     style="color:var(--accent);text-decoration:none;">Members</a>
  <span style="color:var(--muted);">/</span>
  <span style="color:var(--muted);">{{ strtoupper($member->name) }}</span>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start;">

  {{-- LEFT: Profile Card --}}
  <div class="card" style="padding:28px;text-align:center;">

    {{-- Photo --}}
    <div style="margin-bottom:16px;">
      @if($member->photo)
        <img src="{{ asset('storage/' . $member->photo) }}"
             alt="{{ $member->name }}"
             style="width:110px;height:110px;border-radius:50%;object-fit:cover;
                    border:3px solid var(--accent);margin:0 auto;display:block;"/>
      @else
        <div style="width:110px;height:110px;border-radius:50%;background:rgba(232,255,42,0.1);
                    border:3px solid var(--accent);display:flex;align-items:center;
                    justify-content:center;margin:0 auto;font-family:'Bebas Neue',sans-serif;
                    font-size:36px;color:var(--accent);">
          {{ strtoupper(substr($member->name,0,2)) }}
        </div>
      @endif
    </div>

    {{-- Name & Email --}}
    <div style="font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:1px;margin-bottom:4px;">
      {{ strtoupper($member->name) }}
    </div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:4px;">{{ $member->email }}</div>
    <div style="font-size:12px;color:var(--muted);margin-bottom:16px;">
      Member #{{ str_pad($member->id, 5, '0', STR_PAD_LEFT) }}
    </div>

    {{-- Status --}}
    <div style="margin-bottom:16px;">
      <span style="display:block;width:100%;padding:10px;border-radius:var(--radius);
                   font-weight:600;font-size:14px;letter-spacing:1px;
        {{ $member->status === 'Active'
          ? 'background:rgba(74,222,128,0.15);color:var(--success);border:1px solid rgba(74,222,128,0.3);'
          : 'background:rgba(248,113,113,0.15);color:var(--danger);border:1px solid rgba(248,113,113,0.3);' }}
      ">
        {{ $member->status }}
      </span>
    </div>

    {{-- Action Buttons --}}
    @if(auth()->user()->isAdmin())
    <a href="{{ route('members.edit', $member) }}"
       class="btn btn-primary"
       style="width:100%;justify-content:center;margin-bottom:8px;">
      ✏ Edit Member
    </a>
    @endif
    <a href="{{ route('members.index') }}"
       class="btn btn-secondary"
       style="width:100%;justify-content:center;">
      ← Back to List
    </a>
  </div>

  {{-- RIGHT: Details + Payment History --}}
  <div>

    {{-- Member Information Card --}}
    <div class="card" style="padding:24px;margin-bottom:20px;">
      <div style="font-size:15px;font-weight:600;margin-bottom:20px;
                  display:flex;align-items:center;gap:8px;">
        👤 Member Information
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">Phone</div>
          <div style="font-size:14px;font-weight:500;">{{ $member->phone ?? '—' }}</div>
        </div>

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">Gender</div>
          <div style="font-size:14px;font-weight:500;">{{ $member->gender ?? '—' }}</div>
        </div>

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">Birthdate</div>
          <div style="font-size:14px;font-weight:500;">
            {{ $member->birthdate ? \Carbon\Carbon::parse($member->birthdate)->format('M d, Y') : '—' }}
          </div>
        </div>

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">Address</div>
          <div style="font-size:14px;font-weight:500;">{{ $member->address ?? '—' }}</div>
        </div>

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">Membership</div>
          <div style="font-size:14px;font-weight:500;">{{ $member->membership_type ?? '—' }}</div>
        </div>

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">Fee</div>
          <div style="font-size:14px;font-weight:500;color:var(--success);">
            ₱{{ number_format($member->fee ?? 0, 2) }}
          </div>
        </div>

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">Start Date</div>
          <div style="font-size:14px;font-weight:500;">
            {{ $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('M d, Y') : '—' }}
          </div>
        </div>

        <div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;
                      letter-spacing:1.5px;margin-bottom:4px;">End Date</div>
          @php
            $end = $member->end_date ? \Carbon\Carbon::parse($member->end_date) : null;
          @endphp
          <div style="font-size:14px;font-weight:500;
            {{ $end && $end->isPast() ? 'color:var(--danger);' : ($end && $end->diffInDays(now()) <= 7 ? 'color:var(--warning);' : '') }}
          ">
            {{ $end ? $end->format('M d, Y') : '—' }}
          </div>
        </div>

      </div>
    </div>

    {{-- Payment History Card --}}
    <div class="card" style="padding:24px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div style="font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px;">
          💳 Payment History
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">
          + Add Payment
        </a>
        @endif
      </div>

      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          @forelse($member->payments ?? [] as $i => $payment)
          <tr>
            <td style="color:var(--muted)">{{ $i + 1 }}</td>
            <td style="color:var(--muted)">
              {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
            </td>
            <td style="color:var(--success);font-weight:600;">
              ₱{{ number_format($payment->amount, 2) }}
            </td>
            <td>
              <span class="badge" style="background:var(--surface3);color:var(--text);
                                         border:1px solid var(--border);">
                {{ $payment->method }}
              </span>
            </td>
            <td style="color:var(--muted)">{{ $payment->notes ?? '—' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align:center;color:var(--muted);padding:24px;">
              No payments recorded.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection