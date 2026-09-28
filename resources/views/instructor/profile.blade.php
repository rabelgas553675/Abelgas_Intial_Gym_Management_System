@extends('layouts.instructor')
@section('title', 'My Profile – APEX')
@section('active', 'profile')

@section('content')

{{-- Page Header --}}
<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    My <span style="color:var(--accent);">Profile</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">View and update your instructor information</p>
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

    @php $qr = \App\Models\UserQrToken::where('user_id', auth()->id())->first(); @endphp
    @if($qr && $qr->qr_code_path)
    <div class="qr-section">
      <img src="{{ asset('storage/' . $qr->qr_code_path) }}" alt="QR Code">
      <div class="qr-token">{{ $qr->qr_token }}</div>
    </div>
    @endif

    @if($instructor->specialization)
    <div class="specialization-badge">{{ $instructor->specialization }}</div>
    @endif

    @if($instructor->experience_years)
    <div class="experience-text">{{ $instructor->experience_years }} years of experience</div>
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
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="field" value="{{ old('name', $instructor->name) }}" required/>
        @error('name')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="form-text">{{ $instructor->email }}</div>
      </div>

      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone" class="field" value="{{ old('phone', $instructor->phone) }}" placeholder="Not set"/>
        @error('phone')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Specialization</label>
        <input type="text" name="specialization" class="field"
               value="{{ old('specialization', $instructor->specialization) }}"
               placeholder="e.g. Bodybuilding, Calisthenics"/>
        @error('specialization')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">Years of Experience</label>
        <input type="number" name="experience_years" class="field" min="0" max="50"
               value="{{ old('experience_years', $instructor->experience_years) }}" placeholder="0"/>
        @error('experience_years')<div class="error-text">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label class="form-label">City / Address</label>
        <input type="text" name="address" class="field" value="{{ old('address', $instructor->address) }}" placeholder="Not set"/>
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

<style>
  /* Charcoal & gold — colours come from the tokens in layouts/instructor.blade.php */

  .profile-grid { display: grid; grid-template-columns: 300px 1fr; gap: 24px; align-items: start; }

  .profile-card, .details-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: 16px;
  }
  html:root[data-theme="light"] :is(.profile-card,.details-card) { box-shadow: var(--shadow-card); }

  .profile-card { padding: 36px 24px; text-align: center; }

  .avatar-wrapper { position: relative; display: inline-block; margin-bottom: 20px; }
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

  .profile-name { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
  .profile-role { font-size: 13px; color: var(--accent); font-weight: 600; margin-bottom: 16px; }

  .qr-section {
    margin-bottom: 20px; padding: 15px; background: #ffffff; border-radius: 12px;
    border: 1px solid var(--border); display: inline-block;
  }
  .qr-section img { width: 100px; height: 100px; display: block; margin: 0 auto; }
  .qr-token { font-family: monospace; font-size: 9px; color: #666; margin-top: 8px; word-break: break-all; }

  .specialization-badge {
    display: inline-flex; align-items: center; padding: 6px 14px; border-radius: 100px;
    font-size: 12px; font-weight: 600; background: var(--accent-soft);
    border: 1px solid rgba(224,169,59,0.3); color: var(--accent); margin-bottom: 16px;
  }
  .experience-text { font-size: 13px; color: var(--muted); margin-bottom: 16px; }
  .member-since { font-size: 12px; color: var(--muted); padding-top: 16px; border-top: 1px solid var(--border); }

  .details-card { padding: 32px; }
  .details-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 28px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
  }
  .details-title { font-size: 17px; font-weight: 700; color: var(--accent); }
  .status-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--success); animation: pulse 2s infinite; }
  @keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .5; transform: scale(.8); } }

  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px 40px; }
  .form-group { display: flex; flex-direction: column; margin-bottom: 0; }
  .form-label { margin-bottom: 8px; }
  .field {
    background: transparent; border: none; border-bottom: 2px solid var(--border); border-radius: 0;
    padding: 4px 0 8px; font-size: 15px; font-weight: 600; color: var(--text);
    font-family: 'DM Sans', sans-serif; transition: border-color .15s; outline: none; width: 100%;
  }
  .field:focus { border-bottom-color: var(--accent); }
  .field::placeholder { color: var(--muted); font-weight: 400; }
  .form-text { font-size: 15px; font-weight: 600; padding: 4px 0 8px; border-bottom: 2px solid var(--border); color: var(--muted); word-break: break-word; }
  .error-text { color: var(--danger); font-size: 11px; margin-top: 3px; }

  .action-buttons {
    margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap;
  }
  .action-buttons .btn { padding: 11px 24px; font-size: 14px; }

  @media (max-width: 1024px) {
    .profile-grid { grid-template-columns: 1fr; gap: 20px; }
    .profile-card { max-width: 400px; width: 100%; margin: 0 auto; }
  }
  @media (max-width: 768px) {
    .profile-card { padding: 28px 20px; max-width: 100%; }
    .avatar-preview { width: 140px; height: 140px; }
    .details-card { padding: 24px; }
    .form-grid { grid-template-columns: 1fr; gap: 18px; }
    .action-buttons { justify-content: center; }
    .action-buttons .btn { flex: 1; justify-content: center; min-width: 120px; }
  }
  @media (max-width: 480px) {
    .profile-card { padding: 20px 16px; }
    .avatar-preview { width: 120px; height: 120px; }
    .details-card { padding: 18px; }
    .details-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .details-title { font-size: 15px; }
    .field { font-size: 16px; }
    .action-buttons { flex-direction: column; }
    .action-buttons .btn { width: 100%; }
    .qr-section img { width: 80px; height: 80px; }
    .profile-name { font-size: 18px; }
  }
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
</script>

@endsection