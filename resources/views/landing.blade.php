<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IRONFORGE – Ultimate Gym Management</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow+Condensed:wght@400;600;700;800;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;scroll-behavior:smooth;}
body{background:#0e1108;color:#fff;font-family:'DM Sans',sans-serif;overflow-x:hidden;cursor:none;}

.cursor{width:10px;height:10px;background:#c8ff00;border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9999;mix-blend-mode:difference;transition:transform 0.15s;}
.cursor-ring{width:36px;height:36px;border:1.5px solid rgba(200,255,0,0.5);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9998;}

.scroll-dots{position:fixed;right:28px;top:50%;transform:translateY(-50%);z-index:100;display:flex;flex-direction:column;gap:12px;align-items:center;}
.scroll-dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.25);border:1.5px solid rgba(255,255,255,0.3);cursor:pointer;transition:all 0.3s ease;position:relative;}
.scroll-dot::after{content:attr(data-label);position:absolute;right:18px;top:50%;transform:translateY(-50%);background:rgba(14,17,8,0.9);border:1px solid rgba(200,255,0,0.3);color:#c8ff00;font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;padding:4px 10px;border-radius:4px;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity 0.2s;}
.scroll-dot:hover::after{opacity:1;}
.scroll-dot.active{background:#c8ff00;border-color:#c8ff00;width:10px;height:10px;box-shadow:0 0 8px rgba(200,255,0,0.5);}
.scroll-dot:hover{background:rgba(200,255,0,0.5);border-color:#c8ff00;}

.hero{position:relative;width:100%;height:100vh;min-height:600px;display:flex;flex-direction:column;overflow:hidden;}
.hero-image{position:absolute;inset:0;background-image:url("statue3.png");background-size:cover;background-position:center right;background-repeat:no-repeat;}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(100deg,rgba(14,17,8,0.97) 0%,rgba(14,17,8,0.90) 28%,rgba(14,17,8,0.55) 52%,rgba(14,17,8,0.05) 100%),linear-gradient(180deg,rgba(14,17,8,0.45) 0%,transparent 18%,transparent 72%,rgba(14,17,8,0.88) 100%);}

nav{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:20px 48px;flex-shrink:0;}
.nav-left{display:flex;align-items:center;gap:12px;}
.nav-logo{width:40px;height:40px;background:#c8ff00;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.nav-logo svg{width:22px;height:22px;}
.nav-brand{font-family:'Barlow Condensed',sans-serif;font-size:20px;font-weight:700;letter-spacing:3px;color:#fff;text-transform:uppercase;}
.nav-center{display:flex;align-items:center;gap:6px;border:1px solid rgba(200,255,0,0.4);border-radius:100px;padding:7px 18px;}
.nav-center-dot{width:6px;height:6px;background:#c8ff00;border-radius:50%;animation:blink 2s infinite;}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:0.3;}}
.nav-center-text{font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:600;letter-spacing:3px;color:#c8ff00;text-transform:uppercase;}
.nav-right{display:flex;align-items:center;gap:10px;}
.btn-nav-login{padding:10px 24px;background:transparent;border:1px solid rgba(255,255,255,0.3);border-radius:8px;color:#fff;font-family:'Barlow Condensed',sans-serif;font-size:14px;font-weight:600;letter-spacing:1px;cursor:pointer;transition:all 0.2s;text-transform:uppercase;}
.btn-nav-login:hover{border-color:#c8ff00;color:#c8ff00;}
.btn-nav-register{padding:10px 24px;background:#c8ff00;border:none;border-radius:8px;color:#111;font-family:'Barlow Condensed',sans-serif;font-size:14px;font-weight:800;letter-spacing:1px;cursor:pointer;transition:all 0.2s;text-transform:uppercase;}
.btn-nav-register:hover{background:#b8ef00;transform:translateY(-1px);}

.hero-content{position:relative;z-index:5;flex:1;display:flex;flex-direction:column;justify-content:center;padding:0 48px 48px;max-width:680px;}
.hero-title{font-family:'Barlow Condensed',sans-serif;font-size:clamp(60px,10.5vw,148px);font-weight:900;line-height:0.88;letter-spacing:-2px;text-transform:uppercase;color:#fff;margin-bottom:28px;animation:fadeUp 0.5s ease both;}
.hero-title span{color:#c8ff00;}
.hero-features{display:flex;flex-direction:column;gap:10px;margin-bottom:32px;animation:fadeUp 0.5s 0.15s ease both;}
.hero-feature{display:flex;align-items:center;gap:14px;}
.feature-divider{width:1px;height:20px;background:rgba(200,255,0,0.4);}
.feature-icon-box{width:30px;height:30px;border:1px solid rgba(200,255,0,0.35);border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.feature-icon-box svg{width:14px;height:14px;stroke:#c8ff00;}
.feature-text{font-family:'Barlow Condensed',sans-serif;font-size:15px;font-weight:700;letter-spacing:3px;color:rgba(255,255,255,0.85);text-transform:uppercase;}
.hero-cta{display:flex;gap:14px;align-items:center;animation:fadeUp 0.5s 0.25s ease both;}
.btn-join{padding:16px 34px;background:#c8ff00;color:#111;border:none;border-radius:10px;font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:900;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all 0.2s;}
.btn-join:hover{background:#b8ef00;transform:translateY(-2px);box-shadow:0 10px 30px rgba(200,255,0,0.25);}
.btn-login-hero{padding:16px 34px;background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,0.25);border-radius:10px;font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;gap:8px;}
.btn-login-hero:hover{border-color:rgba(255,255,255,0.6);}
.hero-diamond{position:absolute;bottom:24px;right:48px;z-index:10;display:flex;align-items:center;gap:8px;}
.diamond{width:28px;height:28px;background:#c8ff00;transform:rotate(45deg);border-radius:3px;animation:pulse-diamond 3s ease-in-out infinite;}
.diamond-sm{width:18px;height:18px;background:rgba(200,255,0,0.4);transform:rotate(45deg);border-radius:2px;margin-left:-10px;}
@keyframes pulse-diamond{0%,100%{opacity:1;transform:rotate(45deg) scale(1);}50%{opacity:0.7;transform:rotate(45deg) scale(0.9);}}
@keyframes fadeUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.88);backdrop-filter:blur(10px);z-index:200;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.25s;}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{background:#111;border:1px solid rgba(200,255,0,0.15);border-radius:20px;width:100%;max-width:440px;padding:36px 40px;position:relative;transform:translateY(20px) scale(0.97);transition:all 0.25s;max-height:90vh;overflow-y:auto;}
.modal-overlay.open .modal{transform:translateY(0) scale(1);}
.modal-close{position:absolute;top:16px;right:16px;width:32px;height:32px;background:#1a1a1a;border:1px solid rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#666;font-size:18px;transition:all 0.15s;}
.modal-close:hover{color:#fff;border-color:#c8ff00;}
.modal-logo-row{display:flex;align-items:center;gap:8px;margin-bottom:20px;}
.modal-logo-icon{width:28px;height:28px;background:#c8ff00;border-radius:6px;display:flex;align-items:center;justify-content:center;}
.modal-logo-icon svg{width:16px;height:16px;}
.modal-logo-name{font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;letter-spacing:3px;color:#c8ff00;text-transform:uppercase;}
.modal-tabs{display:flex;gap:4px;background:#1a1a1a;border-radius:10px;padding:4px;margin-bottom:24px;}
.modal-tab{flex:1;padding:9px;text-align:center;font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;border-radius:7px;color:#555;transition:all 0.15s;border:none;background:transparent;}
.modal-tab.active{background:#c8ff00;color:#111;}
.modal-heading{font-family:'Barlow Condensed',sans-serif;font-size:32px;font-weight:900;letter-spacing:2px;margin-bottom:4px;text-transform:uppercase;}
.modal-sub{font-size:13px;color:#555;margin-bottom:20px;}
.form-panel{display:none;}
.form-panel.active{display:block;}
.fm-group{margin-bottom:14px;}
.fm-label{display:block;font-size:11px;font-weight:600;color:#555;margin-bottom:10px;letter-spacing:2px;text-transform:uppercase;}
.fm-input{width:100%;padding:11px 14px;background:#1a1a1a;border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:#fff;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
.fm-input:focus{border-color:#c8ff00;}
.fm-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.btn-submit{width:100%;padding:14px;background:#c8ff00;color:#111;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:900;letter-spacing:3px;text-transform:uppercase;border:none;border-radius:10px;cursor:pointer;margin-top:8px;transition:all 0.2s;}
.btn-submit:hover{background:#b8ef00;transform:translateY(-1px);}
.fm-footer{text-align:center;font-size:13px;color:#555;margin-top:14px;}
.fm-footer a{color:#c8ff00;text-decoration:none;cursor:pointer;}
.fm-error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#ef4444;}
.fm-member-badge{
  display:flex;align-items:center;gap:10px;
  background:rgba(200,255,0,0.06);
  border:1px solid rgba(200,255,0,0.2);
  border-radius:10px;padding:10px 14px;
  margin-bottom:18px;
}
.fm-member-badge svg{width:16px;height:16px;stroke:#c8ff00;flex-shrink:0;}
.fm-member-badge-text{font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:#c8ff00;}

/* SECTIONS */
.section{padding:80px 60px;position:relative;}
.section-dark{background:#0a0c05;}
.section-mid{background:#0e1108;}
.section-eyebrow{font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:600;letter-spacing:4px;color:#c8ff00;text-transform:uppercase;margin-bottom:10px;}
.section-title{font-family:'Barlow Condensed',sans-serif;font-size:clamp(36px,5vw,60px);font-weight:900;letter-spacing:1px;line-height:1;text-transform:uppercase;margin-bottom:48px;}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:rgba(255,255,255,0.05);}
.feat-card{background:#0e1108;padding:36px 32px;border-top:2px solid transparent;transition:all 0.3s;}
.feat-card:hover{background:#141808;border-top-color:#c8ff00;}
.feat-card:hover .feat-icon-wrap{background:rgba(200,255,0,0.12);}
.feat-icon-wrap{width:44px;height:44px;background:rgba(200,255,0,0.06);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:18px;transition:background 0.3s;}
.feat-icon-wrap svg{width:22px;height:22px;stroke:#c8ff00;fill:none;}
.feat-title{font-family:'Barlow Condensed',sans-serif;font-size:20px;font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:10px;}
.feat-desc{font-size:14px;color:#555;line-height:1.7;}
.plans-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px;}
.plan-card{background:#0a0c05;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:32px 28px;position:relative;transition:all 0.3s;}
.plan-card:hover{transform:translateY(-4px);border-color:rgba(200,255,0,0.2);}
.plan-card.hot{border-color:#c8ff00;background:linear-gradient(135deg,rgba(200,255,0,0.04),transparent);}
.plan-hot-badge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:#c8ff00;color:#111;font-size:10px;font-weight:900;letter-spacing:2px;text-transform:uppercase;padding:3px 14px;border-radius:100px;white-space:nowrap;font-family:'Barlow Condensed',sans-serif;}
.plan-label{font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:#555;margin-bottom:6px;}
.plan-price{font-family:'Barlow Condensed',sans-serif;font-size:52px;font-weight:900;line-height:1;margin-bottom:4px;}
.plan-dur{font-size:13px;color:#444;margin-bottom:24px;}
.plan-feats{list-style:none;margin-bottom:28px;}
.plan-feats li{font-size:13px;color:#555;padding:7px 0;border-bottom:1px solid rgba(255,255,255,0.05);display:flex;align-items:center;gap:8px;}
.plan-feats li:last-child{border:none;}
.plan-feats li::before{content:"✓";color:#c8ff00;font-weight:700;font-size:11px;flex-shrink:0;}
.btn-plan{width:100%;padding:13px;border-radius:9px;font-family:'Barlow Condensed',sans-serif;font-size:14px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;border:none;transition:all 0.2s;}
.btn-plan-ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.1);}
.btn-plan-ghost:hover{border-color:#c8ff00;color:#c8ff00;}
.btn-plan-solid{background:#c8ff00;color:#111;}
.btn-plan-solid:hover{background:#b8ef00;}
footer{padding:48px 60px 32px;border-top:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:space-between;}
.footer-name{font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;letter-spacing:3px;color:#444;text-transform:uppercase;}
.footer-name span{color:#c8ff00;}
.footer-copy{font-size:12px;color:#333;}
.reveal{opacity:0;transform:translateY(24px);transition:all 0.5s ease;}
.reveal.visible{opacity:1;transform:translateY(0);}
body::after{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.025'/%3E%3C/svg%3E");pointer-events:none;z-index:500;opacity:0.35;}
</style>
</head>
<body>

<div class="scroll-dots" id="scrollDots">
  <div class="scroll-dot active" data-target="hero" data-label="HOME" onclick="scrollToSection('hero')"></div>
  <div class="scroll-dot" data-target="features" data-label="FEATURES" onclick="scrollToSection('features')"></div>
  <div class="scroll-dot" data-target="plans" data-label="PLANS" onclick="scrollToSection('plans')"></div>
  <div class="scroll-dot" data-target="footer-section" data-label="CONTACT" onclick="scrollToSection('footer-section')"></div>
</div>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<section class="hero" id="hero">
  <div class="hero-image"></div>
  <div class="hero-overlay"></div>
  <nav>
    <div class="nav-left">
      <div class="nav-logo"><svg fill="none" stroke="#111" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
      <span class="nav-brand">IRONFORGE</span>
    </div>
    <div class="nav-center"><div class="nav-center-dot"></div><span class="nav-center-text">Gym Management System</span></div>
    <div class="nav-right">
      <button class="btn-nav-login" onclick="openModal('login')">Log In</button>
      <button class="btn-nav-register" onclick="openModal('register')">Get Started</button>
    </div>
  </nav>
  <div class="hero-content">
    <h1 class="hero-title">MAKE YOUR<br>BODY<br><span>STRONGER.</span></h1>
    <div class="hero-features">
      <div class="hero-feature"><div class="feature-icon-box"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div><div class="feature-divider"></div><span class="feature-text">Secure Access</span></div>
      <div class="hero-feature"><div class="feature-icon-box"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg></div><div class="feature-divider"></div><span class="feature-text">Full Dashboard</span></div>
      <div class="hero-feature"><div class="feature-icon-box"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><div class="feature-divider"></div><span class="feature-text">Member Mgmt</span></div>
    </div>
    <div class="hero-cta">
      <button class="btn-join" onclick="openModal('register')">JOIN NOW</button>
      <button class="btn-login-hero" onclick="openModal('login')">LOGIN <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></button>
    </div>
  </div>
  <div class="hero-diamond"><div class="diamond"></div><div class="diamond-sm"></div></div>
</section>

<section class="section section-mid" id="features">
  <div class="reveal"><div class="section-eyebrow">Why IRONFORGE</div><div class="section-title">EVERYTHING YOU NEED</div></div>
  <div class="features-grid reveal">
    <div class="feat-card"><div class="feat-icon-wrap"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><div class="feat-title">Member Management</div><p class="feat-desc">Complete CRUD with photo upload, personal info, membership plans, and auto end-date calculation.</p></div>
    <div class="feat-card"><div class="feat-icon-wrap"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div><div class="feat-title">Payment Tracking</div><p class="feat-desc">Record payments via Cash, GCash, or Bank Transfer. Track monthly and total revenue in real time.</p></div>
    <div class="feat-card"><div class="feat-icon-wrap"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div><div class="feat-title">Role-Based Access</div><p class="feat-desc">Admin, Staff, Instructor, and Member roles — each seeing only what they need.</p></div>
    <div class="feat-card"><div class="feat-icon-wrap"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div><div class="feat-title">Membership Plans</div><p class="feat-desc">Monthly, Quarterly, and Annual plans with auto-computed fees and due dates.</p></div>
    <div class="feat-card"><div class="feat-icon-wrap"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div><div class="feat-title">Live Dashboard</div><p class="feat-desc">Real-time stats: active members, expired accounts, revenue this month, and recent registrations.</p></div>
    <div class="feat-card"><div class="feat-icon-wrap"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div><div class="feat-title">Laravel Breeze Auth</div><p class="feat-desc">Secure authentication with protected routes, session handling, and role-based redirects.</p></div>
  </div>
</section>

<section class="section section-dark" id="plans">
  <div class="reveal"><div class="section-eyebrow">Membership Tiers</div><div class="section-title">CHOOSE YOUR PLAN</div></div>
  <div class="plans-grid reveal">
    <div class="plan-card"><div class="plan-label">Monthly</div><div class="plan-price">₱800</div><div class="plan-dur">/ 30 days</div><ul class="plan-feats"><li>Full gym access</li><li>Locker included</li><li>4 trainer sessions</li><li>Group classes</li></ul><button class="btn-plan btn-plan-ghost" onclick="openModal('register')">Get Started</button></div>
    <div class="plan-card hot"><div class="plan-hot-badge">Most Popular</div><div class="plan-label">Quarterly</div><div class="plan-price" style="color:#c8ff00;">₱2,100</div><div class="plan-dur">/ 90 days</div><ul class="plan-feats"><li>Full gym access</li><li>Locker included</li><li>12 trainer sessions</li><li>Group classes</li><li>Save ₱300 vs monthly</li></ul><button class="btn-plan btn-plan-solid" onclick="openModal('register')">Get Started</button></div>
    <div class="plan-card"><div class="plan-label">Annual</div><div class="plan-price">₱7,500</div><div class="plan-dur">/ 365 days</div><ul class="plan-feats"><li>Full gym access</li><li>Locker included</li><li>Unlimited sessions</li><li>Group classes</li><li>2 guest passes/month</li></ul><button class="btn-plan btn-plan-ghost" onclick="openModal('register')">Get Started</button></div>
  </div>
</section>

<footer id="footer-section">
  <div class="footer-name">IRON<span>FORGE</span> GMS</div>
  <div class="footer-copy">© 2026 IRONFORGE. Built with Laravel + Breeze.</div>
</footer>

<!-- ══════════════════════════════════════════════
     MODAL  —  role selectors REMOVED
     Registration always creates role = member
     Login determines role from backend only
     ══════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalOverlay" onclick="handleOverlay(event)">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">×</button>
    <div class="modal-logo-row">
      <div class="modal-logo-icon"><svg fill="none" stroke="#111" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
      <div class="modal-logo-name">IRONFORGE</div>
    </div>
    <div class="modal-tabs">
      <button class="modal-tab active" id="tab-login" onclick="switchTab('login')">Login</button>
      <button class="modal-tab" id="tab-register" onclick="switchTab('register')">Register</button>
    </div>

    <!-- ── LOGIN ── -->
    <div class="form-panel active" id="panel-login">
      <div class="modal-heading">WELCOME BACK</div>
      <div class="modal-sub">Sign in to your account</div>

      @if($errors->has('email') && old('_form') === 'login')
        <div class="fm-error">{{ $errors->first('email') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf
        {{-- Hidden field so we can identify which form errored --}}
        <input type="hidden" name="_form" value="login"/>

        <div class="fm-group">
          <label class="fm-label">Email</label>
          <input type="email" name="email" class="fm-input"
                 value="{{ old('email') }}"
                 placeholder="you@example.com" required autofocus/>
        </div>
        <div class="fm-group">
          <label class="fm-label">Password</label>
          <input type="password" name="password" class="fm-input"
                 placeholder="••••••••" required/>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
          <a href="/forgot-password" style="font-size:12px;color:#444;text-decoration:none;">
            Forgot password?
          </a>
        </div>
        <button type="submit" class="btn-submit">Sign In →</button>
      </form>
      <div class="fm-footer">No account? <a onclick="switchTab('register')">Create one</a></div>
    </div>

    <!-- ── REGISTER ── -->
    <div class="form-panel" id="panel-register">
      <div class="modal-heading">JOIN NOW</div>
      <div class="modal-sub">Create your free account</div>

      {{-- Member role badge --}}
      <div class="fm-member-badge">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="4" r="2"/>
          <line x1="12" y1="6" x2="12" y2="13"/>
          <line x1="8" y1="9" x2="16" y2="9"/>
          <line x1="12" y1="13" x2="9" y2="20"/>
          <line x1="12" y1="13" x2="15" y2="20"/>
        </svg>
        <span class="fm-member-badge-text">Registering as Member</span>
      </div>

      @if($errors->any() && old('_form') === 'register')
        <div class="fm-error">
          @foreach($errors->all() as $error)
            {{ $error }}<br>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('register') }}">
        @csrf
        {{-- Hidden field so we can identify which form errored --}}
        <input type="hidden" name="_form" value="register"/>
        {{-- Role is always member — set on backend, NOT from UI --}}

        <div class="fm-group">
          <label class="fm-label">Full Name</label>
          <input type="text" name="name" class="fm-input"
                 value="{{ old('name') }}"
                 placeholder="Juan Dela Cruz" required/>
        </div>
        <div class="fm-group">
          <label class="fm-label">Email</label>
          <input type="email" name="email" class="fm-input"
                 value="{{ old('email') }}"
                 placeholder="juan@email.com" required/>
        </div>
        <div class="fm-row">
          <div class="fm-group">
            <label class="fm-label">Password</label>
            <input type="password" name="password" class="fm-input"
                   placeholder="••••••••" required/>
          </div>
          <div class="fm-group">
            <label class="fm-label">Confirm</label>
            <input type="password" name="password_confirmation" class="fm-input"
                   placeholder="••••••••" required/>
          </div>
        </div>
        <button type="submit" class="btn-submit">Create Account →</button>
      </form>
      <div class="fm-footer">Have an account? <a onclick="switchTab('login')">Sign in</a></div>
    </div>

  </div>
</div>

<script>
const cursor=document.getElementById('cursor'),ring=document.getElementById('cursorRing');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cursor.style.left=mx-5+'px';cursor.style.top=my-5+'px';});
(function tick(){rx+=(mx-rx-18)*0.1;ry+=(my-ry-18)*0.1;ring.style.left=rx+'px';ring.style.top=ry+'px';requestAnimationFrame(tick);})();
document.querySelectorAll('button,a,input,label').forEach(el=>{
  el.addEventListener('mouseenter',()=>cursor.style.transform='scale(2.5)');
  el.addEventListener('mouseleave',()=>cursor.style.transform='scale(1)');
});

function scrollToSection(id){const el=document.getElementById(id);if(el)el.scrollIntoView({behavior:'smooth'});}
const sections=[{id:'hero'},{id:'features'},{id:'plans'},{id:'footer-section'}];
function updateDots(){
  const dots=document.querySelectorAll('.scroll-dot');let active=0;
  sections.forEach((s,i)=>{const el=document.getElementById(s.id);if(el&&el.getBoundingClientRect().top<=window.innerHeight*0.5)active=i;});
  dots.forEach((d,i)=>d.classList.toggle('active',i===active));
}
window.addEventListener('scroll',updateDots,{passive:true});updateDots();

function openModal(tab){document.getElementById('modalOverlay').classList.add('open');switchTab(tab);document.body.style.overflow='hidden';}
function closeModal(){document.getElementById('modalOverlay').classList.remove('open');document.body.style.overflow='';}
function handleOverlay(e){if(e.target===document.getElementById('modalOverlay'))closeModal();}
function switchTab(tab){
  document.querySelectorAll('.modal-tab').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.form-panel').forEach(p=>p.classList.remove('active'));
  document.getElementById('tab-'+tab).classList.add('active');
  document.getElementById('panel-'+tab).classList.add('active');
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});

// Re-open modal on validation error and restore correct tab
@if($errors->any())
  @if(old('_form') === 'login')
    openModal('login');
  @elseif(old('_form') === 'register')
    openModal('register');
  @endif
@endif

const obs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('visible');});},{threshold:0.1});
document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
</script>
</body>
</html>