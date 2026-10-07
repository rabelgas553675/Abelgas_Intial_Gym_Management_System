<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'APEX FITNESS GYM')</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow+Condensed:wght@400;600;700;800;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/*
  Standalone auth page that reuses the LANDING PAGE login-modal design
  (resources/views/landing.blade.php) — same tokens, fonts, card, inputs and buttons.
  Keep the values below in sync with that file.
*/
:root{--accent:#e0a93b;--accent-2:#f0c060;--accent-dark:#b8862a;--accent-soft:rgba(224,169,59,0.12);}
*{box-sizing:border-box;margin:0;padding:0;}
html,body{min-height:100%;}
body{
  background:#0d0d0d;color:#fff;font-family:'DM Sans',sans-serif;overflow-x:hidden;
  min-height:100vh;min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:24px 20px;
  position:relative;
}
/* soft gold glows, like the blurred landing page behind the login modal */
body::before{
  content:'';position:fixed;inset:0;z-index:-1;pointer-events:none;
  background:
    radial-gradient(ellipse 45% 40% at 12% 88%, rgba(224,169,59,0.16), transparent 70%),
    radial-gradient(ellipse 40% 35% at 92% 70%, rgba(224,169,59,0.10), transparent 70%),
    radial-gradient(ellipse 50% 30% at 50% 0%,  rgba(224,169,59,0.06), transparent 70%);
}

/* ── MODAL CARD (same as landing .modal) ── */
.modal{background:#151515;border:1px solid rgba(224,169,59,0.25);border-radius:20px;width:100%;max-width:440px;padding:36px 40px;position:relative;box-shadow:0 24px 70px rgba(0,0,0,0.55);}
.modal-close{position:absolute;top:16px;right:16px;width:32px;height:32px;background:#1c1c1c;border:1px solid rgba(255,255,255,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#666;font-size:18px;line-height:1;text-decoration:none;transition:all 0.15s;}
.modal-close:hover{color:#fff;border-color:var(--accent);}
.modal-logo-row{display:flex;align-items:center;gap:8px;margin-bottom:20px;}
.modal-logo-icon{width:28px;height:28px;background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));border-radius:6px;display:flex;align-items:center;justify-content:center;}
.modal-logo-icon svg{width:16px;height:16px;}
.modal-logo-name{font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;letter-spacing:3px;color:var(--accent);text-transform:uppercase;}
.modal-tabs{display:flex;gap:4px;background:#1c1c1c;border-radius:10px;padding:4px;margin-bottom:24px;}
.modal-tab{flex:1;padding:9px;text-align:center;font-family:'Barlow Condensed',sans-serif;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;cursor:pointer;border-radius:7px;color:#666;transition:all 0.15s;border:none;background:transparent;text-decoration:none;display:block;}
.modal-tab:hover{color:#fff;}
.modal-tab.active{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;}
.modal-heading{font-family:'Barlow Condensed',sans-serif;font-size:32px;font-weight:900;letter-spacing:2px;margin-bottom:4px;text-transform:uppercase;}
.modal-sub{font-size:13px;color:#666;margin-bottom:20px;line-height:1.5;}

/* ── FORM ── */
.fm-group{margin-bottom:14px;}
.fm-label{display:block;font-size:11px;font-weight:600;color:#777;margin-bottom:10px;letter-spacing:2px;text-transform:uppercase;}
.fm-input{width:100%;padding:11px 14px;background:#1c1c1c;border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:#fff;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:border-color 0.15s;}
.fm-input::placeholder{color:#555;}
.fm-input:focus{border-color:var(--accent);}
.fm-input.is-invalid{border-color:rgba(239,68,68,0.6);}
/* keep browser autofill dark instead of pale blue */
.fm-input:-webkit-autofill,.fm-input:-webkit-autofill:focus{-webkit-text-fill-color:#fff;caret-color:#fff;box-shadow:0 0 0 1000px #1c1c1c inset;transition:background-color 9999s ease-out;}
.fm-pass{position:relative;}
.fm-pass .fm-input{padding-right:48px;}
.fm-eye{position:absolute;top:50%;right:6px;transform:translateY(-50%);width:36px;height:34px;border:0;border-radius:8px;background:transparent;color:#777;cursor:pointer;font-family:'Barlow Condensed',sans-serif;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;transition:color .15s;}
.fm-eye:hover{color:var(--accent);}
.btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;font-family:'Barlow Condensed',sans-serif;font-size:16px;font-weight:900;letter-spacing:3px;text-transform:uppercase;border:none;border-radius:10px;cursor:pointer;margin-top:8px;transition:all 0.2s;}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(224,169,59,0.3);}
.btn-submit:disabled{opacity:.65;cursor:not-allowed;transform:none;box-shadow:none;}
.fm-footer{text-align:center;font-size:13px;color:#666;margin-top:14px;}
.fm-footer a{color:var(--accent);text-decoration:none;cursor:pointer;}
.fm-footer a:hover{text-decoration:underline;}
.fm-error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#ef4444;}
.fm-success{background:rgba(74,222,128,0.10);border:1px solid rgba(74,222,128,0.30);border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#4ade80;}
.fm-field-error{color:#ef4444;font-size:12px;margin-top:6px;}
a:focus-visible,button:focus-visible,.fm-input:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}

@media (max-width:768px){ .fm-input{font-size:16px;} } /* stops iOS zoom on focus */
@media (max-width:600px){
  .modal{padding:28px 22px;max-width:92vw;border-radius:16px;}
  .modal-heading{font-size:26px;}
}
@media (prefers-reduced-motion: reduce){ *{transition-duration:0.01ms !important;animation-duration:0.01ms !important;} }
</style>
</head>
<body>
<main class="modal" role="dialog" aria-labelledby="authHeading">
  <a class="modal-close" href="{{ url('/') }}" aria-label="Back to home">×</a>

  <div class="modal-logo-row">
    <div class="modal-logo-icon"><svg fill="none" stroke="#1a1a1a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
    <div class="modal-logo-name">APEX FITNESS GYM</div>
  </div>

  {{-- Same Login | Register tab bar as the landing-page modal. Forgot-password belongs to the Login flow, so Login is highlighted. --}}
  <div class="modal-tabs">
    <a class="modal-tab active" href="{{ url('/') }}?auth=login">Login</a>
    <a class="modal-tab" href="{{ url('/') }}?auth=register">Register</a>
  </div>

  @yield('content')
</main>
@stack('scripts')
</body>
</html>