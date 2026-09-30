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

                    <div class="form-group">
                        <label class="form-label">Amount (₱)</label>
                        <input type="number" name="amount" class="form-control"
                               step="0.01" min="0" placeholder="0.00"
                               value="{{ old('amount') }}" required/>
                        @error('amount')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
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

    <script>
        (function () {
            var toggle  = document.getElementById('topnavToggle');
            var links   = document.getElementById('topnavLinks');
            var overlay = document.getElementById('topnavOverlay');
            var iconOpen  = document.getElementById('navIconOpen');
            var iconClose = document.getElementById('navIconClose');

            function closeMenu() {
                links.classList.remove('open');
                overlay.classList.remove('open');
                document.body.classList.remove('menu-open');
                toggle.setAttribute('aria-expanded', 'false');
                iconOpen.style.display = '';
                iconClose.style.display = 'none';
            }

            function openMenu() {
                links.classList.add('open');
                overlay.classList.add('open');
                document.body.classList.add('menu-open');
                toggle.setAttribute('aria-expanded', 'true');
                iconOpen.style.display = 'none';
                iconClose.style.display = '';
            }

            toggle.addEventListener('click', function () {
                links.classList.contains('open') ? closeMenu() : openMenu();
            });

            overlay.addEventListener('click', closeMenu);

            links.querySelectorAll('.nav-link').forEach(function (link) {
                link.addEventListener('click', closeMenu);
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 860) closeMenu();
            });
        })();
    </script>

</body>
</html>