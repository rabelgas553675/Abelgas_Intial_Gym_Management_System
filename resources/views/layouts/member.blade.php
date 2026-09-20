<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0"/>
  <title>@yield('title', 'APEX FITNESS GYM')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root{--bg:#0d0d0d;--surface:#151515;--surface2:#1c1c1c;--border:#2a2a2a;--accent:#ff2b3d;--text:#f0f0f0;--muted:#888;--success:#4ade80;--danger:#f87171;--warning:#fbbf24;--radius:10px;}
    *{box-sizing:border-box;margin:0;padding:0;}
    html{-webkit-text-size-adjust:100%;}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;min-height:100vh;overflow-x:hidden;}

    /* ===== NAVBAR ===== */
    .navbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 32px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
    .navbar-brand{font-family:'Bebas Neue',sans-serif;font-size:24px;color:var(--accent);letter-spacing:2px;text-decoration:none;white-space:nowrap;}
    .navbar-brand:hover{color:#ff4757;}
    .navbar-nav{display:flex;align-items:center;gap:4px;}
    .nav-item{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;color:var(--muted);font-size:14px;font-weight:500;text-decoration:none;transition:all 0.15s;white-space:nowrap;}
    .nav-item:hover{color:var(--text);background:var(--surface2);}
    .nav-item.active{color:var(--accent);background:rgba(255,43,61,0.08);}
    .nav-item.active svg{stroke:var(--accent);}
    .nav-item svg{width:16px;height:16px;stroke:var(--muted);transition:stroke 0.15s;flex-shrink:0;}
    .nav-item:hover svg{stroke:var(--text);}
    .navbar-right{display:flex;align-items:center;gap:16px;}
    .user-chip{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:500;white-space:nowrap;}
    .user-chip .avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;}
    .user-chip .avatar-placeholder{width:30px;height:30px;border-radius:50%;background:rgba(255,43,61,0.15);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--accent);}
    .btn-logout-top{display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;background:transparent;border:1px solid var(--border);color:var(--muted);font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all 0.15s;white-space:nowrap;}
    .btn-logout-top:hover{border-color:var(--danger);color:var(--danger);background:rgba(248,113,113,0.05);}

    /* Hamburger toggle button - hidden on desktop */
    .navbar-toggle{display:none;background:transparent;border:1px solid var(--border);border-radius:8px;width:38px;height:38px;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;}
    .navbar-toggle:hover{border-color:var(--accent);}
    .navbar-toggle svg{width:20px;height:20px;stroke:var(--text);}

    /* Overlay behind mobile menu */
    .navbar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:90;}
    .navbar-overlay.open{display:block;}

    .page-content{max-width:1100px;margin:0 auto;padding:40px 32px;}

    .btn{padding:8px 16px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
    .btn-primary{background:var(--accent);color:#111;}
    .btn-primary:hover{background:#e0141f;transform:translateY(-1px);}
    .btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
    .btn-secondary:hover{background:#242424;}
    .form-label{display:block;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
    .form-control{width:100%;padding:11px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
    .form-control:focus{border-color:var(--accent);}
    .form-control option{background:var(--surface2);}
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;margin-bottom:24px;}
    .alert-success{background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.2);color:var(--success);}
    .alert-danger{background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.2);color:var(--danger);}

    /* Prevent body scroll when mobile menu is open */
    body.menu-open{overflow:hidden;}

    /* ===== TABLET (<=900px): tighten spacing, shrink text ===== */
    @media (max-width: 900px){
      .navbar{padding:0 20px;}
      .nav-item{padding:8px 10px;font-size:13px;}
      .user-chip{font-size:13px;}
      .page-content{padding:28px 20px;}
    }

    /* ===== MOBILE (<=768px): collapse into hamburger drawer ===== */
    @media (max-width: 768px){
      .navbar{padding:0 16px;height:56px;}
      .navbar-brand{font-size:20px;}

      .navbar-toggle{display:flex;order:3;}

      /* Slide-in drawer from right */
      .navbar-nav{
        position:fixed;
        top:0;
        right:0;
        height:100vh;
        width:min(78vw,300px);
        background:var(--surface);
        border-left:1px solid var(--border);
        flex-direction:column;
        align-items:stretch;
        gap:2px;
        padding:80px 14px 20px;
        transform:translateX(100%);
        transition:transform 0.25s ease;
        z-index:95;
        overflow-y:auto;
      }
      .navbar-nav.open{transform:translateX(0);}
      .nav-item{padding:13px 14px;font-size:15px;border-radius:10px;width:100%;}
      .nav-item svg{width:18px;height:18px;}

      /* Right side: hide the text user chip, keep avatar + logout compact */
      .navbar-right{gap:8px;order:2;}
      .user-chip .user-name{display:none;}
      .btn-logout-top span{display:none;}
      .btn-logout-top{padding:8px;width:38px;height:38px;justify-content:center;}

      .page-content{padding:20px 14px;}
    }

    /* ===== SMALL PHONES (<=420px) ===== */
    @media (max-width: 420px){
      .navbar-brand{font-size:17px;letter-spacing:1px;}
      .navbar{height:52px;padding:0 12px;}
      .page-content{padding:16px 10px;}
      .navbar-nav{width:82vw;padding-top:70px;}
    }
  </style>
</head>
<body>

{{-- Read active section before navbar renders --}}
@php $active = View::getSection('active') ?? ''; @endphp

<nav class="navbar">
  <!-- Brand with APEX FITNESS GYM -->
  <a href="{{ route('member.dashboard') }}" class="navbar-brand">APEX FITNESS GYM</a>

  <!-- Navigation Links -->
  <div class="navbar-nav" id="navbarNav">
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

  <!-- Right side: User info + Logout -->
  <div class="navbar-right">
    <div class="user-chip">
      @if(auth()->user()->photo)
        <img src="{{ asset('storage/'.auth()->user()->photo) }}" class="avatar" alt="Avatar"/>
      @else
        <div class="avatar-placeholder">
          {{ strtoupper(substr(auth()->user()->name,0,2)) }}
        </div>
      @endif
      <span class="user-name">{{ auth()->user()->name }}</span>
    </div>
    
    <!-- Logout button -->
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
      @csrf
      <button type="submit" class="btn-logout-top">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span>Logout</span>
      </button>
    </form>
  </div>

  {{-- Hamburger toggle (mobile only) --}}
  <button type="button" class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarNav">
    <svg id="iconOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="3" y1="6" x2="21" y2="6"/>
      <line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
    <svg id="iconClose" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
      <line x1="18" y1="6" x2="6" y2="18"/>
      <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
</nav>

<div class="navbar-overlay" id="navbarOverlay"></div>

<div class="page-content">
  @if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">✕ {{ session('error') }}</div>
  @endif
  @yield('content')
</div>

<script>
  (function(){
    var toggle = document.getElementById('navbarToggle');
    var nav = document.getElementById('navbarNav');
    var overlay = document.getElementById('navbarOverlay');
    var iconOpen = document.getElementById('iconOpen');
    var iconClose = document.getElementById('iconClose');

    function closeMenu(){
      nav.classList.remove('open');
      overlay.classList.remove('open');
      document.body.classList.remove('menu-open');
      toggle.setAttribute('aria-expanded', 'false');
      iconOpen.style.display = '';
      iconClose.style.display = 'none';
    }

    function openMenu(){
      nav.classList.add('open');
      overlay.classList.add('open');
      document.body.classList.add('menu-open');
      toggle.setAttribute('aria-expanded', 'true');
      iconOpen.style.display = 'none';
      iconClose.style.display = '';
    }

    toggle.addEventListener('click', function(){
      nav.classList.contains('open') ? closeMenu() : openMenu();
    });

    overlay.addEventListener('click', closeMenu);

    // Close the drawer after tapping a nav link
    nav.querySelectorAll('.nav-item').forEach(function(link){
      link.addEventListener('click', closeMenu);
    });

    // Reset state if the viewport grows back to desktop size
    window.addEventListener('resize', function(){
      if (window.innerWidth > 768) closeMenu();
    });
  })();
</script>

</body>
</html>