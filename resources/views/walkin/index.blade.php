@extends(auth()->user()->isStaff() ? 'layouts.staff' : 'layouts.admin')

@section('title', 'Walk-In Payments – APEX')
@section('page_title', 'Payments')
@section('active_nav', 'payments')

@section('content')
<style>
  .wi-page, .wi-page * { box-sizing:border-box; }
  .wi-page { width:100%; min-width:0; }
  .wi-head { margin-bottom:18px; }
  .wi-head h1 { font-size:26px; font-weight:700; margin-bottom:4px; }
  .wi-head p  { color:var(--muted); font-size:14px; }

  .wi-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,210px),1fr)); gap:14px; margin-bottom:22px; }
  .wi-stat { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:16px 18px; min-width:0; }
  .wi-stat label { display:block; font-size:10px; font-weight:700; letter-spacing:1.6px; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
  .wi-stat b { display:block; font-size:26px; font-weight:700; line-height:1.1; }
  .wi-stat small { display:block; color:var(--muted); font-size:12px; margin-top:5px; }
  .wi-stat.gold b { color:var(--accent-2); }

  .wi-layout { display:grid; grid-template-columns:340px minmax(0,1fr); gap:22px; align-items:start; }
  @media (max-width:1000px){ .wi-layout { grid-template-columns:1fr; } }

  .wi-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px; }
  .wi-card-title { font-size:15px; font-weight:700; margin-bottom:4px; }
  .wi-card-sub { font-size:12px; color:var(--muted); margin-bottom:16px; }
  .wi-notice { display:flex; gap:8px; align-items:flex-start; font-size:12px; line-height:1.45; color:var(--muted); background:var(--accent-soft,rgba(224,169,59,.10)); border:1px solid rgba(224,169,59,.25); border-radius:10px; padding:10px 12px; margin-bottom:16px; }
  .wi-notice b { color:var(--accent-2); }

  .wi-field { margin-bottom:14px; }
  .wi-field label { display:block; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
  .wi-field label i { color:var(--danger,#f87171); font-style:normal; }
  .wi-field .form-control { width:100%; }
  .wi-field .form-control[readonly] { opacity:.85; cursor:not-allowed; font-weight:700; color:var(--accent-2); }
  .wi-err { color:var(--danger,#f87171); font-size:12px; margin-top:5px; }
  .wi-hint { font-size:12px; margin-top:5px; color:var(--muted); min-height:16px; }
  .wi-hint.warn { color:var(--warning,#fbbf24); }
  .wi-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  @media (max-width:480px){ .wi-row { grid-template-columns:1fr; } }
  .wi-submit { width:100%; justify-content:center; }

  .wi-filters { display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr auto; gap:10px; align-items:end; margin-bottom:14px; }
  .wi-filters .wi-field { margin:0; }
  .wi-filters .form-control { font-size:13px; padding:9px 11px; }
  .wi-filters-actions { display:flex; gap:8px; }
  @media (max-width:1250px){ .wi-filters { grid-template-columns:repeat(3,1fr); } .wi-filters .wi-search { grid-column:1/-1; } }
  @media (max-width:560px){ .wi-filters { grid-template-columns:1fr 1fr; } }

  .wi-table-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
  .wi-table-top { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; padding:16px 20px; border-bottom:1px solid var(--border); }
  .wi-table-top b { font-size:15px; }
  .wi-table-scroll { width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
  .wi-table { width:100%; min-width:1080px; border-collapse:collapse; }
  .wi-table th { padding:11px 14px; text-align:left; font-size:10px; font-weight:700; letter-spacing:1.6px; text-transform:uppercase; color:var(--muted); background:var(--surface2,transparent); white-space:nowrap; border-bottom:1px solid var(--border); }
  .wi-table td { padding:13px 14px; font-size:13.5px; border-bottom:1px solid var(--border); vertical-align:middle; color:var(--text-soft,inherit); }
  .wi-table tr:last-child td { border-bottom:0; }
  .wi-mono { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:12px; white-space:nowrap; }
  .wi-name { font-weight:600; color:var(--text); }
  .wi-muted { color:var(--muted); }
  .wi-money { font-weight:700; white-space:nowrap; color:var(--text); }

  .wi-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 11px; border-radius:6px; font-size:11px; font-weight:700; white-space:nowrap; }
  .wi-badge::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
  .wi-badge.walkin { background:rgba(96,165,250,.15); color:var(--info,#60a5fa); }
  .wi-badge.paid   { background:rgba(74,222,128,.15); color:var(--success,#4ade80); }
  .wi-badge.partial{ background:rgba(251,191,36,.16); color:var(--warning,#fbbf24); }

  .wi-empty { text-align:center; padding:48px 20px; color:var(--muted); }
  .wi-empty b { display:block; color:var(--text); font-size:15px; margin-bottom:4px; }
  .wi-pager { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:14px 20px; border-top:1px solid var(--border); font-size:13px; color:var(--muted); }
  .wi-pager .btn[aria-disabled="true"] { opacity:.4; pointer-events:none; }
</style>

<div class="wi-page">

  @include('partials.payment-tabs', ['active' => 'walkin'])

  <div class="wi-head">
    <h1>Walk-In Payments</h1>
    <p>Day Pass sales for non-members. These records are kept separate from membership payments.</p>
  </div>

  {{-- ── Stats (walk-in only) ── --}}
  <div class="wi-stats">
    <div class="wi-stat"><label>Today</label><b>{{ $todayCount }}</b><small>₱{{ number_format($todayTotal, 2) }} collected</small></div>
    <div class="wi-stat"><label>This Month</label><b>₱{{ number_format($monthTotal, 0) }}</b><small>{{ now()->format('F Y') }}</small></div>
    <div class="wi-stat gold"><label>Total Walk-In Revenue</label><b>₱{{ number_format($allTotal, 0) }}</b><small>{{ number_format($allCount) }} {{ \Illuminate\Support\Str::plural('transaction', $allCount) }}</small></div>
  </div>

  <div class="wi-layout">

    {{-- ══ RECORD FORM ══ --}}
    <div class="wi-card">
      <div class="wi-card-title">+ Record Walk-In</div>
      <div class="wi-card-sub">Day Pass for a non-member customer.</div>

      <div class="wi-notice">
        <span>ℹ</span>
        <span>This does <b>not</b> register a member or change any membership. For memberships, use <b>Member Payments</b>.</span>
      </div>

      <form method="POST" action="{{ route('walkin.store') }}" id="walkin-form" novalidate>
        @csrf

        <div class="wi-field">
          <label for="customer_name">Walk-In Customer Name <i>*</i></label>
          <input type="text" id="customer_name" name="customer_name" class="form-control"
                 value="{{ old('customer_name') }}" maxlength="120" placeholder="e.g. Juan Dela Cruz" required autocomplete="off">
          @error('customer_name')<div class="wi-err">{{ $message }}</div>@enderror
        </div>

        <div class="wi-field">
          <label for="contact_number">Contact Number <i>*</i></label>
          <input type="tel" id="contact_number" name="contact_number" class="form-control"
                 value="{{ old('contact_number') }}" maxlength="20" placeholder="e.g. 0917 123 4567" required autocomplete="off">
          @error('contact_number')<div class="wi-err">{{ $message }}</div>@enderror
        </div>

        <div class="wi-field">
          <label for="day_pass_rate_display">Day Pass Rate</label>
          <input type="text" id="day_pass_rate_display" class="form-control" value="₱{{ number_format($dayPassRate, 2) }}" readonly tabindex="-1">
          <div class="wi-hint">Set in the gym rate settings (Admin → Payments → Subscription Rates).</div>
        </div>

        <div class="wi-row">
          <div class="wi-field">
            <label for="payment_method">Payment Method <i>*</i></label>
            <select id="payment_method" name="payment_method" class="form-control" required>
              @foreach($methods as $m)
                <option value="{{ $m }}" @selected(old('payment_method', 'Cash') === $m)>{{ $m }}</option>
              @endforeach
            </select>
            @error('payment_method')<div class="wi-err">{{ $message }}</div>@enderror
          </div>
          <div class="wi-field">
            <label for="payment_date">Payment Date <i>*</i></label>
            <input type="date" id="payment_date" name="payment_date" class="form-control"
                   value="{{ old('payment_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
            @error('payment_date')<div class="wi-err">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="wi-field">
          <label for="amount">Amount Paid (₱) <i>*</i></label>
          <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" max="100000"
                 value="{{ old('amount', number_format($dayPassRate, 2, '.', '')) }}" required>
          <div class="wi-hint" id="amount-hint"></div>
          @error('amount')<div class="wi-err">{{ $message }}</div>@enderror
        </div>

        <div class="wi-field">
          <label for="notes">Notes (optional)</label>
          <input type="text" id="notes" name="notes" class="form-control" maxlength="500" value="{{ old('notes') }}" placeholder="Optional">
          @error('notes')<div class="wi-err">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary wi-submit" id="walkin-submit">✓ Record Walk-In Payment</button>
      </form>
    </div>

    {{-- ══ RECORDS ══ --}}
    <div>
      <div class="wi-card" style="margin-bottom:16px">
        <form method="GET" action="{{ route('walkin.index') }}" class="wi-filters">
          <div class="wi-field wi-search">
            <label>Search</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name, contact or receipt no.">
          </div>
          <div class="wi-field">
            <label>Method</label>
            <select name="method" class="form-control">
              <option value="">All</option>
              @foreach($methods as $m)<option value="{{ $m }}" @selected(request('method') === $m)>{{ $m }}</option>@endforeach
            </select>
          </div>
          <div class="wi-field">
            <label>Status</label>
            <select name="status" class="form-control">
              <option value="">All</option>
              <option value="Paid"    @selected(request('status') === 'Paid')>Paid</option>
              <option value="Partial" @selected(request('status') === 'Partial')>Partial</option>
            </select>
          </div>
          <div class="wi-field"><label>From</label><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
          <div class="wi-field"><label>To</label><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
          <div class="wi-filters-actions">
            <button class="btn btn-primary" type="submit">Filter</button>
            <a class="btn btn-secondary" href="{{ route('walkin.index') }}">Reset</a>
          </div>
        </form>
        @if($errors->has('date_to') || $errors->has('search') || $errors->has('method') || $errors->has('status') || $errors->has('date_from'))
          <div class="wi-err" style="margin-top:10px">{{ $errors->first('date_to') ?: $errors->first() }}</div>
        @endif
      </div>

      <div class="wi-table-card">
        <div class="wi-table-top">
          <b>Walk-In Records</b>
          <span class="wi-muted" style="font-size:13px">{{ number_format($payments->total()) }} {{ \Illuminate\Support\Str::plural('record', $payments->total()) }}</span>
        </div>
        <div class="wi-table-scroll">
          <table class="wi-table">
            <thead>
              <tr>
                <th>Receipt No.</th>
                <th>Type</th>
                <th>Customer Name</th>
                <th>Contact Number</th>
                <th>Day Pass Rate</th>
                <th>Amount Paid</th>
                <th>Payment Method</th>
                <th>Payment Date</th>
                <th>Recorded By</th>
                <th>Status</th>
                @if(auth()->user()->isAdmin())<th></th>@endif
              </tr>
            </thead>
            <tbody>
            @forelse($payments as $p)
              <tr>
                <td class="wi-mono">{{ $p->receipt_number }}</td>
                <td><span class="wi-badge walkin">Walk-In / {{ $p->pass_type }}</span></td>
                <td class="wi-name">{{ $p->customer_name }}</td>
                <td>{{ $p->contact_number ?: '—' }}</td>
                <td class="wi-money">₱{{ number_format((float) $p->day_pass_rate, 2) }}</td>
                <td class="wi-money">₱{{ number_format((float) $p->amount, 2) }}</td>
                <td>{{ $p->method }}</td>
                <td style="white-space:nowrap">{{ $p->payment_date->format('M d, Y') }}</td>
                <td>{{ $p->processedBy?->name ?? '—' }}</td>
                <td><span class="wi-badge {{ $p->status === 'Paid' ? 'paid' : 'partial' }}">{{ $p->status }}</span></td>
                @if(auth()->user()->isAdmin())
                <td>
                  <form method="POST" action="{{ route('walkin.destroy', $p->id) }}"
                        onsubmit="return confirm('Delete walk-in record {{ $p->receipt_number }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm btn-danger-soft">Delete</button>
                  </form>
                </td>
                @endif
              </tr>
            @empty
              <tr><td colspan="{{ auth()->user()->isAdmin() ? 11 : 10 }}" style="border-bottom:0">
                <div class="wi-empty"><b>No walk-in records found</b>Record a Day Pass using the form, or adjust the filters.</div>
              </td></tr>
            @endforelse
            </tbody>
          </table>
        </div>

        @if($payments->total() > 0)
        <div class="wi-pager">
          <span>Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ number_format($payments->total()) }}</span>
          <div style="display:flex;align-items:center;gap:8px">
            <a class="btn btn-secondary btn-sm" href="{{ $payments->previousPageUrl() ?? '#' }}" @if($payments->onFirstPage()) aria-disabled="true" @endif>← Previous</a>
            <span>Page {{ $payments->currentPage() }} of {{ $payments->lastPage() }}</span>
            <a class="btn btn-secondary btn-sm" href="{{ $payments->nextPageUrl() ?? '#' }}" @if(! $payments->hasMorePages()) aria-disabled="true" @endif>Next →</a>
          </div>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>

<script>
  (function () {
    var rate   = {{ (float) $dayPassRate }};
    var amount = document.getElementById('amount');
    var hint   = document.getElementById('amount-hint');
    var form   = document.getElementById('walkin-form');
    var btn    = document.getElementById('walkin-submit');

    function peso(n){ return '₱' + Number(n).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}); }
    function refresh(){
      var v = parseFloat(amount.value);
      hint.className = 'wi-hint';
      if (isNaN(v) || v <= 0) { hint.textContent = ''; return; }
      if (v < rate)      { hint.textContent = 'Partial payment — balance ' + peso(rate - v) + '. Status will be "Partial".'; hint.classList.add('warn'); }
      else if (v > rate) { hint.textContent = 'Amount is ' + peso(v - rate) + ' above the Day Pass rate.'; }
      else               { hint.textContent = 'Matches the Day Pass rate. Status will be "Paid".'; }
    }
    amount.addEventListener('input', refresh);
    refresh();

    // Prevent double submits (the server also rejects duplicates).
    form.addEventListener('submit', function () {
      if (!form.checkValidity()) return;
      btn.disabled = true; btn.textContent = 'Saving…';
    });
  })();
</script>
@endsection