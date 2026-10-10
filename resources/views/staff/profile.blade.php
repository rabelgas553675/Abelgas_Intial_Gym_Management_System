@extends('layouts.staff')
@section('title', 'My Profile – APEX')
@section('page_title', 'Profile')

@section('content')

{{-- Page Header --}}
<div class="pf-head">
  <h1>My <span style="color:var(--accent);">Profile</span></h1>
  <p>View and update your personal information</p>
</div>

@if(session('success'))
  <div class="pf2-alert pf2-alert-success">✓ {{ session('success') }}</div>
@endif

<form action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data">
  @csrf

  <div class="profile-grid">

    {{-- LEFT: Avatar + identity --}}
    <div class="profile-side-card">

      <div class="avatar-wrapper">
        <div class="avatar-preview" id="avatarPreview">
          @if($user->photo)
            <img src="{{ asset('storage/'.$user->photo) }}" alt="Profile Photo"/>
          @else
            <span class="initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
          @endif
        </div>
        <label class="avatar-upload-btn" title="Change photo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
            <circle cx="12" cy="13" r="4"/>
          </svg>
          <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
        </label>
      </div>

      <div class="profile-side-name">{{ $user->name }}</div>

      {{-- Chips: wrap & centre on any width --}}
      <div class="chip-row">
        <div class="role-chip">Staff</div>
        <div class="active-chip">
          <span class="dot"></span>
          Active Staff
        </div>
      </div>

      @php $qr = \App\Models\UserQrToken::where('user_id', $user->id)->first(); @endphp
      @if($qr && $qr->qr_code_path)
        <div class="qr-box">
          <img id="qrImg" src="{{ asset('storage/' . $qr->qr_code_path) }}" alt="QR Code"/>
          <div class="qr-token" id="qrToken">{{ $qr->qr_token }}</div>
        </div>

        <button type="button" class="qr-print-btn" id="qrPrintBtn"
                data-name="{{ $user->name }}" data-role="Staff">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
          </svg>
          Print QR
        </button>
      @endif

      <div class="member-since-row">
        Member since {{ $user->created_at->format('M d, Y') }}
      </div>
    </div>

    {{-- RIGHT: Bio & form fields --}}
    <div class="profile-main-card">

      <div class="section-label">Bio &amp; Other Details</div>

      <div class="pf2-row">
        <div class="pf2-group">
          <label class="pf2-label" for="pf_name">Full Name</label>
          <input id="pf_name" type="text" name="name" class="pf2-control"
                 value="{{ old('name', $user->name) }}" required/>
          @error('name')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf2-group">
          <label class="pf2-label" for="pf_email">Email Address</label>
          <input id="pf_email" type="email" class="pf2-control" value="{{ $user->email }}" disabled/>
        </div>
      </div>

      <div class="pf2-row">
        <div class="pf2-group">
          <label class="pf2-label" for="pf_phone">Phone Number</label>
          <input id="pf_phone" type="tel" name="phone" class="pf2-control"
                 value="{{ old('phone', $user->phone) }}" placeholder="09..."/>
          @error('phone')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf2-group">
          <label class="pf2-label" for="pf_birth">Date of Birth</label>
          <input id="pf_birth" type="date" name="birthdate" class="pf2-control"
                 value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"
                 style="color-scheme:dark;"/>
          @error('birthdate')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="pf2-row">
        <div class="pf2-group">
          <label class="pf2-label" for="pf_gender">Gender</label>
          <select id="pf_gender" name="gender" class="pf2-control pf2-select">
            <option value="">— Select —</option>
            @foreach(['Male','Female','Other'] as $g)
              <option value="{{ $g }}" {{ old('gender', $user->gender) === $g ? 'selected' : '' }}>
                {{ $g }}
              </option>
            @endforeach
          </select>
          @error('gender')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf2-group">
          <label class="pf2-label" for="pf_address">City / Address</label>
          <input id="pf_address" type="text" name="address" class="pf2-control"
                 value="{{ old('address', $user->address) }}" placeholder="Your city or address..."/>
          @error('address')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="pf2-row pf2-row-last">
        <div class="pf2-group">
          <label class="pf2-label">Role</label>
          <div class="pf2-static">Staff</div>
        </div>
        <div class="pf2-group">
          <label class="pf2-label">Account Created</label>
          <div class="pf2-static">{{ $user->created_at->format('M d, Y') }}</div>
        </div>
      </div>

      <div class="pf2-actions">
        <a href="{{ route('staff.dashboard') }}" class="pf2-btn pf2-btn-secondary">Cancel</a>
        <button type="submit" class="pf2-btn pf2-btn-primary">Save Changes</button>
      </div>

    </div>
  </div>

</form>

{{-- Change Password (all roles share this card) --}}
@include('partials.change-password-card')

<style>
  /* Reuses the charcoal & gold tokens (--accent, --accent-2, --accent-dark,
     --surface, --surface2, --border, --text, --text-soft, --muted,
     --success, --warning, --danger, --info, --accent-soft) from layouts/staff. */

  /* ── Page header ── */
  .pf-head { margin-bottom: 28px; }
  .pf-head h1 { font-size: clamp(22px, 5.5vw, 28px); font-weight: 700; margin-bottom: 4px; line-height: 1.2; }
  .pf-head p  { color: var(--muted); font-size: 14px; }

  .pf2-alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; }
  .pf2-alert-success { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.25); color: var(--success); }

  /* ── Grid ── */
  .profile-grid {
    display: grid;
    grid-template-columns: 320px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
  }

  /* ── Side card: single centred column so nothing sits side-by-side by accident ── */
  .profile-side-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px 24px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 0;
  }

  .avatar-wrapper { position: relative; display: inline-block; margin-bottom: 18px; }
  .avatar-preview {
    width: 140px; height: 140px; border-radius: 50%; overflow: hidden;
    background: var(--surface2); border: 2px solid var(--accent); margin: 0 auto;
    display: flex; align-items: center; justify-content: center;
  }
  .avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
  .avatar-preview .initials { font-size: 44px; font-weight: 700; color: var(--accent); }

  .avatar-upload-btn {
    position: absolute; bottom: 6px; right: 6px; width: 34px; height: 34px; border-radius: 50%;
    background: var(--accent); display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: 3px solid var(--surface); box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    transition: filter 0.2s ease;
  }
  .avatar-upload-btn:hover { filter: brightness(1.1); }
  .avatar-upload-btn svg { width: 15px; height: 15px; stroke: #1a1a1a; fill: none; stroke-width: 2.5; }

  .profile-side-name {
    font-size: 20px; font-weight: 700; margin-bottom: 12px; color: var(--text);
    max-width: 100%; overflow-wrap: anywhere;
  }

  .chip-row {
    display: flex; flex-wrap: wrap; justify-content: center; align-items: center;
    gap: 8px; margin-bottom: 22px; max-width: 100%;
  }

  .role-chip {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: var(--accent-soft);
    color: var(--accent);
    border: 1px solid rgba(224,169,59,0.3);
  }

  .active-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    border-radius: 20px;
    background: rgba(74,222,128,0.12);
    border: 1px solid rgba(74,222,128,0.25);
    font-size: 13px;
    font-weight: 600;
    color: var(--success);
    white-space: nowrap;
  }
  .active-chip .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--success); display: inline-block; }

  /* ── QR ── */
  .qr-box {
    padding: 14px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--border);
    width: 100%;
    max-width: 220px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .qr-box img { width: 100%; max-width: 170px; height: auto; aspect-ratio: 1 / 1; display: block; image-rendering: pixelated; }
  .qr-box .qr-token { font-family: monospace; font-size: 10px; color: #555; margin-top: 8px; word-break: break-all; text-align: center; }

  .qr-print-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    margin-top: 12px; margin-bottom: 22px;
    width: 100%; max-width: 220px;
    padding: 10px 16px;
    border-radius: 10px;
    background: transparent;
    border: 1px solid rgba(224,169,59,0.45);
    color: var(--accent);
    font-family: inherit; font-size: 13px; font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .qr-print-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
  .qr-print-btn:hover { background: var(--accent-soft); border-color: var(--accent); }
  .qr-print-btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

  .member-since-row {
    width: 100%;
    font-size: 12px; color: var(--muted);
    padding-top: 16px; margin-top: auto;
    border-top: 1px solid var(--border);
  }
  /* when there is no QR, keep a little space above the divider */
  .chip-row + .member-since-row { margin-top: 4px; }

  /* ── Main card ── */
  .profile-main-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px;
    min-width: 0;
  }

  .section-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
  }

  .pf2-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; margin-bottom: 24px; }
  .pf2-row-last { margin-bottom: 8px; }
  .pf2-group { min-width: 0; }

  .pf2-label {
    display: block;
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
  }
  .pf2-control {
    display: block;
    width: 100%;
    min-width: 0;
    padding: 8px 0;
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    font-family: inherit;
    font-size: 16px;            /* 16px stops iOS Safari zooming on focus */
    font-weight: 600;
    outline: none;
    border-radius: 0;
    transition: border-color 0.2s ease;
  }
  .pf2-control:focus { border-bottom-color: var(--accent); }
  .pf2-control[disabled] { opacity: 0.5; cursor: not-allowed; }
  .pf2-control option { background: var(--surface2); color: var(--text); }
  input[type="date"].pf2-control { min-height: 38px; }

  .pf2-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(224,169,59,0.7)' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0 center;
    background-size: 14px;
    padding-right: 24px;
    cursor: pointer;
  }

  .pf2-static { font-size: 15px; font-weight: 600; padding: 8px 0; border-bottom: 1px solid var(--border); color: var(--muted); }
  .pf2-error { color: var(--danger); font-size: 12px; margin-top: 4px; }

  .pf2-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px; }
  .pf2-btn {
    padding: 11px 28px;
    border-radius: 10px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s ease;
  }
  .pf2-btn-primary {
    background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
    color: #1a1a1a;
    padding: 11px 32px;
  }
  .pf2-btn-primary:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(224,169,59,0.25);
  }
  .pf2-btn-secondary { background: transparent; border: 1px solid var(--border); color: var(--text); }
  .pf2-btn-secondary:hover { background: var(--surface2); }

  /* ═══════════ RESPONSIVE ═══════════ */

  /* Large tablets / small laptops: narrower side column */
  @media (max-width: 1180px) {
    .profile-grid { grid-template-columns: 280px minmax(0, 1fr); gap: 20px; }
    .profile-main-card { padding: 28px; }
  }

  /* Tablets & phones: stack. Side card becomes a tidy horizontal card on wider tablets */
  @media (max-width: 960px) {
    .profile-grid { grid-template-columns: minmax(0, 1fr); }
    .profile-side-card { max-width: 440px; width: 100%; margin: 0 auto; }
  }

  /* Phones */
  @media (max-width: 640px) {
    .pf-head { margin-bottom: 20px; }

    .profile-grid { gap: 16px; }
    .profile-side-card { max-width: 100%; padding: 24px 16px 18px; }
    .avatar-preview { width: 120px; height: 120px; }
    .avatar-preview .initials { font-size: 38px; }
    .profile-side-name { font-size: 18px; }

    .profile-main-card { padding: 20px 16px; border-radius: 14px; }
    .section-label { margin-bottom: 16px; padding-bottom: 12px; }

    .pf2-row { grid-template-columns: minmax(0, 1fr); gap: 16px; margin-bottom: 16px; }
    .pf2-row-last { margin-bottom: 4px; gap: 16px; }

    /* Primary action first (on top) and full width — easy to tap */
    .pf2-actions { flex-direction: column-reverse; gap: 10px; }
    .pf2-actions .pf2-btn { width: 100%; padding: 13px 20px; }
  }

  /* Very small phones */
  @media (max-width: 380px) {
    .avatar-preview { width: 104px; height: 104px; }
    .avatar-preview .initials { font-size: 32px; }
    .qr-box { max-width: 200px; padding: 12px; }
    .qr-print-btn { max-width: 200px; }
    .active-chip { font-size: 12px; padding: 6px 12px; }
  }

  /* Landscape phones: avoid a huge stacked avatar */
  @media (max-height: 480px) and (orientation: landscape) {
    .avatar-preview { width: 96px; height: 96px; }
  }

  /* Light theme tweaks for this page */
  html:root[data-theme="light"] .qr-box { border-color: rgba(20,16,8,0.15); box-shadow: 0 1px 2px rgba(20,16,8,.06); }
  html:root[data-theme="light"] .qr-print-btn { border-color: rgba(184,134,42,0.55); color: var(--accent); }
  html:root[data-theme="light"] .qr-print-btn:hover { background: rgba(184,134,42,0.12); }
</style>

<script>
function previewAvatar(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('avatarPreview').innerHTML =
      `<img src="${e.target.result}" alt="Profile Photo"/>`;
  };
  reader.readAsDataURL(input.files[0]);
}

/* ── Print QR ──
   Builds a clean, print-only page inside a hidden iframe so ONLY the QR sheet
   is printed (no navbar, no form, no blank pages). */
(function () {
  var btn = document.getElementById('qrPrintBtn');
  if (!btn) return;

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  btn.addEventListener('click', function () {
    var img   = document.getElementById('qrImg');
    var token = document.getElementById('qrToken');
    if (!img) return;

    var name  = btn.getAttribute('data-name') || '';
    var role  = btn.getAttribute('data-role') || '';
    var src   = img.currentSrc || img.src;
    var tok   = token ? token.textContent.trim() : '';

    var html =
      '<!DOCTYPE html><html><head><meta charset="utf-8"><title>QR – ' + esc(name) + '</title>' +
      '<style>' +
        '@page{size:auto;margin:14mm;}' +
        '*{box-sizing:border-box;}' +
        'html,body{margin:0;padding:0;background:#fff;color:#111;font-family:Arial,Helvetica,sans-serif;}' +
        '.sheet{width:100%;max-width:340px;margin:0 auto;padding:24px 20px;text-align:center;border:2px solid #111;border-radius:14px;}' +
        '.brand{font-size:13px;font-weight:800;letter-spacing:3px;margin-bottom:14px;}' +
        '.name{font-size:22px;font-weight:700;margin-bottom:4px;word-break:break-word;}' +
        '.role{display:inline-block;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;' +
              'padding:4px 12px;border:1px solid #111;border-radius:20px;margin-bottom:18px;}' +
        '.qr{display:block;width:240px;max-width:100%;height:auto;margin:0 auto 10px;image-rendering:pixelated;}' +
        '.token{font-family:"Courier New",monospace;font-size:12px;color:#333;word-break:break-all;margin-bottom:12px;}' +
        '.note{font-size:11px;color:#555;}' +
      '</style></head><body>' +
      '<div class="sheet">' +
        '<div class="brand">APEX FITNESS GYM</div>' +
        '<div class="name">' + esc(name) + '</div>' +
        '<div class="role">' + esc(role) + '</div>' +
        '<img id="qr" class="qr" src="' + esc(src) + '" alt="QR Code">' +
        '<div class="token">' + esc(tok) + '</div>' +
        '<div class="note">Scan this QR code for attendance</div>' +
      '</div></body></html>';

    var frame = document.createElement('iframe');
    frame.setAttribute('aria-hidden', 'true');
    frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;';
    document.body.appendChild(frame);

    var win = frame.contentWindow;
    var doc = win.document;
    doc.open();
    doc.write(html);
    doc.close();

    var done = false;
    function cleanup() {
      if (frame && frame.parentNode) frame.parentNode.removeChild(frame);
    }
    function go() {
      if (done) return;
      done = true;
      try {
        win.focus();
        win.onafterprint = cleanup;
        win.print();
      } catch (e) { /* ignore */ }
      setTimeout(cleanup, 60000); // fallback cleanup
    }

    var qrImg = doc.getElementById('qr');
    if (qrImg.complete && qrImg.naturalWidth > 0) {
      go();
    } else {
      qrImg.onload = go;
      qrImg.onerror = go;
    }
  });
})();
</script>

@endsection