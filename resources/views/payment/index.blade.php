@extends('layouts.app')
@section('title', 'Payments – IRONFORGE')
@section('page_title', 'Payments')

@section('content')
<div style="display:grid;grid-template-columns:380px 1fr;gap:24px;align-items:start;">

  {{-- LEFT: Record Payment Form --}}
  <div class="card" style="padding:24px;">
    <div style="font-size:14px;font-weight:600;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;">
      <span style="color:var(--success);">⊕</span> Record Payment
    </div>

    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">✕ {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}">
      @csrf

      <div class="form-group">
        <label class="form-label">Member *</label>
        <select name="member_id" class="form-control" required>
          <option value="">Select Member</option>
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

      <div class="form-group">
        <label class="form-label">Amount (₱) *</label>
        <input type="number" name="amount" class="form-control"
               step="0.01" min="0" placeholder="0.00"
               value="{{ old('amount') }}" required/>
        @error('amount')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Payment Date *</label>
        <input type="date" name="payment_date" class="form-control"
               value="{{ old('payment_date', date('Y-m-d')) }}" required/>
        @error('payment_date')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Method *</label>
        <select name="method" class="form-control" required>
          <option value="">Select Method</option>
          @foreach(['Cash','GCash','Bank Transfer','Card'] as $method)
            <option value="{{ $method }}" {{ old('method') == $method ? 'selected' : '' }}>
              {{ $method }}
            </option>
          @endforeach
        </select>
        @error('method')
          <div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control"
                  rows="3" placeholder="Optional note...">{{ old('notes') }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px;">
        ✓ Record Payment
      </button>
    </form>
  </div>

  {{-- RIGHT: Recent Payments Table --}}
  <div>
    <div class="section-header">
      <div class="section-title" style="display:flex;align-items:center;gap:8px;">
        <span style="color:var(--info);">💳</span> Recent Payments
      </div>
      <div style="font-size:13px;color:var(--muted);">
        Total collected:
        <strong style="color:var(--success);font-size:15px;">
          ₱{{ number_format($payments->sum('amount'), 2) }}
        </strong>
      </div>
    </div>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Member</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Method</th>
            <th>Recorded By</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments as $i => $payment)
          <tr>
            <td style="color:var(--muted)">{{ $i + 1 }}</td>
            <td><strong>{{ $payment->member->name ?? '—' }}</strong></td>
            <td style="color:var(--success);font-weight:600;">
              ₱{{ number_format($payment->amount, 2) }}
            </td>
            <td style="color:var(--muted)">
              {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}
            </td>
            <td>
              <span class="badge" style="background:var(--surface3);color:var(--text);border:1px solid var(--border);">
                {{ $payment->method }}
              </span>
            </td>
            <td style="color:var(--muted)">
              {{ $payment->recordedBy->name ?? '—' }}
            </td>
            <td>
              <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                    onsubmit="return confirm('Delete this payment?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align:center;color:var(--muted);padding:40px;">
              No payments recorded yet.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection