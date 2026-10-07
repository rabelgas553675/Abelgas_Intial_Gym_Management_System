@extends('layouts.staff')
@section('title', 'Payment Transactions – APEX')
@section('page_title', 'Payments')

@section('content')

{{-- Payments sections: Member Payments | Walk-In Payments --}}
@include('partials.payment-tabs', ['active' => 'member'])

<style>
  /* ── Page-scoped styles (uses the theme variables from layouts/staff.blade.php) ── */

  /* Use the whole available width on this page only */
  .page-wrap { max-width: 100%; }
  .pay-page, .pay-page * { box-sizing: border-box; }
  .pay-page { width: 100%; min-width: 0; }

  /* Header */
  .pay-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
  .pay-head h1 { font-size:28px; font-weight:700; margin-bottom:4px; }
  .pay-head p  { color:var(--muted); font-size:14px; }

  /* Stat cards: inherit .stat-grid from the layout, just make sure they never force width */
  .pay-page .stat-grid { grid-template-columns:repeat(auto-fit, minmax(min(100%, 220px), 1fr)); }
  .pay-page .stat-card { min-width:0; }

  /* Toolbar above the table */
  .pay-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:14px; }
  .pay-toolbar-title { font-size:16px; font-weight:700; }
  .pay-record-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    padding:11px 22px; min-height:44px; border:0; border-radius:10px; cursor:pointer;
    font-family:'DM Sans',sans-serif; font-size:14px; font-weight:700; color:#1a1a1a;
    background:linear-gradient(135deg, var(--accent-2), var(--accent-dark));
    transition:filter .15s, transform .15s;
  }
  .pay-record-btn:hover { filter:brightness(1.08); transform:translateY(-1px); }

  /* Table card — the ONLY element allowed to scroll horizontally */
  .payments-container {
    width:100%; min-width:0;
    background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden;
  }
  .payments-container .table-responsive {
    width:100%; overflow-x:auto; overflow-y:hidden;
    -webkit-overflow-scrolling:touch; overscroll-behavior-x:contain;
  }
  .payments-container table { width:100%; min-width:900px; border-collapse:collapse; table-layout:auto; }
  .payments-container th,
  .payments-container td { padding:14px 16px; white-space:nowrap; vertical-align:middle; }
  .payments-container th:first-child, .payments-container td:first-child { padding-left:22px; }
  .payments-container th:last-child,  .payments-container td:last-child  { padding-right:22px; text-align:center; }

  .transaction-id { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:12.5px; letter-spacing:.3px; color:var(--text-soft); }
  .payment-amount { font-weight:700; color:var(--text); }
  .payment-date   { color:var(--muted); }

  .member-cell { display:flex; align-items:center; gap:10px; min-width:0; }
  .payment-avatar, .payment-avatar-placeholder { width:34px; height:34px; border-radius:50%; flex-shrink:0; object-fit:cover; }
  .payment-avatar-placeholder { display:flex; align-items:center; justify-content:center; background:var(--accent-soft); color:var(--accent); font-size:12px; font-weight:700; }
  .payment-member-name { font-weight:500; color:var(--text); text-transform:capitalize; max-width:220px; overflow:hidden; text-overflow:ellipsis; }

  .method-chip { display:inline-block; padding:3px 10px; border-radius:999px; background:var(--surface3); font-size:12px; }
  .type-chip   { display:inline-block; padding:3px 10px; border-radius:6px; font-size:11px; font-weight:700; letter-spacing:.4px; text-transform:uppercase; }
  .type-gym      { background:rgba(224,169,59,.15);  color:var(--accent); }
  .type-coach    { background:rgba(96,165,250,.15);  color:var(--info); }
  .type-platform { background:rgba(167,139,250,.15); color:#a78bfa; }

  .pay-delete-btn { background:rgba(248,113,113,.1); border:1px solid rgba(248,113,113,.2); color:var(--danger); width:34px; height:34px; border-radius:8px; cursor:pointer; font-size:14px; }
  .pay-delete-btn:hover { background:rgba(248,113,113,.2); }
  .pay-muted { color:var(--muted); }

  .pay-scroll-hint { display:none; padding:8px 16px; font-size:11px; color:var(--muted); border-top:1px solid var(--border); text-align:center; }
  .pay-pagination { padding:14px 18px; border-top:1px solid var(--border); overflow-x:auto; }

  /* ── Record Payment modal ── */
  .pay-modal { position:fixed; inset:0; z-index:1000; display:none; align-items:center; justify-content:center; padding:16px; background:rgba(0,0,0,.65); backdrop-filter:blur(3px); }
  .pay-modal.open { display:flex; }
  .pay-modal-box { width:100%; max-width:480px; max-height:calc(100dvh - 32px); display:flex; flex-direction:column; background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.5); }
  .pay-modal-head { display:flex; align-items:center; justify-content:space-between; padding:16px 22px; border-bottom:1px solid var(--border); font-size:15px; font-weight:700; color:var(--accent); }
  .pay-modal-close { background:none; border:0; color:var(--muted); font-size:22px; line-height:1; cursor:pointer; }
  .pay-modal-close:hover { color:var(--text); }
  .pay-modal-body { padding:22px; overflow-y:auto; }

  .pay-modal .form-group { margin-bottom:16px; }
  .pay-modal select.form-control {
    appearance:none; -webkit-appearance:none; -moz-appearance:none; cursor:pointer; padding-right:42px;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23e0a93b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 14px center; background-size:16px;
  }
  .pay-modal input[type=date] { color-scheme:dark; cursor:pointer; }
  html:root[data-theme="light"] .pay-modal input[type=date] { color-scheme:light; }
  .pay-hint { color:var(--muted); font-size:12px; margin-top:4px; }
  .pay-errors { background:rgba(248,113,113,.12); color:var(--danger); border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
  .pay-errors ul { margin:6px 0 0 18px; }

  /* ── Responsive ── */
  @media (max-width:1024px) {
    .payments-container th, .payments-container td { padding:12px 12px; }
  }
  @media (max-width:768px) {
    .pay-head h1 { font-size:24px; }
    .pay-toolbar { align-items:stretch; }
    .pay-record-btn { width:100%; }
    .pay-scroll-hint { display:block; }
    .payments-container table { min-width:820px; }
  }
  @media (max-width:480px) {
    .pay-modal { padding:0; align-items:flex-end; }
    .pay-modal-box { max-width:100%; max-height:92dvh; border-radius:16px 16px 0 0; }
  }
</style>

<div class="pay-page">

  {{-- Page Header --}}
  <div class="pay-head">
    <div>
      <h1>Payment <span style="color:var(--accent);">Transactions</span></h1>
      <p>Record and view all member payment records.</p>
    </div>
  </div>

  {{-- Stat Cards --}}
  <div class="stat-grid">

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

  {{-- Toolbar: title + Record Payment button (above the table) --}}
  <div class="pay-toolbar">
    <div class="pay-toolbar-title">All Transactions</div>
    <button type="button" class="pay-record-btn" id="openPaymentModal">+ Record Payment</button>
  </div>

  {{-- Transactions Table (full width) --}}
  <div class="payments-container">
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Receipt No.</th>
            <th>Member</th>
            <th>Payment Type</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Method</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payments ?? [] as $p)
          @php
            $pPhoto = data_get($p, 'member.user.photo') ?? data_get($p, 'member.photo');
            $status = strtolower(data_get($p, 'status', 'paid') ?? 'paid');
            $badgeClass = in_array($status, ['paid','completed','success']) ? 'pill-active'
                        : ($status === 'pending' ? 'pill-expiring' : 'pill-expired');

            $type = data_get($p, 'payment_type', 'gym_fee');
            [$typeLabel, $typeClass] = match ($type) {
                'coach_fee'    => ['Coach Fee',    'type-coach'],
                'platform_fee' => ['Platform Fee', 'type-platform'],
                default        => ['Gym Fee',      'type-gym'],
            };
          @endphp
          <tr>
            <td class="transaction-id">
              {{ data_get($p, 'receipt_number') ?? 'TXN-'.str_pad(data_get($p, 'id', 0), 5, '0', STR_PAD_LEFT) }}
            </td>
            <td>
              <div class="member-cell">
                @if($pPhoto)
                  <img src="{{ asset('storage/'.$pPhoto) }}" class="payment-avatar" alt=""/>
                @else
                  <div class="payment-avatar-placeholder">
                    {{ strtoupper(substr(data_get($p, 'member.name', '?'), 0, 2)) }}
                  </div>
                @endif
                <div class="payment-member-name">{{ data_get($p, 'member.name', '—') }}</div>
              </div>
            </td>
            <td><span class="type-chip {{ $typeClass }}">{{ $typeLabel }}</span></td>
            <td class="payment-amount">₱{{ number_format(data_get($p, 'amount', 0) ?? 0, 0) }}</td>
            <td class="payment-date">
              {{ data_get($p, 'payment_date') ? \Carbon\Carbon::parse(data_get($p, 'payment_date'))->format('M d, Y') : '—' }}
            </td>
            <td><span class="method-chip">{{ data_get($p, 'method', 'Cash') ?: 'Cash' }}</span></td>
            <td><span class="status-pill {{ $badgeClass }}">{{ ucfirst(data_get($p, 'status') ?? 'Paid') }}</span></td>
            <td>
              @if(auth()->user()->role === 'admin')
                <form method="POST" action="{{ route('payments.destroy', $p) }}" style="display:inline;"
                      onsubmit="return confirm('Delete this transaction?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="pay-delete-btn" title="Delete" aria-label="Delete transaction">🗑</button>
                </form>
              @else
                <span class="pay-muted">—</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" style="padding:40px;text-align:center;color:var(--muted);white-space:normal;">
              No transactions yet.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pay-scroll-hint">← Swipe sideways to see all columns →</div>

    @if(isset($payments) && method_exists($payments, 'hasPages') && $payments->hasPages())
      <div class="pay-pagination">{{ $payments->links() }}</div>
    @endif
  </div>

</div>

{{-- Record Payment modal (same form, same route: payments.store) --}}
<div class="pay-modal" id="paymentModal" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
  <div class="pay-modal-box">
    <div class="pay-modal-head">
      <span id="paymentModalTitle">+ Record Payment</span>
      <button type="button" class="pay-modal-close" id="closePaymentModal" aria-label="Close">&times;</button>
    </div>

    <div class="pay-modal-body">
      @if($errors->any())
        <div class="pay-errors">
          ✕ Please fix the following:
          <ul>
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('payments.store') }}">
        @csrf

        <div class="form-group">
          <label class="form-label">Member</label>
          <select name="member_id" class="form-control" required>
            <option value="" disabled {{ old('member_id') ? '' : 'selected' }}>— Select Member —</option>
            @foreach($members ?? [] as $member)
              <option value="{{ data_get($member, 'id') }}" {{ old('member_id') == data_get($member, 'id') ? 'selected' : '' }}>
                {{ data_get($member, 'name') }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Membership Plan</label>
          <select name="membership_type" id="staff_membership_type" class="form-control" required>
            <option value="" disabled {{ old('membership_type') ? '' : 'selected' }}>— Select Plan —</option>
            @foreach(['Monthly','Quarterly','Semi-Annual','Annually'] as $plan)
              <option value="{{ $plan }}" {{ old('membership_type') == $plan ? 'selected' : '' }}>
                {{ $plan }} (₱{{ number_format($gymRates[$plan] ?? 0, 0) }})
              </option>
            @endforeach
          </select>
          <div class="pay-hint">The total is calculated automatically.</div>
        </div>

        <div class="form-group">
          <label class="form-label">Instructor (optional)</label>
          <select name="instructor_id" id="staff_instructor" class="form-control">
            <option value="">— No personal coaching —</option>
            @foreach($instructors ?? [] as $ins)
              <option value="{{ data_get($ins, 'id') }}" {{ old('instructor_id') == data_get($ins, 'id') ? 'selected' : '' }}>{{ data_get($ins, 'name') }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group" id="staff_coach_wrap" style="display:none;">
          <label class="form-label">Coaching Package</label>
          <select name="coach_membership_type" id="staff_coach_type" class="form-control">
            <option value="">— Select Package —</option>
            @foreach(['Monthly','Quarterly','Semi-Annual','Annually'] as $plan)
              <option value="{{ $plan }}" {{ old('coach_membership_type') == $plan ? 'selected' : '' }}>
                {{ $plan }} (₱{{ number_format($coachRates[$plan] ?? 0, 0) }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Total Amount</label>
          <input type="text" id="staff_total_amount" class="form-control" value="₱0" readonly>
          <input type="hidden" name="amount" id="staff_amount_hidden" value="{{ old('amount', 0) }}">
        </div>

        <div class="form-group">
          <label class="form-label">Payment Date</label>
          <input type="date" name="payment_date" id="staff_payment_date" class="form-control"
                 onclick="try{this.showPicker()}catch(e){}"
                 value="{{ old('payment_date', date('Y-m-d')) }}" required/>
        </div>

        <div class="form-group">
          <label class="form-label">Method</label>
          <select name="method" class="form-control" required>
            <option value="" disabled {{ old('method') ? '' : 'selected' }}>— Select Method —</option>
            @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
              <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Notes (optional)</label>
          <textarea name="notes" class="form-control" rows="3" maxlength="500"
                    placeholder="Any additional notes...">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="pay-record-btn" style="width:100%;">✓ Record Payment</button>
      </form>
    </div>
  </div>
</div>

<script>
  (function () {
    var modal   = document.getElementById('paymentModal');
    var openBtn = document.getElementById('openPaymentModal');
    var closeBtn = document.getElementById('closePaymentModal');

    function openModal()  { modal.classList.add('open');    document.body.style.overflow = 'hidden'; }
    function closeModal() { modal.classList.remove('open'); document.body.style.overflow = ''; }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    // Re-open automatically if the server returned validation errors
    @if($errors->any()) openModal(); @endif

    var ins  = document.getElementById('staff_instructor');
    var wrap = document.getElementById('staff_coach_wrap');
    var type = document.getElementById('staff_coach_type');
    var gymSelect = document.getElementById('staff_membership_type');
    var totalBox = document.getElementById('staff_total_amount');
    var hiddenAmount = document.getElementById('staff_amount_hidden');
    var gymRates = @json($gymRates ?? []);
    var coachRates = @json($coachRates ?? []);

    function syncCoach() {
      var on = !!ins.value;
      wrap.style.display = on ? '' : 'none';
      type.required = on;
      if (!on) type.value = '';
      syncTotal();
    }

    function syncTotal() {
      var gymType = gymSelect ? gymSelect.value : '';
      var coachType = type ? type.value : '';
      var gymTotal = gymType && gymRates[gymType] ? Number(gymRates[gymType]) : 0;
      var coachTotal = ins && ins.value && coachType && coachRates[coachType] ? Number(coachRates[coachType]) : 0;
      var total = gymTotal + coachTotal;

      if (totalBox) totalBox.value = '₱' + total.toLocaleString('en-PH');
      if (hiddenAmount) hiddenAmount.value = total;
    }

    ins.addEventListener('change', syncCoach);
    if (gymSelect) gymSelect.addEventListener('change', syncTotal);
    if (type) type.addEventListener('change', syncTotal);
    syncCoach();
    syncTotal();
  })();
</script>

@endsection