@extends('layouts.staff')
@section('title', 'Payment Transactions – APEX')
@section('page_title', 'Payments')

@section('content')

{{-- Page Header --}}
<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    Payment <span style="color:var(--accent);">Transactions</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">Record and view all member payment records.</p>
</div>

{{-- Stat Cards --}}
<div class="stat-grid pay-stat-grid">

  <div class="stat-card green">
    <div class="stat-card-left">
      <div class="stat-label">Total Transactions</div>
      <div class="stat-value">{{ $totalCount ?? 0 }}</div>
      <div class="stat-sub stat-up">All time</div>
    </div>
    <div class="stat-icon icon-green">
      <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <rect x="1" y="4" width="22" height="16" rx="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
    </div>
  </div>

  <div class="stat-card orange">
    <div class="stat-card-left">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:28px;">₱{{ number_format($thisMonth ?? 0, 0) }}</div>
      <div class="stat-sub">Current month collections</div>
    </div>
    <div class="stat-icon icon-orange">
      <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
    </div>
  </div>

  <div class="stat-card gold">
    <div class="stat-card-left">
      <div class="stat-label">Total Collected</div>
      <div class="stat-value" style="font-size:28px;color:var(--accent-2);">₱{{ number_format($totalCollected ?? 0, 0) }}</div>
      <div class="stat-sub">All time revenue</div>
    </div>
    <div class="stat-icon icon-yellow">
      <svg viewBox="0 0 24 24" stroke-width="1.5" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0
                 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946
                 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138
                 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806
                 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438
                 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
      </svg>
    </div>
  </div>

</div>

{{-- Form + Table --}}
<div class="pay-split">

  {{-- LEFT: Record Payment Form --}}
  <div class="form-panel">
    <div class="form-panel-header">+ Record Payment</div>

    <div class="form-panel-body">
      @if(session('success'))
        <div class="alert-box alert-success-box">✓ {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert-box alert-danger-box">✕ {{ session('error') }}</div>
      @endif

      <form method="POST" action="{{ route('payments.store') }}">
        @csrf

        <div class="pf-group">
          <label class="pf-label">Member</label>
          <select name="member_id" class="pf-control pf-select" required>
            <option value="" disabled selected>— Select Member —</option>
            @foreach($members ?? [] as $member)
              <option value="{{ data_get($member, 'id', '') }}" {{ old('member_id') == data_get($member, 'id') ? 'selected' : '' }}>
                {{ data_get($member, 'name', '') }}
              </option>
            @endforeach
          </select>
          @error('member_id')<div class="pf-error">{{ $message }}</div>@enderror
        </div>

        <div class="pf-group">
          <label class="pf-label">Amount (₱)</label>
          <input type="number" name="amount" class="pf-control" step="0.01" min="0"
                 placeholder="0.00" value="{{ old('amount') }}" required/>
          @error('amount')<div class="pf-error">{{ $message }}</div>@enderror
        </div>

        <div class="pf-group">
          <label class="pf-label">Payment Date</label>
          <div class="pf-date-wrapper">
            <input type="date" name="payment_date" id="staff_payment_date" class="pf-control"
                   value="{{ old('payment_date', date('Y-m-d')) }}" required/>
            <button type="button" class="pf-date-btn"
                    onclick="document.getElementById('staff_payment_date').showPicker()"
                    title="Open calendar">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </button>
          </div>
          @error('payment_date')<div class="pf-error">{{ $message }}</div>@enderror
        </div>

        <div class="pf-group">
          <label class="pf-label">Method</label>
          <select name="method" class="pf-control pf-select" required>
            <option value="" disabled selected>— Select Method —</option>
            @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
              <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
          @error('method')<div class="pf-error">{{ $message }}</div>@enderror
        </div>

        <div class="pf-group">
          <label class="pf-label">Notes (optional)</label>
          <textarea name="notes" class="pf-control" rows="3"
                    placeholder="Any additional notes...">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="pf-submit-btn">✓ Record Payment</button>
      </form>
    </div>
  </div>

  {{-- RIGHT: Transactions Table --}}
  <div>
    <div class="pay-section-header">
      <div class="pay-section-title">All Transactions</div>
    </div>

    <div class="payments-container">
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Member</th>
              <th>Amount</th>
              <th>Date</th>
              <th>Method</th>
              <th>Status</th>
              @if(auth()->user()->role === 'admin')<th>Action</th>@endif
            </tr>
          </thead>
          <tbody>
            @forelse($payments ?? [] as $p)
            @php
              $pPhoto = data_get($p, 'member.user.photo') ?? data_get($p, 'member.photo');
              $status = strtolower(data_get($p, 'status', 'paid') ?? 'paid');
              $badgeClass = in_array($status, ['paid','completed','success']) ? 'pill-active'
                          : ($status === 'pending' ? 'pill-expiring' : 'pill-expired');
            @endphp
            <tr>
              <td class="transaction-id">
                {{ data_get($p, 'receipt_number') ?? 'TXN-'.str_pad(data_get($p, 'id', 0), 5, '0', STR_PAD_LEFT) }}
              </td>
              <td>
                <div class="member-cell">
                  @if($pPhoto)
                    <img src="{{ asset('storage/'.$pPhoto) }}" class="payment-avatar"/>
                  @else
                    <div class="payment-avatar-placeholder">
                      {{ strtoupper(substr(data_get($p, 'member.name', '?'), 0, 2)) }}
                    </div>
                  @endif
                  <div class="payment-member-name">{{ data_get($p, 'member.name', '—') }}</div>
                </div>
              </td>
              <td class="payment-amount">₱{{ number_format(data_get($p, 'amount', 0) ?? 0, 0) }}</td>
              <td class="payment-date">
                {{ data_get($p, 'payment_date') ? \Carbon\Carbon::parse(data_get($p, 'payment_date'))->format('M d, Y') : '—' }}
              </td>
              <td><span class="method-chip">{{ data_get($p, 'method', 'Cash') ?: 'Cash' }}</span></td>
              <td><span class="status-pill {{ $badgeClass }}">{{ ucfirst(data_get($p, 'status') ?? 'Paid') }}</span></td>
              @if(auth()->user()->role === 'admin')
              <td>
                <form method="POST" action="{{ route('payments.destroy', $p) }}"
                      onsubmit="return confirm('Delete this transaction?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="pay-delete-btn" title="Delete">🗑</button>
                </form>
              </td>
              @endif
            </tr>
            @empty
            <tr>
              <td colspan="{{ auth()->user()->role === 'admin' ? '7' : '6' }}"
                  style="padding:40px;text-align:center;color:var(--muted);">
                No transactions yet.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if(isset($payments) && $payments->hasPages())
      <div class="pay-pagination">{{ $payments->links() }}</div>
      @endif
    </div>
  </div>

</div>

<style>
  /* Reuses the charcoal & gold tokens (--accent, --accent-2, --accent-dark,
     --surface, --surface2, --border, --text, --text-soft, --muted,
     --success, --warning, --danger, --info, --accent-soft) from layouts/staff. */

  .pay-stat-grid { grid-template-columns: repeat(3, 1fr); margin-bottom: 28px; }

  .pay-split {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 24px;
    align-items: start;
    margin-bottom: 28px;
  }

  /* Form Panel */
  .form-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
  }
  .form-panel-header {
    padding: 18px 22px;
    font-weight: 700;
    font-size: 15px;
    color: var(--accent);
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, rgba(224,169,59,0.08), transparent);
  }
  .form-panel-body { padding: 22px; }

  .alert-box { padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; }
  .alert-success-box { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.25); color: var(--success); }
  .alert-danger-box  { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25); color: var(--danger); }

  .pf-group { margin-bottom: 16px; }
  .pf-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 6px;
  }
  .pf-control {
    width: 100%;
    padding: 10px 14px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
  }
  .pf-control:focus { border-color: var(--accent); }
  .pf-control option { background: var(--surface2); color: var(--text); }
  textarea.pf-control { resize: vertical; min-height: 80px; }

  .pf-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(224,169,59,0.7)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    background-size: 14px;
    padding-right: 40px;
    cursor: pointer;
  }

  .pf-date-wrapper { position: relative; display: flex; }
  .pf-date-wrapper input[type="date"] { padding-right: 48px; flex: 1; color-scheme: dark; }
  .pf-date-wrapper input[type="date"]::-webkit-calendar-picker-indicator { opacity: 0; width: 0; padding: 0; margin: 0; }
  .pf-date-btn {
    position: absolute; right: 0; top: 0; bottom: 0; width: 44px;
    background: var(--accent-soft);
    border: none; border-left: 1px solid var(--border);
    border-radius: 0 10px 10px 0;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--accent);
    transition: background 0.2s ease;
  }
  .pf-date-btn:hover { background: rgba(224,169,59,0.28); }
  .pf-date-btn svg { width: 18px; height: 18px; }

  .pf-error { color: var(--danger); font-size: 12px; margin-top: 4px; }

  .pf-submit-btn {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
    color: #1a1a1a;
    font-size: 14px;
    font-weight: 800;
    transition: all 0.3s ease;
  }
  .pf-submit-btn:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(224,169,59,0.25);
  }

  /* Table Panel */
  .pay-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
  .pay-section-title { font-size: 16px; font-weight: 700; color: var(--accent); }

  .payments-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
  }
  .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
  table { width: 100%; border-collapse: collapse; min-width: 640px; font-size: 13px; }
  th {
    padding: 14px 16px;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
    font-weight: 600;
    text-align: left;
  }
  td { padding: 14px 16px; vertical-align: middle; color: var(--text-soft); border-top: 1px solid var(--border); }
  tr:hover td { background: rgba(255,255,255,0.02); }

  .transaction-id { font-family: monospace; font-size: 11px; color: var(--muted); }
  .member-cell { display: flex; align-items: center; gap: 10px; }
  .payment-avatar {
    width: 32px; height: 32px; border-radius: 50%; object-fit: cover;
    border: 1px solid var(--border); flex-shrink: 0;
  }
  .payment-avatar-placeholder {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--accent-soft);
    border: 1px solid rgba(224,169,59,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: var(--accent); flex-shrink: 0;
  }
  .payment-member-name { font-size: 13px; font-weight: 600; color: var(--text); }
  .payment-amount { font-size: 14px; font-weight: 700; color: var(--accent-2); }
  .payment-date { color: var(--muted); }
  .method-chip {
    background: var(--surface2);
    border: 1px solid var(--border);
    padding: 3px 12px;
    border-radius: 6px;
    font-size: 11px;
    color: var(--text);
  }

  .pill-active   { background: rgba(74,222,128,0.15);  color: var(--success); border: 1px solid rgba(74,222,128,0.3); }
  .pill-expiring { background: rgba(251,191,36,0.15);  color: var(--warning); border: 1px solid rgba(251,191,36,0.3); }
  .pill-expired  { background: rgba(248,113,113,0.15); color: var(--danger);  border: 1px solid rgba(248,113,113,0.3); }
  .status-pill {
    font-size: 10px; font-weight: 700; padding: 4px 12px; border-radius: 40px;
    text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap;
  }

  .pay-delete-btn {
    background: rgba(248,113,113,0.12);
    color: var(--danger);
    border: 1px solid rgba(248,113,113,0.25);
    padding: 5px 10px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
  }
  .pay-delete-btn:hover { background: rgba(248,113,113,0.25); }

  .pay-pagination { padding: 16px 20px; border-top: 1px solid var(--border); }

  /* Responsive */
  @media (max-width: 1024px) {
    .pay-stat-grid { grid-template-columns: repeat(2, 1fr); }
    .pay-split { grid-template-columns: 1fr; }
  }
  @media (max-width: 640px) {
    .pay-stat-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    table { min-width: 560px; }
    th, td { padding: 10px 12px; }
  }
  @media (max-width: 400px) {
    .pay-stat-grid { grid-template-columns: 1fr; }
  }
</style>

@endsection
