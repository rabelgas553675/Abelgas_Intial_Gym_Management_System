@extends('layouts.admin')
@section('title', 'Payment Transactions – IRONFORGE')
@section('page_title', 'Payments')
@section('active_nav', 'payments')

@section('content')

<style>
select.form-control {
  appearance:none;-webkit-appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 12px center;background-size:16px;
  padding-right:40px!important;cursor:pointer;
}
select.form-control option { background-color:var(--surface);color:white; }
select.form-control:focus  { border-color:#3b82f6;outline:none;box-shadow:0 0 0 2px rgba(59,130,246,.2); }
.earn-tab { padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;
            border:1px solid var(--border);background:transparent;color:var(--muted);transition:.15s; }
.earn-tab.active { background:var(--accent);color:#111;border-color:var(--accent); }
input[type="date"]::-webkit-calendar-picker-indicator { opacity:0;width:0;padding:0;margin:0; }
input[type="date"] { color-scheme:dark; }
</style>

{{-- Page header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
  <div>
    <h1 style="font-size:30px;font-weight:700;margin-bottom:4px;">Payment Transactions</h1>
    <p style="color:var(--muted);font-size:14px;">Track gym fees, coach fees, and earnings per role.</p>
  </div>
  <div style="display:flex;gap:8px;">
    <button class="earn-tab active" onclick="showTab('admin')" id="tab-admin">Admin Earnings</button>
    <button class="earn-tab" onclick="showTab('instructor')" id="tab-instructor">Instructor Earnings</button>
  </div>
</div>

{{-- ══ ADMIN EARNINGS PANEL ══════════════════════════════════════════════════ --}}
<div id="panel-admin">

  <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
    <div class="stat-card">
      <div class="stat-label">Total Transactions</div>
      <div class="stat-value">{{ $totalCount }}</div>
      <div class="stat-sub">Gym fee payments</div>
    </div>
    <div class="stat-card green">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:24px;color:var(--warning);">
        ₱{{ number_format($thisMonth, 0) }}
      </div>
      <div class="stat-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card blue">
      <div class="stat-label">Total Collected</div>
      <div class="stat-value" style="font-size:24px;color:var(--success);">
        ₱{{ number_format($totalCollected, 0) }}
      </div>
      <div class="stat-sub">All-time gym revenue</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Coach Fees Paid Out</div>
      <div class="stat-value" style="font-size:24px;color:#60a5fa;">
        ₱{{ number_format($totalCoachFees, 0) }}
      </div>
      <div class="stat-sub">To instructors total</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">

    {{-- Record Payment Form --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;">
      <div style="font-size:15px;font-weight:700;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border);">
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

        {{-- $members is a plain array now — use ['key'] syntax --}}
        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Member</label>
          <select name="member_id" class="form-control" required>
            <option value="" disabled selected>— Select Member —</option>
            @foreach($members as $member)
              <option value="{{ $member['id'] }}" {{ old('member_id') == $member['id'] ? 'selected' : '' }}>
                {{ $member['name'] }}
              </option>
            @endforeach
          </select>
          @error('member_id')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Amount (₱)</label>
          <input type="number" name="amount" class="form-control" step="0.01" min="0"
                 placeholder="0.00" value="{{ old('amount') }}" required/>
          @error('amount')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Payment Date</label>
          <div style="position:relative;display:flex;">
            <input type="date" name="payment_date" id="payment_date" class="form-control"
                   value="{{ old('payment_date', date('Y-m-d')) }}" required
                   style="padding-right:48px;flex:1;border-radius:8px;"/>
            <button type="button"
                    onclick="document.getElementById('payment_date').showPicker()"
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
          @error('payment_date')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Method</label>
          <select name="method" class="form-control" required>
            <option value="" disabled selected>— Select Method —</option>
            @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
              <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
          @error('method')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary"
                style="width:100%;justify-content:center;padding:12px;font-weight:600;">
          ✓ Record Payment
        </button>
      </form>
    </div>

    {{-- Admin Transactions Table --}}
    {{-- $payments is a plain array — use ['key'] syntax --}}
    <div>
      <div class="section-header">
        <div class="section-title">Gym Fee Transactions
          <span style="font-size:12px;color:var(--muted);font-weight:400;margin-left:8px;">
            (Platform / Admin earnings only — coach fees excluded)
          </span>
        </div>
      </div>
      <div class="card">
        <table>
          <thead>
            <tr>
              <th>Receipt</th>
              <th>Member</th>
              <th>Plan</th>
              <th>Duration</th>
              <th>Amount</th>
              <th>Date</th>
              <th>Method</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $payment)
            @php
              $memberPhoto = $payment['member']['user']['photo']
                          ?? $payment['member']['photo']
                          ?? null;
              $memberName  = $payment['member']['name'] ?? '?';
              $receiptNum  = $payment['receipt_number']
                          ?? 'TXN-' . str_pad($payment['id'], 5, '0', STR_PAD_LEFT);
            @endphp
            <tr>
              <td style="font-family:monospace;font-size:11px;color:var(--muted);">
                {{ $receiptNum }}
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  @if($memberPhoto)
                    <img src="{{ asset('storage/'.$memberPhoto) }}"
                         style="width:32px;height:32px;border-radius:50%;object-fit:cover;
                                border:1px solid rgba(200,255,0,0.25);flex-shrink:0;">
                  @else
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(200,255,0,0.08);
                                border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                                justify-content:center;font-size:10px;font-weight:700;
                                color:var(--accent);flex-shrink:0;">
                      {{ strtoupper(substr($memberName, 0, 2)) }}
                    </div>
                  @endif
                  <span style="font-weight:600;">{{ $memberName }}</span>
                </div>
              </td>
              <td style="font-size:13px;">{{ $payment['fitness_plan'] ?? '—' }}</td>
              <td>
                <span style="background:rgba(96,165,250,.1);color:#60a5fa;padding:3px 10px;
                             border-radius:6px;font-size:11px;font-weight:700;">
                  {{ $payment['membership_type'] ?? '—' }}
                </span>
              </td>
              <td style="font-weight:700;color:var(--accent);">
                ₱{{ number_format($payment['amount'], 0) }}
              </td>
              <td style="color:var(--muted);">
                {{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}
              </td>
              <td>
                <span style="background:var(--surface2);border:1px solid var(--border);
                             padding:3px 10px;border-radius:6px;font-size:12px;">
                  {{ $payment['method'] ?? 'Cash' }}
                </span>
              </td>
              <td>
                <form method="POST" action="{{ route('payments.destroy', $payment['id']) }}"
                      onsubmit="return confirm('Delete this transaction?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" style="text-align:center;color:var(--muted);padding:48px;">
                No gym fee transactions yet.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- ══ INSTRUCTOR EARNINGS PANEL ════════════════════════════════════════════ --}}
{{-- $instructorLeaderboard and $coachFeePayments are plain arrays --}}
<div id="panel-instructor" style="display:none;">

  <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
    <div class="stat-card">
      <div class="stat-label">Total Coach Fees</div>
      <div class="stat-value" style="color:#60a5fa;">₱{{ number_format($totalCoachFees, 0) }}</div>
      <div class="stat-sub">All-time instructor payouts</div>
    </div>
    <div class="stat-card green">
      <div class="stat-label">This Month (Coach)</div>
      <div class="stat-value" style="font-size:24px;color:var(--warning);">
        ₱{{ number_format($thisMonthCoachFees, 0) }}
      </div>
      <div class="stat-sub">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Active Instructors Paid</div>
      <div class="stat-value">{{ $instructorsPaidCount }}</div>
      <div class="stat-sub">With at least one coach fee</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

    {{-- Instructor leaderboard --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;">
      <div style="font-size:15px;font-weight:700;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border);">
        Instructor Earnings Breakdown
      </div>
      @forelse($instructorLeaderboard as $row)
      @php
        $instrPhoto = $row['instructor']['photo'] ?? null;
        $instrName  = $row['instructor']['name'] ?? '?';
      @endphp
      <div style="display:flex;align-items:center;justify-content:space-between;
                  padding:12px 0;border-bottom:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:12px;">
          @if($instrPhoto)
            <img src="{{ asset('storage/'.$instrPhoto) }}"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
          @else
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(200,255,0,0.08);
                        border:1px solid rgba(200,255,0,0.2);display:flex;align-items:center;
                        justify-content:center;font-size:12px;font-weight:700;color:var(--accent);">
              {{ strtoupper(substr($instrName, 0, 2)) }}
            </div>
          @endif
          <div>
            <div style="font-size:14px;font-weight:600;">{{ $instrName }}</div>
            <div style="font-size:12px;color:var(--muted);">{{ $row['txn_count'] }} transaction(s)</div>
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:16px;font-weight:700;color:var(--accent);">
            ₱{{ number_format($row['total'], 0) }}
          </div>
          <div style="font-size:11px;color:var(--muted);">total earned</div>
        </div>
      </div>
      @empty
      <div style="text-align:center;color:var(--muted);padding:32px;">No instructor fees recorded yet.</div>
      @endforelse
    </div>

    {{-- Coach fee transaction log --}}
    <div>
      <div class="section-header">
        <div class="section-title">Coach Fee Transactions</div>
      </div>
      <div class="card">
        <table>
          <thead>
            <tr>
              <th>Receipt</th>
              <th>Member → Instructor</th>
              <th>Amount</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @forelse($coachFeePayments as $cp)
            <tr>
              <td style="font-family:monospace;font-size:11px;color:var(--muted);">
                {{ $cp['receipt_number'] ?? '—' }}
              </td>
              <td>
                <div style="font-size:13px;">
                  <span style="font-weight:600;">{{ $cp['member']['name'] ?? '—' }}</span>
                  <span style="color:var(--muted);margin:0 6px;">→</span>
                  <span style="color:#60a5fa;font-weight:600;">{{ $cp['instructor']['name'] ?? '—' }}</span>
                </div>
                <div style="font-size:11px;color:var(--muted);">
                  {{ $cp['fitness_plan'] ?? '' }} · {{ $cp['membership_type'] ?? '' }}
                </div>
              </td>
              <td style="font-weight:700;color:#60a5fa;">₱{{ number_format($cp['amount'], 0) }}</td>
              <td style="color:var(--muted);font-size:13px;">
                {{ \Carbon\Carbon::parse($cp['payment_date'])->format('M d, Y') }}
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" style="text-align:center;color:var(--muted);padding:48px;">No coach fee payments yet.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function showTab(tab) {
  document.getElementById('panel-admin').style.display      = tab === 'admin'      ? 'block' : 'none';
  document.getElementById('panel-instructor').style.display = tab === 'instructor' ? 'block' : 'none';
  document.getElementById('tab-admin').classList.toggle('active',      tab === 'admin');
  document.getElementById('tab-instructor').classList.toggle('active', tab === 'instructor');
}
</script>
@endsection