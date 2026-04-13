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

    /* Brand — icon + name side by side */
    .navbar-brand{
      display:flex;align-items:center;gap:10px;text-decoration:none;
    }
    .brand-icon{
      width:32px;height:32px;background:var(--accent);border-radius:8px;
      display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .brand-icon svg{width:18px;height:18px;fill:none;stroke:#111;stroke-width:2.5;}
    .brand-name{
      font-family:'Bebas Neue',sans-serif;font-size:20px;color:var(--accent);
      letter-spacing:3px;line-height:1;
    }

    /* Nav links */
    .navbar-nav{display:flex;align-items:center;gap:2px;}
    .nav-item{
      display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:8px;
      color:var(--muted);font-size:13px;font-weight:500;text-decoration:none;transition:all 0.15s;
    }
    .nav-item:hover{color:var(--text);background:var(--surface2);}
    .nav-item.active{
      color:var(--accent);
      background:transparent;
      border-bottom:2px solid var(--accent);
      border-radius:0;
      padding-bottom:6px;
    }
    .nav-item svg{width:15px;height:15px;stroke:var(--muted);fill:none;transition:stroke 0.15s;flex-shrink:0;}
    .nav-item:hover svg{stroke:var(--text);}
    .nav-item.active svg{stroke:var(--accent);}

    /* Right side */
    .navbar-right{display:flex;align-items:center;gap:16px;}
    .user-chip{
      display:flex;align-items:center;gap:8px;
      font-size:13px;font-weight:500;color:var(--muted);
    }
    .user-avatar-sm{
      width:28px;height:28px;border-radius:50%;
      background:rgba(255,107,53,0.12);border:1px solid rgba(255,107,53,0.25);
      display:flex;align-items:center;justify-content:center;
      font-size:11px;font-weight:700;color:#ff6b35;flex-shrink:0;
    }
    .btn-logout-top{
      display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;
      background:transparent;border:1px solid var(--border);color:var(--muted);
      font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all 0.15s;
    }
    .btn-logout-top:hover{border-color:var(--danger);color:var(--danger);}
    .btn-logout-top svg{width:13px;height:13px;stroke:currentColor;fill:none;}

    /* ── PAGE ── */
    .page-content{max-width:1200px;margin:0 auto;padding:36px 36px;}

    /* ── BUTTONS ── */
    .btn{padding:8px 18px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
    .btn-primary{background:var(--accent);color:#111;}
    .btn-primary:hover{background:#b8ef00;transform:translateY(-1px);}
    .btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border);}
    .btn-secondary:hover{background:var(--surface3);}
    .btn-sm{padding:5px 12px;font-size:12px;}

    /* ── FORMS ── */
    .form-label{display:block;font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
    .form-control{width:100%;padding:11px 14px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
    .form-control:focus{border-color:var(--accent);}
    .form-control option{background:var(--surface2);}
    textarea.form-control{resize:vertical;min-height:90px;}

    /* ── ALERTS ── */
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;margin-bottom:20px;}
    .alert-success{background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.2);color:var(--success);}
    .alert-danger{background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:var(--danger);}

    /* ── STAT CARDS ── */
    .stat-grid{display:grid;gap:14px;margin-bottom:28px;}
    .stat-card{
      background:var(--surface);border:1px solid var(--border);border-radius:14px;
      padding:22px 24px;position:relative;overflow:hidden;
      display:flex;align-items:center;justify-content:space-between;
    }
    .stat-card-left{}
    .stat-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:8px;}
    .stat-value{font-size:38px;font-weight:800;line-height:1;margin-bottom:4px;}
    .stat-sub{font-size:12px;color:var(--muted);}
    .stat-icon{
      width:44px;height:44px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .stat-icon svg{width:22px;height:22px;fill:none;}
    .icon-green {background:rgba(200,255,0,0.08);}
    .icon-green svg{stroke:var(--accent);}
    .icon-orange{background:rgba(74,222,128,0.1);}
    .icon-orange svg{stroke:var(--success);}
    .icon-yellow{background:rgba(251,191,36,0.1);}
    .icon-yellow svg{stroke:var(--warning);}

    /* ── SPLIT PANEL (dashboard) ── */
    .split-panel{display:grid;grid-template-columns:360px 1fr;gap:16px;align-items:start;}
    .members-panel{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
    .members-panel-header{padding:18px 20px 14px;border-bottom:1px solid var(--border);}
    .members-panel-title{font-size:15px;font-weight:700;margin-bottom:12px;}
    .members-search{position:relative;}
    .members-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:var(--muted);fill:none;}
    .members-search input{width:100%;padding:9px 12px 9px 34px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color 0.15s;}
    .members-search input:focus{border-color:var(--accent);}
    .members-list{padding:8px;max-height:520px;overflow-y:auto;}
    .member-item{display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:8px;cursor:pointer;transition:all 0.15s;border:1px solid transparent;margin-bottom:4px;}
    .member-item:hover{background:var(--surface2);border-color:var(--border);}
    .member-item.active-item{background:rgba(200,255,0,0.05);border-color:rgba(200,255,0,0.18);}
    .member-item-info{display:flex;flex-direction:column;}
    .member-item-name{font-size:14px;font-weight:600;}
    .member-item-email{font-size:12px;color:var(--muted);margin-top:1px;}
    .status-pill{font-size:11px;font-weight:700;padding:3px 10px;border-radius:100px;white-space:nowrap;}
    .pill-active  {background:rgba(74,222,128,0.15);color:var(--success);}
    .pill-expiring{background:rgba(251,191,36,0.15);color:var(--warning);}
    .pill-expired {background:rgba(248,113,113,0.15);color:var(--danger);}

    /* Details panel */
    .details-panel{background:var(--surface);border:1px solid var(--border);border-radius:14px;min-height:480px;display:flex;flex-direction:column;}
    .details-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);padding:60px 20px;}
    .details-empty svg{width:44px;height:44px;stroke:var(--muted);margin-bottom:12px;opacity:0.3;}
    .details-content{display:none;padding:26px;flex:1;}
    .details-content.visible{display:block;}
    .details-name{font-size:22px;font-weight:700;margin-bottom:6px;}
    .details-section-title{font-size:10px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin:18px 0 10px;font-weight:700;}
    .details-row{display:flex;align-items:center;gap:10px;font-size:13px;margin-bottom:8px;}
    .details-row svg{width:14px;height:14px;stroke:var(--muted);flex-shrink:0;fill:none;}
    .view-full-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--accent);color:#111;font-size:13px;font-weight:700;border-radius:8px;text-decoration:none;margin-top:20px;transition:all 0.15s;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;}
    .view-full-btn:hover{background:#b8ef00;}
    .view-full-btn svg{width:14px;height:14px;stroke:#111;fill:none;}

    /* ── TABLE ── */
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
  </style>
</head>
<body>

@php $active = View::getSection('active') ?? ''; @endphp

{{-- NAVBAR --}}
<nav class="navbar">

  {{-- Brand: icon + name --}}
  <a href="{{ route('instructor.dashboard') }}" class="navbar-brand">
    <div class="brand-icon">
      <svg viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
    </div>
    <span class="brand-name">IRONFORGE</span>
  </a>

  {{-- Nav links --}}
  <div class="navbar-nav">
    <a href="{{ route('instructor.dashboard') }}"
       class="nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      Dashboard
    </a>

    {{-- Added: Workout Scheduler Link --}}
    <a href="{{ route('workout.index') }}"
       class="nav-item {{ $active === 'workout' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      Workout Scheduler
    </a>

    <a href="{{ route('instructor.profile') }}"
       class="nav-item {{ $active === 'profile' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Profile
    </a>
    <a href="{{ route('instructor.payments') }}"
       class="nav-item {{ $active === 'payments' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="1" y="4" width="22" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="1" y1="10" x2="23" y2="10" stroke-linecap="round"/>
      </svg>
      Payments
    </a>
  </div>

  {{-- Right: user + logout --}}
  <div class="navbar-right">
    <div class="user-chip">
      <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
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

{{-- CONTENT --}}
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