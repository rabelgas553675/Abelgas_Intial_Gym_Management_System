<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title', 'IRONFORGE')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root{
      --bg:#0a0a0a;--surface:#111111;--surface2:#181818;--surface3:#202020;
      --border:#222222;--accent:#c8ff00;--text:#f0f0f0;--muted:#555;
      --success:#4ade80;--danger:#f87171;--warning:#fbbf24;--info:#60a5fa;
      --accent2:#ff6b35;--radius:10px;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;min-height:100vh;}

    /* ── NAVBAR ── */
    .navbar{
      background:var(--surface);border-bottom:1px solid var(--border);
      padding:0 36px;height:60px;display:flex;align-items:center;
      justify-content:space-between;position:sticky;top:0;z-index:100;
    }
    .navbar-brand{display:flex;align-items:center;gap:10px;text-decoration:none;}
    .brand-icon{width:32px;height:32px;background:var(--accent);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand-icon svg{width:18px;height:18px;fill:none;stroke:#111;stroke-width:2.5;}
    .brand-name{font-family:'Bebas Neue',sans-serif;font-size:20px;color:var(--accent);letter-spacing:3px;line-height:1;}

    .navbar-nav{display:flex;align-items:center;gap:2px;}
    .nav-item{
      display:flex;align-items:center;gap:7px;padding:8px 14px;
      color:var(--muted);font-size:13px;font-weight:500;text-decoration:none;
      transition:all 0.15s;border-bottom:2px solid transparent;
    }
    .nav-item:hover{color:var(--text);}
    .nav-item.active{color:var(--accent);border-bottom-color:var(--accent);}
    .nav-item svg{width:15px;height:15px;stroke:currentColor;fill:none;flex-shrink:0;}

    .navbar-right{display:flex;align-items:center;gap:14px;}
    .role-chip{
      font-size:10px;font-weight:700;padding:3px 10px;border-radius:4px;
      letter-spacing:1.5px;text-transform:uppercase;
    }
    .role-admin{background:rgba(200,255,0,0.12);color:var(--accent);border:1px solid rgba(200,255,0,0.25);}
    .role-staff{background:rgba(251,191,36,0.12);color:var(--warning);border:1px solid rgba(251,191,36,0.25);}
    .user-chip{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:500;color:var(--muted);}
    .user-chip svg{width:14px;height:14px;stroke:var(--muted);fill:none;}
    .btn-logout-top{
      display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;
      background:transparent;border:1px solid var(--border);color:var(--muted);
      font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all 0.15s;
    }
    .btn-logout-top:hover{border-color:var(--danger);color:var(--danger);}
    .btn-logout-top svg{width:13px;height:13px;stroke:currentColor;fill:none;}

    /* ── TOPBAR (page title + actions) ── */
    .page-topbar{
      background:var(--surface);border-bottom:1px solid var(--border);
      padding:0 36px;height:52px;display:flex;align-items:center;
      justify-content:space-between;
    }
    .page-title{font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:2px;}

    /* ── CONTENT ── */
    .page-content{max-width:1300px;margin:0 auto;padding:28px 36px;}

    /* ── BUTTONS ── */
    .btn{padding:8px 18px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
    .btn-primary{background:var(--accent);color:#111;}
    .btn-primary:hover{background:#b8ef00;transform:translateY(-1px);}
    .btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
    .btn-secondary:hover{background:var(--surface3);}
    .btn-danger-soft{background:rgba(248,113,113,0.1);color:var(--danger);border:1px solid rgba(248,113,113,0.2);}
    .btn-danger-soft:hover{background:rgba(248,113,113,0.2);}
    .btn-sm{padding:5px 12px;font-size:12px;}

    /* ── STAT CARDS ── */
    .stat-grid{display:grid;gap:14px;margin-bottom:28px;}
    .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px 24px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;}
    .stat-card::before{content:'';position:absolute;top:0;left:0;width:3px;height:100%;background:var(--accent);}
    .stat-card.orange::before{background:var(--accent2);}
    .stat-card.blue::before{background:var(--info);}
    .stat-card.green::before{background:var(--success);}
    .stat-card.yellow::before{background:var(--warning);}
    .stat-card.red::before{background:var(--danger);}
    .stat-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;}
    .stat-value{font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;}
    .stat-sub{font-size:12px;color:var(--muted);}
    .stat-up{color:var(--success);}

    /* ── CARD / TABLE ── */
    .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
    .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
    .section-title{font-size:17px;font-weight:700;}
    table{width:100%;border-collapse:collapse;}
    thead{background:var(--surface2);}
    th{padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;}
    td{padding:14px 18px;font-size:14px;border-top:1px solid var(--border);vertical-align:middle;}
    tr:hover td{background:rgba(255,255,255,0.012);}

    /* ── BADGES ── */
    .badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:6px;font-size:11px;font-weight:700;}
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
    .form-control{width:100%;padding:11px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
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
    <span class="brand-name">IRONFORGE</span>
  </a>

  {{-- Nav links — role-aware --}}
  <div class="navbar-nav">

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
        <img src="{{ asset('storage/'.auth()->user()->photo) }}"
             style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid var(--border);"/>
      @else
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      @endif
      {{ auth()->user()->name }}
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout-top">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Logout
      </button>
    </form>
  </div>

</nav>

{{-- ── PAGE TOPBAR (title + actions) ── --}}
<div class="page-topbar">
  <div class="page-title">@yield('page_title', 'Dashboard')</div>
  <div style="display:flex;gap:8px;align-items:center;">
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

</body>
</html>