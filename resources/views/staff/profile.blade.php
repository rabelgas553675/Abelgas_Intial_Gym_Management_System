@extends('layouts.staff')
@section('title', 'My Profile – APEX')
@section('page_title', 'Profile')

@section('content')

{{-- Page Header --}}
<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    My <span style="color:var(--accent);">Profile</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">View and update your personal information</p>
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
        <label class="avatar-upload-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
            <circle cx="12" cy="13" r="4"/>
          </svg>
          <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
        </label>
      </div>

      <div class="profile-side-name">{{ $user->name }}</div>
      <div class="role-chip">Staff</div>

      <div class="active-chip">
        <span class="dot"></span>
        Active Staff
      </div>

      @php $qr = \App\Models\UserQrToken::where('user_id', $user->id)->first(); @endphp
      @if($qr && $qr->qr_code_path)
        <div class="qr-box">
          <img src="{{ asset('storage/' . $qr->qr_code_path) }}" alt="QR Code"/>
          <div class="qr-token">{{ $qr->qr_token }}</div>
        </div>
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
          <label class="pf2-label">Full Name</label>
          <input type="text" name="name" class="pf2-control"
                 value="{{ old('name', $user->name) }}" required/>
          @error('name')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf2-group">
          <label class="pf2-label">Email Address</label>
          <input type="email" class="pf2-control" value="{{ $user->email }}" disabled/>
        </div>
      </div>

      <div class="pf2-row">
        <div class="pf2-group">
          <label class="pf2-label">Phone Number</label>
          <input type="text" name="phone" class="pf2-control"
                 value="{{ old('phone', $user->phone) }}" placeholder="09..."/>
          @error('phone')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf2-group">
          <label class="pf2-label">Date of Birth</label>
          <input type="date" name="birthdate" class="pf2-control"
                 value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"
                 style="color-scheme:dark;"/>
          @error('birthdate')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="pf2-row">
        <div class="pf2-group">
          <label class="pf2-label">Gender</label>
          <select name="gender" class="pf2-control pf2-select">
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
          <label class="pf2-label">City / Address</label>
          <input type="text" name="address" class="pf2-control"
                 value="{{ old('address', $user->address) }}" placeholder="Your city or address..."/>
          @error('address')<div class="pf2-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="pf2-row" style="margin-bottom: 8px;">
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

  .pf2-alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; }
  .pf2-alert-success { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.25); color: var(--success); }

  .profile-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
    align-items: start;
  }

  .profile-side-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 36px 28px;
    text-align: center;
  }

  .avatar-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
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

  .profile-side-name { font-size: 20px; font-weight: 700; margin-bottom: 8px; color: var(--text); }

  .role-chip {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: var(--accent-soft);
    color: var(--accent);
    border: 1px solid rgba(224,169,59,0.3);
    margin-bottom: 14px;
  }

  .active-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 18px;
    border-radius: 20px;
    background: rgba(74,222,128,0.12);
    border: 1px solid rgba(74,222,128,0.25);
    font-size: 13px;
    font-weight: 600;
    color: var(--success);
    margin-bottom: 24px;
  }
  .active-chip .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--success); display: inline-block; }

  .qr-box {
    margin-bottom: 24px;
    padding: 15px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--border);
    display: inline-block;
  }
  .qr-box img { width: 120px; height: 120px; display: block; margin: 0 auto; }
  .qr-box .qr-token { font-family: monospace; font-size: 10px; color: #666; margin-top: 8px; word-break: break-all; }

  .member-since-row { font-size: 12px; color: var(--muted); padding-top: 16px; border-top: 1px solid var(--border); }

  .profile-main-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px;
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

  .pf2-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }

  .pf2-label {
    display: block;
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
  }
  .pf2-control {
    width: 100%;
    padding: 8px 0;
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--border);
    color: var(--text);
    font-family: inherit;
    font-size: 15px;
    font-weight: 600;
    outline: none;
    transition: border-color 0.2s ease;
  }
  .pf2-control:focus { border-bottom-color: var(--accent); }
  .pf2-control[disabled] { opacity: 0.5; cursor: not-allowed; }
  .pf2-control option { background: var(--surface2); color: var(--text); }

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

  /* Responsive */
  @media (max-width: 1024px) {
    .profile-grid { grid-template-columns: 1fr; }
    .profile-side-card { max-width: 400px; margin: 0 auto; }
  }
  @media (max-width: 640px) {
    .pf2-row { grid-template-columns: 1fr; gap: 16px; margin-bottom: 16px; }
    .pf2-actions { flex-direction: column; }
    .pf2-actions .pf2-btn { width: 100%; justify-content: center; }
    .profile-side-card { max-width: 100%; }
  }
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
</script>

@endsection