@extends('layouts.staff')
@section('title', 'Payment Transactions – IRONFORGE')
@section('page_title', 'Payment Transactions')
@section('active_nav', 'staff.payments')

@section('content')

<style>
  .custom-select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    background-size: 14px !important;
    padding-right: 40px !important;
    cursor: pointer;
  }
  .custom-select option { background-color: #1a1a1a; color: white; }

  /* Hide native calendar icon, keep input clickable */
  input[type="date"]::-webkit-calendar-picker-indicator { opacity:0;width:0;padding:0;margin:0; }
  input[type="date"] { color-scheme: dark; }
</style>

<div style="margin-bottom:28px;">
  <h1 style="font-size:30px;font-weight:700;margin-bottom:4px;">Payment Transactions</h1>
  <p style="color:var(--muted);font-size:14px;">Record and view all member payment records.</p>
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

<div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">

  {{-- LEFT: Record Payment Form --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;">
    <div style="font-size:15px;font-weight:700;margin-bottom:20px;padding-bottom:14px;
                border-bottom:1px solid var(--border);">
      + Record Payment
    </div>

    @if(session('success'))
      <div class="alert alert-success" style="margin-bottom:16px;padding:10px;font-size:13px;">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger" style="margin-bottom:16px;padding:10px;font-size:13px;">✕ {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}">
      @csrf

      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Member</label>
        <select name="member_id" class="form-control custom-select" required>
          <option value="" disabled selected>— Select Member —</option>
          @foreach($members as $member)
            <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
              {{ $member->name }}
            </option>
          @endforeach
        </select>
        @error('member_id')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Amount (₱)</label>
        <input type="number" name="amount" class="form-control"
               step="0.01" min="0" placeholder="0.00"
               value="{{ old('amount') }}" required/>
        @error('amount')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      {{-- Payment Date with custom visible calendar icon --}}
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Payment Date</label>
        <div style="position:relative;display:flex;">
          <input type="date" name="payment_date" id="staff_payment_date" class="form-control"
                 value="{{ old('payment_date', date('Y-m-d')) }}" required
                 style="padding-right:48px;flex:1;border-radius:8px;"/>
          <button type="button"
                  onclick="document.getElementById('staff_payment_date').showPicker()"
                  title="Open calendar"
                  style="position:absolute;right:0;top:0;bottom:0;width:44px;
                         background:rgba(200,255,0,0.10);
                         border:none;border-left:1px solid var(--border);
                         border-radius:0 8px 8px 0;
                         cursor:pointer;display:flex;align-items:center;justify-content:center;
                         transition:background .15s;"
                  onmouseover="this.style.background='rgba(200,255,0,0.22)'"
                  onmouseout="this.style.background='rgba(200,255,0,0.10)'">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"
                 stroke="var(--accent)" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8"  y1="2" x2="8"  y2="6"/>
              <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
          </button>
        </div>
        @error('payment_date')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Method</label>
        <select name="method" class="form-control custom-select" required>
          <option value="" disabled selected>— Select Method —</option>
          @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
            <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
        @error('method')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group" style="margin-bottom:20px;">
        <label class="form-label">Notes (optional)</label>
        <textarea name="notes" class="form-control" rows="3"
                  placeholder="Any additional notes...">{{ old('notes') }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary"
              style="width:100%;justify-content:center;padding:12px;font-weight:700;">
        ✓ Record Payment
      </button>
    </form>
  </div>

  {{-- RIGHT: Transactions Table --}}
  <div>
    <div class="section-header">
      <div class="section-title">All Transactions</div>
    </div>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Transaction ID</th>
            <th>Member</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Method</th>
            <th>Status</th>
            @if(auth()->user()->role === 'admin')
            <th>Action</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $p)
          @php
            // FIX: photo lives on users table — resolve via member->user->photo
            $pPhoto = $p->member?->user?->photo ?? $p->member?->photo ?? null;
          @endphp
          <tr>
            <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--muted);">
              {{ $p->receipt_number ?? 'TXN-'.str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                @if($pPhoto)
                  <img src="{{ asset('storage/'.$pPhoto) }}"
                       style="width:32px;height:32px;border-radius:50%;object-fit:cover;
                              flex-shrink:0;border:1px solid rgba(200,255,0,0.2);"/>
                @else
                  <div style="width:32px;height:32px;border-radius:50%;
                              background:rgba(200,255,0,0.08);border:1px solid rgba(200,255,0,0.15);
                              display:flex;align-items:center;justify-content:center;
                              font-size:10px;font-weight:700;color:var(--accent);flex-shrink:0;">
                    {{ strtoupper(substr($p->member?->name ?? '?', 0, 2)) }}
                  </div>
                @endif
                <span style="font-weight:600;">{{ $p->member?->name ?? '—' }}</span>
              </div>
            </td>
            <td style="font-weight:700;color:var(--accent);">₱{{ number_format($p->amount, 0) }}</td>
            <td style="color:var(--muted);">
              {{ $p->payment_date instanceof \Carbon\Carbon ? $p->payment_date->format('M d, Y') : \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') }}
            </td>
            <td>
              <span style="background:var(--surface2);border:1px solid var(--border);
                           padding:3px 10px;border-radius:6px;font-size:12px;">
                {{ $p->method ?? 'Cash' }}
              </span>
            </td>
            <td>
              <span class="badge badge-{{ strtolower($p->status ?? 'paid') }}">{{ $p->status ?? 'Paid' }}</span>
            </td>
            @if(auth()->user()->role === 'admin')
            <td>
              <form method="POST" action="{{ route('payments.destroy', $p) }}"
                    onsubmit="return confirm('Delete this transaction?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
              </form>
            </td>
            @endif
          </tr>
          @empty
          <tr>
            <td colspan="{{ auth()->user()->role === 'admin' ? '7' : '6' }}"
                style="text-align:center;color:var(--muted);padding:48px;">
              No transactions yet.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>

      @if($payments->hasPages())
      <div style="padding:16px 18px;border-top:1px solid var(--border);">
        {{ $payments->links() }}
      </div>
      @endif
    </div>
  </div>
</div>

@endsection