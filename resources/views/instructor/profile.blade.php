@extends('layouts.instructor')
@section('title', 'My Profile – IRONFORGE')
@section('active', 'profile')

@section('content')

<div style="margin-bottom:28px;">
  <h1 style="font-size:32px;font-weight:700;margin-bottom:6px;">My Profile</h1>
  <p style="color:var(--muted);font-size:14px;">View and update your instructor information</p>
</div>

<form action="{{ route('instructor.profile.update') }}" method="POST" enctype="multipart/form-data">
@csrf

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">

  {{-- LEFT: Photo Card --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              padding:36px 24px;text-align:center;">

    <div style="position:relative;display:inline-block;margin-bottom:20px;">
      <div id="avatar-preview"
           style="width:160px;height:160px;border-radius:50%;overflow:hidden;
                  background:var(--surface2);border:3px solid var(--border);margin:0 auto;">
        @if($instructor->photo)
          <img src="{{ asset('storage/'.$instructor->photo) }}"
               style="width:100%;height:100%;object-fit:cover;"/>
        @else
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
            <svg width="56" height="56" fill="none" stroke="var(--muted)" stroke-width="1.2" viewBox="0 0 24 24">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </div>
        @endif
      </div>
      <label style="position:absolute;bottom:6px;right:6px;width:34px;height:34px;
                     border-radius:50%;background:var(--accent);cursor:pointer;
                     display:flex;align-items:center;justify-content:center;
                     border:2px solid var(--bg);box-shadow:0 2px 8px rgba(0,0,0,0.5);">
        <svg width="15" height="15" fill="none" stroke="#111" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
          <circle cx="12" cy="13" r="4"/>
        </svg>
        <input type="file" name="photo" accept="image/*" style="display:none;"
               onchange="previewAvatar(this)"/>
      </label>
    </div>

    <div style="font-size:20px;font-weight:700;margin-bottom:4px;">{{ $instructor->name }}</div>
    <div style="font-size:13px;color:#ff6b35;font-weight:600;margin-bottom:20px;">Instructor</div>

    {{-- Specialization badge --}}
    @if($instructor->specialization)
      <div style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;
                  border-radius:100px;font-size:12px;font-weight:600;
                  background:rgba(255,107,53,0.1);border:1px solid rgba(255,107,53,0.25);
                  color:#ff6b35;margin-bottom:20px;">
        💪 {{ $instructor->specialization }}
      </div>
    @endif

    @if($instructor->experience_years)
      <div style="font-size:13px;color:var(--muted);margin-bottom:16px;">
        {{ $instructor->experience_years }} years of experience
      </div>
    @endif

    <div style="font-size:12px;color:var(--muted);padding-top:16px;border-top:1px solid var(--border);">
      Member since {{ auth()->user()->created_at->format('M d, Y') }}
    </div>
  </div>

  {{-- RIGHT: Details Card --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
      <div style="font-size:17px;font-weight:700;">Bio & other details</div>
      <div style="width:10px;height:10px;border-radius:50%;background:var(--accent);
                  box-shadow:0 0 8px rgba(200,255,0,0.5);"></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px 40px;">

      {{-- Full Name --}}
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:8px;letter-spacing:0.5px;">Full Name</div>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $instructor->name) }}" required
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:4px 0;font-size:15px;font-weight:600;"/>
        @error('name')
          <div style="color:var(--danger);font-size:11px;margin-top:3px;">{{ $message }}</div>
        @enderror
      </div>

      {{-- Email (read-only) --}}
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:8px;letter-spacing:0.5px;">Email Address</div>
        <div style="font-size:15px;font-weight:600;color:var(--muted);padding:4px 0;
                    border-bottom:1px solid var(--border);">
          {{ $instructor->email }}
        </div>
      </div>

      {{-- Phone --}}
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:8px;letter-spacing:0.5px;">Phone Number</div>
        <input type="text" name="phone" class="form-control"
               value="{{ old('phone', $instructor->phone) }}" placeholder="Not set"
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:4px 0;font-size:15px;font-weight:600;"/>
      </div>

      {{-- Specialization --}}
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:8px;letter-spacing:0.5px;">Specialization</div>
        <input type="text" name="specialization" class="form-control"
               value="{{ old('specialization', $instructor->specialization) }}"
               placeholder="e.g. Bodybuilding, Calisthenics"
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:4px 0;font-size:15px;font-weight:600;"/>
      </div>

      {{-- Experience Years --}}
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:8px;letter-spacing:0.5px;">Years of Experience</div>
        <input type="number" name="experience_years" class="form-control" min="0" max="50"
               value="{{ old('experience_years', $instructor->experience_years) }}"
               placeholder="0"
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:4px 0;font-size:15px;font-weight:600;"/>
      </div>

      {{-- Address --}}
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:8px;letter-spacing:0.5px;">City / Address</div>
        <input type="text" name="address" class="form-control"
               value="{{ old('address', $instructor->address) }}" placeholder="Not set"
               style="background:transparent;border:none;border-bottom:1px solid var(--border);
                      border-radius:0;padding:4px 0;font-size:15px;font-weight:600;"/>
      </div>

      {{-- Assigned Members (read-only info) --}}
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:8px;letter-spacing:0.5px;">Assigned Members</div>
        <div style="font-size:15px;font-weight:600;color:var(--accent);padding:4px 0;
                    border-bottom:1px solid var(--border);">
          {{ auth()->user()->assignedMembers()->count() }} members
        </div>
      </div>

    </div>

    {{-- Actions --}}
    <div style="margin-top:36px;padding-top:24px;border-top:1px solid var(--border);
                display:flex;justify-content:flex-end;gap:12px;">
      <a href="{{ route('instructor.dashboard') }}" class="btn btn-secondary" style="padding:11px 24px;">
        Cancel
      </a>
      <button type="submit" class="btn btn-primary" style="padding:11px 28px;font-size:14px;">
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