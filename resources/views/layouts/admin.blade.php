<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1"/>
  <title>@yield('title', 'APEX FITNESS GYM')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root{
      --bg:#0a0a0a;--surface:#111111;--surface2:#181818;--surface3:#202020;
      --border:#222222;--accent:#ff2b3d;--text:#f0f0f0;--muted:#555;
      --success:#4ade80;--danger:#f87171;--warning:#fbbf24;--info:#60a5fa;
      --accent2:#ff6b35;--radius:10px;
      --navbar-h:60px;--topbar-h:52px;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    html{-webkit-text-size-adjust:100%;}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;min-height:100vh;overflow-x:hidden;}
    img{max-width:100%;}

    /* ── NAVBAR ── */
    .navbar{
      background:var(--surface);border-bottom:1px solid var(--border);
      padding:0 36px;height:var(--navbar-h);display:flex;align-items:center;
      justify-content:space-between;position:sticky;top:0;z-index:200;
      gap:12px;
    }
    .navbar-brand{display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0;}
    .brand-icon{width:32px;height:32px;background:var(--accent);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand-icon svg{width:18px;height:18px;fill:none;stroke:#111;stroke-width:2.5;}
    .brand-name{font-family:'Bebas Neue',sans-serif;font-size:20px;color:var(--accent);letter-spacing:3px;line-height:1;white-space:nowrap;}

    .navbar-nav{display:flex;align-items:center;gap:2px;}
    .nav-item{
      display:flex;align-items:center;gap:7px;padding:8px 14px;
      color:var(--muted);font-size:13px;font-weight:500;text-decoration:none;
      transition:all 0.15s;border-bottom:2px solid transparent;white-space:nowrap;
    }
    .nav-item:hover{color:var(--text);}
    .nav-item.active{color:var(--accent);border-bottom-color:var(--accent);}
    .nav-item svg{width:15px;height:15px;stroke:currentColor;fill:none;flex-shrink:0;}

    .navbar-right{display:flex;align-items:center;gap:14px;flex-shrink:0;}
    .role-chip{
      font-size:10px;font-weight:700;padding:3px 10px;border-radius:4px;
      letter-spacing:1.5px;text-transform:uppercase;white-space:nowrap;
    }
    .role-admin{background:rgba(255,43,61,0.12);color:var(--accent);border:1px solid rgba(255,43,61,0.25);}
    .role-staff{background:rgba(251,191,36,0.12);color:var(--warning);border:1px solid rgba(251,191,36,0.25);}
    .user-chip{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:500;color:var(--muted);white-space:nowrap;}
    .user-chip svg{width:14px;height:14px;stroke:var(--muted);fill:none;}
    .user-chip img{width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid var(--border);flex-shrink:0;}
    .btn-logout-top{
      display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;
      background:transparent;border:1px solid var(--border);color:var(--muted);
      font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all 0.15s;white-space:nowrap;
    }
    .btn-logout-top:hover{border-color:var(--danger);color:var(--danger);}
    .btn-logout-top svg{width:13px;height:13px;stroke:currentColor;fill:none;}

    /* ── MOBILE NAV TOGGLE ── */
    .nav-toggle{
      display:none;background:transparent;border:1px solid var(--border);
      border-radius:8px;width:38px;height:38px;align-items:center;justify-content:center;
      cursor:pointer;flex-shrink:0;color:var(--text);
    }
    .nav-toggle svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;}
    .nav-toggle .icon-close{display:none;}
    .nav-toggle.open .icon-menu{display:none;}
    .nav-toggle.open .icon-close{display:block;}

    .nav-scrim{
      display:none;position:fixed;inset:0;top:var(--navbar-h);
      background:rgba(0,0,0,0.6);z-index:150;
    }
    .nav-scrim.show{display:block;}

    /* ── TOPBAR (page title + actions) ── */
    .page-topbar{
      background:var(--surface);border-bottom:1px solid var(--border);
      padding:0 36px;min-height:var(--topbar-h);display:flex;align-items:center;
      justify-content:space-between;flex-wrap:wrap;gap:10px;
    }
    .page-title{font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:2px;}
    #topbar-actions-slot{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}

    /* ── CONTENT ── */
    .page-content{max-width:1300px;margin:0 auto;padding:28px 36px;}

    /* ── BUTTONS ── */
    .btn{padding:8px 18px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
    .btn-primary{background:var(--accent);color:#111;}
    .btn-primary:hover{background:#e0141f;transform:translateY(-1px);}
    .btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
    .btn-secondary:hover{background:var(--surface3);}
    .btn-danger-soft{background:rgba(248,113,113,0.1);color:var(--danger);border:1px solid rgba(248,113,113,0.2);}
    .btn-danger-soft:hover{background:rgba(248,113,113,0.2);}
    .btn-sm{padding:5px 12px;font-size:12px;}

    /* ── STAT CARDS ── */
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:28px;}
    .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px 24px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:12px;min-width:0;}
    .stat-card::before{content:'';position:absolute;top:0;left:0;width:3px;height:100%;background:var(--accent);}
    .stat-card.orange::before{background:var(--accent2);}
    .stat-card.blue::before{background:var(--info);}
    .stat-card.green::before{background:var(--success);}
    .stat-card.yellow::before{background:var(--warning);}
    .stat-card.red::before{background:var(--danger);}
    .stat-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;}
    .stat-value{font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;word-break:break-word;}
    .stat-sub{font-size:12px;color:var(--muted);}
    .stat-up{color:var(--success);}

    /* ── CARD / TABLE ── */
    .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
    .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap;}
    .section-title{font-size:17px;font-weight:700;}
    .table-scroll{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}
    table{width:100%;border-collapse:collapse;min-width:640px;}
    thead{background:var(--surface2);}
    th{padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;white-space:nowrap;}
    td{padding:14px 18px;font-size:14px;border-top:1px solid var(--border);vertical-align:middle;}
    tr:hover td{background:rgba(255,255,255,0.012);}

    /* ── BADGES ── */
    .badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:6px;font-size:11px;font-weight:700;white-space:nowrap;}
    .badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;}
    .badge-active   {background:rgba(74,222,128,0.15);color:var(--success);}
    .badge-expired  {background:rgba(248,113,113,0.15);color:var(--danger);}
    .badge-pending  {background:rgba(251,191,36,0.15);color:var(--warning);}
    .badge-paid     {background:rgba(74,222,128,0.15);color:var(--success);}
    .badge-monthly  {background:rgba(96,165,250,0.15);color:var(--info);}
    .badge-quarterly{background:rgba(34,211,238,0.15);color:#22d3ee;}
    .badge-annually,.badge-annual,.badge-yearly{background:rgba(74,222,128,0.15);color:var(--success);}

    /* ── FORMS ── */
    .form-label{display:block;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
    .form-control{width:100%;padding:11px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:16px;outline:none;transition:border-color 0.15s;}
    .form-control:focus{border-color:var(--accent);}
    .form-control option{background:var(--surface2);}
    textarea.form-control{resize:vertical;min-height:90px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:20px;}
    .form-card-title{font-size:14px;font-weight:600;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);}
    .form-group{margin-bottom:16px;}
    .form-page{max-width:640px;}

    /* ── ALERTS ── */
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;margin-bottom:20px;}
    .alert-success{background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.2);color:var(--success);}
    .alert-danger{background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:var(--danger);}

    /* ── PLANS ── */
    .plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;}
    .plan-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:24px;position:relative;}
    .plan-card.featured{border-color:var(--accent);}
    .plan-badge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:var(--accent);color:#111;font-size:10px;font-weight:700;padding:3px 10px;border-radius:5px;letter-spacing:1px;white-space:nowrap;}
    .plan-name{font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:1px;margin-bottom:6px;}
    .plan-price{font-size:30px;font-weight:700;}
    .plan-period{font-size:12px;color:var(--muted);}
    .plan-features{margin-top:14px;list-style:none;}
    .plan-features li{font-size:13px;color:var(--muted);padding:5px 0;display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--border);}
    .plan-features li:last-child{border-bottom:none;}
    .plan-features li::before{content:'✓';color:var(--success);font-weight:700;font-size:12px;}

    /* ── MISC ── */
    .access-denied{text-align:center;padding:80px 20px;color:var(--muted);}
    .access-denied h2{font-size:22px;font-weight:600;color:var(--text);margin-bottom:8px;}
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;}

    /* ═══════════════════════════════════════════
       RESPONSIVE — TABLET (≤ 1024px)
       ═══════════════════════════════════════════ */
    @media (max-width:1024px){
      .navbar{padding:0 20px;}
      .page-topbar{padding:0 20px;}
      .page-content{padding:24px 20px;}
      .plan-grid{grid-template-columns:repeat(2,1fr);}
      .two-col{grid-template-columns:1fr;}
      .user-chip span.user-name-text{display:none;}
    }

    /* ═══════════════════════════════════════════
       RESPONSIVE — MOBILE (≤ 768px)
       ═══════════════════════════════════════════ */
    @media (max-width:768px){
      .navbar{padding:0 14px;gap:8px;}
      .brand-name{font-size:16px;letter-spacing:2px;}
      .brand-icon{width:28px;height:28px;}

      .nav-toggle{display:flex;order:3;}

      .navbar-nav{
        position:fixed;top:var(--navbar-h);left:0;right:0;
        background:var(--surface);border-bottom:1px solid var(--border);
        flex-direction:column;align-items:stretch;gap:0;
        max-height:0;overflow:hidden;z-index:150;
        transition:max-height 0.25s ease;
      }
      .navbar-nav.open{max-height:calc(100vh - var(--navbar-h));overflow-y:auto;}
      .nav-item{padding:14px 20px;border-bottom:1px solid var(--border);border-left:3px solid transparent;width:100%;}
      .nav-item.active{border-bottom-color:var(--border);border-left-color:var(--accent);background:rgba(255,43,61,0.06);}

      .navbar-right{gap:8px;}
      .role-chip{display:none;}
      .user-chip{gap:0;}
      .user-chip svg,.user-chip img{width:26px;height:26px;}
      .btn-logout-top span.logout-text{display:none;}
      .btn-logout-top{padding:8px;}

      .page-topbar{padding:12px 14px;}
      .page-title{font-size:19px;}
      #topbar-actions-slot{width:100%;}
      #topbar-actions-slot .btn{flex:1;justify-content:center;}

      .page-content{padding:18px 14px;}

      .stat-grid{grid-template-columns:1fr 1fr;gap:10px;}
      .stat-card{padding:16px;flex-direction:column;align-items:flex-start;gap:6px;}
      .stat-value{font-size:26px;}

      .plan-grid{grid-template-columns:1fr;}
      .form-row{grid-template-columns:1fr;gap:0;}
      .form-card{padding:18px;}

      .section-header{flex-direction:column;align-items:flex-start;}
      .section-header .btn{width:100%;justify-content:center;}
    }

    @media (max-width:480px){
      .stat-grid{grid-template-columns:1fr;}
      .brand-name{display:none;}
    }
  </style>
</head>
<body>

@php $activeNav = View::getSection('active_nav') ?? ''; @endphp

{{-- ── MAIN NAVBAR ── --}}
<nav class="navbar">

  {{-- Brand --}}
  <a href="{{ route('dashboard') }}" class="navbar-brand">
    <div class="brand-icon">
      <svg viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
    </div>
    <span class="brand-name">APEX FITNESS GYM</span>
  </a>

  {{-- Mobile nav toggle --}}
  <button type="button" class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
    <svg class="icon-menu" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    <svg class="icon-close" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>

  {{-- Nav links — role-aware --}}
  <div class="navbar-nav" id="navbarNav">

    @if(auth()->user()->isStaff())
      {{-- ── STAFF NAV ── --}}
      <a href="{{ route('staff.dashboard') }}"
         class="nav-item {{ $activeNav === 'staff.dashboard' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1"/>
          <rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/>
          <rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Dashboard
      </a>
      <a href="{{ route('members.index') }}"
         class="nav-item {{ $activeNav === 'members' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Members
      </a>
      {{-- Attendance for Staff --}}
      <a href="{{ route('attendance.scan') }}"
         class="nav-item {{ $activeNav === 'attendance' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1"/>
          <rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M14 17h3m3 0h-3m0 0v-3m0 3v3"/>
        </svg>
        Attendance
      </a>
      <a href="{{ route('staff.payments') }}"
         class="nav-item {{ $activeNav === 'staff.payments' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <rect x="1" y="4" width="22" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        Payments
      </a>
      <a href="{{ route('staff.profile') }}"
         class="nav-item {{ $activeNav === 'staff.profile' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        My Profile
      </a>

    @else
      {{-- ── ADMIN NAV ── --}}
      <a href="{{ route('dashboard') }}"
         class="nav-item {{ $activeNav === 'dashboard' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1"/>
          <rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/>
          <rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Dashboard
      </a>
      <a href="{{ route('members.index') }}"
         class="nav-item {{ $activeNav === 'members' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Members
      </a>
      {{-- Attendance for Admin --}}
      <a href="{{ route('attendance.scan') }}"
         class="nav-item {{ $activeNav === 'attendance' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1"/>
          <rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M14 17h3m3 0h-3m0 0v-3m0 3v3"/>
        </svg>
        Attendance
      </a>
      <a href="{{ route('payments.index') }}"
         class="nav-item {{ $activeNav === 'payments' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <rect x="1" y="4" width="22" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
          <line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        Payments
      </a>
      <a href="{{ route('users.index') }}"
         class="nav-item {{ $activeNav === 'users' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Manage Users
      </a>
      {{-- Integrated My Profile for Admin --}}
      <a href="{{ route('admin.profile') }}"
         class="nav-item {{ $activeNav === 'admin.profile' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        My Profile
      </a>
    @endif

  </div>

  {{-- Right side -- role chip + user + logout --}}
  <div class="navbar-right">
    <span class="role-chip {{ auth()->user()->isAdmin() ? 'role-admin' : 'role-staff' }}">
      {{ strtoupper(auth()->user()->role) }}
    </span>
    <div class="user-chip">
      @if(auth()->user()->photo)
        <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt="{{ auth()->user()->name }}"/>
      @else
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      @endif
      <span class="user-name-text">{{ auth()->user()->name }}</span>
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout-top">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span class="logout-text">Logout</span>
      </button>
    </form>
  </div>

</nav>

{{-- Dark backdrop shown behind the mobile nav drawer --}}
<div class="nav-scrim" id="navScrim"></div>

{{-- ── PAGE TOPBAR (title + actions) ── --}}
<div class="page-topbar">
  <div class="page-title">@yield('page_title', 'Dashboard')</div>
  <div id="topbar-actions-slot">
    @yield('topbar_actions')
  </div>
</div>

{{-- ── CONTENT ── --}}
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
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('navbarNav');
    var scrim = document.getElementById('navScrim');
    if(!toggle || !nav) return;

    function closeNav(){
      nav.classList.remove('open');
      toggle.classList.remove('open');
      toggle.setAttribute('aria-expanded','false');
      scrim.classList.remove('show');
    }
    function toggleNav(){
      var isOpen = nav.classList.toggle('open');
      toggle.classList.toggle('open', isOpen);
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      scrim.classList.toggle('show', isOpen);
    }

    toggle.addEventListener('click', toggleNav);
    scrim.addEventListener('click', closeNav);
    // Close the drawer after tapping a nav link
    nav.querySelectorAll('a').forEach(function(link){
      link.addEventListener('click', closeNav);
    });
    // Reset state if the viewport is resized back to desktop width
    window.addEventListener('resize', function(){
      if(window.innerWidth > 768) closeNav();
    });
  })();
</script>

</body>
</html>