<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>@yield('title', 'APEX FITNESS GYM – Staff')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root {
      --bg:      #0a0a0a;
      --surface: #111111;
      --surface2:#181818;
      --surface3:#202020;
      --border:  rgba(255,255,255,0.07);
      --accent:  #e63946;
      --text:    #f0f0f0;
      --muted:   #666;
      --success: #4ade80;
      --danger:  #f87171;
      --warning: #fbbf24;
      --radius:  12px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      min-height: 100vh;
      overflow-x: hidden;
    }

    body.menu-open { overflow: hidden; }

    /* ── TOP NAVBAR ── */
    .topnav {
      position: sticky;
      top: 0;
      z-index: 100;
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 36px;
      gap: 12px;
    }

    .topnav-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      min-width: 0;
    }

    .topnav-logo {
      width: 34px; height: 34px;
      background: var(--accent);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }

    .topnav-logo svg { width: 18px; height: 18px; }

    .topnav-name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 20px;
      color: var(--text);
      letter-spacing: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .topnav-links {
      display: flex;
      align-items: center;
      gap: 2px;
    }

    .nav-link {
      display: flex;
      align-items: center;
      gap: 7px;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      color: var(--muted);
      text-decoration: none;
      transition: all 0.15s;
      white-space: nowrap;
    }

    .nav-link svg {
      width: 16px; height: 16px;
      stroke: var(--muted);
      fill: none;
      flex-shrink: 0;
      transition: stroke 0.15s;
    }

    .nav-link:hover {
      color: var(--text);
      background: var(--surface2);
    }

    .nav-link:hover svg { stroke: var(--text); }

    .nav-link.active {
      color: var(--accent);
      background: rgba(230,57,70,0.1);
      font-weight: 600;
    }

    .nav-link.active svg { stroke: var(--accent); }

    /* active underline (desktop only, see media query) */
    .nav-link.active { position: relative; }
    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: -11px;
      left: 16px; right: 16px;
      height: 2px;
      background: var(--accent);
      border-radius: 2px;
    }

    .topnav-right {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-shrink: 0;
    }

    .staff-badge {
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
      padding: 4px 12px;
      background: rgba(251,191,36,0.12);
      color: var(--warning);
      border: 1px solid rgba(251,191,36,0.2);
      border-radius: 6px;
      white-space: nowrap;
    }

    .user-chip {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      font-weight: 500;
      color: var(--text);
      white-space: nowrap;
    }

    .user-avatar {
      width: 32px; height: 32px;
      border-radius: 50%;
      background: rgba(230,57,70,0.12);
      border: 1px solid rgba(230,57,70,0.25);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Bebas Neue', sans-serif;
      font-size: 13px;
      color: var(--accent);
      overflow: hidden;
      flex-shrink: 0;
    }

    .user-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .btn-logout {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--muted);
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      text-decoration: none;
      transition: all 0.15s;
      white-space: nowrap;
    }

    .btn-logout svg { width: 14px; height: 14px; stroke: var(--muted); transition: stroke 0.15s; flex-shrink: 0; }
    .btn-logout:hover { border-color: var(--danger); color: var(--danger); }
    .btn-logout:hover svg { stroke: var(--danger); }

    /* Hamburger toggle - hidden on desktop */
    .topnav-toggle {
      display: none;
      align-items: center;
      justify-content: center;
      width: 38px; height: 38px;
      background: transparent;
      border: 1px solid var(--border);
      border-radius: 8px;
      cursor: pointer;
      flex-shrink: 0;
    }
    .topnav-toggle svg { width: 20px; height: 20px; stroke: var(--text); fill: none; }

    .topnav-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      z-index: 90;
    }
    .topnav-overlay.open { display: block; }

    /* ── PAGE CONTENT ── */
    .page-wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 36px 36px;
    }

    /* ── ALERTS ── */
    .alert {
      padding: 12px 16px;
      border-radius: var(--radius);
      font-size: 13px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .alert-success { background: rgba(74,222,128,0.08); border: 1px solid rgba(74,222,128,0.2); color: var(--success); }
    .alert-danger  { background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.2); color: var(--danger); }

    /* ── SHARED COMPONENTS ── */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 28px;
    }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 22px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
      min-width: 0;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 3px; height: 100%;
      background: var(--accent);
    }

    .stat-card.orange::before { background: #f97316; }
    .stat-card.yellow::before { background: var(--warning); }
    .stat-card.green::before  { background: var(--success); }

    .stat-card-left { flex: 1; min-width: 0; }
    .stat-label { font-size: 11px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .stat-value { font-family: 'Bebas Neue', sans-serif; font-size: 44px; line-height: 1; letter-spacing: 1px; }
    .stat-sub   { font-size: 12px; color: var(--muted); margin-top: 5px; }
    .stat-up    { color: var(--accent); font-weight: 600; }

    .stat-icon {
      width: 44px; height: 44px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .stat-icon svg { width: 22px; height: 22px; fill: none; stroke: currentColor; }
    .icon-green  { background: rgba(230,57,70,0.1);  color: var(--accent); }
    .icon-orange { background: rgba(249,115,22,0.1); color: #f97316; }
    .icon-yellow { background: rgba(251,191,36,0.1); color: var(--warning); }

    /* split panel (for member detail views) */
    .split-panel {
      display: grid;
      grid-template-columns: 360px 1fr;
      gap: 16px;
      align-items: start;
    }

    .members-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
    }

    .members-panel-header {
      padding: 18px 20px 14px;
      border-bottom: 1px solid var(--border);
    }

    .members-panel-title {
      font-size: 15px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .members-search {
      position: relative;
    }

    .members-search svg {
      position: absolute;
      left: 12px; top: 50%;
      transform: translateY(-50%);
      width: 14px; height: 14px;
      stroke: var(--muted); fill: none;
    }

    .members-search input {
      width: 100%;
      padding: 9px 12px 9px 34px;
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text);
      font-size: 13px;
      font-family: 'DM Sans', sans-serif;
      outline: none;
      transition: border-color 0.15s;
    }

    .members-search input:focus { border-color: var(--accent); }

    .members-list { padding: 8px; max-height: 540px; overflow-y: auto; }

    .member-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 11px 12px;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.15s;
      border: 1px solid transparent;
      margin-bottom: 4px;
      gap: 8px;
    }

    .member-item:hover { background: var(--surface2); border-color: var(--border); }

    .member-item.active-item {
      background: rgba(230,57,70,0.07);
      border-color: rgba(230,57,70,0.25);
    }

    .member-item-info { display: flex; flex-direction: column; min-width: 0; }
    .member-item-name  { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .member-item-email { font-size: 11px; color: var(--muted); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .status-pill {
      font-size: 11px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 100px;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .pill-active   { background: rgba(74,222,128,0.15);  color: var(--success); }
    .pill-expiring { background: rgba(251,191,36,0.15);  color: var(--warning); }
    .pill-expired  { background: rgba(248,113,113,0.15); color: var(--danger); }

    .details-panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      min-height: 480px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .details-empty {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: var(--muted);
      padding: 60px 20px;
      text-align: center;
    }

    .details-empty svg {
      width: 40px; height: 40px;
      stroke: var(--muted); fill: none;
      margin-bottom: 12px;
      opacity: 0.35;
    }

    .details-content { display: none; }
    .details-content.visible { display: block; }

    /* buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 9px 18px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      font-family: 'DM Sans', sans-serif;
      cursor: pointer;
      border: none;
      text-decoration: none;
      transition: all 0.15s;
    }

    .btn-primary   { background: var(--accent); color: #f5f5f5; }
    .btn-primary:hover  { background: #c9303c; transform: translateY(-1px); }
    .btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
    .btn-secondary:hover { border-color: rgba(230,57,70,0.35); color: var(--accent); }
    .btn-sm { padding: 6px 14px; font-size: 12px; }
    .btn-danger { background: rgba(248,113,113,0.1); color: var(--danger); border: 1px solid rgba(248,113,113,0.2); }

    /* forms */
    .form-label {
      display: block;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--muted);
      margin-bottom: 7px;
    }

    .form-control {
      width: 100%;
      padding: 10px 14px;
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      outline: none;
      transition: border-color 0.15s;
    }

    .form-control:focus { border-color: var(--accent); }
    .form-control option { background: var(--surface2); }

    /* badge */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 10px;
      border-radius: 5px;
      font-size: 11px;
      font-weight: 700;
      white-space: nowrap;
    }

    .badge-active, .badge-paid    { background: rgba(74,222,128,0.12);  color: var(--success); }
    .badge-expired                 { background: rgba(248,113,113,0.12); color: var(--danger); }
    .badge-pending                 { background: rgba(251,191,36,0.12);  color: var(--warning); }
    .badge-monthly                 { background: rgba(160,160,160,0.12); color: #a0a0a0; }
    .badge-quarterly               { background: rgba(200,200,200,0.12); color: #c8c8c8; }
    .badge-annually, .badge-annual { background: rgba(74,222,128,0.12);  color: var(--success); }

    /* table — wrap any <table> in the content with <div class="table-responsive"> for
       horizontal scroll on narrow screens instead of squeezed/broken columns */
    .table-responsive {
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      border-radius: var(--radius);
    }
    table { width: 100%; border-collapse: collapse; min-width: 560px; }
    th {
      padding: 12px 20px;
      text-align: left;
      font-size: 10px;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 2px;
      background: var(--surface2);
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }
    td { padding: 14px 20px; border-bottom: 1px solid var(--border); font-size: 14px; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(255,255,255,0.015); }

    /* section header */
    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }
    .section-title { font-size: 17px; font-weight: 700; }

    /* card */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 24px;
    }

    /* ══════════════════════════════════════════════
       RESPONSIVE BREAKPOINTS
       ══════════════════════════════════════════════ */

    /* Tablet / laptop (≤1100px): stack the split panel */
    @media (max-width: 1100px) {
      .split-panel {
        grid-template-columns: 1fr;
      }
      .members-list { max-height: 320px; }
    }

    /* Tablet (≤1024px): tighten navbar + content spacing */
    @media (max-width: 1024px) {
      .topnav { padding: 0 20px; }
      .topnav-links { gap: 0; }
      .nav-link { padding: 8px 10px; font-size: 13px; }
      .nav-link svg { width: 15px; height: 15px; }
      .page-wrap { padding: 28px 20px; }
    }

    /* Mobile (≤860px): collapse nav links into a hamburger drawer */
    @media (max-width: 860px) {
      .topnav { height: 56px; }
      .topnav-name { font-size: 17px; max-width: 46vw; }

      .topnav-toggle { display: flex; order: 3; }

      .topnav-links {
        position: fixed;
        top: 0;
        right: 0;
        height: 100vh;
        width: min(80vw, 300px);
        background: var(--surface);
        border-left: 1px solid var(--border);
        flex-direction: column;
        align-items: stretch;
        gap: 3px;
        padding: 74px 14px 20px;
        transform: translateX(100%);
        transition: transform 0.25s ease;
        z-index: 95;
        overflow-y: auto;
      }
      .topnav-links.open { transform: translateX(0); }

      .nav-link { width: 100%; padding: 13px 14px; font-size: 15px; border-radius: 10px; }
      .nav-link svg { width: 18px; height: 18px; }
      .nav-link.active::after { display: none; }
      .nav-link.active { border-left: 3px solid var(--accent); }

      .topnav-right { gap: 8px; order: 2; }
      .user-chip span,
      .user-chip { font-size: 0; gap: 0; } /* collapse text, keep avatar visible */
      .user-avatar { font-size: 13px; }
      .staff-badge { display: none; }
      .btn-logout { padding: 8px; width: 38px; height: 38px; }
      .btn-logout span { display: none; }

      .page-wrap { padding: 20px 14px; }

      .stat-value { font-size: 34px; }
      .stat-card { padding: 18px 18px; }
    }

    /* Small phones (≤480px) */
    @media (max-width: 480px) {
      .topnav { padding: 0 12px; height: 52px; }
      .topnav-name { font-size: 15px; max-width: 38vw; }
      .topnav-logo { width: 28px; height: 28px; }
      .topnav-links { width: 84vw; }
      .page-wrap { padding: 14px 10px; }

      .stat-grid { grid-template-columns: 1fr; }
      .stat-value { font-size: 30px; }

      .section-header { align-items: flex-start; }

      th, td { padding: 10px 12px; font-size: 13px; }
    }
  </style>
</head>
<body>

{{-- TOP NAVBAR --}}
<nav class="topnav">
  <a href="{{ route('staff.dashboard') }}" class="topnav-brand">
    <div class="topnav-logo">
      <svg fill="none" stroke="#0a0a0a" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
    </div>
    <span class="topnav-name">APEX FITNESS GYM</span>
  </a>

  <div class="topnav-links" id="topnavLinks">
    <a href="{{ route('staff.dashboard') }}"
       class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      Dashboard
    </a>

    <a href="{{ route('members.index') }}"
       class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                 M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                 m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      Members
    </a>

    {{-- ATTENDANCE LINK ADDED HERE --}}
    <a href="{{ route('attendance.scan') }}"
       class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M14 17h3m3 0h-3m0 0v-3m0 3v3"/>
      </svg>
      Attendance
    </a>

    <a href="{{ route('staff.payments') }}"
       class="nav-link {{ request()->routeIs('staff.payments') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="1" y="4" width="22" height="16" rx="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
      Payments
    </a>

    <a href="{{ route('staff.profile') }}"
       class="nav-link {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      Profile
    </a>
  </div>

  <div class="topnav-right">
    <span class="staff-badge">Staff</span>

    <div class="user-chip">
      <div class="user-avatar">
        @if(auth()->user()->photo)
          <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt=""/>
        @else
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        @endif
      </div>
      <span>{{ auth()->user()->name }}</span>
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

  {{-- Hamburger toggle (mobile only) --}}
  <button type="button" class="topnav-toggle" id="topnavToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="topnavLinks">
    <svg id="navIconOpen" viewBox="0 0 24 24" stroke-width="2">
      <line x1="3" y1="6" x2="21" y2="6"/>
      <line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
    <svg id="navIconClose" viewBox="0 0 24 24" stroke-width="2" style="display:none;">
      <line x1="18" y1="6" x2="6" y2="18"/>
      <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
</nav>

<div class="topnav-overlay" id="topnavOverlay"></div>

{{-- MAIN CONTENT --}}
<main class="page-wrap">
  @if(session('success'))
    <div class="alert alert-success">✓ {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">✕ {{ session('error') }}</div>
  @endif

  @yield('content')
</main>

<script>
  (function () {
    var toggle  = document.getElementById('topnavToggle');
    var links   = document.getElementById('topnavLinks');
    var overlay = document.getElementById('topnavOverlay');
    var iconOpen  = document.getElementById('navIconOpen');
    var iconClose = document.getElementById('navIconClose');

    function closeMenu() {
      links.classList.remove('open');
      overlay.classList.remove('open');
      document.body.classList.remove('menu-open');
      toggle.setAttribute('aria-expanded', 'false');
      iconOpen.style.display = '';
      iconClose.style.display = 'none';
    }

    function openMenu() {
      links.classList.add('open');
      overlay.classList.add('open');
      document.body.classList.add('menu-open');
      toggle.setAttribute('aria-expanded', 'true');
      iconOpen.style.display = 'none';
      iconClose.style.display = '';
    }

    toggle.addEventListener('click', function () {
      links.classList.contains('open') ? closeMenu() : openMenu();
    });

    overlay.addEventListener('click', closeMenu);

    links.querySelectorAll('.nav-link').forEach(function (link) {
      link.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 860) closeMenu();
    });
  })();
</script>

</body>
</html>