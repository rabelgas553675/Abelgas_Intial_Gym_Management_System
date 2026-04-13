@extends('layouts.guest')
@section('title', 'Login – IRONFORGE')

@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:var(--bg);padding:20px;">

  <div style="width:100%;max-width:440px;">

    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:32px;">
      <div style="font-family:'Bebas Neue',sans-serif;font-size:48px;color:var(--accent);
                  letter-spacing:4px;line-height:1;">IRONFORGE</div>
      <div style="font-size:12px;color:var(--muted);letter-spacing:3px;text-transform:uppercase;
                  margin-top:4px;">Gym Management System</div>
    </div>

    <div class="card" style="padding:32px;">

      <div style="font-size:18px;font-weight:700;margin-bottom:6px;">Welcome back</div>
      <div style="font-size:13px;color:var(--muted);margin-bottom:24px;">Sign in to your account</div>

      @if(session('error'))
        <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);
                    border-radius:var(--radius);padding:10px 14px;margin-bottom:18px;
                    font-size:13px;color:var(--danger);">
          {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Role Selector --}}
        <div style="margin-bottom:20px;">
          <label class="form-label">I am logging in as</label>
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
                <input type="radio" name="login_role" value="{{ $role['value'] }}"
                       style="display:none;" class="role-radio"
                       {{ old('login_role', 'member') === $role['value'] ? 'checked' : '' }}/>
                <div class="role-card" data-role="{{ $role['value'] }}"
                     style="border:2px solid var(--border);border-radius:8px;padding:10px 6px;
                            text-align:center;transition:all 0.2s;background:var(--surface2);
                            {{ old('login_role','member') === $role['value'] ? 'border-color:var(--accent);background:rgba(232,255,42,0.08);' : '' }}">
                  <div style="font-size:20px;margin-bottom:4px;">{{ $role['icon'] }}</div>
                  <div style="font-size:11px;font-weight:600;color:var(--muted);">{{ $role['label'] }}</div>
                </div>
              </label>
            @endforeach
          </div>
        </div>

        {{-- Email --}}
        <div style="margin-bottom:16px;">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control"
                 value="{{ old('email') }}" required autofocus
                 placeholder="you@example.com"/>
          @error('email')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        {{-- Password --}}
        <div style="margin-bottom:20px;">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control"
                 required placeholder="••••••••"/>
          @error('password')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
        </div>

        {{-- Remember --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
            <input type="checkbox" name="remember" style="accent-color:var(--accent);"/>
            Remember me
          </label>
          @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}"
               style="font-size:13px;color:var(--accent);text-decoration:none;">Forgot password?</a>
          @endif
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;font-size:15px;">
          Sign In →
        </button>

        @if(Route::has('register'))
          <div style="text-align:center;margin-top:20px;font-size:13px;color:var(--muted);">
            Don't have an account?
            <a href="{{ route('register') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">
              Register here
            </a>
          </div>
        @endif

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
  });
});
</script>
@endsection