@extends('layouts.admin')
@section('title', 'Payment Transactions – IRONFORGE')
@section('page_title', 'Payments')
@section('active_nav', 'payments')

@section('content')

<style>
  select.form-control {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px !important;
    cursor: pointer;
  }
  select.form-control option {
    background-color: var(--surface);
    color: white;
  }
  select.form-control:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
  }
</style>

<div style="margin-bottom:28px;">
  <h1 style="font-size:30px;font-weight:700;margin-bottom:4px;">Payment Transactions</h1>
  <p style="color:var(--muted);font-size:14px;">Record and manage all member payment transactions.</p>
</div>

{{-- Summary Cards --}}
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Transactions</div>
      <div class="stat-value">{{ $totalCount }}</div>
      <div class="stat-sub">All time</div>
    </div>
  </div>
  <div class="stat-card green">
    <div>
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:26px;color:var(--warning);">
        ₱{{ number_format($thisMonth, 0) }}
      </div>
      <div class="stat-sub">Current month collections</div>
    </div>
  </div>
  <div class="stat-card blue">
    <div>
      <div class="stat-label">Total Collected</div>
      <div class="stat-value" style="font-size:26px;color:var(--success);">
        ₱{{ number_format($totalCollected, 0) }}
      </div>
      <div class="stat-sub">All time revenue</div>
    </div>
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
      <div class="alert alert-success" style="margin-bottom:15px;font-size:13px;">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger" style="margin-bottom:15px;font-size:13px;">✕ {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}">
      @csrf

      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Member</label>
        <select name="member_id" class="form-control" required>
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

      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Payment Date</label>
        <input type="date" name="payment_date" class="form-control"
               value="{{ old('payment_date', date('Y-m-d')) }}" required/>
        @error('payment_date')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group" style="margin-bottom:20px;">
        <label class="form-label">Method</label>
        <select name="method" class="form-control" required>
          <option value="" disabled selected>— Select Method —</option>
          @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
            <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
        @error('method')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-primary"
              style="width:100%;justify-content:center;padding:12px;font-weight:600;">
        ✓ Record Payment
      </button>
    </form>
  </div>

  {{-- RIGHT: Transactions Table --}}
  <div>
    <div class="section-header">
      <div class="section-title">All Transactions</div>
      <div style="font-size:12px;color:var(--muted);">Sorted by most recent</div>
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
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $payment)
          <tr>
            <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--muted);">
              {{ $payment->receipt_number ?? 'TXN-'.str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                @if($payment->member?->user?->photo)
                  <img src="{{ asset('storage/'.$payment->member->user->photo) }}"
                       style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid var(--border);"/>
                @else
                  <div style="width:30px;height:30px;border-radius:50%;background:rgba(200,255,0,0.08);
                              border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                              justify-content:center;font-size:10px;font-weight:700;
                              color:var(--accent);flex-shrink:0;">
                    {{ strtoupper(substr($payment->member->name ?? '?', 0, 2)) }}
                  </div>
                @endif
                <div>
                  <div style="font-weight:600;">{{ $payment->member->name ?? '—' }}</div>
                  <div style="font-size:11px;color:var(--muted);">{{ $payment->member->email ?? '' }}</div>
                </div>
              </div>
            </td>
            <td style="font-weight:700;color:var(--accent);">
              ₱{{ number_format($payment->amount, 0) }}
            </td>
            <td style="color:var(--muted);font-size:13px;">
              {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
            </td>
            <td>
              <span style="background:var(--surface2);border:1px solid var(--border);
                           padding:3px 10px;border-radius:6px;font-size:12px;">
                {{ $payment->method ?? 'Cash' }}
              </span>
            </td>
            <td>
              <span class="badge badge-paid">Paid</span>
            </td>
            <td>
              <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                    onsubmit="return confirm('Delete this transaction?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align:center;color:var(--muted);padding:48px;">
              No transactions recorded yet.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>

      {{-- Table Footer Summary --}}
      @if($payments->count() > 0)
      <div style="padding:14px 18px;border-top:1px solid var(--border);
                  display:flex;justify-content:space-between;align-items:center;
                  background:var(--surface2);">
        <span style="font-size:12px;color:var(--muted);">
          Total Transactions: <strong style="color:var(--text);">{{ $totalCount }}</strong>
        </span>
        <span style="font-size:13px;color:var(--muted);">
          Total Collected: <strong style="color:var(--accent);">₱{{ number_format($totalCollected, 0) }}</strong>
        </span>
      </div>
      @endif
    </div>
  </div>
</div>

@endsection