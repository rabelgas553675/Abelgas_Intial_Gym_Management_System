<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1"/>
  <title>@yield('title', 'APEX FITNESS GYM')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  {{-- Global theme bootstrap: runs before CSS/JS paint to avoid a flash of the wrong theme.
       Same localStorage key/default as resources/js/app.js (single global theme). --}}
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
    /* Colours for backgrounds, cards, borders and text come from the GLOBAL theme
       tokens in resources/css/app.css (--bg-page, --bg-card, --border, --text-primary,
       --text-secondary, ...). This layout only defines the CHARCOAL & GOLD accent
       palette (same values as layouts/staff.blade.php). */
    :root{
      --accent:#e0a93b;            /* gold */
      --accent-2:#f3c866;
      --accent-dark:#b8862a;
      --accent-soft:rgba(224,169,59,0.12);
      --accent2:var(--accent-2);   /* legacy name still used by some admin views */
      --text-soft:#d2d3d8;
      --success:#4ade80;--danger:#f87171;--warning:#fbbf24;--info:#60a5fa;
      --radius:10px;
      --navbar-h:60px;--topbar-h:52px;
    }

    /* Light mode: darker gold + readable semantic colours (mirrors the staff layout).
       `html:root[...]` outranks the legacy :root[data-theme="light"] rules in app.css. */
    html:root[data-theme="light"]{
      --accent:#a97a17;--accent-2:#e0a93b;--accent-dark:#b8862a;
      --accent-soft:rgba(184,134,42,0.12);
      --gold:#b8862a;--black:#111111;
      --text-soft:#3a3833;
      --success:#15803d;--danger:#dc2626;--warning:#b45309;--info:#2563eb;
      --shadow-card:0 1px 2px rgba(20,16,8,.05), 0 4px 16px rgba(20,16,8,.06);
    }

    /* Legacy token names still used by admin page views -> aliased to the global tokens.
       Higher specificity than the legacy [data-theme="light"] block in app.css so the
       global values always win, in both modes. */
    :root,
    html:root[data-theme]{
      --bg:var(--bg-page);--surface:var(--bg-card);--surface2:var(--bg-card-secondary);
      --surface3:var(--table-header);--text:var(--text-primary);--muted:var(--text-secondary);
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    html{-webkit-text-size-adjust:100%;}
    body{background:var(--bg-page);color:var(--text-primary);font-family:'DM Sans',sans-serif;font-size:15px;min-height:100vh;overflow-x:hidden;}
    img{max-width:100%;}

    /* ── NAVBAR (always charcoal/black, like the staff navbar) ── */
    .navbar{
      background:rgba(20,21,24,0.92);backdrop-filter:blur(10px);
      border-bottom:1px solid rgba(255,255,255,0.07);
      padding:0 36px;height:var(--navbar-h);display:flex;align-items:center;
      justify-content:space-between;position:sticky;top:0;z-index:200;
      gap:12px;
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
      letter-spacing:2.5px;line-height:1;white-space:nowrap;
      overflow:hidden;text-overflow:ellipsis;
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

    .navbar-right{display:flex;align-items:center;gap:14px;flex-shrink:0;}
    .role-chip{
      font-size:10px;font-weight:800;padding:5px 14px;border-radius:6px;
      letter-spacing:1.5px;text-transform:uppercase;white-space:nowrap;
    }
    /* Gold badge for admin & staff (same as the staff layout's STAFF badge) */
    .role-admin,.role-staff{
      background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));
      color:#1a1a1a;box-shadow:0 0 14px rgba(224,169,59,0.25);
    }
    .user-chip{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:#e4e5e8;white-space:nowrap;}
    .user-avatar{
      width:30px;height:30px;border-radius:50%;
      background:var(--accent-soft);border:1px solid rgba(224,169,59,0.3);
      display:flex;align-items:center;justify-content:center;
      font-weight:700;font-size:12px;color:var(--accent-2);overflow:hidden;flex-shrink:0;
    }
    .user-avatar img{width:100%;height:100%;object-fit:cover;}

    /* Clickable user chip → profile page (admin). Same pill + gold underline as .nav-item.active.
       Height is 34px (30px avatar + 2×2px) so the underline lands exactly where nav items' do. */
    .user-chip--link{
      padding:2px 12px 2px 4px;border-radius:8px;text-decoration:none;position:relative;
      transition:all 0.15s;
    }
    .user-chip--link:hover{background:rgba(255,255,255,0.05);color:#fff;}
    .user-chip--link:focus-visible{outline:2px solid var(--accent-2);outline-offset:2px;}
    .user-chip--link.active{background:rgba(255,255,255,0.05);color:#fff;font-weight:600;}
    .user-chip--link.active .user-avatar{border-color:var(--accent-2);}
    .user-chip--link.active::after{
      content:'';position:absolute;bottom:-11px;left:10px;right:10px;
      height:2px;background:var(--accent-2);border-radius:2px;
    }
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

    .nav-scrim{
      display:none;position:fixed;inset:0;top:var(--navbar-h);
      background:rgba(0,0,0,0.6);z-index:150;
    }
    .nav-scrim.show{display:block;}

    /* ── TOPBAR (page title + actions) ── */
    .page-topbar{
      background:rgba(0,0,0,0.25);border-bottom:1px solid var(--border);
      padding:0 36px;min-height:var(--topbar-h);display:flex;align-items:center;
      justify-content:space-between;flex-wrap:wrap;gap:10px;
    }
    .page-title{font-family:'DM Sans',sans-serif;font-size:15px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:16px 0;}
    #topbar-actions-slot{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}

    /* ── CONTENT ── */
    .page-content{max-width:1300px;margin:0 auto;padding:28px 36px;}
    /* Optional helper for headings like "Welcome, <span>Name</span>" */
    .page-content h1 span,.gold-text{color:var(--accent);}

    /* ── BUTTONS ── */
    .btn{padding:8px 18px;border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:1px solid transparent;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.15s;}
    .btn-primary{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;font-weight:700;}
    .btn-primary:hover{filter:brightness(1.08);transform:translateY(-1px);}
    /* Near-black button → fixed light text so it stays visible in light mode */
    .btn-secondary{background:#0e0e10;color:#ffffff;border-color:var(--border);}
    .btn-secondary:hover{color:var(--accent-2);border-color:rgba(224,169,59,0.45);}
    .btn-danger-soft{background:rgba(248,113,113,0.1);color:var(--danger);border:1px solid rgba(248,113,113,0.2);}
    .btn-danger-soft:hover{background:rgba(248,113,113,0.2);}
    .btn-sm{padding:5px 12px;font-size:12px;}

    /* ── STAT CARDS (glow style, same as the staff dashboard) ── */
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:28px;}
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
    .stat-label{font-size:10px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;}
    .stat-value{font-size:36px;font-weight:800;line-height:1;margin-bottom:4px;word-break:break-word;}
    .stat-sub{font-size:12px;color:var(--text-secondary);}
    .stat-up{color:var(--success);}

    /* ── CARD / TABLE ── */
    .card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
    .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:10px;flex-wrap:wrap;}
    .section-title{font-size:17px;font-weight:700;}
    .table-scroll{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;}
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
    .badge-pending,
    .badge-expiring {background:rgba(251,191,36,0.15);color:var(--warning);}
    .badge-paid     {background:rgba(74,222,128,0.15);color:var(--success);}
    .badge-monthly  {background:rgba(96,165,250,0.15);color:var(--info);}
    .badge-quarterly{background:rgba(167,139,250,0.15);color:#a78bfa;}
    .badge-annually,.badge-annual,.badge-yearly{background:rgba(224,169,59,0.15);color:var(--accent);}
    html:root[data-theme="light"] .badge-quarterly{background:rgba(109,79,216,0.10);color:#6d4fd8;}

    /* ── FORMS ── */
    .form-label{display:block;font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
    .form-control{width:100%;padding:11px 14px;background:var(--input-bg);border:1px solid var(--input-border);border-radius:10px;color:var(--text-primary);font-family:'DM Sans',sans-serif;font-size:16px;outline:none;transition:border-color 0.15s, box-shadow 0.15s;}
    .form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
    .form-control option{background:var(--input-bg);color:var(--text-primary);}
    textarea.form-control{resize:vertical;min-height:90px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .form-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:20px;}
    .form-card-title{font-size:14px;font-weight:600;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);}
    .form-group{margin-bottom:16px;}
    .form-page{max-width:640px;}

    /* ── ALERTS ── */
    .alert{padding:12px 16px;border-radius:var(--radius);font-size:13px;margin-bottom:20px;}
    .alert-success{background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.2);color:var(--success);}
    .alert-danger{background:rgba(248,113,113,0.08);border:1px solid rgba(248,113,113,0.2);color:var(--danger);}

    /* ── PLANS ── */
    .plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;}
    .plan-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;position:relative;}
    .plan-card.featured{border-color:var(--accent);}
    .plan-badge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;font-size:10px;font-weight:800;padding:3px 10px;border-radius:5px;letter-spacing:1px;white-space:nowrap;}
    .plan-name{font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:1px;margin-bottom:6px;}
    .plan-price{font-size:30px;font-weight:700;}
    .plan-period{font-size:12px;color:var(--text-secondary);}
    .plan-features{margin-top:14px;list-style:none;}
    .plan-features li{font-size:13px;color:var(--text-secondary);padding:5px 0;display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--border);}
    .plan-features li:last-child{border-bottom:none;}
    .plan-features li::before{content:'✓';color:var(--success);font-weight:700;font-size:12px;}

    /* ── MISC ── */
    .access-denied{text-align:center;padding:80px 20px;color:var(--text-secondary);}
    .access-denied h2{font-size:22px;font-weight:600;color:var(--text-primary);margin-bottom:8px;}
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;}

    /* ═══════════════════════════════════════════
       LIGHT MODE — GOLD & BLACK (mirrors layouts/staff.blade.php)
       ═══════════════════════════════════════════ */

    /* ── Navbar: black bar, light text, gold active (FIXED — same as staff) ── */
    html:root[data-theme="light"] .navbar{background:rgba(15,15,16,0.96);border-color:rgba(255,255,255,0.08);}
    html:root[data-theme="light"] .brand-icon{background:linear-gradient(145deg,#2a2a2c,#0b0b0c);border-color:rgba(224,169,59,0.45);}
    html:root[data-theme="light"] .brand-icon svg{stroke:var(--accent-2);}
    html:root[data-theme="light"] .brand-name,
    html:root[data-theme="light"] .user-chip{color:#f1f1f3;}

    /* !important on nav link colours: legacy app.css rules for .nav-item otherwise win */
    html:root[data-theme="light"] .nav-item{color:#c6c8ce !important;}
    html:root[data-theme="light"] .nav-item svg{stroke:#c6c8ce;}
    html:root[data-theme="light"] .nav-item:hover{color:#fff !important;background:rgba(255,255,255,0.07);}
    html:root[data-theme="light"] .nav-item:hover svg{stroke:#fff;}
    html:root[data-theme="light"] .nav-item.active{color:#fff !important;background:rgba(255,255,255,0.07);}
    html:root[data-theme="light"] .nav-item.active svg{stroke:var(--accent-2);}
    html:root[data-theme="light"] .nav-item.active::after{background:var(--accent-2);}

   html:root[data-theme="light"] .role-admin,
   html:root[data-theme="light"] .role-staff{
   background:linear-gradient(135deg,var(--accent-2),var(--accent-dark)) !important;
   color:#111 !important;
   border:1px solid rgba(0,0,0,0.15);
   display:block;
   box-shadow:none;
}
    html:root[data-theme="light"] .user-avatar{
      background:rgba(224,169,59,0.14);border-color:rgba(224,169,59,0.45);color:var(--accent-2);
    }
    html:root[data-theme="light"] .btn-logout-top{
      background:transparent;border-color:rgba(248,113,113,0.45);color:#f87171;
    }
    html:root[data-theme="light"] .btn-logout-top:hover{background:rgba(248,113,113,0.12);}
    html:root[data-theme="light"] .nav-toggle{border-color:rgba(255,255,255,0.15);color:#fff;}

    html:root[data-theme="light"] .page-topbar{background:rgba(255,255,255,0.6);}
    html:root[data-theme="light"] .page-title{border-left:3px solid var(--accent-2);padding-left:12px;}

    /* ── Stat cards ── */
    html:root[data-theme="light"] .stat-card{background:var(--bg-card);border:1px solid var(--border);box-shadow:var(--shadow-card);}
    html:root[data-theme="light"] .stat-card.green{--glow:17,17,17;}
    html:root[data-theme="light"] .stat-card.orange{--glow:180,83,9;}
    html:root[data-theme="light"] .stat-card.blue{--glow:37,99,235;}
    html:root[data-theme="light"] .stat-card.yellow,
    html:root[data-theme="light"] .stat-card.gold{--glow:184,134,42;}
    html:root[data-theme="light"] :is(.card,.form-card,.plan-card){box-shadow:var(--shadow-card);}

    /* ── Tables ── */
    html:root[data-theme="light"] th{background:var(--black);color:var(--accent-2);}
    html:root[data-theme="light"] tr:hover td{background:rgba(184,134,42,0.06);}

    /* ── Buttons ── */
    html:root[data-theme="light"] .btn-primary{color:#111;box-shadow:0 1px 2px rgba(20,16,8,.15);}
    html:root[data-theme="light"] .btn-primary:hover{box-shadow:0 6px 18px rgba(184,134,42,.35);}
    html:root[data-theme="light"] .btn-secondary{background:var(--black);color:#fff;border-color:var(--black);}
    html:root[data-theme="light"] .btn-secondary:hover{color:var(--accent-2);border-color:var(--accent-2);}
    html:root[data-theme="light"] .btn-danger-soft{background:rgba(220,38,38,.07);border-color:rgba(220,38,38,.25);}

    /* ── Badges ── */
    html:root[data-theme="light"] .badge-active,
    html:root[data-theme="light"] .badge-paid{background:rgba(21,128,61,.10);}
    html:root[data-theme="light"] .badge-pending,
    html:root[data-theme="light"] .badge-expiring{background:rgba(180,83,9,.10);}
    html:root[data-theme="light"] .badge-expired{background:rgba(220,38,38,.09);}
    html:root[data-theme="light"] .badge-monthly{background:rgba(37,99,235,.10);}
    html:root[data-theme="light"] :is(.badge-annually,.badge-annual,.badge-yearly){background:rgba(184,134,42,.14);color:var(--gold);}

    /* ── Forms ── */
    html:root[data-theme="light"] .form-control:focus{border-color:var(--accent-dark);}
    html:root[data-theme="light"] input[type="date"]{color-scheme:light !important;}

    /* Floating theme toggle (created by app.js) */
    html:root[data-theme="light"] .theme-toggle{
      background:var(--black);color:var(--accent-2);
      border-color:rgba(224,169,59,.4);box-shadow:var(--shadow-card);
    }

    /* ═══════════════════════════════════════════
       RESPONSIVE — TABLET (≤ 1024px)
       ═══════════════════════════════════════════ */
    @media (max-width:1024px){
      .navbar{padding:0 20px;}
      .nav-item{padding:8px 10px;}
      .page-topbar{padding:0 20px;}
      .page-content{padding:24px 20px;}
      .plan-grid{grid-template-columns:repeat(2,1fr);}
      .two-col{grid-template-columns:1fr;}
      .user-chip span.user-name-text{display:none;}
      .user-chip--link{padding:2px;}
      .user-chip--link.active::after{left:2px;right:2px;}
    }

    /* ═══════════════════════════════════════════
       RESPONSIVE — MOBILE (≤ 768px)
       ═══════════════════════════════════════════ */
    @media (max-width:768px){
      .navbar{padding:0 14px;gap:8px;}
      .brand-name{font-size:13px;letter-spacing:2px;}
      .brand-icon{width:28px;height:28px;}

      .nav-toggle{display:flex;order:3;}

      .navbar-nav{
        position:fixed;top:var(--navbar-h);left:0;right:0;
        background:#131417;border-bottom:1px solid rgba(255,255,255,0.08);
        flex-direction:column;align-items:stretch;gap:0;
        max-height:0;overflow:hidden;z-index:150;
        transition:max-height 0.25s ease;
      }
      .navbar-nav.open{max-height:calc(100vh - var(--navbar-h));overflow-y:auto;}
      .nav-item{padding:14px 20px;border-radius:0;border-bottom:1px solid rgba(255,255,255,0.08);border-left:3px solid transparent;width:100%;}
      .nav-item.active{border-left-color:var(--accent-2);background:var(--accent-soft);}
      .nav-item.active::after{display:none;}

      /* Light mode: dropdown stays black so the light nav text remains readable */
      html:root[data-theme="light"] .navbar-nav{background:#111112;border-bottom-color:rgba(255,255,255,0.08);}
      html:root[data-theme="light"] .nav-item{border-bottom-color:rgba(255,255,255,0.08);}
      html:root[data-theme="light"] .nav-item.active{border-left-color:var(--accent-2);background:var(--accent-soft);}

      .navbar-right{gap:8px;}
      .role-chip{display:none;}
      .user-chip{gap:0;}
      .btn-logout-top span.logout-text{display:none;}
      .btn-logout-top{padding:8px;}

      .page-topbar{padding:12px 14px;}
      .page-title{font-size:14px;padding:4px 0;}
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

{{-- Pages may set either @section('active_nav', ...) or the older @section('active', ...) --}}
@php $activeNav = View::getSection('active_nav') ?? View::getSection('active') ?? ''; @endphp

{{-- ── MAIN NAVBAR ── --}}
<nav class="navbar">

  {{-- Brand --}}
  <a href="{{ auth()->user()->isStaff() ? route('staff.dashboard') : route('dashboard') }}" class="navbar-brand">
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
      <a href="{{ route('reports.index') }}"
         class="nav-item {{ $activeNav === 'reports' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h6M9 17h6"/>
        </svg>
        Reports
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
      <a href="{{ route('reports.index') }}"
         class="nav-item {{ $activeNav === 'reports' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h6M9 17h6"/>
        </svg>
        Reports
      </a>
      <a href="{{ route('users.index') }}"
         class="nav-item {{ $activeNav === 'users' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Manage Users
      </a>
      {{-- Audit Trail (strict admin only) --}}
      @if(auth()->user()->isAdmin())
      <a href="{{ route('admin.audit.index') }}"
         class="nav-item {{ $activeNav === 'audit' ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v5h5M12 7v5l3 2"/>
        </svg>
        Audit Trail
      </a>
      @endif
    @endif

  </div>

  {{-- Right side -- role chip + user + logout --}}
  <div class="navbar-right">
    <span class="role-chip {{ auth()->user()->isAdmin() ? 'role-admin' : 'role-staff' }}">
      {{ strtoupper(auth()->user()->role) }}
    </span>
    @php $chipIsLink = ! auth()->user()->isStaff(); @endphp
    <{{ $chipIsLink ? 'a' : 'div' }} class="user-chip{{ $chipIsLink ? ' user-chip--link' : '' }}{{ $chipIsLink && $activeNav === 'admin.profile' ? ' active' : '' }}"
      @if($chipIsLink)
        href="{{ route('admin.profile') }}" title="My Profile"
        @if($activeNav === 'admin.profile') aria-current="page" @endif
      @endif>
      <div class="user-avatar">
        @if(auth()->user()->photo)
          <img src="{{ asset('storage/'.auth()->user()->photo) }}" alt="{{ auth()->user()->name }}"/>
        @else
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        @endif
      </div>
      <span class="user-name-text">{{ auth()->user()->name }}</span>
    </{{ $chipIsLink ? 'a' : 'div' }}>
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