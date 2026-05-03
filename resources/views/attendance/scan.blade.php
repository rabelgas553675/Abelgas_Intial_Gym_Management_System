@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'QR Attendance Scanner – IRONFORGE')
@section('active_nav', 'attendance')

@section('content')

<div style="margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;">
  <div>
    <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">QR Attendance Scanner</h1>
    <p style="color:var(--muted);font-size:14px;">Scan member or staff QR codes to record attendance</p>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="{{ route('attendance.qr-list') }}" class="btn btn-secondary btn-sm">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
        <rect x="3" y="14" width="7" height="7"/><line x1="14" y1="14" x2="21" y2="14"/>
        <line x1="14" y1="21" x2="21" y2="21"/><line x1="17.5" y1="14" x2="17.5" y2="21"/>
      </svg>
      QR Codes
    </a>
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary btn-sm">Full Log</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:420px 1fr;gap:20px;align-items:start;">

  {{-- ══ LEFT: Scanner panel ══ --}}
  <div>
    <div style="background:linear-gradient(135deg,#0f0f1a,#1a1a2e);border-radius:20px;
                padding:28px;box-shadow:0 8px 32px rgba(0,0,0,.25);">

      {{-- Live Clock --}}
      <div style="text-align:center;margin-bottom:24px;">
        <div id="liveClock" style="font-size:36px;font-weight:700;color:#fff;letter-spacing:2px;">--:--:--</div>
        <div id="liveDate"  style="color:rgba(255,255,255,.55);font-size:13px;margin-top:2px;"></div>
      </div>

      {{-- Cooldown Overlay --}}
      <div id="cooldownOverlay"
           style="display:none;position:relative;border-radius:14px;overflow:hidden;
                  background:rgba(15,15,26,0.92);border:3px solid rgba(200,255,0,0.5);
                  margin-bottom:12px;padding:40px 20px;text-align:center;z-index:10;">
        <div style="font-size:42px;font-weight:800;color:var(--accent);" id="cooldownNumber">3</div>
        <div style="color:rgba(255,255,255,0.6);font-size:13px;margin-top:6px;">
          Next scan ready in <span id="cooldownSec">3</span>s…
        </div>
        <div style="margin-top:16px;height:4px;background:rgba(255,255,255,0.1);border-radius:4px;overflow:hidden;">
          <div id="cooldownBar"
               style="height:100%;width:100%;background:var(--accent);
                      transition:width 3s linear;border-radius:4px;"></div>
        </div>
      </div>

      {{-- Camera QR Reader --}}
      <div id="reader" style="border-radius:14px;overflow:hidden;
                               border:3px solid rgba(200,255,0,.4);margin-bottom:12px;"></div>

      {{-- Scan line animation --}}
      <div style="height:3px;background:linear-gradient(90deg,transparent,var(--accent),transparent);
                  animation:scanMove 2s linear infinite;border-radius:2px;margin-bottom:12px;"></div>

      {{-- Scanner status badge --}}
      <div id="scannerStatus" style="text-align:center;margin-bottom:12px;">
        <span id="statusBadge"
              style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;
                     border-radius:20px;font-size:12px;font-weight:700;
                     background:rgba(74,222,128,0.12);color:#4ade80;
                     border:1px solid rgba(74,222,128,0.3);">
          <span id="statusDot"
                style="width:7px;height:7px;border-radius:50%;background:#4ade80;
                       animation:pulse 1.5s ease-in-out infinite;display:inline-block;"></span>
          Scanner ready
        </span>
      </div>

      <p style="text-align:center;color:rgba(255,255,255,.45);font-size:12px;margin-bottom:12px;">
        Point camera at any member, staff, or trainer QR code
      </p>

      {{-- Manual QR input --}}
      <div style="display:flex;gap:8px;margin-bottom:6px;">
        <input type="text" id="manualInput"
               placeholder="Paste QR data or type ID..."
               style="flex:1;padding:10px 14px;background:rgba(255,255,255,.08);
                      color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9px 0 0 9px;
                      font-size:13px;outline:none;font-family:'DM Sans',sans-serif;"/>
        <button id="manualSubmitBtn" onclick="processManual()"
                style="background:var(--accent);color:#111;border:none;border-radius:0 9px 9px 0;
                       padding:0 16px;font-weight:700;cursor:pointer;transition:opacity 0.2s;">
          →
        </button>
      </div>
      <small style="color:rgba(255,255,255,.3);font-size:11px;display:block;margin-bottom:14px;">
        Type a numeric ID for quick lookup · 3-second cooldown between scans
      </small>

      {{-- Manual Entry Panel --}}
      <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
                  border-radius:12px;padding:18px;">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);
                    text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">
          Manual Attendance Entry
        </div>

        <select id="manualMemberId"
                style="width:100%;padding:9px 12px;background:#1a1a2e;
                       color:#fff;border:1px solid rgba(255,255,255,.18);border-radius:8px;
                       font-size:13px;margin-bottom:10px;font-family:'DM Sans',sans-serif;appearance:auto;">
          <option value="" style="background:#1a1a2e;color:#fff;">— Select Person —</option>
          <optgroup label="Members" style="background:#0f0f1a;color:var(--accent);">
            @foreach($allMembers as $m)
              <option value="{{ $m->id }}"
                      style="background:#1a1a2e;color:{{ $m->status === 'Expired' ? '#fbbf24' : '#fff' }};">
                {{ $m->name }} (Member) {{ $m->status === 'Expired' ? '⚠️' : '' }}
              </option>
            @endforeach
          </optgroup>
          <optgroup label="Staff / Instructors / Admin" style="background:#0f0f1a;color:#60a5fa;">
            @forelse($allStaff ?? [] as $s)
              <option value="staff-{{ $s->id }}" style="background:#1a1a2e;color:#fff;">
                {{ $s->name }} ({{ ucfirst($s->role) }})
              </option>
            @empty
              <option disabled style="background:#1a1a2e;color:rgba(255,255,255,0.3);">No Staff Records Found</option>
            @endforelse
          </optgroup>
        </select>

        <div style="display:flex;gap:8px;">
          <button id="timeInBtn" onclick="manualRecord('timein')"
                  style="flex:1;padding:9px;background:#4ade80;color:#111;border:none;
                         border-radius:8px;font-weight:700;cursor:pointer;font-size:13px;
                         transition:opacity 0.2s;">
            ↓ Time In
          </button>
          <button id="timeOutBtn" onclick="manualRecord('timeout')"
                  style="flex:1;padding:9px;background:#60a5fa;color:#111;border:none;
                         border-radius:8px;font-weight:700;cursor:pointer;font-size:13px;
                         transition:opacity 0.2s;">
            ↑ Time Out
          </button>
        </div>
        <div id="manualMsg" style="margin-top:8px;font-size:12px;min-height:16px;"></div>
      </div>

      {{-- Counters --}}
      <div style="display:flex;justify-content:space-between;margin-top:20px;padding-top:16px;
                  border-top:1px solid rgba(255,255,255,.1);">
        <div style="text-align:center;">
          <div id="insideCount" style="font-size:22px;font-weight:700;color:#4ade80;">{{ $insideNow }}</div>
          <div style="color:rgba(255,255,255,.45);font-size:11px;">Inside Now</div>
        </div>
        <div style="text-align:center;">
          <div id="todayTotal" style="font-size:22px;font-weight:700;color:#fbbf24;">{{ $todayLogs->count() }}</div>
          <div style="color:rgba(255,255,255,.45);font-size:11px;">Today's Visits</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:22px;font-weight:700;color:#fff;">{{ now()->format('d') }}</div>
          <div style="color:rgba(255,255,255,.45);font-size:11px;">{{ now()->format('M Y') }}</div>
        </div>
      </div>
    </div>

    {{-- Result Card --}}
    <div id="resultCard" style="display:none;border-radius:16px;padding:24px;text-align:center;
                                 margin-top:16px;animation:popIn .35s ease;">
      <div id="resultAvatar"     style="margin-bottom:10px;"></div>
      <h5  id="resultName"       style="font-weight:700;margin-bottom:4px;"></h5>
      <p   id="resultMembership" style="color:var(--muted);font-size:13px;margin-bottom:8px;"></p>
      <div id="resultTimes"      style="margin-bottom:8px;"></div>
      <p   id="resultMessage"    style="font-weight:600;font-size:14px;margin:0;"></p>
    </div>
  </div>

  {{-- ══ RIGHT: Live attendance table ══ --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
    <div style="padding:18px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;">
      <div style="font-size:15px;font-weight:700;">
        Today's Attendance — {{ now()->format('F d, Y') }}
      </div>
      <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);">
        <span id="liveDot"
              style="width:8px;height:8px;border-radius:50%;background:#4ade80;
                     animation:pulse 2s ease-in-out infinite;display:inline-block;"></span>
        <span id="liveLabel">Live</span>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--surface2);">
            <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;"></th>
            <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Name</th>
            <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Time In</th>
            <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Time Out</th>
            <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Duration</th>
            <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Method</th>
            <th style="padding:11px 16px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;">Status</th>
          </tr>
        </thead>
        <tbody id="todayLogBody">
          @forelse($todayLogs as $log)
          @php
            $personName  = $log->member?->name ?? $log->user?->name ?? 'Staff';
            $personRole  = $log->member?->membership_type ?? ucfirst($log->user?->role ?? 'Staff');
            $personPhoto = $log->member?->user?->photo ?? $log->member?->photo ?? $log->user?->photo;
            $isStaffRow  = !$log->member_id;
            $dataKey     = $isStaffRow ? 'staff-'.$log->staff_user_id : $log->member_id;
          @endphp
          <tr id="log-{{ $log->id }}" data-member="{{ $dataKey }}"
              style="border-top:1px solid var(--border);">
            <td style="padding:12px 16px;">
              @if($personPhoto)
                <img src="{{ asset('storage/'.$personPhoto) }}"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid var(--border);"/>
              @else
                <div style="width:34px;height:34px;border-radius:50%;
                            background:{{ !$isStaffRow ? 'rgba(200,255,0,0.08)' : 'rgba(96,165,250,0.08)' }};
                            border:1px solid {{ !$isStaffRow ? 'rgba(200,255,0,0.15)' : 'rgba(96,165,250,0.15)' }};
                            display:flex;align-items:center;justify-content:center;
                            font-size:12px;font-weight:700;
                            color:{{ !$isStaffRow ? 'var(--accent)' : '#60a5fa' }};">
                  {{ strtoupper(substr($personName,0,1)) }}
                </div>
              @endif
            </td>
            <td style="padding:12px 16px;">
              <div style="font-size:13px;font-weight:600;">{{ $personName }}</div>
              <div style="font-size:11px;color:var(--muted);">{{ $personRole }}</div>
            </td>
            <td style="padding:12px 16px;font-size:13px;">{{ $log->time_in?->format('h:i A') ?? '—' }}</td>
            <td style="padding:12px 16px;font-size:13px;">
              @if($log->time_out)
                {{ $log->time_out->format('h:i A') }}
              @else
                <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;
                             font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span>
              @endif
            </td>
            <td style="padding:12px 16px;font-size:13px;color:var(--muted);">{{ $log->duration_formatted }}</td>
            <td style="padding:12px 16px;">
              <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;
                           {{ $log->entry_method === 'manual' ? 'background:rgba(251,191,36,0.15);color:#fbbf24;' : 'background:var(--surface2);color:var(--muted);' }}">
                {{ $log->entry_method === 'manual' ? 'Manual' : 'QR Scan' }}
              </span>
            </td>
            <td style="padding:12px 16px;">
              @if($log->time_out)
                <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;
                             font-weight:700;background:var(--surface2);color:var(--muted);">Done</span>
              @else
                <span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;
                             font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span>
              @endif
            </td>
          </tr>
          @empty
          <tr id="emptyRow">
            <td colspan="7" style="padding:48px;text-align:center;color:var(--muted);font-size:14px;">
              No scans today yet. Start scanning!
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<style>
@keyframes scanMove {
  0%   { opacity:0; transform:translateY(-60px); }
  50%  { opacity:1; }
  100% { opacity:0; transform:translateY(60px); }
}
@keyframes popIn {
  from { opacity:0; transform:scale(.88); }
  to   { opacity:1; transform:scale(1); }
}
@keyframes pulse {
  0%,100% { opacity:1; }
  50%      { opacity:0.3; }
}
@keyframes slideIn {
  from { opacity:0; transform:translateY(-8px); }
  to   { opacity:1; transform:translateY(0); }
}
.result-timein  { background:linear-gradient(135deg,#d4edda,#c3e6cb); border:2px solid #28a745; }
.result-timeout { background:linear-gradient(135deg,#cce5ff,#b8daff); border:2px solid #007bff; }
.result-error   { background:linear-gradient(135deg,#f8d7da,#f5c6cb); border:2px solid #dc3545; color:#111; }
.result-expired,.result-suspended { background:linear-gradient(135deg,#fff3cd,#ffeeba); border:2px solid #ffc107; color:#111; }
.row-new { animation: slideIn 0.4s ease; }
</style>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── Live Clock ────────────────────────────────────────────────────────────
  function updateClock() {
    const now = new Date();
    const clockEl = document.getElementById('liveClock');
    const dateEl  = document.getElementById('liveDate');
    if (clockEl) clockEl.textContent = now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    if (dateEl)  dateEl.textContent  = now.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  }
  setInterval(updateClock, 1000);
  updateClock();

  // ── Cooldown state ────────────────────────────────────────────────────────
  let scanning     = false;
  let lastScanned  = '';
  let cooldownTimer = null;
  const COOLDOWN_MS = 3000;

  function startCooldown() {
    scanning = true;
    setStatus('locked');

    const overlay = document.getElementById('cooldownOverlay');
    const reader  = document.getElementById('reader');
    if (overlay) overlay.style.display = 'block';
    if (reader)  reader.style.opacity  = '0.15';

    setButtonsEnabled(false);

    let remaining = COOLDOWN_MS / 1000;
    const numEl = document.getElementById('cooldownNumber');
    const secEl = document.getElementById('cooldownSec');
    if (numEl) numEl.textContent = remaining;
    if (secEl) secEl.textContent = remaining;

    const bar = document.getElementById('cooldownBar');
    if (bar) {
      bar.style.transition = 'none';
      bar.style.width      = '100%';
      bar.getBoundingClientRect();
      bar.style.transition = `width ${COOLDOWN_MS}ms linear`;
      bar.style.width      = '0%';
    }

    cooldownTimer = setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) remaining = 0;
      if (numEl) numEl.textContent = remaining;
      if (secEl) secEl.textContent = remaining;
    }, 1000);

    setTimeout(() => {
      clearInterval(cooldownTimer);
      scanning    = false;
      lastScanned = '';
      if (overlay) overlay.style.display = 'none';
      if (reader)  reader.style.opacity  = '1';
      setButtonsEnabled(true);
      setStatus('ready');
    }, COOLDOWN_MS);
  }

  // ── Status badge ──────────────────────────────────────────────────────────
  function setStatus(state) {
    const badge = document.getElementById('statusBadge');
    if (!badge) return;
    if (state === 'ready') {
      badge.innerHTML = `<span style="width:7px;height:7px;border-radius:50%;background:#4ade80;
        animation:pulse 1.5s ease-in-out infinite;display:inline-block;"></span> Scanner ready`;
      badge.style.background = 'rgba(74,222,128,0.12)';
      badge.style.color      = '#4ade80';
      badge.style.border     = '1px solid rgba(74,222,128,0.3)';
    } else {
      badge.innerHTML = `<span style="width:7px;height:7px;border-radius:50%;background:#f87171;
        display:inline-block;"></span> Cooldown — next scan in 3s`;
      badge.style.background = 'rgba(248,113,113,0.12)';
      badge.style.color      = '#f87171';
      badge.style.border     = '1px solid rgba(248,113,113,0.3)';
    }
  }

  // ── Enable / disable buttons ──────────────────────────────────────────────
  function setButtonsEnabled(enabled) {
    ['manualSubmitBtn', 'timeInBtn', 'timeOutBtn'].forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      el.disabled      = !enabled;
      el.style.opacity = enabled ? '1' : '0.4';
      el.style.cursor  = enabled ? 'pointer' : 'not-allowed';
    });
    const input = document.getElementById('manualInput');
    if (input) {
      input.disabled      = !enabled;
      input.style.opacity = enabled ? '1' : '0.5';
    }
  }

  // ── QR Scanner init ───────────────────────────────────────────────────────
  const html5QrCode = new Html5Qrcode("reader");
  html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: { width: 240, height: 240 } },
    (decodedText) => {
      if (scanning) return;
      if (decodedText === lastScanned) return;
      lastScanned = decodedText;
      startCooldown();
      processQR(decodedText);
    },
    () => {}
  ).catch(() => {
    const reader = document.getElementById('reader');
    if (reader) reader.innerHTML =
      '<div style="color:rgba(255,255,255,.5);text-align:center;padding:40px;font-size:13px;">' +
      'Camera unavailable.<br>Use Manual Entry below.</div>';
  });

  // ── Manual input ──────────────────────────────────────────────────────────
  function processManual() {
    if (scanning) return;
    const input = document.getElementById('manualInput');
    const val   = input ? input.value.trim() : '';
    if (!val) return;
    startCooldown();
    processQR(val);
    if (input) input.value = '';
  }
  window.processManual = processManual;

  const manualInput = document.getElementById('manualInput');
  if (manualInput) {
    manualInput.addEventListener('keydown', e => {
      if (e.key === 'Enter') processManual();
    });
  }

  // ── Core AJAX ─────────────────────────────────────────────────────────────
  function processQR(qrData) {
    fetch('{{ route("attendance.scan.process") }}', {
      method:  'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: 'qr_data=' + encodeURIComponent(qrData)
    })
    .then(r => r.json())
    .then(data => showResult(data))
    .catch(() => showResult({ success: false, message: 'Server error. Try again.' }));
  }

  // ── Manual entry panel ────────────────────────────────────────────────────
  function manualRecord(action) {
    if (scanning) return;
    const mid = document.getElementById('manualMemberId')?.value;
    const msg = document.getElementById('manualMsg');
    if (!mid) {
      if (msg) msg.innerHTML = '<span style="color:#fbbf24;">Select a person first.</span>';
      return;
    }
    if (msg) msg.innerHTML = '<span style="color:rgba(255,255,255,0.5);">Processing…</span>';
    startCooldown();

    fetch('{{ route("attendance.manual") }}', {
      method:  'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: 'manual_member_id=' + encodeURIComponent(mid) + '&manual_action=' + action
    })
    .then(r => { if (!r.ok) throw new Error('Server error'); return r.json(); })
    .then(data => {
      if (msg) msg.innerHTML = data.success
        ? `<span style="color:#4ade80;">✅ ${data.message}</span>`
        : `<span style="color:#f87171;">❌ ${data.message}</span>`;
      if (data.success) {
        if (action === 'timein') appendLogRow(data, 'timein');
        else updateLogRowTimeout(data);
        playBeep(true);
        const sel = document.getElementById('manualMemberId');
        if (sel) sel.value = '';
      } else {
        playBeep(false);
      }
    })
    .catch(() => {
      if (msg) msg.innerHTML = '<span style="color:#f87171;">❌ Connection error.</span>';
    });
  }
  window.manualRecord = manualRecord;

  // ── Show result card ──────────────────────────────────────────────────────
  function showResult(data) {
    const card    = document.getElementById('resultCard');
    if (!card) return;
    const isStaff = data.is_staff || false;

    const avatar = data.photo
      ? `<img src="${data.photo}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:4px solid #fff;margin:0 auto;display:block;">`
      : `<div style="width:80px;height:80px;border-radius:50%;
          background:${isStaff ? 'linear-gradient(135deg,#60a5fa,#3b82f6)' : 'linear-gradient(135deg,#c8ff00,#4ade80)'};
          display:flex;align-items:center;justify-content:center;font-size:28px;
          font-weight:700;color:#111;margin:0 auto;">
          ${(data.member || '?').charAt(0).toUpperCase()}</div>`;

    document.getElementById('resultAvatar').innerHTML       = avatar;
    document.getElementById('resultName').textContent       = data.member     || 'Unknown';
    document.getElementById('resultMembership').textContent = data.membership || '';

    let cls = 'result-error';
    if (data.success && data.action === 'timein')  { cls = 'result-timein';  appendLogRow(data, 'timein'); }
    if (data.success && data.action === 'timeout') { cls = 'result-timeout'; updateLogRowTimeout(data); }
    if (data.status === 'expired')   cls = 'result-expired';
    if (data.status === 'suspended') cls = 'result-suspended';
    card.className = cls;

    const timesEl = document.getElementById('resultTimes');
    if (data.success && data.action === 'timein') {
      timesEl.innerHTML =
        `<span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">
          Time In: <strong>${data.time_in}</strong></span>
         ${!isStaff ? `<span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">
          Valid Until: <strong>${data.end_date || 'N/A'}</strong></span>` : ''}`;
    } else if (data.success && data.action === 'timeout') {
      timesEl.innerHTML =
        `<span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">
          In: <strong>${data.time_in}</strong></span>
         <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">
          Out: <strong>${data.time_out}</strong></span>
         <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">
          Duration: <strong>${data.duration}</strong></span>`;
    } else {
      timesEl.innerHTML = '';
    }

    document.getElementById('resultMessage').textContent = data.message || '';
    card.style.display = 'block';
    playBeep(data.success);
  }

  // ── Append Time-In row ────────────────────────────────────────────────────
  function appendLogRow(data, action) {
    const tbody    = document.getElementById('todayLogBody');
    if (!tbody) return;
    const emptyRow = document.getElementById('emptyRow');
    if (emptyRow) emptyRow.remove();

    const existing = tbody.querySelector(`[data-member="${data.member_id}"]`);
    if (existing) existing.remove();

    const isStaff = data.is_staff ||
                    (typeof data.member_id === 'string' && data.member_id.toString().startsWith('staff-'));

    const avatar = data.photo
      ? `<img src="${data.photo}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">`
      : `<div style="width:34px;height:34px;border-radius:50%;
          background:${isStaff ? 'rgba(96,165,250,0.08)' : 'rgba(200,255,0,0.08)'};
          border:1px solid ${isStaff ? 'rgba(96,165,250,0.15)' : 'rgba(200,255,0,0.15)'};
          display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;
          color:${isStaff ? '#60a5fa' : 'var(--accent)'};">
          ${(data.member || '?').charAt(0).toUpperCase()}</div>`;

    const methodBadge = data.entry_method === 'manual'
      ? `<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(251,191,36,0.15);color:#fbbf24;">Manual</span>`
      : `<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:var(--surface2);color:var(--muted);">QR Scan</span>`;

    const insideBadge = `<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span>`;

    const row = document.createElement('tr');
    row.setAttribute('data-member', data.member_id);
    row.classList.add('row-new');
    row.style.borderTop = '1px solid var(--border)';
    row.innerHTML = `
      <td style="padding:12px 16px;">${avatar}</td>
      <td style="padding:12px 16px;">
        <div style="font-size:13px;font-weight:600;">${data.member}</div>
        <div style="font-size:11px;color:var(--muted);">${data.membership || ''}</div>
      </td>
      <td style="padding:12px 16px;font-size:13px;">${data.time_in}</td>
      <td style="padding:12px 16px;">${insideBadge}</td>
      <td style="padding:12px 16px;font-size:13px;color:var(--muted);">—</td>
      <td style="padding:12px 16px;">${methodBadge}</td>
      <td style="padding:12px 16px;">${insideBadge}</td>`;
    tbody.prepend(row);

    const insideEl = document.getElementById('insideCount');
    const totalEl  = document.getElementById('todayTotal');
    if (insideEl) insideEl.textContent = parseInt(insideEl.textContent || 0) + 1;
    if (totalEl)  totalEl.textContent  = parseInt(totalEl.textContent  || 0) + 1;
  }

  // ── Update row on Time Out ────────────────────────────────────────────────
  function updateLogRowTimeout(data) {
    const row = document.querySelector(`[data-member="${data.member_id}"]`);
    if (row) {
      row.cells[3].textContent = data.time_out;
      row.cells[4].textContent = data.duration;
      row.cells[6].innerHTML   =
        '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:var(--surface2);color:var(--muted);">Done</span>';
      row.classList.add('row-new');
      setTimeout(() => row.classList.remove('row-new'), 500);
    }
    const insideEl = document.getElementById('insideCount');
    if (insideEl) insideEl.textContent = Math.max(0, parseInt(insideEl.textContent || 1) - 1);
  }

  // ── Beep ──────────────────────────────────────────────────────────────────
  function playBeep(success) {
    try {
      const ctx  = new (window.AudioContext || window.webkitAudioContext)();
      const osc  = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.frequency.value = success ? 880 : 300;
      osc.type = 'sine';
      gain.gain.setValueAtTime(0.3, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
      osc.start();
      osc.stop(ctx.currentTime + 0.4);
    } catch (e) {}
  }

  // ── Live polling ──────────────────────────────────────────────────────────
  const POLL_INTERVAL_MS = 10000;

  function getRenderedKeys() {
    return Array.from(
      document.querySelectorAll('#todayLogBody tr[data-member]')
    ).map(r => r.getAttribute('data-member'));
  }

  function pollAttendance() {
    fetch('{{ route("attendance.live") }}', {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ known_keys: getRenderedKeys() })
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
      if (!data || !data.rows || data.rows.length === 0) { flashLiveIndicator(); return; }
      data.rows.forEach(row => {
        if (document.querySelector(`[data-member="${row.key}"]`)) return;
        const tbody    = document.getElementById('todayLogBody');
        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) emptyRow.remove();
        const el = document.createElement('tr');
        el.setAttribute('data-member', row.key);
        el.classList.add('row-new');
        el.style.borderTop = '1px solid var(--border)';
        el.innerHTML = row.html;
        tbody.prepend(el);
      });
      const insideEl = document.getElementById('insideCount');
      const totalEl  = document.getElementById('todayTotal');
      if (data.inside_count !== undefined && insideEl) insideEl.textContent = data.inside_count;
      if (data.today_total  !== undefined && totalEl)  totalEl.textContent  = data.today_total;
      flashLiveIndicator();
    })
    .catch(() => {});
  }

  function flashLiveIndicator() {
    const dot   = document.getElementById('liveDot');
    const label = document.getElementById('liveLabel');
    if (!dot) return;
    dot.style.background = '#fbbf24';
    if (label) label.textContent = 'Synced';
    setTimeout(() => {
      dot.style.background = '#4ade80';
      if (label) label.textContent = 'Live';
    }, 600);
  }

  setInterval(pollAttendance, POLL_INTERVAL_MS);

}); // end DOMContentLoaded
</script>

@endsection