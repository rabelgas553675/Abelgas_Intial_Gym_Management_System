@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'QR Attendance Scanner – APEX')
@section('active_nav', 'attendance')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .scanner-container { max-width: 1400px; margin: 0 auto; padding: 0 16px; }

    /* Header */
    .scanner-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
    .scanner-header-left h1 { font-size: 28px; font-weight: 700; margin-bottom: 4px; }
    .scanner-header-left p { color: var(--muted); font-size: 14px; }
    .scanner-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

    /* Grid */
    .scanner-grid { display: grid; grid-template-columns: 420px 1fr; gap: 20px; align-items: start; }

    /* Scanner Panel */
    .scanner-panel { background: linear-gradient(135deg, #0f0f1a, #1a1a2e); border-radius: 20px; padding: 28px; box-shadow: 0 8px 32px rgba(0,0,0,.25); }
    .live-clock { text-align: center; margin-bottom: 24px; }
    .live-clock-time { font-size: 36px; font-weight: 700; color: #fff; letter-spacing: 2px; }
    .live-clock-date { color: rgba(255,255,255,.55); font-size: 13px; margin-top: 2px; }

    /* Cooldown */
    .cooldown-overlay { display: none; position: relative; border-radius: 14px; overflow: hidden; background: rgba(15,15,26,.92); border: 3px solid rgba(200,255,0,.5); margin-bottom: 12px; padding: 40px 20px; text-align: center; z-index: 10; }
    .cooldown-overlay.active { display: block; }
    .cooldown-number { font-size: 42px; font-weight: 800; color: var(--accent); }
    .cooldown-text { color: rgba(255,255,255,.6); font-size: 13px; margin-top: 6px; }
    .cooldown-bar-track { margin-top: 16px; height: 4px; background: rgba(255,255,255,.1); border-radius: 4px; overflow: hidden; }
    .cooldown-bar { height: 100%; width: 100%; background: var(--accent); transition: width 3s linear; border-radius: 4px; }

    /* Reader */
    .reader-wrapper { border-radius: 14px; overflow: hidden; border: 3px solid rgba(200,255,0,.4); margin-bottom: 12px; position: relative; background: #0a0a15; min-height: 200px; }
    .reader-wrapper #reader { width: 100%; min-height: 200px; }
    .reader-wrapper #reader video { width: 100% !important; height: auto !important; }
    .scan-line { height: 3px; background: linear-gradient(90deg, transparent, var(--accent), transparent); animation: scanMove 2s linear infinite; border-radius: 2px; margin-bottom: 12px; }

    /* Status */
    .scanner-status { text-align: center; margin-bottom: 12px; }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; background: rgba(74,222,128,.12); color: #4ade80; border: 1px solid rgba(74,222,128,.3); }
    .status-dot { width: 7px; height: 7px; border-radius: 50%; background: #4ade80; animation: pulse 1.5s ease-in-out infinite; display: inline-block; }
    .status-dot.locked { background: #f87171; animation: none; }
    .scanner-hint { text-align: center; color: rgba(255,255,255,.45); font-size: 12px; margin-bottom: 12px; }

    /* Manual Input */
    .manual-input-group { display: flex; gap: 8px; margin-bottom: 6px; }
    .manual-input-group input { flex: 1; padding: 10px 14px; background: rgba(255,255,255,.08); color: #fff; border: 1px solid rgba(255,255,255,.2); border-radius: 9px 0 0 9px; font-size: 13px; outline: none; font-family: 'DM Sans', sans-serif; min-width: 0; min-height: 44px; }
    .manual-input-group input:focus { border-color: var(--accent); }
    .manual-submit-btn { background: var(--accent); color: #111; border: none; border-radius: 0 9px 9px 0; padding: 0 16px; font-weight: 700; cursor: pointer; transition: opacity .2s; flex-shrink: 0; min-height: 44px; min-width: 44px; font-size: 18px; }
    .manual-submit-btn:disabled { opacity: .4; cursor: not-allowed; }
    .manual-hint { color: rgba(255,255,255,.3); font-size: 11px; display: block; margin-bottom: 14px; }

    /* Manual Entry Panel */
    .manual-entry-panel { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12); border-radius: 12px; padding: 18px; }
    .manual-entry-panel .panel-label { font-size: 11px; font-weight: 700; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
    .manual-btn-group { display: flex; gap: 8px; }
    .manual-btn-group button { flex: 1; padding: 9px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; transition: opacity .2s; min-height: 44px; }
    .manual-btn-group button:disabled { opacity: .4; cursor: not-allowed; }
    .btn-timein { background: #4ade80; color: #111; }
    .btn-timeout { background: #60a5fa; color: #111; }
    .manual-msg { margin-top: 8px; font-size: 12px; min-height: 16px; }

    /* ===== Person picker (search + results list) ===== */
    .person-picker { margin-bottom: 14px; }
    .manual-select-hidden { display: none !important; }   /* hidden <select> only stores the chosen value */

    .person-search-wrap { position: relative; }
    .person-search-wrap svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; stroke: rgba(255,255,255,.45); fill: none; stroke-width: 2; pointer-events: none; }
    .manual-search { width: 100%; box-sizing: border-box; padding: 11px 12px 11px 38px; background: rgba(255,255,255,.08); color: #fff; border: 1px solid rgba(255,255,255,.18); border-radius: 10px; font-size: 14px; font-family: 'DM Sans', sans-serif; outline: none; min-height: 46px; transition: border-color .2s, background .2s; }
    .manual-search::placeholder { color: rgba(255,255,255,.4); }
    .manual-search:focus { border-color: var(--accent); background: rgba(255,255,255,.1); }

    .person-results { margin-top: 8px; background: #12121f; border: 1px solid rgba(255,255,255,.14); border-radius: 12px; max-height: 250px; overflow-y: auto; padding: 0 0 6px; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.25) transparent; }
    .person-results[hidden] { display: none; }
    .person-results::-webkit-scrollbar { width: 6px; }
    .person-results::-webkit-scrollbar-thumb { background: rgba(255,255,255,.22); border-radius: 6px; }

    .person-group { position: sticky; top: 0; z-index: 1; display: flex; align-items: center; justify-content: space-between; padding: 10px 14px 7px; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,.5); background: #12121f; border-bottom: 1px solid rgba(255,255,255,.06); margin-bottom: 4px; }
    .person-group span:last-child { color: rgba(255,255,255,.3); letter-spacing: 0; }

    .person-item { display: flex; align-items: center; gap: 10px; margin: 2px 6px; padding: 8px 10px; min-height: 46px; box-sizing: border-box; border-radius: 9px; border: 1px solid transparent; cursor: pointer; transition: background .15s, border-color .15s; }
    .person-item:hover { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.1); }
    .person-item.active { background: color-mix(in srgb, var(--accent) 14%, transparent); border-color: color-mix(in srgb, var(--accent) 40%, transparent); }

    .person-avatar { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
    .person-avatar.member { background: rgba(74,222,128,.14); color: #4ade80; border: 1px solid rgba(74,222,128,.28); }
    .person-avatar.staff  { background: rgba(96,165,250,.14); color: #60a5fa; border: 1px solid rgba(96,165,250,.28); }

    .person-name { flex: 1; min-width: 0; font-size: 13px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .person-role { flex-shrink: 0; font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 999px; letter-spacing: .5px; text-transform: uppercase; }
    .person-role.member { background: rgba(74,222,128,.12); color: #4ade80; border: 1px solid rgba(74,222,128,.28); }
    .person-role.staff  { background: rgba(96,165,250,.12); color: #60a5fa; border: 1px solid rgba(96,165,250,.28); }
    .person-warn { flex-shrink: 0; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 999px; color: #fbbf24; background: rgba(251,191,36,.12); border: 1px solid rgba(251,191,36,.3); }
    .person-empty { padding: 24px 14px; text-align: center; color: rgba(255,255,255,.45); font-size: 13px; }

    .person-selected { display: flex; align-items: center; gap: 10px; margin-top: 8px; padding: 8px 8px 8px 12px; border-radius: 10px; background: color-mix(in srgb, var(--accent) 12%, transparent); border: 1px solid color-mix(in srgb, var(--accent) 35%, transparent); }
    .person-selected[hidden] { display: none; }
    .person-selected .label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: rgba(255,255,255,.5); flex-shrink: 0; }
    .person-selected .value { flex: 1; min-width: 0; font-size: 13px; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .person-selected button { flex-shrink: 0; background: rgba(255,255,255,.08); border: none; color: rgba(255,255,255,.75); font-size: 16px; line-height: 1; width: 28px; height: 28px; border-radius: 8px; cursor: pointer; }
    .person-selected button:hover { background: rgba(255,255,255,.16); color: #fff; }

    /* Counters */
    .scanner-counters { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,.1); gap: 8px; flex-wrap: wrap; }
    .counter-item { text-align: center; flex: 1; min-width: 60px; }
    .counter-value { font-size: 22px; font-weight: 700; }
    .counter-value.green { color: #4ade80; }
    .counter-value.yellow { color: #fbbf24; }
    .counter-value.white { color: #fff; }
    .counter-label { color: rgba(255,255,255,.45); font-size: 11px; }

    /* Result Card */
    .result-card { display: none; border-radius: 16px; padding: 24px; text-align: center; margin-top: 16px; animation: popIn .35s ease; }
    .result-card.visible { display: block; }
    .result-avatar { margin-bottom: 10px; }
    .result-avatar img, .result-avatar div { margin: 0 auto; display: block; }
    .result-name { font-weight: 700; margin-bottom: 4px; font-size: 18px; color: #111; }
    .result-membership { color: var(--muted); font-size: 13px; margin-bottom: 8px; }
    .result-times { margin-bottom: 8px; display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; }
    .result-times .time-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(0,0,0,.08); border-radius: 8px; padding: 6px 12px; font-size: 13px; font-weight: 600; margin: 3px; }
    .result-message { font-weight: 600; font-size: 14px; margin: 0; }
    .result-timein { background: linear-gradient(135deg, #d4edda, #c3e6cb); border: 2px solid #28a745; color: #111; }
    .result-timeout { background: linear-gradient(135deg, #cce5ff, #b8daff); border: 2px solid #007bff; color: #111; }
    .result-error { background: linear-gradient(135deg, #f8d7da, #f5c6cb); border: 2px solid #dc3545; color: #111; }
    .result-expired, .result-suspended { background: linear-gradient(135deg, #fff3cd, #ffeeba); border: 2px solid #ffc107; color: #111; }

    /* Table Section */
    .table-section { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
    .table-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
    .table-header .title { font-size: 15px; font-weight: 700; }
    .table-header .live-indicator { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--muted); }
    .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; animation: pulse 2s ease-in-out infinite; display: inline-block; }
    .live-dot.synced { background: #fbbf24; animation: none; }
    .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .attendance-table { width: 100%; border-collapse: collapse; min-width: 700px; }
    .attendance-table th { padding: 11px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 2px; background: var(--surface2); white-space: nowrap; }
    .attendance-table td { padding: 12px 16px; border-top: 1px solid var(--border); vertical-align: middle; font-size: 13px; }
    .attendance-table tr:first-child td { border-top: none; }
    .attendance-table .user-avatar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; }
    .attendance-table .user-avatar-placeholder { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }
    .attendance-table .user-name { font-size: 13px; font-weight: 600; }
    .attendance-table .user-role { font-size: 11px; color: var(--muted); }

    .status-inside, .status-done, .method-manual, .method-qr { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .status-inside { background: rgba(74,222,128,.15); color: #4ade80; }
    .status-done { background: var(--surface2); color: var(--muted); }
    .method-manual { background: rgba(251,191,36,.15); color: #fbbf24; }
    .method-qr { background: var(--surface2); color: var(--muted); }

    .empty-row td { padding: 48px; text-align: center; color: var(--muted); font-size: 14px; }
    .row-new { animation: slideIn .4s ease; }

    /* Animations */
    @keyframes scanMove { 0% { opacity: 0; transform: translateY(-60px); } 50% { opacity: 1; } 100% { opacity: 0; transform: translateY(60px); } }
    @keyframes popIn { from { opacity: 0; transform: scale(.88); } to { opacity: 1; transform: scale(1); } }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }
    @keyframes slideIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

    /* Buttons */
    .btn-sm { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all .2s; border: none; cursor: pointer; min-height: 40px; white-space: nowrap; }
    .btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
    .btn-secondary:hover { background: var(--border); }
    .btn-primary { background: var(--accent); color: #000; }
    .btn-primary:hover { opacity: .9; }

    /* ===== RESPONSIVE BREAKPOINTS ===== */
    @media (max-width: 1024px) {
        .scanner-grid { grid-template-columns: 1fr; gap: 20px; }
        .scanner-panel { max-width: 600px; margin: 0 auto; }
        .scanner-header-left h1 { font-size: 24px; }
    }

    @media (max-width: 768px) {
        .scanner-container { padding: 0 12px; }
        .scanner-header { flex-direction: column; align-items: flex-start; }
        .scanner-header-left h1 { font-size: 22px; }
        .scanner-header-left p { font-size: 13px; }
        .scanner-header-actions { width: 100%; }
        .scanner-header-actions .btn-sm { flex: 1; justify-content: center; min-width: 60px; font-size: 12px; padding: 6px 12px; min-height: 36px; }
        .scanner-panel { padding: 20px 16px; border-radius: 16px; }
        .live-clock-time { font-size: 28px; }
        .live-clock-date { font-size: 12px; }
        .cooldown-overlay { padding: 30px 16px; }
        .cooldown-number { font-size: 32px; }
        .reader-wrapper, .reader-wrapper #reader { min-height: 180px; }
        .manual-input-group input { font-size: 14px; padding: 8px 12px; min-height: 40px; }
        .manual-submit-btn { min-height: 40px; min-width: 40px; font-size: 16px; padding: 0 14px; }
        .manual-entry-panel { padding: 14px; }
        .manual-search { font-size: 14px; min-height: 42px; }
        .person-results { max-height: 220px; }
        .manual-btn-group button { font-size: 12px; padding: 8px; min-height: 40px; }
        .scanner-counters { flex-wrap: wrap; gap: 8px; }
        .counter-item { flex: 0 0 33.33%; }
        .counter-value { font-size: 18px; }
        .result-card { padding: 18px 16px; }
        .result-name { font-size: 16px; }
        .table-header { padding: 14px 16px; }
        .table-header .title { font-size: 14px; }
        .attendance-table th, .attendance-table td { padding: 10px 12px; font-size: 12px; }
        .attendance-table .user-avatar, .attendance-table .user-avatar-placeholder { width: 28px; height: 28px; font-size: 10px; }
        .attendance-table .user-name { font-size: 12px; }
        .attendance-table .user-role { font-size: 10px; }
        .empty-row td { padding: 32px 16px; font-size: 13px; }
    }

    @media (max-width: 480px) {
        .scanner-container { padding: 0 8px; }
        .scanner-header-left h1 { font-size: 20px; }
        .scanner-header-left p { font-size: 12px; }
        .scanner-header-actions .btn-sm { font-size: 11px; padding: 4px 10px; min-width: 50px; min-height: 32px; }
        .scanner-panel { padding: 16px 12px; border-radius: 12px; }
        .live-clock-time { font-size: 24px; }
        .live-clock-date { font-size: 11px; }
        .cooldown-overlay { padding: 20px 12px; }
        .cooldown-number { font-size: 28px; }
        .cooldown-text { font-size: 12px; }
        .reader-wrapper { min-height: 150px; border-radius: 10px; border-width: 2px; }
        .reader-wrapper #reader { min-height: 150px; }
        .scanner-status .status-badge { font-size: 11px; padding: 4px 10px; }
        .manual-input-group input { font-size: 13px; padding: 6px 10px; border-radius: 6px 0 0 6px; min-height: 36px; }
        .manual-submit-btn { min-height: 36px; min-width: 36px; font-size: 14px; padding: 0 12px; border-radius: 0 6px 6px 0; }
        .manual-hint { font-size: 10px; }
        .manual-entry-panel { padding: 12px; }
        .manual-entry-panel .panel-label { font-size: 10px; }
        .manual-search { font-size: 13px; min-height: 40px; padding-left: 34px; }
        .person-item { min-height: 42px; padding: 6px 8px; }
        .person-avatar { width: 26px; height: 26px; font-size: 11px; }
        .person-name { font-size: 12px; }
        .person-role { font-size: 9px; padding: 2px 7px; }
        .manual-btn-group button { font-size: 11px; padding: 6px; min-height: 36px; }
        .manual-msg { font-size: 11px; }
        .counter-value { font-size: 16px; }
        .counter-label { font-size: 10px; }
        .result-card { padding: 14px 12px; border-radius: 12px; }
        .result-name { font-size: 15px; }
        .result-membership { font-size: 12px; }
        .result-times .time-badge { font-size: 11px; padding: 4px 8px; }
        .result-message { font-size: 13px; }
        .table-header { padding: 10px 12px; }
        .table-header .title { font-size: 13px; }
        .table-header .live-indicator { font-size: 11px; }
        .attendance-table { min-width: 550px; }
        .attendance-table th, .attendance-table td { padding: 8px 10px; font-size: 11px; }
        .attendance-table .user-avatar, .attendance-table .user-avatar-placeholder { width: 24px; height: 24px; font-size: 9px; }
        .attendance-table .user-name { font-size: 11px; }
        .attendance-table .user-role { font-size: 9px; }
        .status-inside, .status-done, .method-manual, .method-qr { font-size: 9px; padding: 1px 6px; }
        .empty-row td { padding: 24px 12px; font-size: 12px; }
    }

    @media (max-width: 360px) {
        .scanner-panel { padding: 12px 8px; }
        .live-clock-time { font-size: 20px; }
        .cooldown-number { font-size: 24px; }
        .reader-wrapper { min-height: 120px; }
        .reader-wrapper #reader { min-height: 120px; }
        .manual-input-group input { font-size: 12px; padding: 4px 8px; min-height: 32px; }
        .manual-submit-btn { min-height: 32px; min-width: 32px; font-size: 12px; padding: 0 10px; }
        .manual-search { font-size: 12px; min-height: 36px; }
        .person-role { display: none; }
        .manual-btn-group button { font-size: 10px; min-height: 32px; }
        .counter-value { font-size: 14px; }
        .attendance-table { min-width: 450px; }
        .attendance-table th, .attendance-table td { padding: 6px 8px; font-size: 10px; }
        .attendance-table .user-avatar, .attendance-table .user-avatar-placeholder { width: 20px; height: 20px; font-size: 8px; }
        .attendance-table .user-name { font-size: 10px; }
    }

    @media (prefers-reduced-motion: reduce) {
        * { animation-duration: .01ms !important; transition-duration: .01ms !important; }
    }
</style>

<div class="scanner-container">

    {{-- Header --}}
    <div class="scanner-header">
        <div class="scanner-header-left">
            <h1>QR Attendance Scanner</h1>
            <p>Scan member or staff QR codes to record attendance</p>
        </div>
        <div class="scanner-header-actions">
            <a href="{{ route('attendance.qr-list') }}" class="btn-sm btn-secondary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><line x1="14" y1="14" x2="21" y2="14"/>
                    <line x1="14" y1="21" x2="21" y2="21"/><line x1="17.5" y1="14" x2="17.5" y2="21"/>
                </svg>
                QR Codes
            </a>
            <a href="{{ route('attendance.index') }}" class="btn-sm btn-secondary">Full Log</a>
        </div>
    </div>

    {{-- Scanner Grid --}}
    <div class="scanner-grid">

        {{-- LEFT: Scanner Panel --}}
        <div>
            <div class="scanner-panel">

                {{-- Live Clock --}}
                <div class="live-clock">
                    <div class="live-clock-time" id="liveClock">--:--:--</div>
                    <div class="live-clock-date" id="liveDate"></div>
                </div>

                {{-- Cooldown Overlay --}}
                <div class="cooldown-overlay" id="cooldownOverlay">
                    <div class="cooldown-number" id="cooldownNumber">3</div>
                    <div class="cooldown-text">
                        Next scan ready in <span id="cooldownSec">3</span>s…
                    </div>
                    <div class="cooldown-bar-track">
                        <div class="cooldown-bar" id="cooldownBar"></div>
                    </div>
                </div>

                {{-- Camera Reader --}}
                <div class="reader-wrapper">
                    <div id="reader"></div>
                </div>

                {{-- Scan Line --}}
                <div class="scan-line"></div>

                {{-- Scanner Status --}}
                <div class="scanner-status">
                    <span class="status-badge" id="statusBadge">
                        <span class="status-dot" id="statusDot"></span>
                        Scanner ready
                    </span>
                </div>

                <p class="scanner-hint">Point camera at any member, staff, or trainer QR code</p>

                {{-- Manual QR Input --}}
                <div class="manual-input-group">
                    <input type="text" id="manualInput" placeholder="Paste QR data or type ID..." />
                    <button class="manual-submit-btn" id="manualSubmitBtn" onclick="processManual()">→</button>
                </div>
                <small class="manual-hint">
                    Type a numeric ID for quick lookup · 3-second cooldown between scans
                </small>

                {{-- Manual Entry Panel --}}
                <div class="manual-entry-panel">
                    <div class="panel-label">Manual Attendance Entry</div>

                    {{-- Searchable person picker --}}
                    <div class="person-picker" id="personPicker">
                        <div class="person-search-wrap">
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input type="text" id="manualSearch" class="manual-search"
                                   placeholder="Search member or staff…" autocomplete="off" />
                        </div>

                        {{-- Results list (built by JS) --}}
                        <div class="person-results" id="personResults" role="listbox" hidden></div>

                        {{-- Chosen person --}}
                        <div class="person-selected" id="personSelected" hidden>
                            <span class="label">Selected</span>
                            <span class="value" id="personSelectedName"></span>
                            <button type="button" id="personSelectedClear" title="Clear selection" aria-label="Clear selection">×</button>
                        </div>

                        {{-- Hidden select: source of the people list AND holder of the chosen value --}}
                        <select id="manualMemberId" class="manual-select-hidden" tabindex="-1" aria-hidden="true">
                            <option value="">— Select Person —</option>
                            <optgroup label="Members">
                                @foreach($allMembers as $m)
                                    <option value="{{ $m->id }}">
                                        {{ $m->name }} (Member) {{ $m->status === 'Expired' ? '⚠️' : '' }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Staff / Instructors / Admin">
                                @forelse($allStaff ?? [] as $s)
                                    <option value="staff-{{ data_get($s, 'id', '') }}">
                                        {{ data_get($s, 'name', '') }} ({{ ucfirst(data_get($s, 'role', '')) }})
                                    </option>
                                @empty
                                    <option disabled>No Staff Records Found</option>
                                @endforelse
                            </optgroup>
                        </select>
                    </div>

                    <div class="manual-btn-group">
                        <button class="btn-timein" id="timeInBtn" onclick="manualRecord('timein')">↓ Time In</button>
                        <button class="btn-timeout" id="timeOutBtn" onclick="manualRecord('timeout')">↑ Time Out</button>
                    </div>
                    <div class="manual-msg" id="manualMsg"></div>
                </div>

                {{-- Counters --}}
                <div class="scanner-counters">
                    <div class="counter-item">
                        <div class="counter-value green" id="insideCount">{{ $insideNow }}</div>
                        <div class="counter-label">Inside Now</div>
                    </div>
                    <div class="counter-item">
                        <div class="counter-value yellow" id="todayTotal">{{ $todayLogs->count() }}</div>
                        <div class="counter-label">Today's Visits</div>
                    </div>
                    <div class="counter-item">
                        <div class="counter-value white">{{ now()->format('d') }}</div>
                        <div class="counter-label">{{ now()->format('M Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Result Card --}}
            <div class="result-card" id="resultCard">
                <div class="result-avatar" id="resultAvatar"></div>
                <div class="result-name" id="resultName"></div>
                <div class="result-membership" id="resultMembership"></div>
                <div class="result-times" id="resultTimes"></div>
                <div class="result-message" id="resultMessage"></div>
            </div>
        </div>

        {{-- RIGHT: Live Attendance Table --}}
        <div class="table-section">
            <div class="table-header">
                <div class="title">Today's Attendance — {{ now()->format('F d, Y') }}</div>
                <div class="live-indicator">
                    <span class="live-dot" id="liveDot"></span>
                    <span id="liveLabel">Live</span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Duration</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="todayLogBody">
                        @forelse($todayLogs as $log)
                        @php
                            $personName  = $log->member?->name ?? $log->user?->name ?? 'Staff';
                            $personRole  = $log->member?->membership_type ?? ucfirst($log->user?->role ?? 'Staff');
                            $personPhoto = $log->member?->user?->photo
                                        ?? $log->member?->photo
                                        ?? $log->user?->photo;
                            $isStaffRow  = !$log->member_id;
                            $dataKey     = $isStaffRow ? 'staff-'.$log->staff_user_id : $log->member_id;
                        @endphp
                        <tr id="log-{{ $log->id }}" data-member="{{ $dataKey }}">
                            <td>
                                @if($personPhoto)
                                    <img src="{{ asset('storage/'.$personPhoto) }}" class="user-avatar" alt=""/>
                                @else
                                    <div class="user-avatar-placeholder" style="background:{{ !$isStaffRow ? 'rgba(200,255,0,0.08)' : 'rgba(96,165,250,0.08)' }};border:1px solid {{ !$isStaffRow ? 'rgba(200,255,0,0.15)' : 'rgba(96,165,250,0.15)' }};color:{{ !$isStaffRow ? 'var(--accent)' : '#60a5fa' }};">
                                        {{ strtoupper(substr($personName,0,1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="user-name">{{ $personName }}</div>
                                <div class="user-role">{{ $personRole }}</div>
                            </td>
                            <td>{{ $log->time_in?->format('h:i A') ?? '—' }}</td>
                            <td>
                                @if($log->time_out)
                                    {{ $log->time_out->format('h:i A') }}
                                @else
                                    <span class="status-inside">Inside</span>
                                @endif
                            </td>
                            <td style="color:var(--muted);">{{ $log->duration_formatted }}</td>
                            <td>
                                <span class="{{ $log->entry_method === 'manual' ? 'method-manual' : 'method-qr' }}">
                                    {{ $log->entry_method === 'manual' ? 'Manual' : 'QR Scan' }}
                                </span>
                            </td>
                            <td>
                                @if($log->time_out)
                                    <span class="status-done">Done</span>
                                @else
                                    <span class="status-inside">Inside</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow" class="empty-row">
                            <td colspan="7">No scans today yet. Start scanning!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- ─── SCRIPTS ─── --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════════════════
//  APEX — Attendance Scanner JS (Fully Responsive)
// ═══════════════════════════════════════════════════════════════════════════

// Escape text before putting it into innerHTML
function esc(str) {
  return String(str ?? '').replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

// ── Live Clock ─────────────────────────────────────────────────────────────
function updateClock() {
  const now = new Date();
  const clockEl = document.getElementById('liveClock');
  const dateEl  = document.getElementById('liveDate');
  if (clockEl) clockEl.textContent =
    now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
  if (dateEl) dateEl.textContent =
    now.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}
setInterval(updateClock, 1000);
updateClock();

// ── Cooldown state ─────────────────────────────────────────────────────────
let scanning      = false;
let lastScanned   = '';
let cooldownTimer = null;
const COOLDOWN_MS = 3000;

function startCooldown() {
  scanning = true;
  setStatus('locked');

  const overlay = document.getElementById('cooldownOverlay');
  const reader  = document.getElementById('reader');
  if (overlay) overlay.style.display = 'block';
  if (reader)  reader.style.opacity  = '0.15';
  setButtonsEnabled(false);

  let remaining = COOLDOWN_MS / 1000;
  const numEl = document.getElementById('cooldownNumber');
  const secEl = document.getElementById('cooldownSec');
  if (numEl) numEl.textContent = remaining;
  if (secEl) secEl.textContent = remaining;

  const bar = document.getElementById('cooldownBar');
  if (bar) {
    bar.style.transition = 'none';
    bar.style.width      = '100%';
    bar.getBoundingClientRect(); // force reflow
    bar.style.transition = `width ${COOLDOWN_MS}ms linear`;
    bar.style.width      = '0%';
  }

  clearInterval(cooldownTimer);
  cooldownTimer = setInterval(() => {
    remaining = Math.max(0, remaining - 1);
    if (numEl) numEl.textContent = remaining;
    if (secEl) secEl.textContent = remaining;
  }, 1000);

  setTimeout(() => {
    clearInterval(cooldownTimer);
    scanning    = false;
    lastScanned = '';
    if (overlay) overlay.style.display = 'none';
    if (reader)  reader.style.opacity  = '1';
    setButtonsEnabled(true);
    setStatus('ready');
  }, COOLDOWN_MS);
}

function setStatus(state) {
  const badge = document.getElementById('statusBadge');
  if (!badge) return;
  if (state === 'ready') {
    badge.innerHTML = `<span class="status-dot"></span> Scanner ready`;
    badge.style.background = 'rgba(74,222,128,0.12)';
    badge.style.color      = '#4ade80';
    badge.style.border     = '1px solid rgba(74,222,128,0.3)';
  } else {
    badge.innerHTML = `<span class="status-dot locked"></span> Cooldown — next scan in 3s`;
    badge.style.background = 'rgba(248,113,113,0.12)';
    badge.style.color      = '#f87171';
    badge.style.border     = '1px solid rgba(248,113,113,0.3)';
  }
}

function setButtonsEnabled(enabled) {
  ['manualSubmitBtn', 'timeInBtn', 'timeOutBtn'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.disabled      = !enabled;
    el.style.opacity = enabled ? '1' : '0.4';
    el.style.cursor  = enabled ? 'pointer' : 'not-allowed';
  });
  const input = document.getElementById('manualInput');
  if (input) {
    input.disabled      = !enabled;
    input.style.opacity = enabled ? '1' : '0.5';
  }
}

// ── QR Scanner init ─────────────────────────────────────────────────────────
function showCameraError(message) {
  const readerEl = document.getElementById('reader');
  if (!readerEl) return;
  readerEl.innerHTML = `
    <div style="color:rgba(255,255,255,.72);text-align:center;padding:32px 20px;font-size:13px;line-height:1.6;">
      ${esc(message)}<br>
      <span style="color:rgba(255,255,255,.5);">Use Manual Entry below.</span>
    </div>`;
}

function getCameraErrorMessage(error) {
  if (!error) return 'Camera access is unavailable right now. Please allow camera access to continue.';

  const name    = (error.name || '').toString();
  const message = ((error.message || error) + '').toLowerCase();

  if (name === 'NotAllowedError' || message.includes('permission'))
    return 'Camera permission was blocked. Please allow access to your camera and refresh the page.';
  if (name === 'NotFoundError' || message.includes('no camera') || message.includes('device not found'))
    return 'No camera was detected on this device. Please connect a camera or use Manual Entry.';
  if (name === 'NotReadableError' || message.includes('in use'))
    return 'Your camera is already in use by another app. Close it and try again.';
  if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError' || message.includes('constraint'))
    return 'This browser/device rejected the requested camera mode. Please retry in a normal browser tab.';
  if (name === 'NotSupportedError' || message.includes('secure context'))
    return 'This page must be loaded over HTTPS (or localhost) for camera access.';

  return 'Camera access failed: ' + (error.message || error);
}

async function startQrScanner() {
  if (typeof Html5Qrcode === 'undefined') {
    showCameraError('The QR scanner library failed to load. Refresh the page and try again.');
    return;
  }
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    showCameraError('This browser does not support camera access (HTTPS is required).');
    return;
  }

  const scanConfig = { fps: 10, qrbox: { width: 240, height: 240 } };

  const onScanSuccess = (decodedText) => {
    if (scanning) return;
    if (decodedText === lastScanned) return;
    lastScanned = decodedText;
    startCooldown();
    processQR(decodedText);
  };

  // html5-qrcode 2.3.x leaves its internal state "under transition" after a failed
  // start(), so retrying on the SAME instance only throws "Cannot transition to a
  // new state, already under transition" and hides the real problem. Use a fresh
  // instance for every attempt, and report the FIRST error (the real cause).
  let firstError = null;

  async function tryStart(cameraConfig) {
    const qr = new Html5Qrcode('reader');
    try {
      await qr.start(cameraConfig, scanConfig, onScanSuccess, () => {});
      return true;
    } catch (err) {
      if (!firstError) firstError = err;
      console.warn('Camera start attempt failed:', cameraConfig, err);
      try { await qr.clear(); } catch (_) {}
      const reader = document.getElementById('reader');
      if (reader) reader.innerHTML = '';
      return false;
    }
  }

  // html5-qrcode requires the camera config to have EXACTLY ONE key
  // (either facingMode or a deviceId string) — an empty object is invalid.
  if (await tryStart({ facingMode: { ideal: 'environment' } })) return;
  if (await tryStart({ facingMode: 'user' })) return;

  // Last resort: try the available devices by id
  try {
    const cameras = await Html5Qrcode.getCameras();
    const ordered = (cameras || []).slice().sort((x, y) =>
      (/back|rear|environment/i.test(y.label) ? 1 : 0) - (/back|rear|environment/i.test(x.label) ? 1 : 0));
    for (const cam of ordered) {
      if (await tryStart(cam.id)) return;
    }
  } catch (err) {
    if (!firstError) firstError = err;
  }

  console.error('Html5Qrcode failed to start:', firstError);
  showCameraError(getCameraErrorMessage(firstError));
}

startQrScanner();

// ── Manual input ───────────────────────────────────────────────────────────
function processManual() {
  if (scanning) return;
  const input = document.getElementById('manualInput');
  const val   = input ? input.value.trim() : '';
  if (!val) return;
  startCooldown();
  processQR(val);
  input.value = '';
}

document.getElementById('manualInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') processManual();
});

// ── Searchable person picker (Manual Attendance Entry) ─────────────────────
// The hidden <select id="manualMemberId"> is both the source of the people list
// and the holder of the chosen value, so manualRecord() keeps working unchanged.
const manualSelect       = document.getElementById('manualMemberId');
const manualSearch       = document.getElementById('manualSearch');
const personPicker       = document.getElementById('personPicker');
const personResults      = document.getElementById('personResults');
const personSelected     = document.getElementById('personSelected');
const personSelectedName = document.getElementById('personSelectedName');
const personSelectedClear = document.getElementById('personSelectedClear');

// "Carlos Bautista (Member) ⚠️"  →  { name, role, expired }
function parsePerson(text) {
  const expired = text.includes('⚠️');
  const clean   = text.replace('⚠️', '').replace(/\s+/g, ' ').trim();
  const m       = clean.match(/^(.*)\s+\(([^)]+)\)$/);
  return { name: m ? m[1] : clean, role: m ? m[2] : '', expired };
}

const manualSource = Array.from(manualSelect.querySelectorAll('optgroup')).map(group => ({
  label:   group.label,
  isStaff: /staff/i.test(group.label),
  people:  Array.from(group.querySelectorAll('option'))
    .filter(o => o.value && !o.disabled)
    .map(o => {
      const text = o.textContent.replace(/\s+/g, ' ').trim();
      return Object.assign({ value: o.value, text }, parsePerson(text));
    })
}));

let pickerItems = [];   // [{ el, person }]
let activeIdx   = -1;

function openResults()  { personResults.hidden = false; }
function closeResults() { personResults.hidden = true; }

function setActive(i) {
  if (pickerItems[activeIdx]) pickerItems[activeIdx].el.classList.remove('active');
  activeIdx = i;
  if (pickerItems[i]) {
    pickerItems[i].el.classList.add('active');
    pickerItems[i].el.scrollIntoView({ block: 'nearest' });
  }
}

function renderPeople(query) {
  const tokens = (query || '').toLowerCase().split(/\s+/).filter(Boolean);

  personResults.innerHTML = '';
  pickerItems = [];
  activeIdx   = -1;
  let total   = 0;

  manualSource.forEach(group => {
    const found = group.people.filter(p => {
      const hay = p.text.toLowerCase();
      return tokens.every(t => hay.includes(t));
    });
    if (!found.length) return;

    const head = document.createElement('div');
    head.className = 'person-group';
    const hl = document.createElement('span'); hl.textContent = group.label;
    const hc = document.createElement('span'); hc.textContent = found.length;
    head.append(hl, hc);
    personResults.appendChild(head);

    found.forEach(p => {
      const kind = group.isStaff ? 'staff' : 'member';

      const el = document.createElement('div');
      el.className = 'person-item';
      el.setAttribute('role', 'option');

      const av = document.createElement('div');
      av.className   = 'person-avatar ' + kind;
      av.textContent = (p.name || '?').charAt(0).toUpperCase();

      const nm = document.createElement('div');
      nm.className   = 'person-name';
      nm.textContent = p.name;
      nm.title       = p.name;

      el.append(av, nm);

      if (p.expired) {
        const warn = document.createElement('span');
        warn.className   = 'person-warn';
        warn.textContent = 'Expired';
        el.appendChild(warn);
      }

      const rl = document.createElement('span');
      rl.className   = 'person-role ' + kind;
      rl.textContent = p.role;
      el.appendChild(rl);

      // mousedown (not click) so the search box doesn't lose focus first
      el.addEventListener('mousedown', e => { e.preventDefault(); choosePerson(p); });
      el.addEventListener('mousemove', () => {
        const idx = pickerItems.findIndex(x => x.el === el);
        if (idx !== activeIdx) setActive(idx);
      });

      personResults.appendChild(el);
      pickerItems.push({ el, person: p });
      total++;
    });
  });

  if (!total) {
    const empty = document.createElement('div');
    empty.className   = 'person-empty';
    empty.textContent = 'No matches found';
    personResults.appendChild(empty);
    return;
  }

  setActive(0);
}

function choosePerson(p) {
  manualSelect.value = p.value;
  personSelectedName.textContent = p.text;
  personSelected.hidden = false;
  manualSearch.value = '';
  closeResults();
}

function resetManualSearch() {
  manualSearch.value = '';
  manualSelect.value = '';
  personSelected.hidden = true;
  closeResults();
}

manualSearch.addEventListener('focus', () => {
  openResults();
  renderPeople(manualSearch.value);
});

manualSearch.addEventListener('input', () => {
  // typing again means the person is choosing someone else
  manualSelect.value = '';
  personSelected.hidden = true;
  openResults();
  renderPeople(manualSearch.value);
});

manualSearch.addEventListener('keydown', e => {
  const n = pickerItems.length;

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    if (personResults.hidden) { openResults(); renderPeople(manualSearch.value); return; }
    if (n) setActive((activeIdx + 1) % n);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (n) setActive((activeIdx - 1 + n) % n);
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (!personResults.hidden && pickerItems[activeIdx]) choosePerson(pickerItems[activeIdx].person);
  } else if (e.key === 'Escape') {
    if (!personResults.hidden) closeResults(); else resetManualSearch();
  }
});

personSelectedClear.addEventListener('click', () => {
  resetManualSearch();
  manualSearch.focus();
});

// Close the list when clicking anywhere outside the picker
document.addEventListener('click', e => {
  if (!personPicker.contains(e.target)) closeResults();
});

renderPeople('');

// ── Core AJAX ──────────────────────────────────────────────────────────────
function processQR(qrData) {
  fetch('{{ route("attendance.scan.process") }}', {
    method:  'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: 'qr_data=' + encodeURIComponent(qrData)
  })
  .then(async r => {
    const data = await r.json().catch(() => ({}));
    if (!r.ok && !data.message) data.message = `Attendance could not be saved (HTTP ${r.status}).`;
    return data;
  })
  .then(data => showResult(data))
  .catch(error => showResult({
    success: false,
    message: error.message || 'Connection error. Attendance was not saved.'
  }));
}

// ── Manual entry panel ─────────────────────────────────────────────────────
function manualRecord(action) {
  if (scanning) return;
  const mid = document.getElementById('manualMemberId').value;
  const msg = document.getElementById('manualMsg');

  if (!mid) {
    msg.innerHTML = '<span style="color:#fbbf24;">Select a person first.</span>';
    return;
  }

  msg.innerHTML = '<span style="color:rgba(255,255,255,0.5);">Processing…</span>';
  startCooldown();

  fetch('{{ route("attendance.manual") }}', {
    method:  'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: 'manual_member_id=' + encodeURIComponent(mid) +
          '&manual_action='   + encodeURIComponent(action)
  })
  .then(r => { if (!r.ok) throw new Error('Server error'); return r.json(); })
  .then(data => {
    msg.innerHTML = data.success
      ? `<span style="color:#4ade80;">✅ ${esc(data.message)}</span>`
      : `<span style="color:#f87171;">❌ ${esc(data.message)}</span>`;

    if (data.success) {
      if (action === 'timein') appendLogRow(data, 'timein');
      else                     updateLogRowTimeout(data);
      playBeep(true);
      resetManualSearch();   // clears the search box, the chip and the selection
    } else {
      playBeep(false);
    }
  })
  .catch(() => {
    msg.innerHTML = '<span style="color:#f87171;">❌ Connection error.</span>';
    playBeep(false);
  });
}

// ── Show result card ───────────────────────────────────────────────────────
function showResult(data) {
  const card = document.getElementById('resultCard');
  if (!card) return;

  const isStaff = data.is_staff || false;

  const avatar = data.photo
    ? `<img src="${esc(data.photo)}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:4px solid #fff;margin:0 auto;display:block;">`
    : `<div style="width:80px;height:80px;border-radius:50%;
        background:${isStaff ? 'linear-gradient(135deg,#60a5fa,#3b82f6)' : 'linear-gradient(135deg,#c8ff00,#4ade80)'};
        display:flex;align-items:center;justify-content:center;font-size:28px;
        font-weight:700;color:#111;margin:0 auto;">
        ${esc((data.member || '?').charAt(0).toUpperCase())}</div>`;

  document.getElementById('resultAvatar').innerHTML       = avatar;
  document.getElementById('resultName').textContent       = data.member     || 'Unknown';
  document.getElementById('resultMembership').textContent = data.membership || '';

  let cls = 'result-error';
  if (data.success && data.action === 'timein')  { cls = 'result-timein';  appendLogRow(data, 'timein'); }
  if (data.success && data.action === 'timeout') { cls = 'result-timeout'; updateLogRowTimeout(data); }
  if (data.status === 'expired')                   cls = 'result-expired';
  if (data.status === 'suspended')                 cls = 'result-suspended';

  // keep the base "result-card" class so the styles keep applying
  card.className = 'result-card ' + cls + ' visible';

  const timesEl = document.getElementById('resultTimes');
  if (data.success && data.action === 'timein') {
    timesEl.innerHTML =
      `<span class="time-badge">Time In: <strong>${esc(data.time_in)}</strong></span>
       ${!isStaff ? `<span class="time-badge">Valid Until: <strong>${esc(data.end_date || 'N/A')}</strong></span>` : ''}`;
  } else if (data.success && data.action === 'timeout') {
    timesEl.innerHTML =
      `<span class="time-badge">In: <strong>${esc(data.time_in)}</strong></span>
       <span class="time-badge">Out: <strong>${esc(data.time_out)}</strong></span>
       <span class="time-badge">Duration: <strong>${esc(data.duration)}</strong></span>`;
  } else {
    timesEl.innerHTML = '';
  }

  document.getElementById('resultMessage').textContent = data.message || '';
  playBeep(data.success);
}

// ── Inject a new Time-In row ───────────────────────────────────────────────
function appendLogRow(data, action) {
  const tbody = document.getElementById('todayLogBody');
  if (!tbody) return;

  const emptyRow = document.getElementById('emptyRow');
  if (emptyRow) emptyRow.remove();

  // Replace any existing row for the same person (don't double count visits)
  const existing = tbody.querySelector(`[data-member="${data.member_id}"]`);
  const wasInside = existing ? !!existing.querySelector('.status-inside') && existing.cells[6]?.querySelector('.status-inside') : false;
  if (existing) existing.remove();

  const isStaff = data.is_staff ||
                  (typeof data.member_id === 'string' && data.member_id.startsWith('staff-'));

  const initial = esc((data.member || '?').charAt(0).toUpperCase());
  const avatar = data.photo
    ? `<img src="${esc(data.photo)}" class="user-avatar" alt="">`
    : `<div class="user-avatar-placeholder" style="background:${isStaff ? 'rgba(96,165,250,0.08)' : 'rgba(200,255,0,0.08)'};border:1px solid ${isStaff ? 'rgba(96,165,250,0.15)' : 'rgba(200,255,0,0.15)'};color:${isStaff ? '#60a5fa' : 'var(--accent)'};">${initial}</div>`;

  const methodBadge = data.entry_method === 'manual'
    ? '<span class="method-manual">Manual</span>'
    : '<span class="method-qr">QR Scan</span>';

  const insideBadge = '<span class="status-inside">Inside</span>';

  const row = document.createElement('tr');
  row.setAttribute('data-member', data.member_id);
  row.classList.add('row-new');
  row.innerHTML = `
    <td>${avatar}</td>
    <td>
      <div class="user-name">${esc(data.member)}</div>
      <div class="user-role">${esc(data.membership || '')}</div>
    </td>
    <td>${esc(data.time_in)}</td>
    <td>${insideBadge}</td>
    <td style="color:var(--muted);">—</td>
    <td>${methodBadge}</td>
    <td>${insideBadge}</td>`;

  tbody.prepend(row);

  const insideEl = document.getElementById('insideCount');
  const totalEl  = document.getElementById('todayTotal');
  if (insideEl && !wasInside) insideEl.textContent = (parseInt(insideEl.textContent) || 0) + 1;
  if (totalEl)                totalEl.textContent  = (parseInt(totalEl.textContent)  || 0) + 1;
}

// ── Update an existing row on Time Out ─────────────────────────────────────
function updateLogRowTimeout(data) {
  const row = document.querySelector(`#todayLogBody [data-member="${data.member_id}"]`);
  if (row) {
    const cells = row.querySelectorAll('td');
    if (cells.length >= 7) {
      cells[3].textContent = data.time_out;
      cells[4].textContent = data.duration;
      cells[6].innerHTML   = '<span class="status-done">Done</span>';
    }
    row.classList.add('row-new');
    setTimeout(() => row.classList.remove('row-new'), 500);
  }

  const insideEl = document.getElementById('insideCount');
  if (insideEl) insideEl.textContent = Math.max(0, (parseInt(insideEl.textContent) || 1) - 1);
}

// ── Beep ───────────────────────────────────────────────────────────────────
function playBeep(success) {
  try {
    const ctx  = new (window.AudioContext || window.webkitAudioContext)();
    const osc  = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.value = success ? 880 : 300;
    osc.type = 'sine';
    gain.gain.setValueAtTime(0.3, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
    osc.start();
    osc.stop(ctx.currentTime + 0.4);
    osc.onended = () => ctx.close();
  } catch (e) { /* audio not available */ }
}

// ── Auto-refresh polling ───────────────────────────────────────────────────
const POLL_INTERVAL_MS = 10000;

function getRenderedKeys() {
  return Array.from(
    document.querySelectorAll('#todayLogBody tr[data-member]')
  ).map(r => r.getAttribute('data-member'));
}

function pollAttendance() {
  fetch('{{ route("attendance.live") }}', {
    method:  'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ known_keys: getRenderedKeys() })
  })
  .then(r => r.ok ? r.json() : null)
  .then(data => {
    if (!data) return;

    (data.rows || []).forEach(row => {
      if (document.querySelector(`#todayLogBody [data-member="${row.key}"]`)) return;

      const tbody    = document.getElementById('todayLogBody');
      const emptyRow = document.getElementById('emptyRow');
      if (emptyRow) emptyRow.remove();

      const el = document.createElement('tr');
      el.setAttribute('data-member', row.key);
      el.classList.add('row-new');
      el.innerHTML = row.html;
      tbody.prepend(el);
    });

    const insideEl = document.getElementById('insideCount');
    const totalEl  = document.getElementById('todayTotal');
    if (data.inside_count !== undefined && insideEl) insideEl.textContent = data.inside_count;
    if (data.today_total  !== undefined && totalEl)  totalEl.textContent  = data.today_total;

    flashLiveIndicator();
  })
  .catch(() => {});
}

function flashLiveIndicator() {
  const dot   = document.getElementById('liveDot');
  const label = document.getElementById('liveLabel');
  if (!dot) return;
  dot.classList.add('synced');
  if (label) label.textContent = 'Synced';
  setTimeout(() => {
    dot.classList.remove('synced');
    if (label) label.textContent = 'Live';
  }, 600);
}

setInterval(pollAttendance, POLL_INTERVAL_MS);
</script>

@endsection