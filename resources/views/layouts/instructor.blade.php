<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1"/>
  <title>@yield('title', 'APEX FITNESS GYM')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  {{-- Global theme bootstrap: runs before CSS/JS paint to avoid a flash of the wrong theme. --}}
  <script>
  (function () {
    try {
      var t = localStorage.getItem('apex-color-theme');
      if (t !== 'light' && t !== 'dark') t = 'dark';
      var r = document.documentElement;
      r.classList.toggle('dark', t === 'dark');
      r.setAttribute('data-theme', t);
    } catch (e) {
      document.documentElement.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  })();
  </script>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    /* CHARCOAL & GOLD accent palette (same values as layouts/admin + layouts/staff). */
    :root{
      --accent:#e0a93b;
      --accent-2:#f3c866;
      --accent-dark:#b8862a;
      --accent-hover:#b8862a;
      --accent-soft:rgba(224,169,59,0.12);
      --accent2:var(--accent-2);
      --text-soft:#d2d3d8;
      --success:#4ade80;--danger:#f87171;--warning:#fbbf24;--info:#60a5fa;
      --radius:10px;
      --navbar-h:60px;
    }

    html:root[data-theme="light"]{
      --accent:#a97a17;--accent-2:#e0a93b;--accent-dark:#b8862a;--accent-hover:#b8862a;
      --accent-soft:rgba(184,134,42,0.12);
      --gold:#b8862a;--black:#111111;
      --text-soft:#3a3833;
      --success:#15803d;--danger:#dc2626;--warning:#b45309;--info:#2563eb;
      --shadow-card:0 1px 2px rgba(20,16,8,.05), 0 4px 16px rgba(20,16,8,.06);
    }

    /* Legacy token names aliased to the global tokens. */
    :root,
    html:root[data-theme]{
      --bg:var(--bg-page);--surface:var(--bg-card);--surface2:var(--bg-card-secondary);
      --surface3:var(--table-header);--text:var(--text-primary);--muted:var(--text-secondary);
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    html{-webkit-text-size-adjust:100%;}
    body{background:var(--bg-page);color:var(--text-primary);font-family:'DM Sans',sans-serif;font-size:15px;min-height:100vh;overflow-x:hidden;}
    img{max-width:100%;}

    /* ── NAVBAR ── */
    .navbar{
      background:rgba(20,21,24,0.92);backdrop-filter:blur(10px);
      border-bottom:1px solid rgba(255,255,255,0.07);
      padding:0 36px;height:var(--navbar-h);display:flex;align-items:center;
      justify-content:space-between;position:sticky;top:0;z-index:200;gap:12px;
    }
    .navbar-brand{display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0;min-width:0;}
    .brand-icon{
      width:34px;height:34px;
      background:linear-gradient(145deg,#4a4b52,#2c2d33);
      border:1px solid rgba(255,255,255,0.12);border-radius:9px;
      display:flex;align-items:center;justify-content:center;flex-shrink:0;
    }
    .brand-icon svg{width:18px;height:18px;fill:none;stroke:#e6e6e8;stroke-width:2.5;}
    .brand-name{
      font-family:'DM Sans',sans-serif;font-weight:700;font-size:15px;color:#e4e5e8;
      letter-spacing:2.5px;line-height:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }

    .navbar-nav{display:flex;align-items:center;gap:2px;}
    .nav-item{
      display:flex;align-items:center;gap:7px;padding:8px 14px;border-radius:8px;
      color:#c6c8ce;font-size:13px;font-weight:500;text-decoration:none;
      transition:all 0.15s;white-space:nowrap;position:relative;
    }
    .nav-item:hover{color:#fff;background:rgba(255,255,255,0.05);}
    .nav-item.active{color:#fff;background:rgba(255,255,255,0.05);font-weight:600;}
    .nav-item.active::after{
      content:'';position:absolute;bottom:-11px;left:14px;right:14px;
      height:2px;background:var(--accent-2);border-radius:2px;
    }
    .nav-item svg{width:15px;height:15px;stroke:currentColor;fill:none;flex-shrink:0;}
    .nav-item.active svg{stroke:var(--accent-2);}

    .nav-badge{
      display:inline-flex;align-items:center;justify-content:center;
      min-width:17px;height:17px;padding:0 4px;border-radius:20px;
      background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));
      color:#1a1a1a;font-size:10px;font-weight:800;line-height:1;
    }

    .navbar-right{display:flex;align-items:center;gap:14px;flex-shrink:0;}
    .user-chip{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:#e4e5e8;white-space:nowrap;}
    .user-avatar{
      width:30px;height:30px;border-radius:50%;
      background:var(--accent-soft);border:1px solid rgba(224,169,59,0.3);
      display:flex;align-items:center;justify-content:center;
      font-weight:700;font-size:12px;color:var(--accent-2);overflow:hidden;flex-shrink:0;
    }
    .user-avatar img{width:100%;height:100%;object-fit:cover;}
    .btn-logout-top{
      display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;
      background:rgba(248,113,113,0.04);border:1px solid rgba(248,113,113,0.45);color:#f87171;
      font-size:13px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all 0.15s;white-space:nowrap;
    }
    .btn-logout-top:hover{background:rgba(248,113,113,0.12);border-color:#f87171;}
    .btn-logout-top svg{width:14px;height:14px;stroke:currentColor;fill:none;}

    /* ── MOBILE NAV TOGGLE ── */
    .nav-toggle{
      display:none;background:transparent;border:1px solid rgba(255,255,255,0.15);
      border-radius:8px;width:38px;height:38px;align-items:center;justify-content:center;
      cursor:pointer;flex-shrink:0;color:#fff;
    }
    .nav-toggle svg{width:20px;height:20px;stroke:currentColor;fill:none;stroke-width:2;}
    .nav-toggle .icon-close{display:none;}
    .nav-toggle.open .icon-menu{display:none;}
    .nav-toggle.open .icon-close{display:block;}
    .nav-scrim{display:none;position:fixed;inset:0;top:var(--navbar-h);background:rgba(0,0,0,0.6);z-index:150;}
    .nav-scrim.show{display:block;}

    /* ── PAGE ── */
    .page-content{max-width:1300px;margin:0 auto;padding:28px 36px;}
    .page-content h1 span,.gold-text{color:var(--accent);}

    /* ── BUTTONS ── */
    .btn{padding:8px 18px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:1px solid transparent;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
    .btn-primary{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;font-weight:700;}
    .btn-primary:hover{filter:brightness(1.08);transform:translateY(-1px);}
    .btn-secondary{background:#0e0e10;color:#ffffff;border-color:var(--border);}
    .btn-secondary:hover{color:var(--accent-2);border-color:rgba(224,169,59,0.45);}
    .btn-danger-soft{background:rgba(248,113,113,0.1);color:var(--danger);border:1px solid rgba(248,113,113,0.2);}
    .btn-danger-soft:hover{background:rgba(248,113,113,0.2);}
    .btn-sm{padding:5px 12px;font-size:12px;}

    /* ── FORMS ── */
    .form-label{display:block;font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
    .form-control{width:100%;padding:11px 14px;background:var(--input-bg);border:1px solid var(--input-border);border-radius:10px;color:var(--text-primary);font-family:'DM Sans',sans-serif;font-size:16px;outline:none;transition:border-color 0.15s, box-shadow 0.15s;}
    .form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
    .form-control option{background:var(--input-bg);color:var(--text-primary);}
    textarea.form-control{resize:vertical;min-height:90px;}

    /* ── ALERTS ── */
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;margin-bottom:20px;}
    .alert-success{background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.2);color:var(--success);}
    .alert-danger{background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:var(--danger);}

    /* ── STAT CARDS (glow style) ── */
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:28px;}
    .stat-card{
      --glow:224,169,59;
      background:linear-gradient(145deg,rgba(var(--glow),0.10),var(--bg-card) 70%);
      border:1px solid rgba(var(--glow),0.45);border-radius:14px;padding:22px 24px;
      position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:12px;min-width:0;
      box-shadow:0 0 22px rgba(var(--glow),0.16), inset 0 0 18px rgba(var(--glow),0.04);
    }
    .stat-card.orange{--glow:245,158,11;}
    .stat-card.blue{--glow:96,165,250;}
    .stat-card.green{--glow:74,222,128;}
    .stat-card.yellow,.stat-card.gold{--glow:224,169,59;}
    .stat-card.red{--glow:248,113,113;}
    .stat-card-left{min-width:0;}
    .stat-label{font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;}
    .stat-value{font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;word-break:break-word;color:var(--text-primary);}
    .stat-sub{font-size:12px;color:var(--text-secondary);}
    .stat-up{color:var(--success);}
    .stat-icon{
      width:44px;height:44px;border-radius:12px;flex-shrink:0;
      display:flex;align-items:center;justify-content:center;
      background:rgba(var(--glow),0.14);border:1px solid rgba(var(--glow),0.35);color:rgb(var(--glow));
    }
    .stat-icon svg{width:24px;height:24px;stroke:currentColor;fill:none;}
    .icon-green{--glow:74,222,128;}
    .icon-orange{--glow:245,158,11;}
    .icon-blue{--glow:96,165,250;}
    .icon-yellow{--glow:224,169,59;}

    /* ── SPLIT PANEL ── */
    .split-panel{display:grid;grid-template-columns:360px 1fr;gap:16px;align-items:start;}
    .members-panel{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
    .members-panel-header{padding:18px 20px 14px;border-bottom:1px solid var(--border);}
    .members-panel-title{font-size:15px;font-weight:700;margin-bottom:12px;color:var(--accent);}
    .members-search{position:relative;}
    .members-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:var(--muted);fill:none;}
    .members-search input{
      width:100%;padding:9px 12px 9px 34px;background:var(--surface2);border:1px solid var(--border);
      border-radius:8px;color:var(--text);font-size:16px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color 0.15s;
    }
    .members-search input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
    .members-list{padding:8px;max-height:520px;overflow-y:auto;}
    .member-item{
      display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:8px;
      cursor:pointer;transition:all 0.2s ease;border:1px solid transparent;border-left:3px solid transparent;margin-bottom:4px;gap:8px;
    }
    .member-item:hover{background:var(--surface2);border-left-color:var(--accent);}
    .member-item.active-item{background:var(--accent-soft);border-left-color:var(--accent);}
    .member-item-info{display:flex;flex-direction:column;min-width:0;}
    .member-item-name{font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text);}
    .member-item-email{font-size:12px;color:var(--muted);margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .status-pill{font-size:11px;font-weight:700;padding:3px 10px;border-radius:100px;white-space:nowrap;flex-shrink:0;}
    .pill-active  {background:rgba(74,222,128,0.15); color:var(--success);border:1px solid rgba(74,222,128,0.3);}
    .pill-expiring{background:rgba(251,191,36,0.15); color:var(--warning);border:1px solid rgba(251,191,36,0.3);}
    .pill-expired {background:rgba(248,113,113,0.15);color:var(--danger); border:1px solid rgba(248,113,113,0.3);}

    .details-panel{background:var(--surface);border:1px solid var(--border);border-radius:14px;min-height:480px;display:flex;flex-direction:column;}
    .details-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);padding:60px 20px;}
    .details-empty svg{width:44px;height:44px;stroke:var(--muted);fill:none;margin-bottom:12px;opacity:0.4;}
    .details-content{display:none;padding:26px;flex:1;}
    .details-content.visible{display:block;}
    .details-name{font-size:22px;font-weight:700;margin-bottom:6px;word-break:break-word;color:var(--text);}
    .details-section-title{font-size:10px;color:var(--accent);letter-spacing:2px;text-transform:uppercase;margin:18px 0 10px;font-weight:700;}
    .details-row{display:flex;align-items:center;gap:10px;font-size:13px;margin-bottom:8px;color:var(--text);}
    .details-row svg{width:14px;height:14px;stroke:var(--accent);flex-shrink:0;fill:none;}
    .view-full-btn{
      display:inline-flex;align-items:center;gap:6px;padding:9px 18px;
      background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;
      font-size:13px;font-weight:800;border-radius:8px;text-decoration:none;margin-top:20px;
      transition:all 0.2s ease;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;
    }
    .view-full-btn:hover{filter:brightness(1.08);transform:translateY(-1px);box-shadow:0 8px 25px rgba(224,169,59,0.25);}
    .view-full-btn svg{width:14px;height:14px;stroke:#1a1a1a;fill:none;}

    /* ── CARD / TABLE ── */
    .card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
    .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap;}
    .section-title{font-size:17px;font-weight:700;color:var(--accent);}
    .table-scroll,.table-responsive{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}
    table{width:100%;border-collapse:collapse;min-width:640px;}
    thead{background:var(--table-header);}
    th{padding:12px 18px;text-align:left;font-size:10px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:2px;white-space:nowrap;}
    td{padding:14px 18px;font-size:14px;border-top:1px solid var(--border);vertical-align:middle;}
    tr:hover td{background:var(--table-hover);}

    /* ── BADGES ── */
    .badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:6px;font-size:11px;font-weight:700;white-space:nowrap;}
    .badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;}
    .badge-active   {background:rgba(74,222,128,0.15);color:var(--success);}
    .badge-expired  {background:rgba(248,113,113,0.15);color:var(--danger);}
    .badge-pending  {background:rgba(251,191,36,0.15);color:var(--warning);}
    .badge-paid     {background:rgba(74,222,128,0.15);color:var(--success);}
    .badge-monthly  {background:rgba(96,165,250,0.15);color:var(--info);}
    .badge-quarterly{background:rgba(167,139,250,0.15);color:#a78bfa;}
    .badge-annually,.badge-annual,.badge-yearly{background:rgba(224,169,59,0.15);color:var(--accent);}

    /* ═══ LIGHT MODE — GOLD & BLACK ═══ */
    html:root[data-theme="light"] .navbar{background:rgba(15,15,16,0.96);border-color:rgba(255,255,255,0.08);}
    html:root[data-theme="light"] .brand-icon{background:linear-gradient(145deg,#2a2a2c,#0b0b0c);border-color:rgba(224,169,59,0.45);}
    html:root[data-theme="light"] .brand-icon svg{stroke:var(--accent-2);}
    html:root[data-theme="light"] .brand-name,
    html:root[data-theme="light"] .user-chip{color:#f1f1f3;}
    html:root[data-theme="light"] .nav-item{color:#c6c8ce !important;}
    html:root[data-theme="light"] .nav-item svg{stroke:#c6c8ce;}
    html:root[data-theme="light"] .nav-item:hover{color:#fff !important;background:rgba(255,255,255,0.07);}
    html:root[data-theme="light"] .nav-item:hover svg{stroke:#fff;}
    html:root[data-theme="light"] .nav-item.active{color:#fff !important;background:rgba(255,255,255,0.07);}
    html:root[data-theme="light"] .nav-item.active svg{stroke:var(--accent-2);}
    html:root[data-theme="light"] .nav-item.active::after{background:var(--accent-2);}
    html:root[data-theme="light"] .nav-badge{color:#111;}
    html:root[data-theme="light"] .user-avatar{background:rgba(224,169,59,0.14);border-color:rgba(224,169,59,0.45);color:var(--accent-2);}
    html:root[data-theme="light"] .btn-logout-top{background:transparent;border-color:rgba(248,113,113,0.45);color:#f87171;}
    html:root[data-theme="light"] .btn-logout-top:hover{background:rgba(248,113,113,0.12);}
    html:root[data-theme="light"] .nav-toggle{border-color:rgba(255,255,255,0.15);color:#fff;}

    html:root[data-theme="light"] .stat-card{background:var(--bg-card);border:1px solid var(--border);box-shadow:var(--shadow-card);}
    html:root[data-theme="light"] .stat-card.green{--glow:17,17,17;}
    html:root[data-theme="light"] .stat-card.orange{--glow:180,83,9;}
    html:root[data-theme="light"] .stat-card.blue{--glow:37,99,235;}
    html:root[data-theme="light"] .stat-card.yellow,
    html:root[data-theme="light"] .stat-card.gold{--glow:184,134,42;}
    html:root[data-theme="light"] .icon-green{--glow:17,17,17;}
    html:root[data-theme="light"] .icon-orange{--glow:180,83,9;}
    html:root[data-theme="light"] .icon-blue{--glow:37,99,235;}
    html:root[data-theme="light"] .icon-yellow{--glow:184,134,42;}
    html:root[data-theme="light"] :is(.card,.members-panel,.details-panel){box-shadow:var(--shadow-card);}

    html:root[data-theme="light"] th{background:var(--black);color:var(--accent-2);}
    html:root[data-theme="light"] tr:hover td{background:rgba(184,134,42,0.06);}

    html:root[data-theme="light"] .btn-primary,
    html:root[data-theme="light"] .view-full-btn{color:#111;}
    html:root[data-theme="light"] .btn-secondary{background:var(--black);color:#fff;border-color:var(--black);}
    html:root[data-theme="light"] .btn-secondary:hover{color:var(--accent-2);border-color:var(--accent-2);}
    html:root[data-theme="light"] .form-control:focus{border-color:var(--accent-dark);}
    html:root[data-theme="light"] input[type="date"]{color-scheme:light !important;}
    html:root[data-theme="light"] .theme-toggle{background:var(--black);color:var(--accent-2);border-color:rgba(224,169,59,.4);box-shadow:var(--shadow-card);}

    /* ═══ RESPONSIVE ═══ */
    @media (max-width:1024px){
      .navbar{padding:0 20px;}
      .nav-item{padding:8px 10px;}
      .page-content{padding:24px 20px;}
      .split-panel{grid-template-columns:300px 1fr;}
      .user-chip span.user-name-text{display:none;}
    }

    @media (max-width:900px){
      .split-panel{grid-template-columns:1fr;}
      .members-list{max-height:320px;}
      .details-panel{min-height:0;}
    }

    @media (max-width:768px){
      .navbar{padding:0 14px;gap:8px;}
      .brand-name{font-size:13px;letter-spacing:2px;}
      .brand-icon{width:28px;height:28px;}
      .nav-toggle{display:flex;order:3;}

      .navbar-nav{
        position:fixed;top:var(--navbar-h);left:0;right:0;
        background:#131417;border-bottom:1px solid rgba(255,255,255,0.08);
        flex-direction:column;align-items:stretch;gap:0;
        max-height:0;overflow:hidden;z-index:150;transition:max-height 0.25s ease;
      }
      .navbar-nav.open{max-height:calc(100vh - var(--navbar-h));overflow-y:auto;}
      .nav-item{padding:14px 20px;border-radius:0;border-bottom:1px solid rgba(255,255,255,0.08);border-left:3px solid transparent;width:100%;}
      .nav-item.active{border-left-color:var(--accent-2);background:var(--accent-soft);}
      .nav-item.active::after{display:none;}
      html:root[data-theme="light"] .navbar-nav{background:#111112;border-bottom-color:rgba(255,255,255,0.08);}
      html:root[data-theme="light"] .nav-item{border-bottom-color:rgba(255,255,255,0.08);}
      html:root[data-theme="light"] .nav-item.active{border-left-color:var(--accent-2);background:var(--accent-soft);}

      .navbar-right{gap:8px;}
      .user-chip{gap:0;}
      .btn-logout-top span.logout-text{display:none;}
      .btn-logout-top{padding:8px;}

      .page-content{padding:18px 14px;}
      .stat-grid{grid-template-columns:1fr 1fr;gap:10px;}
      .stat-card{padding:16px;flex-direction:column;align-items:flex-start;gap:6px;}
      .stat-value{font-size:26px;}
      .stat-icon{width:36px;height:36px;}
      .stat-icon svg{width:20px;height:20px;}
      .section-header{flex-direction:column;align-items:flex-start;}
      .section-header .btn{width:100%;justify-content:center;}
    }

    @media (max-width:480px){
      .stat-grid{grid-template-columns:1fr;}
      .brand-name{display:none;}
      .details-content{padding:18px;}
      .member-item{flex-wrap:wrap;}
    }
  </style>
</head>
<body>

@php
  $active = View::getSection('active') ?? View::getSection('active_nav') ?? '';

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
    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
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