{{-- resources/views/layouts/navigation.blade.php
     Same look as the Staff layout: black bar, gold accents, hamburger drawer on mobile.
     Nav styles are included here so it works standalone. --}}

<style>
  :root {
    --bg:#131417; --surface:#1a1b1f; --surface2:#212227;
    --border:rgba(255,255,255,0.07);
    --accent:#e0a93b; --accent-2:#f3c866; --accent-dark:#b8862a;
    --accent-soft:rgba(224,169,59,0.12);
    --text:#ffffff; --danger:#f87171;
  }
  body.menu-open { overflow: hidden; }

  .topnav {
    position: sticky; top: 0; z-index: 100;
    background: rgba(20,21,24,0.92);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
    height: 60px;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 36px; gap: 12px;
    font-family: 'DM Sans', sans-serif;
  }
  .topnav-brand { display:flex; align-items:center; gap:10px; text-decoration:none; min-width:0; }
  .topnav-logo {
    width:34px; height:34px;
    background: linear-gradient(145deg,#4a4b52,#2c2d33);
    border:1px solid rgba(255,255,255,0.12);
    border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0;
  }
  .topnav-logo svg { width:18px; height:18px; stroke:#e6e6e8; }
  .topnav-name {
    font-weight:700; font-size:15px; color:#e4e5e8; letter-spacing:2.5px;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }

  .topnav-links { display:flex; align-items:center; gap:2px; }
  .nav-link {
    position: relative;
    display:flex; align-items:center; gap:7px;
    padding:8px 14px; border-radius:8px;
    font-size:13px; font-weight:500; color:#c6c8ce;
    text-decoration:none; transition:all .15s; white-space:nowrap;
  }
  .nav-link svg { width:15px; height:15px; stroke:#c6c8ce; fill:none; flex-shrink:0; transition:stroke .15s; }
  .nav-link:hover { color:var(--text); background:rgba(255,255,255,0.05); }
  .nav-link:hover svg { stroke:var(--text); }
  .nav-link.active { color:var(--text); background:rgba(255,255,255,0.05); font-weight:600; }
  .nav-link.active svg { stroke:var(--accent); }
  .nav-link.active::after {
    content:''; position:absolute; bottom:-11px; left:14px; right:14px;
    height:2px; background:var(--accent); border-radius:2px;
  }

  .topnav-right { display:flex; align-items:center; gap:14px; flex-shrink:0; }
  .role-badge {
    font-size:10px; font-weight:800; letter-spacing:1.5px; text-transform:uppercase;
    padding:5px 14px; border-radius:6px; white-space:nowrap; color:#1a1a1a;
    background: linear-gradient(135deg,var(--accent-2),var(--accent-dark));
    box-shadow: 0 0 14px rgba(224,169,59,0.25);
  }
  .user-chip { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:500; color:#e4e5e8; white-space:nowrap; }
  .user-chip .user-name { max-width:160px; overflow:hidden; text-overflow:ellipsis; }
  .user-avatar {
    width:30px; height:30px; border-radius:50%;
    background:var(--accent-soft); border:1px solid rgba(224,169,59,0.3);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:12px; color:var(--accent); overflow:hidden; flex-shrink:0;
  }
  .user-avatar img { width:100%; height:100%; object-fit:cover; }

  .btn-logout {
    display:flex; align-items:center; gap:6px; padding:7px 14px;
    background:rgba(248,113,113,0.04); border:1px solid rgba(248,113,113,0.45);
    border-radius:8px; color:var(--danger); font-size:13px; font-weight:500;
    cursor:pointer; font-family:'DM Sans',sans-serif; transition:all .15s; white-space:nowrap;
  }
  .btn-logout svg { width:14px; height:14px; stroke:var(--danger); flex-shrink:0; }
  .btn-logout:hover { background:rgba(248,113,113,0.12); border-color:var(--danger); }

  .topnav-toggle {
    display:none; align-items:center; justify-content:center;
    width:38px; height:38px; background:transparent;
    border:1px solid var(--border); border-radius:8px; cursor:pointer; flex-shrink:0;
  }
  .topnav-toggle svg { width:20px; height:20px; stroke:var(--text); fill:none; }
  .topnav-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:90; }
  .topnav-overlay.open { display:block; }

  @media (max-width:1024px) {
    .topnav { padding:0 20px; }
    .topnav-links { gap:0; }
    .nav-link { padding:8px 10px; }
  }
  @media (max-width:860px) {
    .topnav { height:56px; }
    .topnav-name { font-size:13px; max-width:46vw; }
    .topnav-toggle { display:flex; order:3; }
    .topnav-links {
      position:fixed; top:0; right:0; height:100vh; width:min(80vw,300px);
      background:var(--surface); border-left:1px solid var(--border);
      flex-direction:column; align-items:stretch; gap:3px; padding:74px 14px 20px;
      transform:translateX(100%); transition:transform .25s ease; z-index:95; overflow-y:auto;
    }
    .topnav-links.open { transform:translateX(0); }
    .nav-link { width:100%; padding:13px 14px; font-size:15px; border-radius:10px; }
    .nav-link svg { width:18px; height:18px; }
    .nav-link.active::after { display:none; }
    .nav-link.active { border-left:3px solid var(--accent); }
    .topnav-right { gap:8px; order:2; }
    .user-chip .user-name, .role-badge { display:none; }
    .btn-logout { padding:8px; width:38px; height:38px; justify-content:center; }
    .btn-logout span { display:none; }
  }
  @media (max-width:480px) {
    .topnav { padding:0 12px; height:52px; }
    .topnav-name { font-size:12px; letter-spacing:1.5px; max-width:38vw; }
    .topnav-logo { width:28px; height:28px; }
    .topnav-links { width:84vw; }
  }

  /* ── Light theme: bar stays black, gold accents (same as staff) ── */
  html:root[data-theme="light"] .topnav { background:rgba(15,15,16,.96); border-color:rgba(255,255,255,.08); }
  html:root[data-theme="light"] .topnav-logo { background:linear-gradient(145deg,#2a2a2c,#0b0b0c); border-color:rgba(224,169,59,.45); }
  html:root[data-theme="light"] .topnav-logo svg { stroke:var(--accent-2); }
  html:root[data-theme="light"] .nav-link:hover,
  html:root[data-theme="light"] .nav-link.active { color:#fff; background:rgba(255,255,255,.07); }
  html:root[data-theme="light"] .nav-link.active svg { stroke:var(--accent-2); }
  html:root[data-theme="light"] .nav-link.active::after { background:var(--accent-2); }
  html:root[data-theme="light"] .user-avatar { background:rgba(224,169,59,.14); border-color:rgba(224,169,59,.45); color:var(--accent-2); }
  html:root[data-theme="light"] .topnav-toggle { border-color:rgba(255,255,255,.15); }
  html:root[data-theme="light"] .topnav-toggle svg { stroke:#fff; }
  @media (max-width:860px) {
    html:root[data-theme="light"] .topnav-links { background:#111112; border-left-color:rgba(255,255,255,.08); }
  }
</style>

<nav class="topnav">
  <a href="{{ route('dashboard') }}" class="topnav-brand">
    <div class="topnav-logo">
      <svg fill="none" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
    </div>
    <span class="topnav-name">APEX FITNESS GYM</span>
  </a>

  <div class="topnav-links" id="topnavLinks">
    <a href="{{ route('dashboard') }}"
       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      Dashboard
    </a>

    @if (\Illuminate\Support\Facades\Route::has('profile'))
    <a href="{{ \Illuminate\Support\Facades\Route::has('profile.edit') ? route('profile.edit') : route('profile') }}"
       class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Profile
    </a>
    @endif
  </div>

  <div class="topnav-right">
    {{-- Change the label to match this role (Admin / Member) --}}
    <span class="role-badge">Admin</span>

    <div class="user-chip">
      <div class="user-avatar">
        @if(auth()->user()->photo ?? false)
          <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt=""/>
        @else
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        @endif
      </div>
      <span class="user-name">{{ auth()->user()->name }}</span>
    </div>

    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
      @csrf
      <button type="submit" class="btn-logout">
        <svg viewBox="0 0 24 24" stroke-width="2" fill="none">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span>Logout</span>
      </button>
    </form>
  </div>

  <button type="button" class="topnav-toggle" id="topnavToggle"
          aria-label="Toggle navigation" aria-expanded="false" aria-controls="topnavLinks">
    <svg id="navIconOpen" viewBox="0 0 24 24" stroke-width="2">
      <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
    <svg id="navIconClose" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
</nav>

<div class="topnav-overlay" id="topnavOverlay"></div>

<script>
  (function () {
    var toggle = document.getElementById('topnavToggle');
    var links = document.getElementById('topnavLinks');
    var overlay = document.getElementById('topnavOverlay');
    var iconOpen = document.getElementById('navIconOpen');
    var iconClose = document.getElementById('navIconClose');

    function setOpen(state) {
      links.classList.toggle('open', state);
      overlay.classList.toggle('open', state);
      document.body.classList.toggle('menu-open', state);
      toggle.setAttribute('aria-expanded', state ? 'true' : 'false');
      iconOpen.style.display = state ? 'none' : '';
      iconClose.style.display = state ? '' : 'none';
    }

    toggle.addEventListener('click', function () { setOpen(!links.classList.contains('open')); });
    overlay.addEventListener('click', function () { setOpen(false); });
    links.querySelectorAll('.nav-link').forEach(function (l) {
      l.addEventListener('click', function () { setOpen(false); });
    });
    window.addEventListener('resize', function () { if (window.innerWidth > 860) setOpen(false); });
  })();
</script>