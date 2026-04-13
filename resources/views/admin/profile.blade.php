@extends('layouts.admin')
@section('title', 'My Profile – IRONFORGE')
@section('page_title', 'My Profile')
@section('active_nav', 'admin.profile')

@section('content')

<div style="margin-bottom:28px;">
  <h1 style="font-size:30px;font-weight:700;margin-bottom:4px;">My Profile</h1>
  <p style="color:var(--muted);font-size:14px;">View and update your admin account details.</p>
</div>

@if(session('success'))
  <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif

<form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
@csrf

<div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start;">

  {{-- LEFT: Avatar card --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              padding:36px 28px;text-align:center;">
    <div style="position:relative;display:inline-block;margin-bottom:20px;">
      <div id="avatar-preview"
           style="width:140px;height:140px;border-radius:50%;overflow:hidden;
                  background:var(--surface2);border:3px solid var(--border);margin:0 auto;">
        @if($user->photo)
          <img src="{{ asset('storage/'.$user->photo) }}" style="width:100%;height:100%;object-fit:cover;"/>
        @else
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;
                      font-family:'Bebas Neue',sans-serif;font-size:48px;color:var(--accent);">
            {{ strtoupper(substr($user->name,0,2)) }}
          </div>
        @endif
      </div>
      <label style="position:absolute;bottom:6px;right:6px;width:34px;height:34px;border-radius:50%;
                     background:var(--accent);display:flex;align-items:center;justify-content:center;
                     cursor:pointer;border:3px solid var(--bg);">
        <svg width="15" height="15" fill="none" stroke="#111" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
          <circle cx="12" cy="13" r="4"/>
        </svg>
        <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
      </label>
    </div>
    <div style="font-size:20px;font-weight:700;margin-bottom:4px;">{{ $user->name }}</div>
    <div style="display:inline-block;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;
                background:rgba(200,255,0,0.12);color:var(--accent);border:1px solid rgba(200,255,0,0.25);
                margin-bottom:16px;">
      Admin
    </div>
    <div style="display:inline-flex;align-items:center;gap:6px;padding:7px 18px;border-radius:20px;
                background:rgba(74,222,128,0.12);border:1px solid rgba(74,222,128,0.25);
                font-size:13px;font-weight:600;color:var(--success);margin-bottom:24px;">
      <span style="width:7px;height:7px;border-radius:50%;background:var(--success);display:inline-block;"></span>
      Active Admin
    </div>
    <div style="font-size:12px;color:var(--muted);padding-top:16px;border-top:1px solid var(--border);">
      Member since {{ $user->created_at->format('M d, Y') }}
    </div>
  </div>

  {{-- RIGHT: Bio form --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
      <div style="font-size:17px;font-weight:700;">Bio &amp; other details</div>
      <span style="width:10px;height:10px;border-radius:50%;background:var(--accent);display:inline-block;"></span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Full Name</div>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $user->name) }}" required
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:8px 0;font-size:15px;font-weight:600;"/>
        @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
      </div>
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Email Address</div>
        <input type="email" class="form-control" value="{{ $user->email }}" disabled
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:8px 0;font-size:15px;font-weight:600;opacity:0.5;cursor:not-allowed;"/>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Phone Number</div>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $user->phone) }}" placeholder="09..."
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:8px 0;font-size:15px;font-weight:600;"/>
      </div>
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Date of Birth</div>
        <input type="date" name="birthdate" class="form-control"
               value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:8px 0;font-size:15px;font-weight:600;color-scheme:dark;"/>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Gender</div>
        <select name="gender" class="form-control"
                style="background:transparent;border:none;border-bottom:1px solid var(--border);
                       border-radius:0;padding:8px 0;font-size:15px;font-weight:600;-webkit-appearance:none;">
          <option value="">— Select —</option>
          @foreach(['Male','Female','Other'] as $g)
            <option value="{{ $g }}" style="background:var(--surface2);"
                    {{ old('gender', $user->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">City / Address</div>
        <input type="text" name="address" class="form-control"
               value="{{ old('address', $user->address) }}" placeholder="Your city or address..."
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:8px 0;font-size:15px;font-weight:600;"/>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:36px;">
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Role</div>
        <div style="font-size:15px;font-weight:600;color:var(--accent);padding:8px 0;border-bottom:1px solid var(--border);">Admin</div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Account Created</div>
        <div style="font-size:15px;font-weight:600;color:var(--muted);padding:8px 0;border-bottom:1px solid var(--border);">
          {{ $user->created_at->format('M d, Y') }}
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:12px;">
      <a href="{{ route('dashboard') }}" class="btn btn-secondary" style="padding:11px 28px;">Cancel</a>
      <button type="submit" class="btn btn-primary" style="padding:11px 32px;font-size:14px;">
        Save Changes
      </button>
    </div>
  </div>
</div>
</form>

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