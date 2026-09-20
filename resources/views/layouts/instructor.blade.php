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
      --bg:#0a0a0a;
      --surface:#111111;
      --surface2:#1a1a1a;
      --surface3:#222222;
      --border:#2a2a2a;
      --accent:#ff2222;
      --accent-hover:#cc0000;
      --accent-glow:rgba(255,0,0,0.15);
      --text:#f0f0f0;
      --muted:#888888;
      --success:#ff4444;
      --danger:#ff0000;
      --warning:#ff6b35;
      --info:#60a5fa;
      --radius:10px;
      --navbar-h:60px;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    html{-webkit-text-size-adjust:100%;}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-size:15px;min-height:100vh;overflow-x:hidden;}
    img,svg{max-width:100%;}

    /* ── NAVBAR ── */
    .navbar{
      background:var(--surface);
      border-bottom:2px solid var(--accent);
      padding:0 36px;
      height:var(--navbar-h);
      display:flex;
      align-items:center;
      justify-content:space-between;
      position:sticky;
      top:0;
      z-index:200;
      gap:12px;
      box-shadow:0 2px 20px rgba(255,0,0,0.1);
    }
    .navbar-brand{
      display:flex;
      align-items:center;
      gap:10px;
      text-decoration:none;
      flex-shrink:0;
    }
    .brand-icon{
      width:32px;
      height:32px;
      background:var(--accent);
      border-radius:8px;
      display:flex;
      align-items:center;
      justify-content:center;
      flex-shrink:0;
      box-shadow:0 0 20px rgba(255,0,0,0.3);
    }
    .brand-icon svg{
      width:18px;
      height:18px;
      fill:none;
      stroke:#000;
      stroke-width:2.5;
    }
    .brand-name{
      font-family:'Bebas Neue',sans-serif;
      font-size:20px;
      color:var(--accent);
      letter-spacing:3px;
      line-height:1;
      white-space:nowrap;
    }
    .navbar-nav{
      display:flex;
      align-items:center;
      gap:2px;
    }
    .nav-item{
      display:flex;
      align-items:center;
      gap:7px;
      padding:8px 16px;
      border-radius:8px;
      color:var(--muted);
      font-size:13px;
      font-weight:500;
      text-decoration:none;
      transition:all 0.2s ease;
      white-space:nowrap;
      position:relative;
    }
    .nav-item:hover{
      color:var(--text);
      background:var(--surface2);
    }
    .nav-item.active{
      color:var(--accent);
      background:rgba(255,0,0,0.08);
      font-weight:600;
    }
    .nav-item.active::after{
      content:'';
      position:absolute;
      bottom:-11px;
      left:16px;
      right:16px;
      height:2px;
      background:var(--accent);
      border-radius:2px;
      box-shadow:0 0 10px rgba(255,0,0,0.5);
    }
    .nav-item svg{
      width:15px;
      height:15px;
      stroke:var(--muted);
      fill:none;
      transition:stroke 0.2s;
      flex-shrink:0;
    }
    .nav-item:hover svg{
      stroke:var(--text);
    }
    .nav-item.active svg{
      stroke:var(--accent);
    }

    .nav-badge{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      min-width:17px;
      height:17px;
      padding:0 4px;
      border-radius:20px;
      background:var(--accent);
      color:#000;
      font-size:10px;
      font-weight:800;
      line-height:1;
      box-shadow:0 0 10px rgba(255,0,0,0.3);
    }

    .navbar-right{
      display:flex;
      align-items:center;
      gap:16px;
      flex-shrink:0;
    }
    .user-chip{
      display:flex;
      align-items:center;
      gap:8px;
      font-size:13px;
      font-weight:500;
      color:var(--text);
      white-space:nowrap;
    }
    .user-chip svg{
      stroke:var(--accent);
    }
    .user-avatar{
      width:32px;
      height:32px;
      border-radius:50%;
      background:rgba(255,0,0,0.15);
      border:2px solid var(--accent);
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:12px;
      font-weight:700;
      color:var(--accent);
      flex-shrink:0;
      overflow:hidden;
    }
    .user-avatar img{
      width:100%;
      height:100%;
      object-fit:cover;
    }
    .btn-logout-top{
      display:flex;
      align-items:center;
      gap:6px;
      padding:7px 14px;
      border-radius:8px;
      background:transparent;
      border:1px solid var(--border);
      color:var(--muted);
      font-size:12px;
      cursor:pointer;
      font-family:'DM Sans',sans-serif;
      transition:all 0.2s ease;
      white-space:nowrap;
    }
    .btn-logout-top:hover{
      border-color:var(--accent);
      color:var(--accent);
      background:rgba(255,0,0,0.05);
    }
    .btn-logout-top svg{
      width:13px;
      height:13px;
      stroke:currentColor;
      fill:none;
    }

    /* ── MOBILE NAV TOGGLE ── */
    .nav-toggle{
      display:none;
      background:transparent;
      border:1px solid var(--border);
      border-radius:8px;
      width:38px;
      height:38px;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      flex-shrink:0;
      color:var(--text);
      transition:border-color 0.2s ease;
    }
    .nav-toggle:hover{
      border-color:var(--accent);
    }
    .nav-toggle svg{
      width:20px;
      height:20px;
      stroke:currentColor;
      fill:none;
      stroke-width:2;
    }
    .nav-toggle .icon-close{
      display:none;
    }
    .nav-toggle.open .icon-menu{
      display:none;
    }
    .nav-toggle.open .icon-close{
      display:block;
    }
    .nav-scrim{
      display:none;
      position:fixed;
      inset:0;
      top:var(--navbar-h);
      background:rgba(0,0,0,0.7);
      backdrop-filter:blur(4px);
      z-index:150;
    }
    .nav-scrim.show{
      display:block;
    }

    /* ── PAGE ── */
    .page-content{
      max-width:1200px;
      margin:0 auto;
      padding:36px 36px;
    }

    /* ── BUTTONS ── */
    .btn{
      padding:8px 18px;
      border-radius:var(--radius);
      font-family:'DM Sans',sans-serif;
      font-size:13px;
      font-weight:600;
      cursor:pointer;
      border:none;
      display:inline-flex;
      align-items:center;
      gap:6px;
      text-decoration:none;
      transition:all 0.2s ease;
    }
    .btn-primary{
      background:var(--accent);
      color:#000;
      box-shadow:0 4px 15px rgba(255,0,0,0.3);
    }
    .btn-primary:hover{
      background:var(--accent-hover);
      transform:translateY(-1px);
      box-shadow:0 6px 25px rgba(255,0,0,0.4);
    }
    .btn-secondary{
      background:var(--surface2);
      color:var(--text);
      border:1px solid var(--border);
    }
    .btn-secondary:hover{
      border-color:var(--accent);
      color:var(--accent);
      background:rgba(255,0,0,0.05);
    }
    .btn-sm{
      padding:5px 12px;
      font-size:12px;
    }

    /* ── FORMS ── */
    .form-label{
      display:block;
      font-size:11px;
      font-weight:600;
      color:var(--muted);
      text-transform:uppercase;
      letter-spacing:1px;
      margin-bottom:7px;
    }
    .form-control{
      width:100%;
      padding:11px 14px;
      background:var(--surface2);
      border:1px solid var(--border);
      border-radius:10px;
      color:var(--text);
      font-family:'DM Sans',sans-serif;
      font-size:16px;
      outline:none;
      transition:border-color 0.2s ease;
    }
    .form-control:focus{
      border-color:var(--accent);
      box-shadow:0 0 20px rgba(255,0,0,0.1);
    }
    .form-control option{
      background:var(--surface2);
    }
    textarea.form-control{
      resize:vertical;
      min-height:90px;
    }

    /* ── ALERTS ── */
    .alert{
      padding:12px 16px;
      border-radius:var(--radius);
      font-size:13px;
      margin-bottom:20px;
    }
    .alert-success{
      background:rgba(255,68,68,0.08);
      border:1px solid rgba(255,68,68,0.2);
      color:var(--success);
    }
    .alert-danger{
      background:rgba(255,0,0,0.08);
      border:1px solid rgba(255,0,0,0.2);
      color:var(--danger);
    }

    /* ── STAT CARDS ── */
    .stat-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
      gap:14px;
      margin-bottom:28px;
    }
    .stat-card{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:14px;
      padding:22px 24px;
      position:relative;
      overflow:hidden;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      min-width:0;
      transition:all 0.3s ease;
    }
    .stat-card::before{
      content:'';
      position:absolute;
      top:0;
      left:0;
      right:0;
      height:3px;
      background:var(--accent);
      transform:scaleX(0);
      transition:transform 0.3s ease;
    }
    .stat-card:hover::before{
      transform:scaleX(1);
    }
    .stat-card:hover{
      transform:translateY(-2px);
      border-color:var(--accent);
      box-shadow:0 8px 30px rgba(255,0,0,0.12);
    }
    .stat-label{
      font-size:11px;
      color:var(--muted);
      text-transform:uppercase;
      letter-spacing:1.5px;
      margin-bottom:8px;
    }
    .stat-value{
      font-size:38px;
      font-weight:800;
      line-height:1;
      margin-bottom:4px;
      word-break:break-word;
      color:var(--text);
    }
    .stat-sub{
      font-size:12px;
      color:var(--muted);
    }
    .stat-icon{
      width:44px;
      height:44px;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      flex-shrink:0;
    }
    .stat-icon svg{
      width:22px;
      height:22px;
      fill:none;
    }
    .icon-green{
      background:rgba(255,0,0,0.12);
    }
    .icon-green svg{
      stroke:var(--accent);
    }
    .icon-orange{
      background:rgba(255,68,68,0.12);
    }
    .icon-orange svg{
      stroke:var(--success);
    }
    .icon-yellow{
      background:rgba(255,107,53,0.12);
    }
    .icon-yellow svg{
      stroke:var(--warning);
    }

    /* ── SPLIT PANEL ── */
    .split-panel{
      display:grid;
      grid-template-columns:360px 1fr;
      gap:16px;
      align-items:start;
    }
    .members-panel{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:14px;
      overflow:hidden;
      transition:border-color 0.3s ease;
    }
    .members-panel:hover{
      border-color:var(--accent);
    }
    .members-panel-header{
      padding:18px 20px 14px;
      border-bottom:2px solid var(--accent);
    }
    .members-panel-title{
      font-size:15px;
      font-weight:700;
      margin-bottom:12px;
      color:var(--accent);
    }
    .members-search{
      position:relative;
    }
    .members-search svg{
      position:absolute;
      left:12px;
      top:50%;
      transform:translateY(-50%);
      width:14px;
      height:14px;
      stroke:var(--muted);
      fill:none;
    }
    .members-search input{
      width:100%;
      padding:9px 12px 9px 34px;
      background:var(--surface2);
      border:1px solid var(--border);
      border-radius:8px;
      color:var(--text);
      font-size:16px;
      font-family:'DM Sans',sans-serif;
      outline:none;
      transition:border-color 0.2s ease;
    }
    .members-search input:focus{
      border-color:var(--accent);
      box-shadow:0 0 20px rgba(255,0,0,0.1);
    }
    .members-list{
      padding:8px;
      max-height:520px;
      overflow-y:auto;
    }
    .members-list::-webkit-scrollbar{
      width:6px;
    }
    .members-list::-webkit-scrollbar-track{
      background:var(--surface);
    }
    .members-list::-webkit-scrollbar-thumb{
      background:var(--accent);
      border-radius:3px;
    }
    .member-item{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:12px;
      border-radius:8px;
      cursor:pointer;
      transition:all 0.2s ease;
      border:1px solid transparent;
      margin-bottom:4px;
      gap:8px;
    }
    .member-item:hover{
      background:var(--surface2);
      border-color:var(--border);
    }
    .member-item.active-item{
      background:rgba(255,0,0,0.06);
      border-color:rgba(255,0,0,0.2);
      border-left:3px solid var(--accent);
    }
    .member-item-info{
      display:flex;
      flex-direction:column;
      min-width:0;
    }
    .member-item-name{
      font-size:14px;
      font-weight:600;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
      color:var(--text);
    }
    .member-item-email{
      font-size:12px;
      color:var(--muted);
      margin-top:1px;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
    }
    .status-pill{
      font-size:11px;
      font-weight:700;
      padding:3px 10px;
      border-radius:100px;
      white-space:nowrap;
      flex-shrink:0;
    }
    .pill-active{
      background:rgba(255,68,68,0.15);
      color:var(--success);
      border:1px solid rgba(255,68,68,0.2);
    }
    .pill-expiring{
      background:rgba(255,107,53,0.15);
      color:var(--warning);
      border:1px solid rgba(255,107,53,0.2);
    }
    .pill-expired{
      background:rgba(255,0,0,0.15);
      color:var(--danger);
      border:1px solid rgba(255,0,0,0.2);
    }

    /* Details panel */
    .details-panel{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:14px;
      min-height:480px;
      display:flex;
      flex-direction:column;
      transition:border-color 0.3s ease;
    }
    .details-panel:hover{
      border-color:var(--accent);
    }
    .details-empty{
      flex:1;
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      color:var(--muted);
      padding:60px 20px;
    }
    .details-empty svg{
      width:44px;
      height:44px;
      stroke:var(--border);
      margin-bottom:12px;
      opacity:0.3;
    }
    .details-content{
      display:none;
      padding:26px;
      flex:1;
    }
    .details-content.visible{
      display:block;
    }
    .details-name{
      font-size:22px;
      font-weight:700;
      margin-bottom:6px;
      word-break:break-word;
      color:var(--text);
    }
    .details-section-title{
      font-size:10px;
      color:var(--accent);
      letter-spacing:2px;
      text-transform:uppercase;
      margin:18px 0 10px;
      font-weight:700;
    }
    .details-row{
      display:flex;
      align-items:center;
      gap:10px;
      font-size:13px;
      margin-bottom:8px;
      color:var(--text);
    }
    .details-row svg{
      width:14px;
      height:14px;
      stroke:var(--accent);
      flex-shrink:0;
      fill:none;
    }
    .view-full-btn{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:9px 18px;
      background:var(--accent);
      color:#000;
      font-size:13px;
      font-weight:700;
      border-radius:8px;
      text-decoration:none;
      margin-top:20px;
      transition:all 0.2s ease;
      border:none;
      cursor:pointer;
      font-family:'DM Sans',sans-serif;
      box-shadow:0 4px 15px rgba(255,0,0,0.3);
    }
    .view-full-btn:hover{
      background:var(--accent-hover);
      transform:translateY(-1px);
      box-shadow:0 6px 25px rgba(255,0,0,0.4);
    }
    .view-full-btn svg{
      width:14px;
      height:14px;
      stroke:#000;
      fill:none;
    }

    /* ── TABLE ── */
    .card{
      background:var(--surface);
      border:1px solid var(--border);
      border-radius:14px;
      overflow:hidden;
      transition:border-color 0.3s ease;
    }
    .card:hover{
      border-color:var(--accent);
    }
    .section-header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:16px;
      gap:10px;
      flex-wrap:wrap;
    }
    .section-title{
      font-size:17px;
      font-weight:700;
      color:var(--accent);
    }
    .table-scroll{
      width:100%;
      overflow-x:auto;
      -webkit-overflow-scrolling:touch;
    }
    table{
      width:100%;
      border-collapse:collapse;
      min-width:640px;
    }
    thead{
      background:var(--surface2);
      border-bottom:2px solid var(--accent);
    }
    th{
      padding:12px 18px;
      text-align:left;
      font-size:10px;
      font-weight:700;
      color:var(--accent);
      text-transform:uppercase;
      letter-spacing:2px;
      white-space:nowrap;
    }
    td{
      padding:14px 18px;
      font-size:14px;
      border-top:1px solid var(--border);
      vertical-align:middle;
      color:var(--text);
    }
    tr:hover td{
      background:rgba(255,0,0,0.02);
    }

    /* ── BADGES ── */
    .badge{
      display:inline-flex;
      align-items:center;
      gap:5px;
      padding:4px 12px;
      border-radius:6px;
      font-size:11px;
      font-weight:700;
      white-space:nowrap;
    }
    .badge::before{
      content:'';
      width:6px;
      height:6px;
      border-radius:50%;
      background:currentColor;
      flex-shrink:0;
    }
    .badge-active{
      background:rgba(255,68,68,0.15);
      color:var(--success);
      border:1px solid rgba(255,68,68,0.15);
    }
    .badge-expired{
      background:rgba(255,0,0,0.15);
      color:var(--danger);
      border:1px solid rgba(255,0,0,0.15);
    }
    .badge-pending{
      background:rgba(255,107,53,0.15);
      color:var(--warning);
      border:1px solid rgba(255,107,53,0.15);
    }
    .badge-paid{
      background:rgba(255,68,68,0.15);
      color:var(--success);
      border:1px solid rgba(255,68,68,0.15);
    }
    .badge-monthly{
      background:rgba(255,68,68,0.12);
      color:var(--accent);
      border:1px solid rgba(255,68,68,0.15);
    }
    .badge-quarterly{
      background:rgba(255,107,53,0.12);
      color:var(--warning);
      border:1px solid rgba(255,107,53,0.15);
    }
    .badge-annually,.badge-annual,.badge-yearly{
      background:rgba(255,68,68,0.12);
      color:var(--success);
      border:1px solid rgba(255,68,68,0.15);
    }

    /* ═══════════════════════════════════════════
       RESPONSIVE
       ═══════════════════════════════════════════ */
    @media (max-width:1024px){
      .navbar{padding:0 20px;}
      .page-content{padding:28px 20px;}
      .split-panel{grid-template-columns:300px 1fr;}
    }

    @media (max-width:900px){
      .split-panel{grid-template-columns:1fr;}
      .members-list{max-height:320px;}
      .details-panel{min-height:0;}
    }

    @media (max-width:768px){
      .navbar{padding:0 14px;gap:8px;border-bottom-width:2px;}
      .brand-name{font-size:16px;letter-spacing:2px;}
      .brand-icon{width:28px;height:28px;}
      .nav-toggle{display:flex;order:3;}

      .navbar-nav{
        position:fixed;
        top:var(--navbar-h);
        left:0;
        right:0;
        background:var(--surface);
        border-bottom:2px solid var(--accent);
        flex-direction:column;
        align-items:stretch;
        gap:0;
        max-height:0;
        overflow:hidden;
        z-index:150;
        transition:max-height 0.3s ease;
        box-shadow:0 10px 40px rgba(0,0,0,0.5);
      }
      .navbar-nav.open{
        max-height:calc(100vh - var(--navbar-h));
        overflow-y:auto;
      }
      .nav-item{
        padding:14px 20px;
        border-radius:0;
        border-bottom:1px solid var(--border);
        border-left:3px solid transparent;
        width:100%;
      }
      .nav-item.active{
        border-bottom:1px solid var(--border);
        border-left-color:var(--accent);
        background:rgba(255,0,0,0.06);
        padding-bottom:14px;
      }
      .nav-item.active::after{
        display:none;
      }

      .navbar-right{gap:8px;}
      .user-chip span.user-name-text{display:none;}
      .btn-logout-top span.logout-text{display:none;}
      .btn-logout-top{padding:8px;border-radius:50%;width:38px;height:38px;justify-content:center;}

      .page-content{padding:18px 14px;}

      .stat-grid{grid-template-columns:1fr 1fr;gap:10px;}
      .stat-card{padding:16px;}
      .stat-value{font-size:26px;}
      .stat-icon{width:36px;height:36px;}
      .stat-icon svg{width:18px;height:18px;}

      .section-header{flex-direction:column;align-items:flex-start;}
      .section-header .btn{width:100%;justify-content:center;}
    }

    @media (max-width:480px){
      .stat-grid{grid-template-columns:1fr;}
      .brand-name{display:none;}
      .details-content{padding:18px;}
      .member-item{flex-wrap:wrap;}
    }

    /* ── SCROLLBAR ── */
    ::-webkit-scrollbar{
      width:6px;
      height:6px;
    }
    ::-webkit-scrollbar-track{
      background:var(--surface);
    }
    ::-webkit-scrollbar-thumb{
      background:var(--accent);
      border-radius:3px;
    }
    ::-webkit-scrollbar-thumb:hover{
      background:var(--accent-hover);
    }
  </style>
</head>
<body>

@php
  $active = View::getSection('active') ?? '';

  $pendingRequestsCount = \App\Models\CoachRequest::where('instructor_id', auth()->id())
                            ->where('status', 'pending')
                            ->count();
@endphp

{{-- NAVBAR --}}
<nav class="navbar">

  <a href="{{ route('instructor.dashboard') }}" class="navbar-brand">
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

  <div class="navbar-nav" id="navbarNav">

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

    <a href="{{ route('instructor.requests') }}"
       class="nav-item {{ $active === 'requests' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 01-3.46 0"/>
      </svg>
      Requests
      @if($pendingRequestsCount > 0)
        <span class="nav-badge">{{ $pendingRequestsCount }}</span>
      @endif
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

  <div class="navbar-right">
    <div class="user-chip">
      <div class="user-avatar">
        @if(auth()->user()->photo)
          <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt=""/>
        @else
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        @endif
      </div>
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
    nav.querySelectorAll('a').forEach(function(link){
      link.addEventListener('click', closeNav);
    });
    window.addEventListener('resize', function(){
      if(window.innerWidth > 768) closeNav();
    });
  })();
</script>

</body>
</html>