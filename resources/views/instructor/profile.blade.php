@extends('layouts.instructor')
@section('title', 'My Profile – APEX')
@section('active', 'profile')

@section('content')

{{-- Page Header --}}
<div class="pf-head">
  <h1>My <span style="color:var(--accent);">Profile</span></h1>
  <p>View and update your instructor information</p>
</div>

<form action="{{ route('instructor.profile.update') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="profile-grid">

  {{-- LEFT: Photo Card --}}
  <div class="profile-card">
    <div class="avatar-wrapper">
      <div id="avatar-preview" class="avatar-preview">
        @if($instructor->photo)
          <img src="{{ asset('storage/'.$instructor->photo) }}" alt="Profile Photo"/>
        @else
          <div class="avatar-placeholder">
            <svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
        @endif
      </div>
      <label class="upload-btn" title="Upload Photo">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
          <circle cx="12" cy="13" r="4"/>
        </svg>
        <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
      </label>
    </div>

    <div class="profile-name">{{ $instructor->name }}</div>
    <div class="profile-role">Instructor</div>

    @if($instructor->specialization)
      <div class="specialization-badge">{{ $instructor->specialization }}</div>
    @endif

    @if($instructor->experience_years)
      <div class="experience-text">{{ $instructor->experience_years }} years of experience</div>
    @endif

    @php $qr = \App\Models\UserQrToken::where('user_id', auth()->id())->first(); @endphp
    @if($qr && $qr->qr_code_path)
      <div class="qr-section">
        <img id="qrImg" src="{{ asset('storage/' . $qr->qr_code_path) }}" alt="QR Code">
        <div class="qr-token" id="qrToken">{{ $qr->qr_token }}</div>
      </div>

      <button type="button" class="qr-print-btn" id="qrPrintBtn"
              data-name="{{ $instructor->name }}" data-role="Instructor">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 6 2 18 2 18 9"/>
          <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
          <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print QR
      </button>
    @endif

    <div class="member-since">Member since {{ auth()->user()->created_at->format('M d, Y') }}</div>
  </div>

  {{-- RIGHT: Details Card --}}
  <div class="details-card">

    <div class="details-header">
      <div class="details-title">Bio &amp; Other Details</div>
      <div class="status-dot"></div>
    </div>

    <div class="form-grid">

      <div class="form-group">
        <label class="form-label" for="i_name">Full Name</label>
        <input id="i_name" type="text" name="name" class="field" value="{{ old('name', $instructor->name) }}" required/>
        @error('name')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="form-text">{{ $instructor->email }}</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="i_phone">Phone Number</label>
        <input id="i_phone" type="tel" name="phone" class="field" value="{{ old('phone', $instructor->phone) }}" placeholder="Not set"/>
        @error('phone')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="i_spec">Specialization</label>
        <input id="i_spec" type="text" name="specialization" class="field"
               value="{{ old('specialization', $instructor->specialization) }}"
               placeholder="e.g. Bodybuilding, Calisthenics"/>
        @error('specialization')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="i_exp">Years of Experience</label>
        <input id="i_exp" type="number" inputmode="numeric" name="experience_years" class="field" min="0" max="50"
               value="{{ old('experience_years', $instructor->experience_years) }}" placeholder="0"/>
        @error('experience_years')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="i_addr">City / Address</label>
        <input id="i_addr" type="text" name="address" class="field" value="{{ old('address', $instructor->address) }}" placeholder="Not set"/>
        @error('address')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Assigned Members</label>
        <div class="form-text" style="color:var(--accent);font-weight:700;">
          {{ auth()->user()->assignedMembers()->count() }} members
        </div>
      </div>

    </div>

    <div class="action-buttons">
      <a href="{{ route('instructor.dashboard') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>

  </div>
</div>

</form>

{{-- Change Password (all roles share this card) --}}
@include('partials.change-password-card')

<style>
  /* Charcoal & gold — colours come from the tokens in layouts/instructor.blade.php */

  .pf-head { margin-bottom: 28px; }
  .pf-head h1 { font-size: clamp(22px, 5.5vw, 28px); font-weight: 700; margin-bottom: 4px; line-height: 1.2; }
  .pf-head p  { color: var(--muted); font-size: 14px; }

  .profile-grid { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 24px; align-items: start; }

  .profile-card, .details-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: 16px; min-width: 0;
  }
  html:root[data-theme="light"] :is(.profile-card,.details-card) { box-shadow: var(--shadow-card); }

  /* Photo card — one centred column */
  .profile-card {
    padding: 32px 24px 24px; text-align: center;
    display: flex; flex-direction: column; align-items: center;
  }

  .avatar-wrapper { position: relative; display: inline-block; margin-bottom: 18px; }
  .avatar-preview {
    width: 160px; height: 160px; border-radius: 50%; overflow: hidden;
    background: var(--surface2); border: 2px solid var(--accent); margin: 0 auto;
  }
  .avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
  .avatar-placeholder {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    background: var(--accent-soft); color: var(--muted);
  }
  .avatar-placeholder svg { width: 56px; height: 56px; }

  .upload-btn {
    position: absolute; bottom: 6px; right: 6px; width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-2), var(--accent-dark)); color: #1a1a1a;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--bg-page); transition: transform .15s;
  }
  html:root[data-theme="light"] .upload-btn { color: #111; }
  .upload-btn:hover { transform: scale(1.1); }
  .upload-btn svg { width: 15px; height: 15px; stroke: currentColor; }

  .profile-name { font-size: 20px; font-weight: 700; margin-bottom: 4px; max-width: 100%; overflow-wrap: anywhere; }
  .profile-role { font-size: 13px; color: var(--accent); font-weight: 600; margin-bottom: 14px; }

  .specialization-badge {
    display: inline-flex; align-items: center; justify-content: center; text-align: center;
    max-width: 100%; padding: 6px 14px; border-radius: 100px;
    font-size: 12px; font-weight: 600; background: var(--accent-soft);
    border: 1px solid rgba(224,169,59,0.3); color: var(--accent); margin-bottom: 12px;
  }
  .experience-text { font-size: 13px; color: var(--muted); margin-bottom: 18px; }

  /* QR */
  .qr-section {
    padding: 14px; background: #ffffff; border-radius: 12px; border: 1px solid var(--border);
    width: 100%; max-width: 220px; display: flex; flex-direction: column; align-items: center;
  }
  .qr-section img { width: 100%; max-width: 170px; height: auto; aspect-ratio: 1 / 1; display: block; image-rendering: pixelated; }
  .qr-token { font-family: monospace; font-size: 10px; color: #555; margin-top: 8px; word-break: break-all; text-align: center; }

  .qr-print-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    margin-top: 12px; margin-bottom: 22px; width: 100%; max-width: 220px; padding: 10px 16px;
    border-radius: 10px; background: transparent; border: 1px solid rgba(224,169,59,0.45); color: var(--accent);
    font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; transition: all .2s;
  }
  .qr-print-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
  .qr-print-btn:hover { background: var(--accent-soft); border-color: var(--accent); }
  .qr-print-btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  html:root[data-theme="light"] .qr-section { border-color: rgba(20,16,8,0.15); }
  html:root[data-theme="light"] .qr-print-btn { border-color: rgba(184,134,42,0.55); }

  .member-since { width: 100%; font-size: 12px; color: var(--muted); padding-top: 16px; margin-top: auto; border-top: 1px solid var(--border); }
  .experience-text + .member-since,
  .profile-role + .member-since,
  .specialization-badge + .member-since { margin-top: 8px; }

  /* Details */
  .details-card { padding: 32px; }
  .details-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 28px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
  }
  .details-title { font-size: 17px; font-weight: 700; color: var(--accent); }
  .status-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--success); animation: pulse 2s infinite; flex-shrink: 0; }
  @keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .5; transform: scale(.8); } }

  .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 28px 40px; }
  .form-group { display: flex; flex-direction: column; margin-bottom: 0; min-width: 0; }
  .form-label { margin-bottom: 8px; }
  .field {
    display: block; background: transparent; border: none; border-bottom: 2px solid var(--border); border-radius: 0;
    padding: 4px 0 8px; font-size: 16px; font-weight: 600; color: var(--text);   /* 16px: no iOS zoom */
    font-family: 'DM Sans', sans-serif; transition: border-color .15s; outline: none; width: 100%; min-width: 0;
  }
  .field:focus { border-bottom-color: var(--accent); }
  .field::placeholder { color: var(--muted); font-weight: 400; }
  .form-text { font-size: 15px; font-weight: 600; padding: 4px 0 8px; border-bottom: 2px solid var(--border); color: var(--muted); overflow-wrap: anywhere; }
  .error-text { color: var(--danger); font-size: 11px; margin-top: 3px; }

  .action-buttons {
    margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap;
  }
  .action-buttons .btn { padding: 11px 24px; font-size: 14px; justify-content: center; }

  /* ═══ RESPONSIVE ═══ */
  @media (max-width: 1180px) {
    .profile-grid { grid-template-columns: 280px minmax(0, 1fr); gap: 20px; }
    .details-card { padding: 28px; }
    .form-grid { gap: 24px 28px; }
  }
  @media (max-width: 960px) {
    .profile-grid { grid-template-columns: minmax(0, 1fr); gap: 20px; }
    .profile-card { max-width: 440px; width: 100%; margin: 0 auto; }
  }
  @media (max-width: 640px) {
    .pf-head { margin-bottom: 20px; }
    .profile-grid { gap: 16px; }
    .profile-card { padding: 24px 16px 18px; max-width: 100%; border-radius: 14px; }
    .avatar-preview { width: 130px; height: 130px; }
    .profile-name { font-size: 18px; }
    .details-card { padding: 20px 16px; border-radius: 14px; }
    .details-header { margin-bottom: 20px; padding-bottom: 12px; }
    .details-title { font-size: 15px; }
    .form-grid { grid-template-columns: minmax(0, 1fr); gap: 18px; }
    .action-buttons { margin-top: 24px; padding-top: 18px; flex-direction: column-reverse; gap: 10px; }
    .action-buttons .btn { width: 100%; padding: 13px 20px; }
  }
  @media (max-width: 380px) {
    .avatar-preview { width: 108px; height: 108px; }
    .qr-section { max-width: 200px; padding: 12px; }
    .qr-print-btn { max-width: 200px; }
  }
  @media (max-height: 480px) and (orientation: landscape) { .avatar-preview { width: 96px; height: 96px; } }
  @media (prefers-reduced-motion: reduce) { .status-dot { animation: none; } }
</style>

<script>
function previewAvatar(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('avatar-preview').innerHTML = `<img src="${e.target.result}" alt="Profile Photo"/>`;
  };
  reader.readAsDataURL(input.files[0]);
}

/* ── Print QR: prints only a clean QR sheet via a hidden iframe ── */
(function () {
  var btn = document.getElementById('qrPrintBtn');
  if (!btn) return;

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
    });
  }

  btn.addEventListener('click', function () {
    var img = document.getElementById('qrImg');
    var token = document.getElementById('qrToken');
    if (!img) return;

    var name = btn.getAttribute('data-name') || '';
    var role = btn.getAttribute('data-role') || '';
    var src = img.currentSrc || img.src;
    var tok = token ? token.textContent.trim() : '';

    var html =
      '<!DOCTYPE html><html><head><meta charset="utf-8"><title>QR – ' + esc(name) + '</title>' +
      '<style>' +
        '@page{size:auto;margin:14mm;}*{box-sizing:border-box;}' +
        'html,body{margin:0;padding:0;background:#fff;color:#111;font-family:Arial,Helvetica,sans-serif;}' +
        '.sheet{width:100%;max-width:340px;margin:0 auto;padding:24px 20px;text-align:center;border:2px solid #111;border-radius:14px;}' +
        '.brand{font-size:13px;font-weight:800;letter-spacing:3px;margin-bottom:14px;}' +
        '.name{font-size:22px;font-weight:700;margin-bottom:4px;word-break:break-word;}' +
        '.role{display:inline-block;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:4px 12px;border:1px solid #111;border-radius:20px;margin-bottom:18px;}' +
        '.qr{display:block;width:240px;max-width:100%;height:auto;margin:0 auto 10px;image-rendering:pixelated;}' +
        '.token{font-family:"Courier New",monospace;font-size:12px;color:#333;word-break:break-all;margin-bottom:12px;}' +
        '.note{font-size:11px;color:#555;}' +
      '</style></head><body><div class="sheet">' +
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

    var win = frame.contentWindow, doc = win.document;
    doc.open(); doc.write(html); doc.close();

    var done = false;
    function cleanup() { if (frame.parentNode) frame.parentNode.removeChild(frame); }
    function go() {
      if (done) return; done = true;
      try { win.focus(); win.onafterprint = cleanup; win.print(); } catch (e) {}
      setTimeout(cleanup, 60000);
    }
    var qrImg = doc.getElementById('qr');
    if (qrImg.complete && qrImg.naturalWidth > 0) go();
    else { qrImg.onload = go; qrImg.onerror = go; }
  });
})();
</script>

@endsection