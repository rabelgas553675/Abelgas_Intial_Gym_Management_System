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

  {{-- LEFT: Scanner --}}
  <div>
    <div style="background:linear-gradient(135deg,#0f0f1a,#1a1a2e);border-radius:20px;
                padding:28px;box-shadow:0 8px 32px rgba(0,0,0,.25);">

      {{-- Live Clock --}}
      <div style="text-align:center;margin-bottom:24px;">
        <div id="liveClock" style="font-size:36px;font-weight:700;color:#fff;letter-spacing:2px;">--:--:--</div>
        <div id="liveDate"  style="color:rgba(255,255,255,.55);font-size:13px;margin-top:2px;"></div>
      </div>

      {{-- Camera QR Reader --}}
      <div id="reader" style="border-radius:14px;overflow:hidden;border:3px solid rgba(200,255,0,.4);margin-bottom:12px;"></div>

      {{-- Scan line animation --}}
      <div style="height:3px;background:linear-gradient(90deg,transparent,var(--accent),transparent);
                  animation:scanMove 2s linear infinite;border-radius:2px;margin-bottom:12px;"></div>

      <p style="text-align:center;color:rgba(255,255,255,.45);font-size:12px;margin-bottom:12px;">
        Point camera at any member, staff, or trainer QR code
      </p>

      {{-- Manual QR input --}}
      <div style="display:flex;gap:8px;margin-bottom:6px;">
        <input type="text" id="manualInput"
               placeholder="Paste QR data or type member ID..."
               style="flex:1;padding:10px 14px;background:rgba(255,255,255,.08);
                      color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9px 0 0 9px;
                      font-size:13px;outline:none;font-family:'DM Sans',sans-serif;"/>
        <button onclick="processManual()"
                style="background:var(--accent);color:#111;border:none;border-radius:0 9px 9px 0;
                       padding:0 16px;font-weight:700;cursor:pointer;">
          →
        </button>
      </div>
      <small style="color:rgba(255,255,255,.3);font-size:11px;display:block;margin-bottom:14px;">
        Type a numeric member ID for quick lookup
      </small>

      {{-- Manual Entry Panel --}}
      <div style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
                  border-radius:12px;padding:18px;">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);
                    text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;">
          Manual Attendance Entry
        </div>
        <select id="manualMemberId"
                style="width:100%;padding:9px 12px;background:rgba(255,255,255,.08);
                       color:#fff;border:1px solid rgba(255,255,255,.18);border-radius:8px;
                       font-size:13px;margin-bottom:10px;font-family:'DM Sans',sans-serif;">
          <option value="">— Select Member —</option>
          @foreach($allMembers as $m)
            <option value="{{ $m->id }}"
                    {{ $m->status === 'Expired' ? 'style="color:#fbbf24;"' : '' }}>
              {{ $m->name }} ({{ $m->membership_type }})
              {{ $m->status === 'Expired' ? '⚠️ Expired' : '' }}
            </option>
          @endforeach
        </select>
        <div style="display:flex;gap:8px;">
          <button onclick="manualRecord('timein')"
                  style="flex:1;padding:9px;background:#4ade80;color:#111;border:none;
                         border-radius:8px;font-weight:700;cursor:pointer;font-size:13px;">
            ↓ Time In
          </button>
          <button onclick="manualRecord('timeout')"
                  style="flex:1;padding:9px;background:#60a5fa;color:#111;border:none;
                         border-radius:8px;font-weight:700;cursor:pointer;font-size:13px;">
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
      <div id="resultAvatar" style="margin-bottom:10px;"></div>
      <h5 id="resultName" style="font-weight:700;margin-bottom:4px;"></h5>
      <p  id="resultMembership" style="color:var(--muted);font-size:13px;margin-bottom:8px;"></p>
      <div id="resultTimes" style="margin-bottom:8px;"></div>
      <p  id="resultMessage" style="font-weight:600;font-size:14px;margin:0;"></p>
    </div>
  </div>

  {{-- RIGHT: Today's Log --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
    <div style="padding:18px 20px;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;">
      <div style="font-size:15px;font-weight:700;">
        Today's Attendance — {{ now()->format('F d, Y') }}
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
          <tr id="log-{{ $log->id }}" data-member="{{ $log->member_id }}"
              style="border-top:1px solid var(--border);">
            <td style="padding:12px 16px;">
              @if($log->member?->photo)
                <img src="{{ asset('storage/'.$log->member->photo) }}"
                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid var(--border);"/>
              @elseif($log->member)
                <div style="width:34px;height:34px;border-radius:50%;background:rgba(200,255,0,0.08);
                            border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                            justify-content:center;font-size:12px;font-weight:700;color:var(--accent);">
                  {{ strtoupper(substr($log->member->name,0,1)) }}
                </div>
              @else
                <div style="width:34px;height:34px;border-radius:50%;background:var(--surface2);
                            border:1px solid var(--border);display:flex;align-items:center;justify-content:center;">
                  <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                  </svg>
                </div>
              @endif
            </td>
            <td style="padding:12px 16px;">
              <div style="font-size:13px;font-weight:600;">{{ $log->member?->name ?? 'Staff / System' }}</div>
              <div style="font-size:11px;color:var(--muted);">{{ $log->member?->membership_type ?? ($log->scanned_by ? 'Staff QR' : '—') }}</div>
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
            <td style="padding:12px 16px;font-size:13px;color:var(--muted);">
              {{ $log->duration_formatted }}
            </td>
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
.result-timein  { background:linear-gradient(135deg,#d4edda,#c3e6cb); border:2px solid #28a745; }
.result-timeout { background:linear-gradient(135deg,#cce5ff,#b8daff); border:2px solid #007bff; }
.result-error   { background:linear-gradient(135deg,#f8d7da,#f5c6cb); border:2px solid #dc3545; color:#111; }
.result-expired, .result-suspended { background:linear-gradient(135deg,#fff3cd,#ffeeba); border:2px solid #ffc107; color:#111; }
</style>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// Live Clock
function updateClock() {
  const now = new Date();
  document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
  document.getElementById('liveDate').textContent  = now.toLocaleDateString('en-PH',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
}
setInterval(updateClock, 1000); updateClock();

// QR Scanner
let lastScanned = '', cooldown = false;
const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
  { facingMode: "environment" },
  { fps: 10, qrbox: { width: 240, height: 240 } },
  (decodedText) => {
    if (cooldown || decodedText === lastScanned) return;
    lastScanned = decodedText; cooldown = true;
    processQR(decodedText);
    setTimeout(() => { cooldown = false; lastScanned = ''; }, 4000);
  },
  () => {}
).catch(() => {
  document.getElementById('reader').innerHTML =
    '<div style="color:rgba(255,255,255,.5);text-align:center;padding:40px;">Camera unavailable.<br>Use Manual Entry below.</div>';
});

function processManual() {
  const val = document.getElementById('manualInput').value.trim();
  if (!val) return;
  processQR(val);
  document.getElementById('manualInput').value = '';
}
document.getElementById('manualInput').addEventListener('keydown', e => { if (e.key==='Enter') processManual(); });

function processQR(qrData) {
  fetch('{{ route("attendance.scan.process") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: 'qr_data=' + encodeURIComponent(qrData)
  })
  .then(r => r.json())
  .then(data => showResult(data))
  .catch(() => showResult({ success: false, message: 'Server error. Try again.' }));
}

function manualRecord(action) {
  const mid = document.getElementById('manualMemberId').value;
  if (!mid) { document.getElementById('manualMsg').innerHTML = '<span style="color:#fbbf24;">Select a member first.</span>'; return; }

  fetch('{{ route("attendance.manual") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    body: 'manual_member_id=' + encodeURIComponent(mid) + '&manual_action=' + action
  })
  .then(r => r.json())
  .then(data => {
    const msg = document.getElementById('manualMsg');
    msg.innerHTML = data.success
      ? `<span style="color:#4ade80;">✅ ${data.message}</span>`
      : `<span style="color:#f87171;">❌ ${data.message}</span>`;
    if (data.success && action === 'timein') {
      appendLogRow({ member_id: mid, member: data.member, membership: data.membership, time_in: data.time_in, photo: null, entry_method: 'manual' }, 'timein');
      playBeep(true);
      document.getElementById('manualMemberId').value = '';
    } else { playBeep(data.success); }
  });
}

function showResult(data) {
  const card = document.getElementById('resultCard');
  card.className = '';
  const avatar = data.photo
    ? `<img src="${data.photo}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:4px solid #fff;margin:0 auto;display:block;">`
    : data.member ? `<div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#c8ff00,#4ade80);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#111;margin:0 auto;">${data.member.charAt(0).toUpperCase()}</div>` : '';

  document.getElementById('resultAvatar').innerHTML       = avatar;
  document.getElementById('resultName').textContent       = data.member || 'Unknown';
  document.getElementById('resultMembership').textContent = data.membership || '';

  let cls = 'result-error';
  if (data.success && data.action === 'timein')   { cls = 'result-timein'; appendLogRow(data, 'timein'); }
  if (data.success && data.action === 'timeout')  { cls = 'result-timeout'; updateLogRowTimeout(data); }
  if (data.status === 'expired')    cls = 'result-expired';
  if (data.status === 'suspended')  cls = 'result-suspended';

  card.className = cls;

  if (data.success && data.action === 'timein') {
    document.getElementById('resultTimes').innerHTML =
      `<span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">Time In: <strong>${data.time_in}</strong></span>
       <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">Valid Until: <strong>${data.end_date}</strong></span>`;
  } else if (data.success && data.action === 'timeout') {
    document.getElementById('resultTimes').innerHTML =
      `<span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">In: <strong>${data.time_in}</strong></span>
       <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">Out: <strong>${data.time_out}</strong></span>
       <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.08);border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;margin:3px;">Duration: <strong>${data.duration}</strong></span>`;
  } else {
    document.getElementById('resultTimes').innerHTML = '';
  }

  document.getElementById('resultMessage').textContent = data.message || '';
  card.style.display = 'block';
  playBeep(data.success);
}

function appendLogRow(data, action) {
  const tbody = document.getElementById('todayLogBody');
  const emptyRow = document.getElementById('emptyRow');
  if (emptyRow) emptyRow.remove();
  const existing = tbody.querySelector(`[data-member="${data.member_id}"]`);
  if (existing) existing.remove();

  const avatar = data.photo
    ? `<img src="${data.photo}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">`
    : `<div style="width:34px;height:34px;border-radius:50%;background:rgba(200,255,0,0.08);border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--accent);">${(data.member||'?').charAt(0).toUpperCase()}</div>`;

  const methodBadge = data.entry_method === 'manual'
    ? '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(251,191,36,0.15);color:#fbbf24;">Manual</span>'
    : '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:var(--surface2);color:var(--muted);">QR Scan</span>';

  const row = document.createElement('tr');
  row.setAttribute('data-member', data.member_id);
  row.style.borderTop = '1px solid var(--border)';
  row.innerHTML = `
    <td style="padding:12px 16px;">${avatar}</td>
    <td style="padding:12px 16px;"><div style="font-size:13px;font-weight:600;">${data.member}</div><div style="font-size:11px;color:var(--muted);">${data.membership||''}</div></td>
    <td style="padding:12px 16px;font-size:13px;">${data.time_in}</td>
    <td style="padding:12px 16px;"><span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span></td>
    <td style="padding:12px 16px;font-size:13px;color:var(--muted);">—</td>
    <td style="padding:12px 16px;">${methodBadge}</td>
    <td style="padding:12px 16px;"><span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:rgba(74,222,128,0.15);color:#4ade80;">Inside</span></td>`;
  tbody.prepend(row);

  document.getElementById('insideCount').textContent = parseInt(document.getElementById('insideCount').textContent||0) + 1;
  document.getElementById('todayTotal').textContent  = parseInt(document.getElementById('todayTotal').textContent||0)  + 1;
}

function updateLogRowTimeout(data) {
  const row = document.querySelector(`[data-member="${data.member_id}"]`);
  if (row) {
    row.cells[3].textContent = data.time_out;
    row.cells[4].textContent = data.duration;
    row.cells[6].innerHTML = '<span style="display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:var(--surface2);color:var(--muted);">Done</span>';
  }
  document.getElementById('insideCount').textContent = Math.max(0, parseInt(document.getElementById('insideCount').textContent||1) - 1);
}

function playBeep(success) {
  try {
    const ctx = new (window.AudioContext||window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain); gain.connect(ctx.destination);
    osc.frequency.value = success ? 880 : 300;
    osc.type = 'sine';
    gain.gain.setValueAtTime(0.3, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
    osc.start(); osc.stop(ctx.currentTime + 0.4);
  } catch(e) {}
}
</script>

@endsection