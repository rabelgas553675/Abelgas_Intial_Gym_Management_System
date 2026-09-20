@extends('layouts.admin')
@section('title', 'My Profile – APEX FITNESS GYM')
@section('page_title', 'My Profile')
@section('active_nav', 'admin.profile')

@section('content')

<style>
  /* Base responsive styles */
  .profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 16px;
  }

  /* 1. This forces the custom arrow icon to appear on the select box */
  #genderSelect {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 4px center !important;
    background-size: 14px !important;
    padding-right: 24px !important;
  }

  #genderSelect option {
    background-color: #1a1a1a;
    color: white;
    padding: 10px;
  }

  #genderSelect:focus {
    outline: none;
    border-bottom: 1px solid var(--accent) !important;
  }

  /* Profile grid responsive */
  .profile-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
    align-items: start;
  }

  /* Avatar card */
  .avatar-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 36px 28px;
    text-align: center;
    position: sticky;
    top: 20px;
  }

  .avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 20px;
  }

  .avatar-preview {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--surface2);
    border: 3px solid var(--border);
    margin: 0 auto;
  }

  .avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 48px;
    color: var(--accent);
  }

  .avatar-upload-btn {
    position: absolute;
    bottom: 6px;
    right: 6px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid var(--bg);
  }

  .avatar-upload-btn svg {
    width: 15px;
    height: 15px;
    fill: none;
    stroke: #111;
    stroke-width: 2.5;
  }

  /* Form card */
  .form-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  .form-group {
    margin-bottom: 24px;
  }

  .form-label {
    font-size: 11px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
    display: block;
  }

  .form-control-custom {
    background: transparent;
    border: none;
    border-bottom: 1px solid var(--border);
    border-radius: 0;
    padding: 8px 0;
    font-size: 15px;
    font-weight: 600;
    width: 100%;
    color: inherit;
  }

  .form-control-custom:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .form-control-custom[type="date"] {
    color-scheme: dark;
  }

  .form-control-custom:focus {
    outline: none;
    border-bottom-color: var(--accent);
  }

  /* Status badges */
  .badge-admin {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: rgba(255, 43, 61, 0.12);
    color: var(--accent);
    border: 1px solid rgba(255, 43, 61, 0.25);
    margin-bottom: 16px;
  }

  .badge-active {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 18px;
    border-radius: 20px;
    background: rgba(74, 222, 128, 0.12);
    border: 1px solid rgba(74, 222, 128, 0.25);
    font-size: 13px;
    font-weight: 600;
    color: var(--success);
    margin-bottom: 24px;
  }

  .badge-active .dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--success);
    display: inline-block;
  }

  /* QR Code */
  .qr-container {
    margin-bottom: 24px;
    padding: 15px;
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--border);
    display: inline-block;
  }

  .qr-container img {
    width: 120px;
    height: 120px;
    display: block;
    margin: 0 auto;
  }

  .qr-token {
    font-family: monospace;
    font-size: 10px;
    color: #999;
    margin-top: 8px;
  }

  /* Buttons */
  .btn-group {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 12px;
    flex-wrap: wrap;
  }

  .btn {
    padding: 11px 28px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
  }

  .btn-secondary {
    background: var(--surface2);
    color: var(--text);
    border: 1px solid var(--border);
  }

  .btn-primary {
    background: var(--accent);
    color: #111;
    padding: 11px 32px;
  }

  .btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }

  .btn-secondary:hover {
    background: var(--border);
  }

  /* Alert */
  .alert-success {
    padding: 16px 20px;
    border-radius: 12px;
    background: rgba(74, 222, 128, 0.12);
    border: 1px solid rgba(74, 222, 128, 0.25);
    color: var(--success);
    margin-bottom: 20px;
    font-weight: 500;
  }

  /* Page header */
  .page-header {
    margin-bottom: 28px;
  }

  .page-header h1 {
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .page-header p {
    color: var(--muted);
    font-size: 14px;
  }

  /* Form row for small screens */
  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  /* ===== RESPONSIVE BREAKPOINTS ===== */

  /* Tablets and small laptops */
  @media (max-width: 1024px) {
    .profile-grid {
      grid-template-columns: 1fr;
      gap: 24px;
    }

    .avatar-card {
      position: relative;
      top: 0;
      max-width: 400px;
      margin: 0 auto;
    }

    .avatar-preview {
      width: 120px;
      height: 120px;
    }
  }

  /* Mobile devices */
  @media (max-width: 768px) {
    .profile-container {
      padding: 0 12px;
    }

    .page-header h1 {
      font-size: 24px;
    }

    .page-header p {
      font-size: 13px;
    }

    .form-card {
      padding: 20px 16px;
    }

    .form-grid {
      grid-template-columns: 1fr;
      gap: 0;
    }

    .form-row {
      grid-template-columns: 1fr;
      gap: 0;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group:last-child {
      margin-bottom: 0;
    }

    .form-control-custom {
      font-size: 14px;
      padding: 6px 0;
    }

    .avatar-card {
      padding: 24px 16px;
    }

    .avatar-preview {
      width: 100px;
      height: 100px;
    }

    .avatar-upload-btn {
      width: 30px;
      height: 30px;
      bottom: 4px;
      right: 4px;
    }

    .avatar-upload-btn svg {
      width: 13px;
      height: 13px;
    }

    .btn-group {
      justify-content: stretch;
      flex-direction: column;
    }

    .btn-group .btn {
      width: 100%;
      text-align: center;
      justify-content: center;
    }

    .qr-container img {
      width: 100px;
      height: 100px;
    }

    .badge-active {
      font-size: 12px;
      padding: 5px 14px;
    }

    .form-label {
      font-size: 10px;
    }
  }

  /* Small phones */
  @media (max-width: 480px) {
    .profile-container {
      padding: 0 8px;
    }

    .page-header h1 {
      font-size: 20px;
    }

    .form-card {
      padding: 16px 12px;
      border-radius: 12px;
    }

    .avatar-card {
      padding: 20px 12px;
      border-radius: 12px;
    }

    .avatar-preview {
      width: 80px;
      height: 80px;
    }

    .avatar-upload-btn {
      width: 26px;
      height: 26px;
    }

    .avatar-upload-btn svg {
      width: 11px;
      height: 11px;
    }

    .btn {
      padding: 10px 20px;
      font-size: 13px;
    }

    .btn-primary {
      padding: 10px 24px;
    }

    .form-control-custom {
      font-size: 13px;
    }
  }
</style>

<div class="profile-container">
  <div class="page-header">
    <h1>My Profile</h1>
    <p>View and update your admin account details.</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
  @endif

  <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="profile-grid">

      {{-- LEFT: Avatar card --}}
      <div class="avatar-card">
        <div class="avatar-wrapper">
          <div class="avatar-preview" id="avatar-preview">
            @if($user->photo)
              <img src="{{ asset('storage/'.$user->photo) }}" alt="Profile photo"/>
            @else
              <div class="avatar-placeholder">
                {{ strtoupper(substr($user->name,0,2)) }}
              </div>
            @endif
          </div>
          <label class="avatar-upload-btn">
            <svg viewBox="0 0 24 24">
              <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
              <circle cx="12" cy="13" r="4"/>
            </svg>
            <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
          </label>
        </div>

        <div style="font-size:20px;font-weight:700;margin-bottom:4px;">{{ $user->name }}</div>
        <div class="badge-admin">Admin</div>

        <div class="badge-active">
          <span class="dot"></span>
          Active Admin
        </div>

        @php $qr = \App\Models\UserQrToken::where('user_id', $user->id)->first(); @endphp
        @if($qr && $qr->qr_code_path)
          <div class="qr-container">
            <img src="{{ asset('storage/' . $qr->qr_code_path) }}" alt="QR Code"/>
            <div class="qr-token">{{ $qr->qr_token }}</div>
          </div>
        @endif

        <div style="font-size:12px;color:var(--muted);padding-top:16px;border-top:1px solid var(--border);">
          Member since {{ $user->created_at->format('M d, Y') }}
        </div>
      </div>

      {{-- RIGHT: Bio form --}}
      <div class="form-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:8px;">
          <div style="font-size:17px;font-weight:700;">Bio &amp; other details</div>
          <span style="width:10px;height:10px;border-radius:50%;background:var(--accent);display:inline-block;"></span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <div class="form-label">Full Name</div>
            <input type="text" name="name" class="form-control-custom"
                   value="{{ old('name', $user->name) }}" required/>
          </div>
          <div class="form-group">
            <div class="form-label">Email Address</div>
            <input type="email" class="form-control-custom" value="{{ $user->email }}" disabled/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <div class="form-label">Phone Number</div>
            <input type="text" name="phone" class="form-control-custom"
                   value="{{ old('phone', $user->phone) }}" placeholder="09..."/>
          </div>
          <div class="form-group">
            <div class="form-label">Date of Birth</div>
            <input type="date" name="birthdate" class="form-control-custom"
                   value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"/>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <div class="form-label">Gender</div>
            <select name="gender" id="genderSelect" class="form-control-custom">
              <option value="">— Select —</option>
              @foreach(['Male','Female','Other'] as $g)
                <option value="{{ $g }}" {{ old('gender', $user->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <div class="form-label">City / Address</div>
            <input type="text" name="address" class="form-control-custom"
                   value="{{ old('address', $user->address) }}" placeholder="Your city or address..."/>
          </div>
        </div>

        <div class="form-row" style="margin-bottom: 12px;">
          <div class="form-group">
            <div class="form-label">Role</div>
            <div style="font-size:15px;font-weight:600;color:var(--accent);padding:8px 0;border-bottom:1px solid var(--border);">Admin</div>
          </div>
          <div class="form-group">
            <div class="form-label">Account Created</div>
            <div style="font-size:15px;font-weight:600;color:var(--muted);padding:8px 0;border-bottom:1px solid var(--border);">
              {{ $user->created_at->format('M d, Y') }}
            </div>
          </div>
        </div>

        <div class="btn-group">
          <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function previewAvatar(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('avatar-preview').innerHTML =
      `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;"/>`;
  };
  reader.readAsDataURL(input.files[0]);
}
</script>

@endsection