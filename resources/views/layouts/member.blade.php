<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title', 'IRONFORGE')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root{--bg:#0d0d0d;--surface:#151515;--surface2:#1c1c1c;--border:#2a2a2a;--accent:#e8ff2a;--text:#f0f0f0;--muted:#888;--success:#4ade80;--danger:#f87171;--warning:#fbbf24;--radius:10px;}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;min-height:100vh;}
    .navbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 32px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
    .navbar-brand{font-family:'Bebas Neue',sans-serif;font-size:24px;color:var(--accent);letter-spacing:2px;text-decoration:none;}
    .navbar-nav{display:flex;align-items:center;gap:4px;}
    .nav-item{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;color:var(--muted);font-size:14px;font-weight:500;text-decoration:none;transition:all 0.15s;}
    .nav-item:hover{color:var(--text);background:var(--surface2);}
    .nav-item.active{color:var(--accent);background:rgba(232,255,42,0.08);}
    .nav-item.active svg{stroke:var(--accent);}
    .nav-item svg{width:16px;height:16px;stroke:var(--muted);transition:stroke 0.15s;}
    .nav-item:hover svg{stroke:var(--text);}
    .navbar-right{display:flex;align-items:center;gap:16px;}
    .user-chip{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:500;}
    .btn-logout-top{display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;background:transparent;border:1px solid var(--border);color:var(--muted);font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all 0.15s;}
    .btn-logout-top:hover{border-color:var(--danger);color:var(--danger);}
    .page-content{max-width:1100px;margin:0 auto;padding:40px 32px;}
    .btn{padding:8px 16px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
    .btn-primary{background:var(--accent);color:#111;}
    .btn-primary:hover{background:#d4e825;transform:translateY(-1px);}
    .btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
    .btn-secondary:hover{background:#242424;}
    .form-label{display:block;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
    .form-control{width:100%;padding:11px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
    .form-control:focus{border-color:var(--accent);}
    .form-control option{background:var(--surface2);}
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;}
    .alert-success{background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.2);color:var(--success);}
    .alert-danger{background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.2);color:var(--danger);}
  </style>
</head>
<body>

{{-- Read active section before navbar renders --}}
@php $active = View::getSection('active') ?? ''; @endphp

<nav class="navbar">
  <a href="{{ route('member.dashboard') }}" class="navbar-brand">IRONFORGE</a>

  <div class="navbar-nav">
    <a href="{{ route('member.dashboard') }}"
       class="nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      Dashboard
    </a>

    {{-- Show Waiting only if member is pending --}}
    @if(Auth::user()->memberProfile && Auth::user()->memberProfile->coach_status === 'pending')
        <a href="{{ route('member.waiting') }}" 
           class="nav-item {{ $active === 'waiting' ? 'active' : '' }}" 
           style="color: var(--accent);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Waiting Approval
        </a>
    @endif

    <a href="{{ route('member.schedule') }}"
       class="nav-item {{ $active === 'schedule' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2" fill="none" stroke="currentColor">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      My Schedule
    </a>

    <a href="{{ route('member.profile') }}"
       class="nav-item {{ $active === 'profile' ? 'active' : '' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      Profile
    </a>

    <a href="{{ route('member.payments') }}"
       class="nav-item {{ $active === 'payments' ? 'active' : '' }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <rect x="1" y="4" width="22" height="16" rx="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
      Payments
    </a>
  </div>

  <div class="navbar-right">
    <div class="user-chip">
      @if(auth()->user()->photo)
        <img src="{{ asset('storage/'.auth()->user()->photo) }}"
             style="width:30px;height:30px;border-radius:50%;object-fit:cover;"/>
      @else
        <div style="width:30px;height:30px;border-radius:50%;background:rgba(232,255,42,0.15);
                    display:flex;align-items:center;justify-content:center;font-size:12px;
                    font-weight:700;color:var(--accent);">
          {{ strtoupper(substr(auth()->user()->name,0,2)) }}
        </div>
      @endif
      {{ auth()->user()->name }}
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout-top">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Logout
      </button>
    </form>
  </div>
</nav>

<div class="page-content">
  @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:24px;">✓ {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:24px;">✕ {{ session('error') }}</div>
  @endif
  @yield('content')
</div>

</body>
</html>