<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IRONFORGE – Ultimate Gym Management</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow+Condensed:wght@300;400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --black: #080808;
  --surface: #111111;
  --surface2: #1a1a1a;
  --border: rgba(255,255,255,0.08);
  --accent: #c8ff00;
  --accent2: #ff4d00;
  --text: #f0f0f0;
  --muted: #666;
  --radius: 12px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
  background: var(--black);
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 16px;
  overflow-x: hidden;
  cursor: none;
}

/* CUSTOM CURSOR */
.cursor {
  width: 12px; height: 12px;
  background: var(--accent);
  border-radius: 50%;
  position: fixed;
  top: 0; left: 0;
  pointer-events: none;
  z-index: 9999;
  transition: transform 0.15s ease, width 0.2s, height 0.2s;
  mix-blend-mode: difference;
}
.cursor-ring {
  width: 40px; height: 40px;
  border: 1px solid rgba(200,255,0,0.4);
  border-radius: 50%;
  position: fixed;
  top: 0; left: 0;
  pointer-events: none;
  z-index: 9998;
  transition: all 0.1s ease;
}

/* NAV */
nav {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  padding: 20px 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(180deg, rgba(8,8,8,0.95) 0%, transparent 100%);
  backdrop-filter: blur(10px);
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.brand-icon {
  width: 36px; height: 36px;
  background: var(--accent);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-icon svg { width: 20px; height: 20px; }

.brand-name {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 24px;
  color: var(--text);
  letter-spacing: 3px;
}

.brand-name span { color: var(--accent); }

.nav-links {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-nav {
  padding: 10px 24px;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  letter-spacing: 0.5px;
}

.btn-login {
  background: transparent;
  color: var(--text);
  border: 1px solid var(--border);
}
.btn-login:hover { border-color: var(--accent); color: var(--accent); }

.btn-register {
  background: var(--accent);
  color: #111;
  font-weight: 700;
}
.btn-register:hover { background: #b8ef00; transform: translateY(-1px); }

/* HERO */
.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
  padding: 120px 60px 80px;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 60% 50% at 70% 50%, rgba(200,255,0,0.06) 0%, transparent 70%),
    radial-gradient(ellipse 40% 60% at 30% 80%, rgba(255,77,0,0.04) 0%, transparent 60%);
}

.hero-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 60px 60px;
  mask-image: radial-gradient(ellipse at center, black 20%, transparent 80%);
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 680px;
}

.hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(200,255,0,0.08);
  border: 1px solid rgba(200,255,0,0.2);
  color: var(--accent);
  padding: 6px 16px;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 28px;
  animation: fadeUp 0.6s ease both;
}

.hero-tag::before {
  content: '';
  width: 6px; height: 6px;
  background: var(--accent);
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.8); }
}

.hero-title {
  font-family: 'Bebas Neue', sans-serif;
  font-size: clamp(64px, 10vw, 120px);
  line-height: 0.9;
  letter-spacing: 2px;
  margin-bottom: 24px;
  animation: fadeUp 0.6s 0.1s ease both;
}

.hero-title .line1 { display: block; color: var(--text); }
.hero-title .line2 { display: block; color: var(--accent); }
.hero-title .line3 { display: block; color: var(--text); opacity: 0.3; }

.hero-desc {
  font-size: 18px;
  color: var(--muted);
  line-height: 1.7;
  max-width: 480px;
  margin-bottom: 40px;
  font-weight: 300;
  animation: fadeUp 0.6s 0.2s ease both;
}

.hero-actions {
  display: flex;
  gap: 14px;
  align-items: center;
  animation: fadeUp 0.6s 0.3s ease both;
}

.btn-hero-primary {
  padding: 16px 36px;
  background: var(--accent);
  color: #111;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-hero-primary:hover { background: #b8ef00; transform: translateY(-2px); box-shadow: 0 12px 40px rgba(200,255,0,0.2); }

.btn-hero-secondary {
  padding: 16px 28px;
  background: transparent;
  color: var(--text);
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 16px;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  border: 1px solid var(--border);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-hero-secondary:hover { border-color: rgba(255,255,255,0.3); }

.hero-stats {
  display: flex;
  gap: 40px;
  margin-top: 60px;
  padding-top: 40px;
  border-top: 1px solid var(--border);
  animation: fadeUp 0.6s 0.4s ease both;
}

.stat-item { text-align: left; }
.stat-num {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 42px;
  color: var(--accent);
  line-height: 1;
}
.stat-label {
  font-size: 12px;
  color: var(--muted);
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-top: 4px;
}

/* DECORATIVE RIGHT SIDE */
.hero-visual {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 45%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.hero-circle {
  width: 500px; height: 500px;
  border-radius: 50%;
  border: 1px solid rgba(200,255,0,0.1);
  position: absolute;
  animation: rotate 20s linear infinite;
}

.hero-circle::before {
  content: '';
  position: absolute;
  top: -4px; left: 50%;
  width: 8px; height: 8px;
  background: var(--accent);
  border-radius: 50%;
  transform: translateX(-50%);
}

.hero-circle-2 {
  width: 350px; height: 350px;
  border: 1px solid rgba(200,255,0,0.05);
  animation-direction: reverse;
  animation-duration: 15s;
}

.hero-center {
  width: 200px; height: 200px;
  background: radial-gradient(circle, rgba(200,255,0,0.08) 0%, transparent 70%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(200,255,0,0.15);
}

.hero-center-icon {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 56px;
  color: var(--accent);
  opacity: 0.4;
  letter-spacing: 3px;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

/* FEATURES */
.features {
  padding: 100px 60px;
  position: relative;
}

.section-label {
  font-size: 11px;
  color: var(--accent);
  letter-spacing: 4px;
  text-transform: uppercase;
  margin-bottom: 12px;
  font-weight: 500;
}

.section-title {
  font-family: 'Bebas Neue', sans-serif;
  font-size: clamp(36px, 5vw, 64px);
  letter-spacing: 2px;
  line-height: 1;
  margin-bottom: 60px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2px;
}

.feature-card {
  background: var(--surface);
  padding: 40px 36px;
  border: 1px solid var(--border);
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
}

.feature-card:hover {
  background: var(--surface2);
  border-color: rgba(200,255,0,0.2);
}

.feature-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 2px;
  background: var(--accent);
  transform: scaleX(0);
  transition: transform 0.3s;
  transform-origin: left;
}

.feature-card:hover::before { transform: scaleX(1); }

.feature-icon {
  width: 48px; height: 48px;
  background: rgba(200,255,0,0.08);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.feature-icon svg { width: 24px; height: 24px; stroke: var(--accent); }

.feature-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 22px;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 12px;
  text-transform: uppercase;
}

.feature-desc {
  font-size: 14px;
  color: var(--muted);
  line-height: 1.7;
}

/* PLANS */
.plans {
  padding: 100px 60px;
  background: var(--surface);
  position: relative;
}

.plans-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-top: 60px;
}

.plan-card {
  background: var(--black);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 36px 32px;
  position: relative;
  transition: all 0.3s;
}

.plan-card:hover { transform: translateY(-4px); border-color: rgba(200,255,0,0.2); }

.plan-card.featured {
  border-color: var(--accent);
  background: linear-gradient(135deg, rgba(200,255,0,0.04) 0%, transparent 100%);
}

.plan-popular {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--accent);
  color: #111;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 4px 16px;
  border-radius: 100px;
  white-space: nowrap;
}

.plan-name {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 8px;
}

.plan-price {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 56px;
  letter-spacing: 2px;
  color: var(--text);
  line-height: 1;
  margin-bottom: 4px;
}

.plan-price span { font-size: 20px; color: var(--muted); }
.plan-duration { font-size: 13px; color: var(--muted); margin-bottom: 28px; }

.plan-features-list {
  list-style: none;
  margin-bottom: 32px;
}

.plan-features-list li {
  font-size: 14px;
  color: var(--muted);
  padding: 8px 0;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid var(--border);
}

.plan-features-list li:last-child { border-bottom: none; }

.plan-features-list li::before {
  content: '✓';
  color: var(--accent);
  font-weight: 700;
  font-size: 12px;
  flex-shrink: 0;
}

.btn-plan {
  width: 100%;
  padding: 14px;
  border-radius: 10px;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 15px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-plan-outline {
  background: transparent;
  color: var(--text);
  border: 1px solid var(--border);
}
.btn-plan-outline:hover { border-color: var(--accent); color: var(--accent); }

.btn-plan-filled {
  background: var(--accent);
  color: #111;
}
.btn-plan-filled:hover { background: #b8ef00; }

/* MODAL */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.85);
  backdrop-filter: blur(8px);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s;
}

.modal-overlay.open {
  opacity: 1;
  pointer-events: all;
}

.modal {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  width: 100%;
  max-width: 440px;
  padding: 40px;
  position: relative;
  transform: translateY(20px) scale(0.97);
  transition: all 0.25s;
}

.modal-overlay.open .modal {
  transform: translateY(0) scale(1);
}

.modal-close {
  position: absolute;
  top: 16px; right: 16px;
  width: 32px; height: 32px;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--muted);
  font-size: 18px;
  transition: all 0.15s;
}
.modal-close:hover { color: var(--text); border-color: var(--accent); }

.modal-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 24px;
}

.modal-logo {
  width: 30px; height: 30px;
  background: var(--accent);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Bebas Neue', sans-serif;
  font-size: 14px;
  color: #111;
}

.modal-brand-name {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 20px;
  letter-spacing: 2px;
  color: var(--accent);
}

.modal-title {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 36px;
  letter-spacing: 2px;
  margin-bottom: 6px;
}

.modal-sub {
  font-size: 14px;
  color: var(--muted);
  margin-bottom: 28px;
}

.modal-tabs {
  display: flex;
  gap: 0;
  background: var(--surface2);
  border-radius: 10px;
  padding: 4px;
  margin-bottom: 24px;
}

.modal-tab {
  flex: 1;
  padding: 9px;
  text-align: center;
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  cursor: pointer;
  border-radius: 7px;
  color: var(--muted);
  transition: all 0.15s;
  border: none;
  background: transparent;
  font-family: 'Barlow Condensed', sans-serif;
}

.modal-tab.active {
  background: var(--accent);
  color: #111;
}

.form-group { margin-bottom: 16px; }

.form-label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: var(--muted);
  margin-bottom: 6px;
  letter-spacing: 0.5px;
}

.form-input {
  width: 100%;
  padding: 11px 14px;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  outline: none;
  transition: border-color 0.15s;
}

.form-input:focus { border-color: var(--accent); }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.btn-submit {
  width: 100%;
  padding: 14px;
  background: var(--accent);
  color: #111;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 16px;
  font-weight: 800;
  letter-spacing: 3px;
  text-transform: uppercase;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  margin-top: 8px;
  transition: all 0.2s;
}
.btn-submit:hover { background: #b8ef00; transform: translateY(-1px); }

.form-footer {
  text-align: center;
  font-size: 13px;
  color: var(--muted);
  margin-top: 16px;
}

.form-footer a {
  color: var(--accent);
  text-decoration: none;
  cursor: pointer;
}

/* FORM PANELS */
.form-panel { display: none; }
.form-panel.active { display: block; }

/* FOOTER */
footer {
  padding: 60px 60px 40px;
  border-top: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.footer-brand {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 20px;
  letter-spacing: 3px;
  color: var(--muted);
}

.footer-brand span { color: var(--accent); }

.footer-copy {
  font-size: 13px;
  color: var(--muted);
}

/* SCROLL REVEAL */
.reveal {
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.6s ease;
}
.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}

/* NOISE OVERLAY */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
  pointer-events: none;
  z-index: 1000;
  opacity: 0.4;
}

@media (max-width: 768px) {
  nav { padding: 16px 24px; }
  .hero { padding: 100px 24px 60px; }
  .hero-title { font-size: 64px; }
  .hero-visual { display: none; }
  .features { padding: 60px 24px; }
  .features-grid { grid-template-columns: 1fr; }
  .plans { padding: 60px 24px; }
  .plans-grid { grid-template-columns: 1fr; }
  .hero-stats { gap: 24px; }
  footer { padding: 40px 24px; flex-direction: column; gap: 12px; text-align: center; }
}
</style>
</head>
<body>

<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- NAV -->
<nav>
  <a class="nav-brand" href="#">
    <div class="brand-icon">
      <svg fill="none" stroke="#111" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
              d="M13 10V3L4 14h7v7l9-11h-7z"/>
      </svg>
    </div>
    <span class="brand-name">IRON<span>FORGE</span></span>
  </a>
  <div class="nav-links">
    <button class="btn-nav btn-login" onclick="openModal('login')">Log In</button>
    <button class="btn-nav btn-register" onclick="openModal('register')">Get Started</button>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
  <div class="hero-bg"></div>
  <div class="hero-grid"></div>

  <div class="hero-content">
    <div class="hero-tag">Gym Management System</div>
    <h1 class="hero-title">
      <span class="line1">MAKE YOUR</span>
      <span class="line2">BODY FORGE</span>
      <span class="line3">STRONGER</span>
    </h1>
    <p class="hero-desc">
      The complete gym management platform built for modern fitness centers.
      Track members, manage payments, and control access — all in one place.
    </p>
    <div class="hero-actions">
      <button class="btn-hero-primary" onclick="openModal('register')">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Start Now
      </button>
      <button class="btn-hero-secondary" onclick="openModal('login')">
        Sign In →
      </button>
    </div>
    <div class="hero-stats">
      <div class="stat-item">
        <div class="stat-num">500+</div>
        <div class="stat-label">Members</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">4</div>
        <div class="stat-label">Plan Types</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">3</div>
        <div class="stat-label">User Roles</div>
      </div>
    </div>
  </div>

  <div class="hero-visual">
    <div class="hero-circle"></div>
    <div class="hero-circle hero-circle-2"></div>
    <div class="hero-center">
      <div class="hero-center-icon">GMS</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="reveal">
    <div class="section-label">Why IRONFORGE</div>
    <div class="section-title">EVERYTHING<br>YOU NEED</div>
  </div>
  <div class="features-grid reveal">
    <div class="feature-card">
      <div class="feature-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      <div class="feature-title">Member Management</div>
      <p class="feature-desc">Complete CRUD for member records with photo upload, personal details, and membership tracking.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <div class="feature-title">Payment Tracking</div>
      <p class="feature-desc">Record payments via Cash, GCash, or Bank Transfer. Monitor monthly and all-time revenue instantly.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
      </div>
      <div class="feature-title">Role-Based Access</div>
      <p class="feature-desc">Admin, Staff, and User roles. Each level sees only what they need — secure and clean.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <div class="feature-title">Membership Plans</div>
      <p class="feature-desc">Monthly, Quarterly, Semi-Annual, and Annual plans with auto-calculated end dates and fees.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </div>
      <div class="feature-title">Live Dashboard</div>
      <p class="feature-desc">Real-time stats showing active members, expired accounts, revenue this month, and recent activity.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
      </div>
      <div class="feature-title">Laravel Breeze Auth</div>
      <p class="feature-desc">Secure login and registration powered by Laravel Breeze. All routes protected by authentication middleware.</p>
    </div>
  </div>
</section>

<!-- PLANS -->
<section class="plans" id="plans">
  <div class="reveal">
    <div class="section-label">Membership Tiers</div>
    <div class="section-title">CHOOSE YOUR<br>PLAN</div>
  </div>
  <div class="plans-grid reveal">
    <div class="plan-card">
      <div class="plan-name">Monthly</div>
      <div class="plan-price">₱<span></span>800</div>
      <div class="plan-duration">/ 30 days</div>
      <ul class="plan-features-list">
        <li>Full gym access</li>
        <li>Locker included</li>
        <li>4 trainer sessions</li>
        <li>Group classes</li>
      </ul>
      <button class="btn-plan btn-plan-outline" onclick="openModal('register')">Get Started</button>
    </div>
    <div class="plan-card featured">
      <div class="plan-popular">Most Popular</div>
      <div class="plan-name">Quarterly</div>
      <div class="plan-price">₱<span></span>2,100</div>
      <div class="plan-duration">/ 90 days</div>
      <ul class="plan-features-list">
        <li>Full gym access</li>
        <li>Locker included</li>
        <li>12 trainer sessions</li>
        <li>Group classes</li>
        <li>Save ₱300 vs monthly</li>
      </ul>
      <button class="btn-plan btn-plan-filled" onclick="openModal('register')">Get Started</button>
    </div>
    <div class="plan-card">
      <div class="plan-name">Annual</div>
      <div class="plan-price">₱<span></span>7,500</div>
      <div class="plan-duration">/ 365 days</div>
      <ul class="plan-features-list">
        <li>Full gym access</li>
        <li>Locker included</li>
        <li>Unlimited sessions</li>
        <li>Group classes</li>
        <li>2 guest passes/month</li>
      </ul>
      <button class="btn-plan btn-plan-outline" onclick="openModal('register')">Get Started</button>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-brand">IRON<span>FORGE</span> GMS</div>
  <div class="footer-copy">© 2026 IRONFORGE. Built with Laravel + Breeze.</div>
</footer>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="handleOverlayClick(event)">
  <div class="modal" id="modal">
    <button class="modal-close" onclick="closeModal()">×</button>

    <div class="modal-brand">
      <div class="modal-logo">IF</div>
      <div class="modal-brand-name">IRONFORGE</div>
    </div>

    <div class="modal-tabs">
      <button class="modal-tab active" id="tab-login" onclick="switchTab('login')">Login</button>
      <button class="modal-tab" id="tab-register" onclick="switchTab('register')">Register</button>
    </div>

    <!-- LOGIN FORM -->
    <div class="form-panel active" id="panel-login">
      <div class="modal-title">WELCOME BACK</div>
      <div class="modal-sub">Sign in to your account</div>
      <form method="POST" action="/login">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" placeholder="admin@gym.com" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-input" placeholder="••••••••" required/>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-bottom:8px;">
          <a href="/forgot-password" style="font-size:12px;color:var(--muted);text-decoration:none;">Forgot password?</a>
        </div>
        <button type="submit" class="btn-submit">Sign In →</button>
      </form>
      <div class="form-footer">
        Don't have an account?
        <a onclick="switchTab('register')">Create one</a>
      </div>
    </div>

    <!-- REGISTER FORM -->
    <div class="form-panel" id="panel-register">
      <div class="modal-title">JOIN NOW</div>
      <div class="modal-sub">Create your account</div>
      <form method="POST" action="/register">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-input" placeholder="Juan Dela Cruz" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" placeholder="juan@email.com" required/>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" placeholder="••••••••" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm</label>
            <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required/>
          </div>
        </div>
        <button type="submit" class="btn-submit">Create Account →</button>
      </form>
      <div class="form-footer">
        Already have an account?
        <a onclick="switchTab('login')">Sign in</a>
      </div>
    </div>
  </div>
</div>

<script>
// Custom cursor
const cursor = document.getElementById('cursor');
const ring = document.getElementById('cursorRing');
let mx = 0, my = 0, rx = 0, ry = 0;

document.addEventListener('mousemove', e => {
  mx = e.clientX; my = e.clientY;
  cursor.style.left = mx - 6 + 'px';
  cursor.style.top = my - 6 + 'px';
});

function animateRing() {
  rx += (mx - rx - 20) * 0.12;
  ry += (my - ry - 20) * 0.12;
  ring.style.left = rx + 'px';
  ring.style.top = ry + 'px';
  requestAnimationFrame(animateRing);
}
animateRing();

document.querySelectorAll('button, a, input').forEach(el => {
  el.addEventListener('mouseenter', () => cursor.style.transform = 'scale(2.5)');
  el.addEventListener('mouseleave', () => cursor.style.transform = 'scale(1)');
});

// Modal
function openModal(tab) {
  document.getElementById('modalOverlay').classList.add('open');
  switchTab(tab);
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function handleOverlayClick(e) {
  if (e.target === document.getElementById('modalOverlay')) closeModal();
}

function switchTab(tab) {
  document.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  document.getElementById('panel-' + tab).classList.add('active');
}

// Keyboard close
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// Scroll reveal
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>