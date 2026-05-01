<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'IRONFORGE GMS')</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
:root{
  --bg:#0a0a0a;--surface:#111111;--surface2:#181818;--surface3:#202020;
  --border:#222222;--accent:#c8ff00;--accent2:#ff6b35;--text:#f0f0f0;
  --muted:#555;--success:#4ade80;--danger:#f87171;--info:#60a5fa;--warning:#fbbf24;
  --radius:10px;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;display:flex;min-height:100vh;}

/* ── SIDEBAR ── */
.sidebar{width:220px;min-width:220px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;}

.sidebar-brand{padding:22px 20px 18px;border-bottom:1px solid var(--border);}
.brand-name{font-family:'Bebas Neue',sans-serif;font-size:26px;color:var(--accent);letter-spacing:3px;line-height:1;}
.brand-sub{font-size:9px;color:var(--muted);letter-spacing:3px;text-transform:uppercase;margin-top:3px;}
.role-badge{display:inline-block;font-size:10px;padding:3px 10px;border-radius:4px;font-weight:700;letter-spacing:1.5px;margin-top:10px;text-transform:uppercase;}
.role-admin     {background:rgba(200,255,0,0.12);color:var(--accent);border:1px solid rgba(200,255,0,0.25);}
.role-staff     {background:rgba(251,191,36,0.12);color:var(--warning);border:1px solid rgba(251,191,36,0.25);}
.role-instructor{background:rgba(255,107,53,0.12);color:var(--accent2);border:1px solid rgba(255,107,53,0.25);}
.role-member    {background:rgba(74,222,128,0.12);color:var(--success);border:1px solid rgba(74,222,128,0.25);}

.sidebar-nav{padding:10px 0;flex:1;overflow-y:auto;}
.nav-section{font-size:9px;color:var(--muted);letter-spacing:2.5px;text-transform:uppercase;padding:14px 20px 5px;}
.nav-link{display:flex;align-items:center;gap:10px;padding:10px 20px;color:#444;font-size:13px;font-weight:500;border-left:3px solid transparent;text-decoration:none;transition:all 0.15s;}
.nav-link:hover{color:var(--text);background:var(--surface2);}
.nav-link.active{color:var(--accent);background:rgba(200,255,0,0.05);border-left-color:var(--accent);}
.nav-link.active svg{stroke:var(--accent);}
.nav-link svg{width:15px;height:15px;flex-shrink:0;stroke:#444;transition:stroke 0.15s;}
.nav-link:hover svg{stroke:var(--text);}

.sidebar-footer{padding:16px 20px;border-top:1px solid var(--border);}
.user-avatar{width:34px;height:34px;border-radius:50%;background:rgba(200,255,0,0.1);border:1px solid rgba(200,255,0,0.2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:var(--accent);flex-shrink:0;}
.user-name{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.user-email{font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.btn-logout{width:100%;padding:8px;background:transparent;border:1px solid var(--border);border-radius:var(--radius);color:var(--muted);font-size:12px;cursor:pointer;margin-top:10px;font-family:'DM Sans',sans-serif;transition:all 0.15s;letter-spacing:0.5px;}
.btn-logout:hover{border-color:var(--danger);color:var(--danger);}

/* ── MAIN ── */
.main{flex:1;display:flex;flex-direction:column;min-width:0;}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 28px;height:56px;display:flex;align-items:center;justify-content:space-between;}
.page-title{font-family:'Bebas Neue',sans-serif;font-size:26px;letter-spacing:2px;color:var(--text);}
.content{flex:1;padding:28px;overflow-y:auto;}

/* ── BUTTONS ── */
.btn{padding:8px 18px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
.btn-primary{background:var(--accent);color:#111;}
.btn-primary:hover{background:#b8ef00;transform:translateY(-1px);}
.btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
.btn-secondary:hover{background:var(--surface3);}
.btn-danger{background:rgba(248,113,113,0.1);color:var(--danger);border:1px solid rgba(248,113,113,0.2);}
.btn-danger:hover{background:rgba(248,113,113,0.2);}
.btn-sm{padding:5px 12px;font-size:12px;}

/* ── STAT CARDS ── */
.stat-grid{display:grid;gap:14px;margin-bottom:28px;}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px 20px;position:relative;overflow:hidden;}
.stat-card::before{content:'';position:absolute;top:0;left:0;width:3px;height:100%;background:var(--accent);}
.stat-card.orange::before{background:var(--accent2);}
.stat-card.blue::before  {background:var(--info);}
.stat-card.green::before {background:var(--success);}
.stat-card.yellow::before{background:var(--warning);}
.stat-card.red::before   {background:var(--danger);}
.stat-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:10px;}
.stat-value{font-size:36px;font-weight:800;line-height:1;margin-bottom:6px;}
.stat-sub{font-size:12px;color:var(--muted);}
.stat-up{color:var(--success);}

/* ── TABLE ── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.section-title{font-size:17px;font-weight:700;}
table{width:100%;border-collapse:collapse;}
thead{background:var(--surface2);}
th{padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;}
td{padding:14px 18px;font-size:14px;border-top:1px solid var(--border);vertical-align:middle;}
tr:hover td{background:rgba(255,255,255,0.015);}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:6px;font-size:11px;font-weight:700;letter-spacing:0.3px;}
.badge-monthly   {background:rgba(96,165,250,0.15);color:var(--info);}
.badge-quarterly {background:rgba(34,211,238,0.15);color:#22d3ee;}
.badge-yearly,.badge-annual,.badge-annually{background:rgba(74,222,128,0.15);color:var(--success);}
.badge-active    {background:rgba(74,222,128,0.15);color:var(--success);}
.badge-expired   {background:rgba(248,113,113,0.15);color:var(--danger);}
.badge-pending   {background:rgba(251,191,36,0.15);color:var(--warning);}
.badge-paid      {background:rgba(74,222,128,0.15);color:var(--success);}
.badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;}

/* ── FORMS ── */
.form-page{max-width:640px;}
.form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:20px;}
.form-card-title{font-size:14px;font-weight:600;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);}
.form-group{margin-bottom:16px;}
.form-label{display:block;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:1px;}
.form-control{width:100%;padding:10px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
.form-control:focus{border-color:var(--accent);}
.form-control option{background:var(--surface2);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}

/* ── ALERTS ── */
.alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;margin-bottom:20px;}
.alert-success{background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.2);color:var(--success);}
.alert-danger {background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:var(--danger);}

/* ── MISC ── */
.search-bar{padding:9px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;width:220px;}
.search-bar:focus{border-color:var(--accent);}
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
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;}
.access-denied{text-align:center;padding:80px 20px;color:var(--muted);}
.access-denied h2{font-size:22px;font-weight:600;color:var(--text);margin-bottom:8px;}
</style>
</head>
<body>

{{-- SIDEBAR --}}
<div class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-name">IRONFORGE</div>
    <div class="brand-sub">Management System</div>
    @auth
      <span class="role-badge
        @if(auth()->user()->isAdmin())          role-admin
        @elseif(auth()->user()->isStaff())      role-staff
        @elseif(auth()->user()->isInstructor()) role-instructor
        @elseif(auth()->user()->isMember())     role-member
        @endif">
        {{ strtoupper(auth()->user()->role) }}
      </span>
    @endauth
  </div>

  <nav class="sidebar-nav">
    @auth

    {{-- ── INSTRUCTOR NAV ── --}}
    @if(auth()->user()->isInstructor())
      <div class="nav-section">Instructor</div>
      <a href="{{ route('instructor.dashboard') }}"
         class="nav-link {{ request()->routeIs('instructor.dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        My Members
      </a>
      <a href="{{ route('instructor.profile') }}"
         class="nav-link {{ request()->routeIs('instructor.profile') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        My Profile
      </a>

    {{-- ── ADMIN / STAFF NAV ── --}}
    @else
      <div class="nav-section">Main</div>
      <a href="{{ route('dashboard') }}"
         class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Dashboard
      </a>

      <div class="nav-section">Members</div>
      <a href="{{ route('members.index') }}"
         class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        View Members
      </a>

      <div class="nav-section">Plans</div>
      <a href="{{ route('plans') }}"
         class="nav-link {{ request()->routeIs('plans') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Membership Plans
      </a>

      @if(auth()->user()->isAdmin())
      <div class="nav-section">Finance</div>
      <a href="{{ route('payments.index') }}"
         class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        Payments
      </a>

      <div class="nav-section">Admin</div>
      <a href="{{ route('users.index') }}"
         class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Manage Users
      </a>
      @endif
    @endif

    @endauth
  </nav>

  <div class="sidebar-footer">
    @auth
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
      @if(auth()->user()->photo)
        <img src="{{ asset('storage/'.auth()->user()->photo) }}"
             style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1px solid var(--border);"/>
      @else
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name,0,2)) }}</div>
      @endif
      <div style="min-width:0;">
        <div class="user-name">{{ auth()->user()->name }}</div>
        <div class="user-email">{{ auth()->user()->email }}</div>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout">Sign Out</button>
    </form>
    @endauth
  </div>
</div>

{{-- MAIN --}}
<div class="main">
  <div class="topbar">
    <div class="page-title">@yield('page_title', 'Dashboard')</div>
    <div style="display:flex;gap:10px;align-items:center;">
      @yield('topbar_actions')
    </div>
  </div>
  <div class="content">
    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">✕ {{ session('error') }}</div>
    @endif
    @yield('content')
  </div>
</div>

</body>
</html>