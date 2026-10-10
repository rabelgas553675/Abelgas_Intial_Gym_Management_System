<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>APEX FITNESS GYM – Ultimate Gym Management</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow+Condensed:wght@400;600;700;800;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* Charcoal & gold — keep values in sync with layouts/member.blade.php */
:root{--accent:#e0a93b;--accent-2:#f0c060;--accent-dark:#b8862a;--accent-soft:rgba(224,169,59,0.12);--gutter:clamp(20px,4vw,64px);--header-height:76px;}
*{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{min-height:100vh;background:#0d0d0d;color:#fff;font-family:'DM Sans',sans-serif;overflow-x:hidden;cursor:none;}

.cursor{width:10px;height:10px;background:var(--accent);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9999;mix-blend-mode:difference;transition:transform 0.15s;}
.cursor-ring{width:36px;height:36px;border:1.5px solid rgba(224,169,59,0.5);border-radius:50%;position:fixed;top:0;left:0;pointer-events:none;z-index:9998;}

.scroll-dots{position:fixed;right:28px;top:50%;transform:translateY(-50%);z-index:100;display:flex;flex-direction:column;gap:12px;align-items:center;}
.scroll-dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.25);border:1.5px solid rgba(255,255,255,0.3);cursor:pointer;transition:all 0.3s ease;position:relative;}
.scroll-dot::after{content:attr(data-label);position:absolute;right:18px;top:50%;transform:translateY(-50%);background:rgba(13,13,13,0.92);border:1px solid rgba(224,169,59,0.3);color:var(--accent);font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;padding:4px 10px;border-radius:4px;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity 0.2s;}
.scroll-dot:hover::after{opacity:1;}
.scroll-dot.active{background:var(--accent);border-color:var(--accent);width:10px;height:10px;box-shadow:0 0 8px rgba(224,169,59,0.5);}
.scroll-dot:hover{background:rgba(224,169,59,0.5);border-color:var(--accent);}

/* HERO — min-height (never a fixed height) so content can always grow instead of being clipped */
.hero{position:relative;width:100%;min-height:max(100svh,620px);display:flex;flex-direction:column;overflow:hidden;}
.hero-image{position:absolute;inset:0;background-image:url("statue3.png");background-size:cover;background-position:right center;background-repeat:no-repeat;}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(100deg,rgba(13,13,13,0.94) 0%,rgba(13,13,13,0.78) 24%,rgba(13,13,13,0.30) 48%,rgba(13,13,13,0) 68%),linear-gradient(180deg,rgba(13,13,13,0.55) 0%,transparent 16%,transparent 78%,rgba(13,13,13,0.80) 100%);}

nav{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:clamp(10px,1.2vw,16px) var(--gutter);flex-shrink:0;}
.nav-left{display:flex;align-items:center;gap:12px;min-width:0;}
.nav-logo{width:44px;height:44px;background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.nav-logo svg{width:24px;height:24px;}
.nav-brand{font-family:'Barlow Condensed',sans-serif;font-size:clamp(17px,1.6vw,22px);font-weight:700;letter-spacing:3px;color:#fff;text-transform:uppercase;white-space:nowrap;}
.nav-center{display:flex;align-items:center;gap:8px;border:1px solid rgba(224,169,59,0.4);border-radius:100px;padding:8px 20px;flex-shrink:0;background:rgba(13,13,13,0.35);}
.nav-center-dot{width:6px;height:6px;background:var(--accent);border-radius:50%;animation:blink 2s infinite;}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:0.3;}}
.nav-center-text{font-family:'Barlow Condensed',sans-serif;font-size:14px;font-weight:600;letter-spacing:3px;color:var(--accent);text-transform:uppercase;white-space:nowrap;}
.nav-right{display:flex;align-items:center;gap:12px;flex-shrink:0;}
.btn-nav-login,.btn-nav-register{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 clamp(18px,2vw,28px);border-radius:8px;font-family:'Barlow Condensed',sans-serif;font-size:15px;letter-spacing:1.5px;text-transform:uppercase;cursor:pointer;transition:all 0.2s;white-space:nowrap;}
.btn-nav-login{background:rgba(13,13,13,0.4);border:1px solid rgba(255,255,255,0.35);color:#fff;font-weight:600;}
.btn-nav-login:hover{border-color:var(--accent);color:var(--accent);}
.btn-nav-register{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));border:none;color:#1a1a1a;font-weight:800;}
.btn-nav-register:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(224,169,59,0.3);}

/* Hero content fills exactly the space left under the header, and grows if it needs more */
.hero-content{position:relative;z-index:5;flex:1;width:60%;max-width:none;min-width:0;min-height:calc(100svh - var(--header-height));display:flex;flex-direction:column;justify-content:space-between;container-type:inline-size;padding:0 var(--gutter) clamp(20px,3vh,36px);}
/* Headline scales with BOTH width and height so the feature list always stays on screen */
.hero-title{width:100%;font-family:'Barlow Condensed',sans-serif;font-size:clamp(48px,min(11vw,calc((100svh - 370px) / 2.52)),260px);font-size:clamp(48px,min(22.5cqw,calc((100svh - 370px) / 2.52)),260px);white-space:nowrap;font-weight:900;line-height:0.84;letter-spacing:-0.012em;text-transform:uppercase;color:#fff;margin-bottom:clamp(14px,2vh,24px);animation:fadeUp 0.5s ease both;}
.hero-title span{display:block;color:var(--accent);white-space:nowrap;}
.hero-features{display:flex;flex-direction:column;gap:clamp(10px,1.5vh,16px);margin-bottom:clamp(20px,3.5vh,36px);animation:fadeUp 0.5s 0.15s ease both;}
.hero-feature{display:flex;align-items:center;gap:14px;}
.feature-divider{width:1px;height:24px;background:rgba(224,169,59,0.45);flex-shrink:0;}
.feature-icon-box{width:40px;height:40px;border:1px solid rgba(224,169,59,0.4);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(13,13,13,0.35);}
.feature-icon-box svg{width:20px;height:20px;stroke:var(--accent);}
.feature-text{font-family:'Barlow Condensed',sans-serif;font-size:clamp(17px,1.5vw,22px);font-weight:700;letter-spacing:3px;color:rgba(255,255,255,0.92);text-transform:uppercase;}
.hero-cta{display:flex;flex-wrap:wrap;gap:14px;align-items:center;animation:fadeUp 0.5s 0.25s ease both;}
.btn-join,.btn-login-hero{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:56px;padding:0 clamp(26px,3vw,38px);border-radius:10px;font-family:'Barlow Condensed',sans-serif;font-size:clamp(17px,1.5vw,20px);letter-spacing:2px;text-transform:uppercase;cursor:pointer;transition:all 0.2s;}
.btn-join{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;border:none;font-weight:900;}
.btn-join:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(224,169,59,0.3);}
.btn-login-hero{background:rgba(13,13,13,0.4);color:#fff;border:1.5px solid rgba(255,255,255,0.3);font-weight:700;}
.btn-login-hero:hover{border-color:var(--accent);color:var(--accent);}
.hero-diamond{position:absolute;bottom:24px;right:var(--gutter);z-index:10;display:flex;align-items:center;gap:8px;}
.diamond{width:28px;height:28px;background:var(--accent);transform:rotate(45deg);border-radius:3px;animation:pulse-diamond 3s ease-in-out infinite;}
.diamond-sm{width:18px;height:18px;background:rgba(224,169,59,0.4);transform:rotate(45deg);border-radius:2px;margin-left:-10px;}
@keyframes pulse-diamond{0%,100%{opacity:1;transform:rotate(45deg) scale(1);}50%{opacity:0.7;transform:rotate(45deg) scale(0.9);}}
@keyframes fadeUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}

/* While the login/register modal is open: normal system cursor, custom cursor effects off */
body.modal-open{cursor:auto;}
body.modal-open .cursor,body.modal-open .cursor-ring{display:none;}
.modal-overlay,.modal{cursor:auto;}
.modal button,.modal a,.modal .modal-tab,.modal .modal-close,.modal .fm-footer a{cursor:pointer;}
.modal .fm-input{cursor:text;}
/* keep dark inputs when the browser autofills saved credentials (no light-blue flash) */
.fm-input:-webkit-autofill,.fm-input:-webkit-autofill:hover,.fm-input:-webkit-autofill:focus{-webkit-box-shadow:0 0 0 1000px #1c1c1c inset;-webkit-text-fill-color:#fff;caret-color:#fff;transition:background-color 9999s ease-out 0s;}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.88);backdrop-filter:blur(10px);z-index:200;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.25s;}
.modal-overlay.open{opacity:1;pointer-events:all;}
.modal{background:#151515;border:1px solid rgba(224,169,59,0.25);border-radius:20px;width:100%;max-width:440px;padding:36px 40px;position:relative;transform:translateY(20px) scale(0.97);transition:all 0.25s;max-height:90vh;overflow-y:auto;}
.modal-overlay.open .modal{transform:translateY(0) scale(1);}
.modal-close{position:absolute;top:16px;right:16px;width:32px;height:32px;background:#1c1c1c;border:1px solid rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#666;font-size:18px;transition:all 0.15s;}
.modal-close:hover{color:#fff;border-color:var(--accent);}
.modal-logo-row{display:flex;align-items:center;gap:8px;margin-bottom:20px;}
.modal-logo-icon{width:28px;height:28px;background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));border-radius:6px;display:flex;align-items:center;justify-content:center;}
.modal-logo-icon svg{width:16px;height:16px;}
.modal-logo-name{font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;letter-spacing:3px;color:var(--accent);text-transform:uppercase;}
.modal-tabs{display:flex;gap:4px;background:#1c1c1c;border-radius:10px;padding:4px;margin-bottom:24px;}
.modal-tab{flex:1;padding:9px;text-align:center;font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;border-radius:7px;color:#666;transition:all 0.15s;border:none;background:transparent;}
.modal-tab.active{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;}
.modal-heading{font-family:'Barlow Condensed',sans-serif;font-size:32px;font-weight:900;letter-spacing:2px;margin-bottom:4px;text-transform:uppercase;}
.modal-sub{font-size:13px;color:#666;margin-bottom:20px;}
.form-panel{display:none;}
.form-panel.active{display:block;}
.fm-group{margin-bottom:14px;}
.fm-label{display:block;font-size:11px;font-weight:600;color:#777;margin-bottom:10px;letter-spacing:2px;text-transform:uppercase;}
.fm-input{width:100%;padding:11px 14px;background:#1c1c1c;border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:#fff;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
.fm-input:focus{border-color:var(--accent);}
.fm-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:900;letter-spacing:3px;text-transform:uppercase;border:none;border-radius:10px;cursor:pointer;margin-top:8px;transition:all 0.2s;}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(224,169,59,0.3);}
.fm-footer{text-align:center;font-size:13px;color:#666;margin-top:14px;}
.fm-footer a{color:var(--accent);text-decoration:none;cursor:pointer;}
.fm-error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#ef4444;}
.fm-success{background:rgba(74,222,128,0.10);border:1px solid rgba(74,222,128,0.30);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#4ade80;}
.fm-member-badge{display:flex;align-items:center;gap:10px;background:var(--accent-soft);border:1px solid rgba(224,169,59,0.25);border-radius:10px;padding:10px 14px;margin-bottom:18px;}
.fm-member-badge svg{width:16px;height:16px;stroke:var(--accent);flex-shrink:0;}
.fm-member-badge-text{font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:var(--accent);}

/* SECTIONS */
.section{padding:80px 60px;position:relative;}
.section-dark{background:#0a0a0a;}
.section-mid{background:#0d0d0d;}
.section-eyebrow{font-family:'Barlow Condensed',sans-serif;font-size:11px;font-weight:600;letter-spacing:4px;color:var(--accent);text-transform:uppercase;margin-bottom:10px;}
.section-title{font-family:'Barlow Condensed',sans-serif;font-size:clamp(36px,5vw,60px);font-weight:900;letter-spacing:1px;line-height:1;text-transform:uppercase;margin-bottom:48px;}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:rgba(255,255,255,0.05);}
.feat-card{background:#0d0d0d;padding:36px 32px;border-top:2px solid transparent;transition:all 0.3s;display:flex;flex-direction:column;min-width:0;}
.feat-card:hover{background:#1a1813;border-top-color:var(--accent);}
.feat-card:hover .feat-icon-wrap{background:rgba(224,169,59,0.18);border-color:var(--accent);}
.feat-icon-wrap{width:44px;height:44px;background:var(--accent-soft);border:1px solid rgba(224,169,59,0.18);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:18px;flex-shrink:0;transition:background 0.3s,border-color 0.3s;}
.feat-icon-wrap svg{width:22px;height:22px;stroke:var(--accent);fill:none;stroke-linecap:round;stroke-linejoin:round;}
.feat-title{font-family:'Barlow Condensed',sans-serif;font-size:clamp(18px,1.6vw,20px);font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:10px;overflow-wrap:anywhere;}
.feat-desc{font-size:14px;color:#8a8a8a;line-height:1.7;max-width:46ch;}
.plans-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px;}
.plan-card{background:#0a0a0a;border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:32px 28px;position:relative;transition:all 0.3s;}
.plan-card:hover{transform:translateY(-4px);border-color:rgba(224,169,59,0.3);}
.plan-card.hot{border-color:var(--accent);background:linear-gradient(135deg,rgba(224,169,59,0.06),transparent);}
.plan-hot-badge{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;font-size:10px;font-weight:900;letter-spacing:2px;text-transform:uppercase;padding:3px 14px;border-radius:100px;white-space:nowrap;font-family:'Barlow Condensed',sans-serif;}
.plan-label{font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:600;letter-spacing:3px;text-transform:uppercase;color:#777;margin-bottom:6px;}
.plan-price{font-family:'Barlow Condensed',sans-serif;font-size:52px;font-weight:900;line-height:1;margin-bottom:4px;}
.plan-dur{font-size:13px;color:#666;margin-bottom:24px;}
.plan-feats{list-style:none;margin-bottom:28px;}
.plan-feats li{font-size:13px;color:#888;padding:7px 0;border-bottom:1px solid rgba(255,255,255,0.05);display:flex;align-items:center;gap:8px;}
.plan-feats li:last-child{border:none;}
.plan-feats li::before{content:"✓";color:var(--accent);font-weight:700;font-size:11px;flex-shrink:0;}
.btn-plan{width:100%;padding:13px;border-radius:9px;font-family:'Barlow Condensed',sans-serif;font-size:14px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;border:none;transition:all 0.2s;}
.btn-plan-ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.1);}
.btn-plan-ghost:hover{border-color:var(--accent);color:var(--accent);}
.btn-plan-solid{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;}
.btn-plan-solid:hover{box-shadow:0 6px 20px rgba(224,169,59,0.3);}
footer{padding:48px 60px 32px;border-top:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:space-between;}
.footer-name{font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;letter-spacing:3px;color:#555;text-transform:uppercase;}
.footer-name span{color:var(--accent);}
.footer-copy{font-size:12px;color:#444;}
.reveal{opacity:0;transform:translateY(24px);transition:all 0.5s ease;}
.reveal.visible{opacity:1;transform:translateY(0);}
body::after{content:'';position:fixed;inset:0;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.025'/%3E%3C/svg%3E");pointer-events:none;z-index:500;opacity:0.35;}

/* RESPONSIVE */
@media (hover: none), (pointer: coarse){
  body{ cursor:auto; }
  .cursor, .cursor-ring{ display:none; }
}
/* Short laptop screens (e.g. 1366x768): features go in one row under the headline so the headline can stay huge */
@media (max-height: 800px) and (min-width: 641px){
  .hero-title{ font-size:clamp(48px,min(11vw,calc((100svh - 230px) / 2.52)),260px); font-size:clamp(48px,min(22.5cqw,calc((100svh - 230px) / 2.52)),260px); }
  .hero-features{ flex-direction:row; flex-wrap:wrap; gap:10px 22px; margin-bottom:18px; }
  .hero-feature{ gap:10px; }
  .feature-text{ font-size:16px; letter-spacing:2px; }
  .feature-divider{ display:none; }
  .feature-icon-box{ width:32px; height:32px; }
  .feature-icon-box svg{ width:16px; height:16px; }
  .btn-join, .btn-login-hero{ min-height:50px; }
  .hero-content{ padding-bottom:28px; }
}
@media (max-width: 1024px){
  .features-grid{ grid-template-columns:repeat(2,1fr); }
  .plans-grid{ grid-template-columns:repeat(2,1fr); }
  .section{ padding:60px 32px; }
  .hero-content{ width:100%; }
  .hero-content{ justify-content:flex-end; }
  .hero-title{ font-size:clamp(48px,min(18vw,calc((100svh - 370px) / 2.52)),220px); font-size:clamp(48px,min(22.5cqw,calc((100svh - 370px) / 2.52)),220px); }
  .hero-overlay{ background:linear-gradient(100deg,rgba(13,13,13,0.95) 0%,rgba(13,13,13,0.82) 30%,rgba(13,13,13,0.40) 58%,rgba(13,13,13,0.05) 85%),linear-gradient(180deg,rgba(13,13,13,0.55) 0%,transparent 16%,transparent 78%,rgba(13,13,13,0.80) 100%); }
}
@media (max-width: 960px){
  :root{ --header-height:132px; }
  nav{ flex-wrap:wrap; row-gap:12px; }
  .nav-center{ order:3; width:100%; justify-content:center; }
  .hero-diamond, .scroll-dots{ display:none; }
}
@media (max-width: 640px){
  .hero-image{ background-position:70% center; }
  .hero-overlay{ background:linear-gradient(180deg,rgba(13,13,13,0.40) 0%,rgba(13,13,13,0.62) 40%,rgba(13,13,13,0.95) 100%); }
  .hero-content{ justify-content:flex-end; padding-bottom:32px; }
  .hero-title{ font-size:clamp(48px,20vw,120px); font-size:clamp(48px,min(22.5cqw,calc((100svh - 330px) / 2.52)),130px); }
  .feature-text{ letter-spacing:2px; }
  .hero-cta{ flex-direction:column; align-items:stretch; }
  .btn-join, .btn-login-hero{ width:100%; }
  .nav-right{ display:none; }   /* header LOG IN / GET STARTED hidden on phones; hero JOIN NOW + LOGIN remain */
  .nav-brand{ font-size:16px; letter-spacing:2px; }
  .nav-center-text{ font-size:12px; letter-spacing:2px; }
  .section{ padding:48px 20px; }
  .section-title{ margin-bottom:32px; }
  .features-grid, .plans-grid{ grid-template-columns:1fr; }
  .feat-card, .plan-card{ padding:28px 24px; }
  .plan-price{ font-size:44px; }
  .modal{ padding:28px 22px; max-width:92vw; max-height:90dvh; border-radius:16px; }
  .modal-heading{ font-size:26px; }
  .fm-row{ grid-template-columns:1fr; gap:14px; }
  .fm-input{ font-size:16px; } /* stops iOS zoom on focus */
  footer{ flex-direction:column; gap:10px; padding:32px 24px; text-align:center; }
}
@media (max-width: 480px){
  :root{ --header-height:72px; }
  .nav-left{ flex:1 1 100%; }
  .nav-right{ flex:1 1 100%; }
  .nav-right .btn-nav-login, .nav-right .btn-nav-register{ flex:1; }
  .nav-center{ display:none; }
}
@media (prefers-reduced-motion: reduce){
  *{ animation-duration:0.01ms !important; animation-iteration-count:1 !important; transition-duration:0.01ms !important; scroll-behavior:auto !important; }
}
</style>
</head>
<body>

<div class="scroll-dots" id="scrollDots">
  <div class="scroll-dot active" data-target="hero" data-label="HOME" onclick="scrollToSection('hero')"></div>
  <div class="scroll-dot" data-target="features" data-label="FEATURES" onclick="scrollToSection('features')"></div>
  <div class="scroll-dot" data-target="plans" data-label="PLANS" onclick="scrollToSection('plans')"></div>
</div>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<section class="hero" id="hero">
  <div class="hero-image"></div>
  <div class="hero-overlay"></div>
  <nav>
    <div class="nav-left">
      <div class="nav-logo"><svg fill="none" stroke="#1a1a1a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
      <span class="nav-brand">APEX FITNESS GYM</span>
    </div>
    <div class="nav-center"><div class="nav-center-dot"></div><span class="nav-center-text">Gym Management System</span></div>
    <div class="nav-right">
      <button class="btn-nav-login" onclick="openModal('login')">Log In</button>
      <button class="btn-nav-register" onclick="openModal('register')">Get Started</button>
    </div>
  </nav>
  <div class="hero-content">
    <h1 class="hero-title">MAKE YOUR<br>BODY<span>STRONGER.</span></h1>
    <div class="hero-features">
      <div class="hero-feature"><div class="feature-icon-box"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div><div class="feature-divider"></div><span class="feature-text">Secure Access</span></div>
      <div class="hero-feature"><div class="feature-icon-box"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg></div><div class="feature-divider"></div><span class="feature-text">Full Dashboard</span></div>
      <div class="hero-feature"><div class="feature-icon-box"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div><div class="feature-divider"></div><span class="feature-text">Member Management</span></div>
    </div>
    <div class="hero-cta">
      <button class="btn-join" onclick="openModal('register')">JOIN NOW</button>
      <button class="btn-login-hero" onclick="openModal('login')">LOGIN <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></button>
    </div>
  </div>
  <div class="hero-diamond"><div class="diamond"></div><div class="diamond-sm"></div></div>
</section>

<section class="section section-mid" id="features" aria-labelledby="features-title">
  <div class="reveal">
    <div class="section-eyebrow">Why APEX FITNESS GYM</div>
    <h2 class="section-title" id="features-title">EVERYTHING YOUR GYM NEEDS</h2>
  </div>
  <div class="features-grid reveal">

    {{-- 1. Member Records --}}
    <article class="feat-card">
      <div class="feat-icon-wrap" aria-hidden="true"><svg viewBox="0 0 24 24"><path stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
      <h3 class="feat-title">Member Records</h3>
      <p class="feat-desc">Keep member information, profile photos, contact details, and membership status organized in one place. Easily search, view, add, and update member records.</p>
    </article>

    {{-- 2. Membership Plans --}}
    <article class="feat-card">
      <div class="feat-icon-wrap" aria-hidden="true"><svg viewBox="0 0 24 24"><path stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
      <h3 class="feat-title">Membership Plans</h3>
      <p class="feat-desc">Manage monthly, quarterly, and annual membership plans. Track subscription details, payment status, and membership expiration dates.</p>
    </article>

    {{-- 3. Attendance Monitoring (verify wording, e.g. add QR scanning only if implemented) --}}
    <article class="feat-card">
      <div class="feat-icon-wrap" aria-hidden="true"><svg viewBox="0 0 24 24"><path stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
      <h3 class="feat-title">Attendance Monitoring</h3>
      <p class="feat-desc">Monitor gym visits and keep a clear attendance record for every member, so staff always know who has checked in.</p>
    </article>

    {{-- 4. Payment Tracking --}}
    <article class="feat-card">
      <div class="feat-icon-wrap" aria-hidden="true"><svg viewBox="0 0 24 24"><path stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></div>
      <h3 class="feat-title">Payment Tracking</h3>
      <p class="feat-desc">Record membership payments made in Cash, GCash, or Bank Transfer. Review transaction history and track pending and completed payments.</p>
    </article>

    {{-- 5. Workout Programs & Coaching --}}
    <article class="feat-card">
      <div class="feat-icon-wrap" aria-hidden="true"><svg viewBox="0 0 24 24"><path stroke-width="1.5" d="M6.5 6.5v11M17.5 6.5v11M3.5 9v6M20.5 9v6M6.5 12h11"/></svg></div>
      <h3 class="feat-title">Workout Programs &amp; Coaching</h3>
      <p class="feat-desc">Organize workout programs, instructor schedules, and coaching requests, with members and instructors seeing the information relevant to their roles.</p>
    </article>

    {{-- 6. Safe & Secure Login --}}
    <article class="feat-card">
      <div class="feat-icon-wrap" aria-hidden="true"><svg viewBox="0 0 24 24"><path stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
      <h3 class="feat-title">Safe &amp; Secure Login</h3>
      <p class="feat-desc">Accounts are password-protected, and role-based access means admins, staff, instructors, and members only see the pages and actions their role allows.</p>
    </article>

  </div>
</section>

<section class="section section-dark" id="plans">
  <div class="reveal"><div class="section-eyebrow">Membership Tiers</div><div class="section-title">CHOOSE YOUR PLAN</div></div>
  <div class="plans-grid reveal">
    <div class="plan-card"><div class="plan-label">Monthly</div><div class="plan-price">₱800</div><div class="plan-dur">/ 30 days</div><ul class="plan-feats"><li>Full gym access</li><li>Locker included</li><li>4 trainer sessions</li><li>Group classes</li></ul><button class="btn-plan btn-plan-ghost" onclick="openModal('register')">Get Started</button></div>
    <div class="plan-card hot"><div class="plan-hot-badge">Most Popular</div><div class="plan-label">Quarterly</div><div class="plan-price" style="color:var(--accent);">₱2,100</div><div class="plan-dur">/ 90 days</div><ul class="plan-feats"><li>Full gym access</li><li>Locker included</li><li>12 trainer sessions</li><li>Group classes</li><li>Save ₱300 vs monthly</li></ul><button class="btn-plan btn-plan-solid" onclick="openModal('register')">Get Started</button></div>
    <div class="plan-card"><div class="plan-label">Annual</div><div class="plan-price">₱7,500</div><div class="plan-dur">/ 365 days</div><ul class="plan-feats"><li>Full gym access</li><li>Locker included</li><li>Unlimited sessions</li><li>Group classes</li><li>2 guest passes/month</li></ul><button class="btn-plan btn-plan-ghost" onclick="openModal('register')">Get Started</button></div>
  </div>
</section>

<footer id="footer-section">
  <div class="footer-name">APEX<span>FITNESS</span> GYM</div>
  <div class="footer-copy">© 2026 APEX FITNESS GYM. All rights reserved.</div>
</footer>

<div class="modal-overlay" id="modalOverlay" onclick="handleOverlay(event)">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">×</button>
    <div class="modal-logo-row">
      <div class="modal-logo-icon"><svg fill="none" stroke="#1a1a1a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
      <div class="modal-logo-name">APEX FITNESS GYM</div>
    </div>
    <div class="modal-tabs">
      <button class="modal-tab active" id="tab-login" onclick="switchTab('login')">Login</button>
      <button class="modal-tab" id="tab-register" onclick="switchTab('register')">Register</button>
    </div>

    <div class="form-panel active" id="panel-login">
      <div class="modal-heading">WELCOME BACK</div>
      <div class="modal-sub">Sign in to your account</div>

      @if(session('status'))
        <div class="fm-success">✓ {{ session('status') }}</div>
      @endif

      @if($errors->has('email') && old('_form') === 'login')
        <div class="fm-error">{{ $errors->first('email') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf
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
          <a href="/forgot-password" style="font-size:12px;color:#777;text-decoration:none;">
            Forgot password?
          </a>
        </div>
        <button type="submit" class="btn-submit">Sign In →</button>
      </form>
      <div class="fm-footer">No account? <a onclick="switchTab('register')">Create one</a></div>
    </div>

    <div class="form-panel" id="panel-register">
      <div class="modal-heading">JOIN NOW</div>
      <div class="modal-sub">Create your free account</div>

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
        <input type="hidden" name="_form" value="register"/>

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
const isTouch = matchMedia('(hover: none), (pointer: coarse)').matches;
if(!isTouch){
  document.addEventListener('mousemove',e=>{mx=e.clientX;my=e.clientY;cursor.style.left=mx-5+'px';cursor.style.top=my-5+'px';});
  (function tick(){rx+=(mx-rx-18)*0.1;ry+=(my-ry-18)*0.1;ring.style.left=rx+'px';ring.style.top=ry+'px';requestAnimationFrame(tick);})();
  document.querySelectorAll('button,a,input,label').forEach(el=>{
    el.addEventListener('mouseenter',()=>cursor.style.transform='scale(2.5)');
    el.addEventListener('mouseleave',()=>cursor.style.transform='scale(1)');
  });
}

function scrollToSection(id){const el=document.getElementById(id);if(el)el.scrollIntoView({behavior:'smooth'});}
const sections=[{id:'hero'},{id:'features'},{id:'plans'},{id:'footer-section'}];
function updateDots(){
  const dots=document.querySelectorAll('.scroll-dot');let active=0;
  sections.forEach((s,i)=>{const el=document.getElementById(s.id);if(el&&el.getBoundingClientRect().top<=window.innerHeight*0.5)active=i;});
  dots.forEach((d,i)=>d.classList.toggle('active',i===active));
}
window.addEventListener('scroll',updateDots,{passive:true});updateDots();

function openModal(tab){document.getElementById('modalOverlay').classList.add('open');switchTab(tab);document.body.style.overflow='hidden';document.body.classList.add('modal-open');}
function closeModal(){document.getElementById('modalOverlay').classList.remove('open');document.body.style.overflow='';document.body.classList.remove('modal-open');}
function handleOverlay(e){if(e.target===document.getElementById('modalOverlay'))closeModal();}
function switchTab(tab){
  document.querySelectorAll('.modal-tab').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.form-panel').forEach(p=>p.classList.remove('active'));
  document.getElementById('tab-'+tab).classList.add('active');
  document.getElementById('panel-'+tab).classList.add('active');
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});

// Deep links from the forgot/reset password pages: /?auth=login  or  /?auth=register
const authParam=new URLSearchParams(location.search).get('auth');
if(authParam==='login'||authParam==='register'){openModal(authParam);}
@if(session('status'))
  openModal('login');
@endif

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