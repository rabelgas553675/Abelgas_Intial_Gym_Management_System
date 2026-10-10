<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>

  {{-- Apply the saved theme before first paint (same key/logic as staff layout & resources/js/app.js). Default: dark. --}}
  <script>
    (function () {
      try {
        var r = document.documentElement;
        var light = localStorage.getItem('apex-color-theme') === 'light';
        r.classList.toggle('dark', !light);
        r.dataset.theme = light ? 'light' : 'dark';
      } catch (e) {}
    })();
  </script>

  <title>@yield('title', 'APEX FITNESS GYM')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* Design tokens + navbar are kept IDENTICAL to layouts/staff — keep them in sync. */
    :root {
      --bg:       #131417;
      --bg-top:   #1f2024;
      --surface:  #1a1b1f;
      --surface2: #212227;
      --surface3: #2a2b31;
      --border:   rgba(255,255,255,0.07);
      --accent:   #e0a93b;
      --accent-2: #f3c866;
      --accent-dark: #b8862a;
      --accent-soft: rgba(224,169,59,0.12);
      --text:     #ffffff;
      --muted:    #a3a5ad;
      --text-soft: #d2d3d8;
      --success:  #4ade80;
      --danger:   #f87171;
      --warning:  #fbbf24;
      --info:     #60a5fa;
      --radius:   14px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

    body {
      background: linear-gradient(180deg, var(--bg-top) 0%, var(--bg) 380px) fixed, var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      min-height: 100vh;
      overflow-x: hidden;
    }

    body.menu-open { overflow: hidden; }

    /* ── TOP NAVBAR ──
       backdrop-filter lives on ::before (NOT on .topnav) so the fixed mobile
       drawer is positioned against the viewport, not the 60px bar. */
    .topnav {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(20,21,24,0.92);
      border-bottom: 1px solid var(--border);
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 36px;
      gap: 12px;
    }
    .topnav::before {
      content: '';
      position: absolute;
      inset: 0;
      z-index: -1;
      pointer-events: none;
      -webkit-backdrop-filter: blur(10px);
      backdrop-filter: blur(10px);
    }

    .topnav-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      min-width: 0;
      flex-shrink: 1;
    }

    .topnav-logo {
      width: 34px; height: 34px;
      background: linear-gradient(145deg, #4a4b52, #2c2d33);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 9px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }

    .topnav-logo svg { width: 18px; height: 18px; stroke: #e6e6e8; }

    .topnav-name {
      font-family: 'DM Sans', sans-serif;
      font-weight: 700;
      font-size: 15px;
      color: #e4e5e8;
      letter-spacing: 2.5px;
      white-space: nowrap;
      min-width: 0;
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
      padding: 8px 14px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 500;
      color: #c6c8ce;
      text-decoration: none;
      transition: all 0.15s;
      white-space: nowrap;
    }

    .nav-link svg {
      width: 15px; height: 15px;
      stroke: #c6c8ce;
      fill: none;
      flex-shrink: 0;
      transition: stroke 0.15s;
    }

    .nav-link:hover { color: var(--text); background: rgba(255,255,255,0.04); }
    .nav-link:hover svg { stroke: var(--text); }

    .nav-link.active {
      color: var(--text);
      background: rgba(255,255,255,0.05);
      font-weight: 600;
    }
    .nav-link.active svg { stroke: var(--accent); }

    .nav-link.active { position: relative; }
    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: -11px;
      left: 14px; right: 14px;
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
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 5px 14px;
      background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
      color: #1a1a1a;
      border-radius: 6px;
      white-space: nowrap;
      box-shadow: 0 0 14px rgba(224,169,59,0.25);
    }

    .user-chip {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 500;
      color: #e4e5e8;
      white-space: nowrap;
    }

    .user-avatar {
      width: 30px; height: 30px;
      border-radius: 50%;
      background: var(--accent-soft);
      border: 1px solid rgba(224,169,59,0.3);
      display: flex; align-items: center; justify-content: center;
      font-family: 'DM Sans', sans-serif;
      font-weight: 700;
      font-size: 12px;
      color: var(--accent);
      overflow: hidden;
      flex-shrink: 0;
    }

    .user-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .user-chip--link {
      padding: 2px 12px 2px 4px;
      border-radius: 8px;
      text-decoration: none;
      position: relative;
      transition: all 0.15s;
    }
    .user-chip--link:hover { background: rgba(255,255,255,0.05); color: var(--text); }
    .user-chip--link:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

    .btn-logout {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      background: rgba(248,113,113,0.04);
      border: 1px solid rgba(248,113,113,0.45);
      border-radius: 8px;
      color: var(--danger);
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      text-decoration: none;
      transition: all 0.15s;
      white-space: nowrap;
    }

    .btn-logout svg { width: 14px; height: 14px; stroke: var(--danger); flex-shrink: 0; }
    .btn-logout:hover { background: rgba(248,113,113,0.12); border-color: var(--danger); }
    .topnav-logout-form { margin: 0; }

    /* Account block + brand header inside the mobile drawer */
    .nav-account { display: none; }
    .nav-brand-head { display: none; }

    /* Hamburger toggle - hidden on desktop.
       position + z-index keep it ABOVE the slide-in drawer so it can always close it. */
    .topnav-toggle {
      display: none;
      position: relative;
      z-index: 110;
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

    /* ── PAGE TITLE BAR ── */
    .page-titlebar {
      background: rgba(0,0,0,0.25);
      border-bottom: 1px solid var(--border);
      padding: 16px 36px;
    }
    .page-titlebar h1 {
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text);
    }

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

    /* ── BUTTONS ── */
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
    .btn-primary   { background: linear-gradient(135deg, var(--accent-2), var(--accent-dark)); color: #1a1a1a; }
    .btn-primary:hover  { filter: brightness(1.08); transform: translateY(-1px); }
    .btn-secondary { background: #0e0e10; color: var(--text); border: 1px solid var(--border); }
    .btn-secondary:hover { border-color: rgba(224,169,59,0.45); color: var(--accent); }
    .btn-sm { padding: 6px 14px; font-size: 12px; }

    /* ── FORMS — 16px on inputs prevents iOS Safari auto-zoom on focus ── */
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
      font-size: 16px;
      outline: none;
      transition: border-color 0.15s;
    }
    .form-control:focus { border-color: var(--accent); }
    .form-control option { background: var(--surface2); }

    /* ══════════════════════════════════════════════
       RESPONSIVE BREAKPOINTS (same as staff)
       ══════════════════════════════════════════════ */

    @media (max-width: 1024px) {
      .topnav { padding: 0 20px; }
      .topnav-links { gap: 0; }
      .nav-link { padding: 8px 10px; font-size: 13px; }
      .page-titlebar { padding: 14px 20px; }
      .page-wrap { padding: 28px 20px; }
    }

    /* Mobile (≤860px): collapse nav links into a hamburger drawer */
    @media (max-width: 860px) {
      .topnav { height: 56px; }
      .topnav-brand { flex: 1 1 auto; }
      .topnav-name { font-size: 13px; }

      .topnav-toggle { display: flex; order: 3; }

      /* Drawer: full-height panel on the right, hidden (and unfocusable) until opened */
      .topnav-links {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        height: 100vh;
        height: 100dvh;
        width: min(80vw, 300px);
        background: var(--surface);
        border-left: 1px solid var(--border);
        box-shadow: -12px 0 32px rgba(0,0,0,0.45);
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 3px;
        padding: 0 14px 20px;
        transform: translateX(100%);
        visibility: hidden;
        transition: transform 0.25s ease, visibility 0s linear 0.25s;
        z-index: 95;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
      }
      .topnav-links.open {
        transform: translateX(0);
        visibility: visible;
        transition: transform 0.25s ease, visibility 0s;
      }

      .nav-link { width: 100%; flex-shrink: 0; padding: 13px 14px; font-size: 15px; border-radius: 10px; }
      .nav-link svg { width: 18px; height: 18px; }
      .nav-link.active::after { display: none; }
      .nav-link.active { border-left: 3px solid var(--accent); }

      /* Logo + name at the top of the drawer; right padding leaves room for the close button */
      .nav-brand-head {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        min-height: 56px;
        padding: 0 54px 0 6px;
        margin-bottom: 10px;
        border-bottom: 1px solid rgba(255,255,255,0.09);
        text-decoration: none;
      }
      .nav-brand-head .topnav-name { font-size: 13px; }

      /* Divider line under each menu item (drawer is dark in both themes) */
      .nav-link { position: relative; }
      .nav-link::before {
        content: '';
        position: absolute;
        left: 14px; right: 14px; bottom: -2px;
        height: 1px;
        background: rgba(255,255,255,0.09);
        pointer-events: none;
      }
      .nav-link:last-of-type::before { display: none; }

      .topnav-right { gap: 8px; order: 2; }
      .user-chip span,
      .user-chip { font-size: 0; gap: 0; }
      .user-chip--link { padding: 2px; }
      .user-avatar { font-size: 13px; }

      /* Role badge stays visible on mobile, just more compact */
      .topnav-right .staff-badge { display: inline-block; font-size: 9px; padding: 4px 9px; letter-spacing: 1px; }
      .btn-logout { padding: 8px; width: 38px; height: 38px; justify-content: center; }
      .btn-logout span { display: none; }

      .page-wrap { padding: 20px 14px; }
    }

    /* Small phones (≤480px): logout moves into the drawer */
    @media (max-width: 480px) {
      .topnav { padding: 0 12px; height: 52px; gap: 6px; }
      .topnav-brand { gap: 7px; }
      .topnav-name { font-size: 11.5px; letter-spacing: 1.2px; }
      .topnav-logo { width: 28px; height: 28px; }
      .topnav-links { width: 84vw; max-width: 320px; }
      .nav-brand-head { min-height: 52px; }
      .nav-brand-head .topnav-name { font-size: 12px; letter-spacing: 1.5px; }
      .topnav-right { gap: 6px; }
      .topnav-logout-form { display: none; }
      .page-titlebar { padding: 12px 12px; }
      .page-wrap { padding: 14px 10px; }

      .nav-account {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: auto;
        padding: 16px 6px 0;
        border-top: 1px solid var(--border);
      }
      .nav-account-user { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
      .nav-account-name { font-size: 14px; font-weight: 600; color: var(--text); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
      .nav-account .btn-logout { width: 100%; height: auto; padding: 11px 14px; }
      .nav-account .btn-logout span { display: inline; }
    }

    /* Very small phones: badge hides, brand shrinks a bit */
    @media (max-width: 389px) {
      .topnav-right .staff-badge { display: none !important; }
      .topnav-name { font-size: 11px; letter-spacing: 1px; }
    }
    @media (max-width: 340px) {
      .topnav-name { font-size: 10px; letter-spacing: .6px; }
    }

    /* ═══════════════════════════════════════════════════════════════
       LIGHT THEME — GOLD & BLACK (same rules as staff layout)
       Applies only when <html data-theme="light"> (set by app.js).
       ═══════════════════════════════════════════════════════════════ */

    html:root[data-theme="light"] {
      color-scheme: light;

      --bg:#f6f4ee;        --bg-top:#fbf9f4;
      --surface:#ffffff;   --surface2:#f5f2ea;   --surface3:#e8e3d6;
      --border:rgba(20,16,8,0.10);

      --accent:#a97a17;    --accent-2:#e0a93b;   --accent-dark:#b8862a;
      --accent-soft:rgba(184,134,42,0.12);
      --gold:#b8862a;
      --black:#111111;

      --text:#111111;      --text-soft:#3a3833;  --muted:#6f6a5e;

      --success:#15803d;   --danger:#dc2626;
      --warning:#b45309;   --info:#2563eb;

      --shadow-card:0 1px 2px rgba(20,16,8,.05), 0 4px 16px rgba(20,16,8,.06);
    }

    html:root[data-theme="light"] body {
      background: linear-gradient(180deg, var(--bg-top) 0%, var(--bg) 380px) fixed, var(--bg);
      color: var(--text);
    }

    html:root[data-theme="light"] .topnav {
      background: rgba(15,15,16,.96);
      border-color: rgba(255,255,255,.08);
      color: #fff;
    }
    html:root[data-theme="light"] .topnav-logo {
      background: linear-gradient(145deg,#2a2a2c,#0b0b0c);
      border-color: rgba(224,169,59,.45);
    }
    html:root[data-theme="light"] .topnav-logo svg { stroke: var(--accent-2); }
    html:root[data-theme="light"] .topnav-name,
    html:root[data-theme="light"] .user-chip { color: #f1f1f3; }

    html:root[data-theme="light"] .nav-link { color: #c6c8ce; }
    html:root[data-theme="light"] .nav-link svg { stroke: #c6c8ce; }
    html:root[data-theme="light"] .nav-link:hover { color: #fff; background: rgba(255,255,255,.07); }
    html:root[data-theme="light"] .nav-link:hover svg { stroke: #fff; }
    html:root[data-theme="light"] .nav-link.active { color: #fff; background: rgba(255,255,255,.07); }
    html:root[data-theme="light"] .nav-link.active svg { stroke: var(--accent-2); }
    html:root[data-theme="light"] .nav-link.active::after { background: var(--accent-2); }
    html:root[data-theme="light"] .user-chip--link:hover { color: #fff; background: rgba(255,255,255,.07); }

    html:root[data-theme="light"] .staff-badge { color: #111; box-shadow: none; }
    html:root[data-theme="light"] .user-avatar {
      background: rgba(224,169,59,.14);
      border-color: rgba(224,169,59,.45);
      color: var(--accent-2);
    }
    html:root[data-theme="light"] .btn-logout {
      background: transparent;
      border-color: rgba(248,113,113,.45);
      color: #f87171;
    }
    html:root[data-theme="light"] .btn-logout svg { stroke: #f87171; }
    html:root[data-theme="light"] .btn-logout:hover { background: rgba(248,113,113,.12); }
    html:root[data-theme="light"] .topnav-toggle { border-color: rgba(255,255,255,.15); }
    html:root[data-theme="light"] .topnav-toggle svg { stroke: #fff; }
    html:root[data-theme="light"] .topnav-overlay { background: rgba(0,0,0,.5); }
    html:root[data-theme="light"] .page-titlebar {
      background: rgba(255,255,255,.6);
      border-color: var(--border);
    }
    html:root[data-theme="light"] .page-titlebar h1 { border-left: 3px solid var(--accent-2); padding-left: 12px; }

    /* Mobile drawer stays black so the light nav text remains readable */
    @media (max-width: 860px) {
      html:root[data-theme="light"] .topnav-links {
        background: #111112;
        border-left-color: rgba(255,255,255,.08);
      }
      html:root[data-theme="light"] .nav-account { border-top-color: rgba(255,255,255,.1); }
      html:root[data-theme="light"] .nav-account-name { color: #f1f1f3; }
    }

    html:root[data-theme="light"] :is(.btn-primary) {
      color: #111;
      box-shadow: 0 1px 2px rgba(20,16,8,.15);
    }
    html:root[data-theme="light"] .btn-primary:hover { box-shadow: 0 6px 18px rgba(184,134,42,.35); }
    html:root[data-theme="light"] .btn-secondary {
      background: var(--black);
      color: #fff;
      border-color: var(--black);
    }
    html:root[data-theme="light"] .btn-secondary:hover {
      color: var(--accent-2);
      border-color: var(--accent-2);
    }

    /* Use `background-color`, NOT the `background` shorthand: the shorthand would reset the select arrow image. */
    html:root[data-theme="light"] .form-control {
      background-color: var(--surface);
      border-color: var(--border);
      color: var(--text);
    }
    html:root[data-theme="light"] .form-control:focus {
      border-color: var(--accent-dark);
      box-shadow: 0 0 0 3px var(--accent-soft);
    }
    html:root[data-theme="light"] .form-control option { background: var(--surface); color: var(--text); }
    html:root[data-theme="light"] input[type="date"] { color-scheme: light !important; }

    html:root[data-theme="light"] .alert-success { color: var(--success); }
    html:root[data-theme="light"] .alert-danger  { color: var(--danger); }

    html:root[data-theme="light"] .theme-toggle {
      background: var(--black);
      color: var(--accent-2);
      border-color: rgba(224,169,59,.4);
      box-shadow: var(--shadow-card);
    }
  </style>
</head>
<body>

{{-- Read active section before navbar renders --}}
@php $active = View::getSection('active') ?? ''; @endphp

{{-- TOP NAVBAR (same structure as staff) --}}
<nav class="topnav">
  <a href="{{ route('member.dashboard') }}" class="topnav-brand" aria-label="APEX FITNESS GYM">
    <div class="topnav-logo">
      <svg fill="none" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
    </div>
    <span class="topnav-name">APEX FITNESS GYM</span>
  </a>

  <div class="topnav-links" id="topnavLinks">
    {{-- Brand header: only shown inside the drawer on mobile --}}
    <a href="{{ route('member.dashboard') }}" class="nav-brand-head" aria-label="APEX FITNESS GYM">
      <div class="topnav-logo">
        <svg fill="none" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
      </div>
      <span class="topnav-name">APEX FITNESS GYM</span>
    </a>

    <a href="{{ route('member.dashboard') }}"
       class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1"/>
        <rect x="14" y="3" width="7" height="7" rx="1"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <rect x="14" y="14" width="7" height="7" rx="1"/>
      </svg>
      Dashboard
    </a>

    {{-- Show Waiting only if member is pending --}}
    @if(Auth::user()->memberProfile && Auth::user()->memberProfile->coach_status === 'pending')
      <a href="{{ route('member.waiting') }}"
         class="nav-link {{ $active === 'waiting' ? 'active' : '' }}"
         style="color: var(--accent);">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        Waiting Approval
      </a>
    @endif

    <a href="{{ route('member.schedule') }}"
       class="nav-link {{ $active === 'schedule' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      My Schedule
    </a>

    <a href="{{ route('member.profile') }}"
       class="nav-link {{ $active === 'profile' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      Profile
    </a>

    <a href="{{ route('member.attendance-history') }}"
       class="nav-link {{ $active === 'attendance' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <path d="M8 11h8M8 15h5"/>
      </svg>
      Attendance
    </a>

    <a href="{{ route('member.payments') }}"
       class="nav-link {{ $active === 'payments' ? 'active' : '' }}">
      <svg viewBox="0 0 24 24" stroke-width="2">
        <rect x="1" y="4" width="22" height="16" rx="2"/>
        <line x1="1" y1="10" x2="23" y2="10"/>
      </svg>
      Payments
    </a>

    {{-- Account block: only shown inside the drawer on small phones (≤480px) --}}
    <div class="nav-account">
      <div class="nav-account-user">
        <span class="nav-account-name">{{ auth()->user()->name }}</span>
        <span class="staff-badge">{{ strtoupper(auth()->user()->role) }}</span>
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
  </div>

  <div class="topnav-right">
    <span class="staff-badge">{{ strtoupper(auth()->user()->role) }}</span>

    <a href="{{ route('member.profile') }}"
       class="user-chip user-chip--link"
       title="My Profile">
      <div class="user-avatar">
        @if(auth()->user()->photo)
          <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt=""/>
        @else
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        @endif
      </div>
      <span>{{ auth()->user()->name }}</span>
    </a>

    <form method="POST" action="{{ route('logout') }}" class="topnav-logout-form">
      @csrf
      <button type="submit" class="btn-logout" aria-label="Logout">
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

{{-- PAGE TITLE BAR: add @section('page_title', 'Dashboard') in a view to show it --}}
@hasSection('page_title')
  <div class="page-titlebar">
    <h1>@yield('page_title')</h1>
  </div>
@endif

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

    function setOpen(open) {
      links.classList.toggle('open', open);
      overlay.classList.toggle('open', open);
      document.body.classList.toggle('menu-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      iconOpen.style.display = open ? 'none' : '';
      iconClose.style.display = open ? '' : 'none';
    }

    toggle.addEventListener('click', function () {
      setOpen(!links.classList.contains('open'));
    });
    overlay.addEventListener('click', function () { setOpen(false); });
    links.querySelectorAll('.nav-link').forEach(function (link) {
      link.addEventListener('click', function () { setOpen(false); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') setOpen(false);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth > 860) setOpen(false);
    });
  })();
</script>

</body>
</html>