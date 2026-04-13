@extends('layouts.guest')
@section('title', 'Register – IRONFORGE')

@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:var(--bg);padding:20px;">

  <div style="width:100%;max-width:480px;">

    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:28px;">
      <div style="font-family:'Bebas Neue',sans-serif;font-size:44px;color:var(--accent);
                  letter-spacing:4px;line-height:1;">IRONFORGE</div>
      <div style="font-size:12px;color:var(--muted);letter-spacing:3px;text-transform:uppercase;
                  margin-top:4px;">Create Your Account</div>
    </div>

    <div class="card" style="padding:32px;">

      <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Join IRONFORGE</div>
      <div style="font-size:13px;color:var(--muted);margin-bottom:24px;">Fill in your details to get started</div>

      <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Role Selector --}}
        <div style="margin-bottom:20px;">
          <label class="form-label">I am registering as</label>
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
            @php
              $roles = [
                ['value'=>'admin',      'label'=>'Admin',      'icon'=>'🛡️'],
                ['value'=>'staff',      'label'=>'Staff',      'icon'=>'👤'],
                ['value'=>'instructor', 'label'=>'Instructor', 'icon'=>'💪'],
                ['value'=>'member',     'label'=>'Member',     'icon'=>'🏋️'],
              ];
            @endphp
            @foreach($roles as $role)
              <label style="cursor:pointer;">
                <input type="radio" name="role" value="{{ $role['value'] }}"
                       style="display:none;" class="role-radio"
                       {{ old('role', 'member') === $role['value'] ? 'checked' : '' }}/>
                <div class="role-card" data-role="{{ $role['value'] }}"
                     style="border:2px solid var(--border);border-radius:8px;padding:10px 6px;
                            text-align:center;transition:all 0.2s;background:var(--surface2);
                            {{ old('role','member') === $role['value'] ? 'border-color:var(--accent);background:rgba(232,255,42,0.08);' : '' }}">
                  <div style="font-size:20px;margin-bottom:4px;">{{ $role['icon'] }}</div>
                  <div style="font-size:11px;font-weight:600;color:var(--muted);">{{ $role['label'] }}</div>
                </div>
              </label>
            @endforeach
          </div>
          @error('role')<div style="color:var(--danger);font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
        </div>

        {{-- Name --}}
        <div style="margin-bottom:16px;">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control"
                 value="{{ old('name') }}" required autofocus placeholder="Your full name"/>
          @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        {{-- Email --}}
        <div style="margin-bottom:16px;">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control"
                 value="{{ old('email') }}" required placeholder="you@example.com"/>
          @error('email')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        {{-- Instructor selector — shown only for members --}}
        <div id="instructor-section" style="margin-bottom:16px;{{ old('role','member') !== 'member' ? 'display:none;' : '' }}">
          <label class="form-label">Select an Instructor <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
          <select name="instructor_id" class="form-control">
            <option value="">— No instructor —</option>
            @foreach(\App\Models\User::where('role','instructor')->get() as $inst)
              <option value="{{ $inst->id }}" {{ old('instructor_id') == $inst->id ? 'selected' : '' }}>
                {{ $inst->name }}{{ $inst->specialization ? ' – '.$inst->specialization : '' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Password --}}
        <div style="margin-bottom:16px;">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control"
                 required placeholder="Min. 8 characters"/>
          @error('password')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        {{-- Confirm Password --}}
        <div style="margin-bottom:24px;">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="password_confirmation" class="form-control"
                 required placeholder="Repeat password"/>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;font-size:15px;">
          Create Account →
        </button>

        <div style="text-align:center;margin-top:20px;font-size:13px;color:var(--muted);">
          Already have an account?
          <a href="{{ route('login') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">
            Sign in here
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<style>
.role-card:hover {
  border-color: var(--accent) !important;
  background: rgba(232,255,42,0.05) !important;
}
</style>
<script>
// Role card selection highlight
document.querySelectorAll('.role-radio').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.role-card').forEach(c => {
      c.style.borderColor = 'var(--border)';
      c.style.background = 'var(--surface2)';
    });
    if (r.checked) {
      const card = r.closest('label').querySelector('.role-card');
      card.style.borderColor = 'var(--accent)';
      card.style.background = 'rgba(232,255,42,0.08)';
    }
    // Show instructor dropdown only for members
    const section = document.getElementById('instructor-section');
    section.style.display = r.value === 'member' ? 'block' : 'none';
  });
});
</script>
@endsection