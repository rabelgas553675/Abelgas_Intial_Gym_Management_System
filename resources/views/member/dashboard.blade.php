@extends('layouts.member')
@section('title', 'Dashboard – APEX')
@section('active', 'dashboard')

@section('content')

<style>
    /* Colors come from layouts/member.blade.php (charcoal & gold).
       Compact spacing scale: 8 · 12 · 16 · 20 · 24 */
    .dashboard-container { max-width:1400px; margin:0 auto; padding:4px 24px 40px; }

    /* ── Welcome ── */
    .welcome-header { margin-bottom:24px; }
    .welcome-header h1 { font-size:32px; font-weight:700; margin-bottom:6px; line-height:1.15; }
    .welcome-header h1 span { color:var(--accent); }
    .welcome-header p { color:var(--muted); font-size:14px; line-height:1.5; }

    /* ── Banners ── */
    .warning-banner { background:rgba(251,191,36,0.1); border:1px solid rgba(251,191,36,0.3); border-radius:14px; padding:14px 20px; margin-bottom:20px; display:flex; align-items:center; gap:14px; }
    .warning-banner .icon { font-size:20px; flex-shrink:0; }
    .warning-banner .text { line-height:1.45; }
    .warning-banner .text strong { color:var(--warning); }
    .warning-banner .text .sub { font-size:13px; color:var(--muted); margin-top:2px; line-height:1.5; }
    .warning-banner .text a { color:var(--accent); margin-left:6px; text-decoration:none; }
    .warning-banner .text a:hover { text-decoration:underline; }
    .close-advance-banner {
        margin-left:auto; border:none; border-radius:999px; width:32px; height:32px; flex-shrink:0;
        background:rgba(255,255,255,0.06); color:var(--text); font-size:22px; line-height:1;
        cursor:pointer; opacity:0.8; transition:opacity .2s, transform .2s;
    }
    .close-advance-banner:hover { opacity:1; transform:scale(1.04); }

    /* ── Layout ── */
    .top-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; align-items:start; }

    /* ── Cards ── */
    .card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px; transition:transform .2s, box-shadow .2s, border-color .3s; }
    .card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.15); border-color:rgba(224,169,59,0.5); }
    .card-header { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:16px; }
    .card-header .title { font-size:16px; font-weight:700; }
    .card-header svg { width:20px; height:20px; fill:none; stroke:var(--accent); stroke-width:2; flex-shrink:0; }

    /* ── Current Subscription ── */
    .sub-card { display:flex; flex-direction:column; gap:16px; }
    .sub-card .card-header { margin-bottom:0; }

    /* plan + duration share one row */
    .sub-plan { display:flex; align-items:center; gap:16px; }
    .sub-plan .icon-wrap { width:56px; height:56px; display:flex; align-items:center; justify-content:center; background:var(--accent-soft); color:var(--accent); border-radius:14px; flex-shrink:0; }
    .sub-plan .icon-wrap svg { width:28px; height:28px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
    .sub-plan .info .label { font-size:11.5px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; font-weight:600; margin-bottom:3px; }
    .sub-plan .info .value { font-size:20px; font-weight:700; color:var(--text); line-height:1.2; }

    .sub-details { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px; }
    .sub-details .item { background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:12px 16px; }
    .sub-details .item .label { font-size:10.5px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; font-weight:600; margin-bottom:4px; }
    .sub-details .item .value { font-weight:700; font-size:15px; line-height:1.35; }

    .coach-section { display:grid; gap:12px; }
    .coach-box { background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:14px 16px; }
    .coach-box.current  { border-color:rgba(224,169,59,0.55); }
    .coach-box.upcoming { border-style:dashed; border-color:rgba(224,169,59,0.45); }
    .coach-tag { font-size:11px; color:var(--accent); text-transform:uppercase; letter-spacing:1px; font-weight:700; margin-bottom:6px; }
    .coach-row { display:flex; justify-content:space-between; align-items:center; gap:12px; font-size:13.5px; padding:4px 0; line-height:1.35; }
    .coach-row > span:first-child { color:var(--muted); }
    .coach-row strong { font-weight:600; text-align:right; }
    .coach-status { display:inline-block; padding:3px 11px; border-radius:40px; font-size:11px; font-weight:700; background:rgba(74,222,128,0.15); color:#4ade80; }
    .coach-status.scheduled, .coach-status.pending { background:rgba(224,169,59,0.15); color:var(--accent); }
    .coach-status.expired { background:rgba(248,113,113,0.15); color:#f87171; }
    .coach-none { color:var(--muted); font-size:13.5px; font-weight:600; text-align:center; padding:2px 0; }

    /* Status / period / days remaining: tighter than the component's defaults is not needed,
       so only the panel look and small size bumps are set here (prefixed to win over the component) */
    .sub-status { background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:16px 18px 18px; }
    .sub-status .mx-head   { margin-bottom:12px; }
    .sub-status .mx-period { margin-bottom:12px; }
    .sub-status .mx-days   { margin-bottom:8px; }
    .sub-status .mx-days-value { font-size:20px; }
    .sub-status .mx-track  { height:10px; }
    .sub-status .mx-foot   { margin-top:8px; }
    .sub-status .mx-alert  { margin-top:12px; }

    .sub-actions { display:flex; gap:12px; }
    .sub-actions .btn { min-height:48px; font-size:14.5px; }

    /* ── Buttons ── */
    .btn { padding:10px 22px; border:none; border-radius:10px; font-weight:700; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; justify-content:center; gap:8px; font-size:14px; min-height:44px; text-decoration:none; font-family:'DM Sans',sans-serif; }
    .btn-primary { background:linear-gradient(135deg, var(--accent-2), var(--accent-dark)); color:#1a1a1a; }
    .btn-primary:hover { opacity:.92; transform:translateY(-1px); box-shadow:0 6px 20px rgba(224,169,59,0.3); }
    .btn-secondary { background:var(--surface2); color:var(--text); border:1px solid var(--border); }
    .btn-secondary:hover { border-color:var(--accent); color:var(--accent); }
    .btn-sm { padding:6px 14px; font-size:12px; min-height:36px; }

    .status-badge { display:inline-block; padding:4px 14px; border-radius:40px; font-size:12px; font-weight:600; background:rgba(74,222,128,0.15); color:#4ade80; }
    .status-badge.inactive { background:rgba(248,113,113,0.15); color:#f87171; }

    /* ── Profile card ── */
    .profile-card { display:flex; flex-direction:column; }

    .pf-hero { display:flex; flex-direction:column; align-items:center; text-align:center; gap:4px;
               padding:20px 16px 18px; margin-bottom:4px; border-radius:14px;
               background:linear-gradient(180deg, var(--accent-soft), transparent); }
    .pf-avatar { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--accent);
                 box-shadow:0 0 0 5px rgba(224,169,59,0.12); margin-bottom:8px; }
    .pf-avatar-ph { width:80px; height:80px; border-radius:50%; background:var(--surface2); border:3px solid var(--accent);
                    box-shadow:0 0 0 5px rgba(224,169,59,0.12); display:flex; align-items:center; justify-content:center;
                    font-family:'Bebas Neue',sans-serif; font-size:30px; letter-spacing:1px; color:var(--accent); margin-bottom:8px; }
    .pf-name { font-size:19px; font-weight:700; line-height:1.25; text-transform:capitalize; }
    .pf-email { font-size:13px; color:var(--muted); word-break:break-all; line-height:1.4; }
    .pf-hero .status-badge { margin-top:8px; }

    .pf-list { display:flex; flex-direction:column; }
    .pf-row { display:flex; align-items:center; gap:14px; padding:11px 4px; border-bottom:1px solid var(--border); }
    .pf-row:last-child { border-bottom:none; }
    .pf-icon { width:38px; height:38px; border-radius:11px; background:var(--accent-soft); color:var(--accent);
               display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .pf-icon svg { width:17px; height:17px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
    .pf-text { min-width:0; flex:1; }
    .pf-label { font-size:10.5px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:2px; }
    .pf-value { font-weight:600; font-size:14.5px; line-height:1.35; word-break:break-word; }
    .pf-value.empty { color:var(--muted); font-weight:500; }

    .profile-card .btn { margin-top:16px; width:100%; min-height:48px; }

    /* ── Explore banner ── */
    .explore-banner { background:linear-gradient(135deg, var(--accent-2), var(--accent-dark)); border-radius:16px; padding:22px 28px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
    .explore-banner .title { font-size:18px; font-weight:700; color:#1a1a1a; margin-bottom:4px; }
    .explore-banner .sub { font-size:13.5px; line-height:1.5; color:rgba(0,0,0,0.6); }
    .explore-banner .btn-dark { background:#1a1a1a; color:var(--accent); padding:12px 24px; border-radius:10px; font-weight:700; text-decoration:none; white-space:nowrap; font-size:14px; min-height:46px; display:inline-flex; align-items:center; transition:opacity .2s; }
    .explore-banner .btn-dark:hover { opacity:.85; }

    /* ── Payments ── */
    .payment-item { display:flex; align-items:center; justify-content:space-between; padding:14px 2px; border-bottom:1px solid var(--border); gap:16px; }
    .payment-item:last-of-type { border-bottom:none; }
    .payment-left { display:flex; align-items:center; gap:14px; min-width:0; flex:1; }
    .payment-left svg { width:18px; height:18px; stroke:var(--muted); fill:none; stroke-width:2; flex-shrink:0; }
    .payment-info .plan { font-size:14px; font-weight:600; margin-bottom:2px; }
    .payment-info .date { font-size:12.5px; color:var(--muted); }
    .payment-right { display:flex; align-items:center; gap:16px; flex-shrink:0; }
    .payment-amount { font-weight:700; font-size:15px; color:var(--accent); text-align:right; margin-bottom:2px; }
    .payment-status { font-size:12px; color:#4ade80; text-align:right; }
    .payment-receipt { color:var(--muted); text-decoration:none; transition:color .2s; display:inline-flex; padding:6px; border-radius:8px; }
    .payment-receipt:hover { color:var(--accent); background:var(--accent-soft); }
    .payment-receipt svg { width:18px; height:18px; fill:none; stroke:currentColor; stroke-width:2; }
    .view-all-link { text-align:center; margin-top:8px; padding-top:14px; border-top:1px solid var(--border); }
    .view-all-link a { color:var(--accent); font-size:14px; text-decoration:none; font-weight:600; }
    .view-all-link a:hover { text-decoration:underline; }

    .empty-state { text-align:center; color:var(--muted); padding:28px 20px; line-height:1.5; }
    .empty-state .icon { font-size:40px; margin-bottom:10px; }
    .empty-state .title { font-weight:600; margin-bottom:6px; }
    .empty-state .sub { font-size:13px; margin-bottom:16px; }

    /* ── Modal ── */
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:9999; align-items:center; justify-content:center; padding:20px; }
    .modal-overlay.active { display:flex; }
    .modal { background:var(--surface); border:1px solid var(--accent); border-radius:16px; width:100%; max-width:640px; padding:28px; position:relative; max-height:90vh; overflow-y:auto; }
    .modal-close { position:absolute; top:16px; right:16px; background:none; border:none; color:var(--muted); font-size:20px; cursor:pointer; line-height:1; }
    .modal-close:hover { color:var(--accent); }
    .modal-title { font-size:18px; font-weight:700; margin-bottom:4px; color:var(--accent); }
    .modal-sub { font-size:13px; color:var(--muted); margin-bottom:20px; }
    .modal-section-label { font-size:11px; color:var(--accent); text-transform:uppercase; letter-spacing:1px; font-weight:700; margin-bottom:12px; }
    .modal-plan-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; margin-bottom:20px; }
    .modal-plan-option { cursor:pointer; display:block; }
    .modal-plan-option input[type="radio"] { display:none; }
    .modal-plan-card { border-radius:12px; padding:12px 8px; text-align:center; transition:border-color .18s, background .18s; position:relative; background:var(--surface2); border:1.5px solid var(--border); height:100%; box-sizing:border-box; }
    .modal-plan-card:hover { border-color:rgba(224,169,59,0.4); }
    .modal-plan-card .icon { width:32px; height:32px; margin:0 auto 8px; color:var(--muted); }
    .modal-plan-card .icon svg { width:100%; height:100%; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
    .modal-plan-card .name { font-size:11px; font-weight:700; color:var(--text); line-height:1.3; }
    .modal-plan-card .dot { position:absolute; top:7px; right:7px; width:7px; height:7px; border-radius:50%; background:var(--accent); display:none; }
    .modal-plan-card.selected { border-color:var(--accent) !important; background:rgba(224,169,59,0.08) !important; }
    .modal-plan-card.selected .icon { color:var(--accent); }
    .modal-plan-card.selected .dot { display:block; }
    .modal-actions { display:flex; gap:10px; }
    .modal-actions .btn { flex:1; justify-content:center; padding:13px; }
    .modal-actions .btn-secondary { flex:0 1 auto; padding:13px 20px; }

    /* ═══ Responsive ═══ */
    @media (max-width:900px) {
        .dashboard-container { padding:0 16px 32px; }
        .welcome-header { margin-bottom:20px; }
        .welcome-header h1 { font-size:28px; }
        .top-row { grid-template-columns:1fr; gap:16px; margin-bottom:16px; }
        .explore-banner { flex-direction:column; text-align:center; padding:22px 20px; gap:16px; margin-bottom:16px; }
        .explore-banner .btn-dark { width:100%; justify-content:center; }
    }
    @media (max-width:768px) {
        .welcome-header h1 { font-size:24px; }
        .welcome-header p { font-size:13px; }
        .card { padding:20px 18px; border-radius:14px; }
        .warning-banner { padding:12px 16px; flex-wrap:wrap; margin-bottom:16px; }
        .warning-banner .text .sub { font-size:12.5px; }
        .payment-item { flex-wrap:wrap; gap:10px; }
        .payment-left { flex:1 1 100%; }
        .payment-right { flex:1 1 100%; justify-content:flex-end; }
        .modal { padding:22px 18px; max-width:95vw; }
        .modal-plan-grid { grid-template-columns:repeat(3, 1fr); gap:8px; }
        .modal-actions { flex-direction:column; }
        .modal-actions .btn-secondary { flex:1; }
        .explore-banner .title { font-size:16px; }
        .explore-banner .sub { font-size:12.5px; }
    }
    @media (max-width:480px) {
        .dashboard-container { padding:0 12px 24px; }
        .welcome-header h1 { font-size:21px; }
        .card { padding:18px 16px; border-radius:12px; }
        .card-header .title { font-size:15px; }
        .sub-plan .icon-wrap { width:48px; height:48px; border-radius:12px; }
        .sub-plan .icon-wrap svg { width:24px; height:24px; }
        .sub-plan .info .value { font-size:18px; }
        .sub-details { grid-template-columns:1fr 1fr; gap:10px; }
        .coach-row { flex-direction:column; align-items:flex-start; gap:2px; padding:4px 0; }
        .coach-row strong { text-align:left; }
        .sub-status { padding:14px 14px 16px; }
        .sub-actions { flex-direction:column; }
        .sub-actions .btn { width:100%; }
        .pf-avatar, .pf-avatar-ph { width:68px; height:68px; font-size:26px; }
        .pf-name { font-size:17px; }
        .pf-icon { width:34px; height:34px; }
        .pf-row { padding:10px 2px; gap:12px; }
        .pf-value { font-size:14px; }
        .payment-info .plan { font-size:13px; }
        .payment-amount { font-size:14px; }
        .payment-status { font-size:11px; }
        .modal { padding:18px 14px; }
        .modal-title { font-size:16px; }
        .modal-plan-grid { grid-template-columns:repeat(2, 1fr); gap:6px; }
        .modal-plan-card { padding:10px 6px; }
        .modal-actions .btn { font-size:13px; padding:10px; min-height:40px; }
        .explore-banner { padding:18px 16px; }
        .explore-banner .title { font-size:15px; }
        .explore-banner .btn-dark { font-size:13px; padding:10px 16px; min-height:42px; }
        .warning-banner { padding:10px 12px; gap:10px; }
        .warning-banner .icon { font-size:16px; }
    }
    @media (max-width:360px) {
        .modal-plan-card .name { font-size:9.5px; }
        .sub-plan .info .value { font-size:16px; }
        .sub-details { grid-template-columns:1fr; }
    }
    @media (prefers-reduced-motion:reduce) { * { animation-duration:.01ms !important; transition-duration:.01ms !important; } }
</style>

@php
    /*
     * ONE source of truth for this page: $snapshot (App\Services\MemberSnapshot).
     * Active period, days remaining, progress bar, status badge, coach and profile
     * all read from it, so no card can disagree with another.
     */
    $exp     = $snapshot?->expiration;
    $coach   = $snapshot?->coach;
    $profile = $snapshot?->profile;

    $svgPlans = [
        'Calisthenics'        => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><line x1="24" y1="11" x2="24" y2="24"/><line x1="24" y1="24" x2="14" y2="34"/><line x1="24" y1="24" x2="34" y2="34"/><line x1="24" y1="18" x2="14" y2="22"/><line x1="24" y1="18" x2="34" y2="22"/></svg>',
        'Bodybuilding'        => '<svg viewBox="0 0 48 48"><path d="M14 28 Q10 24 14 20 Q18 16 22 20 L26 28 Q30 32 26 36 Q22 40 18 36 Z"/><path d="M26 28 Q30 24 34 20"/><path d="M6 22 L14 20"/><path d="M34 20 L42 18"/><path d="M6 26 L14 28"/><path d="M34 28 L42 26"/></svg>',
        'Plyometrics'         => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><path d="M24 11 L18 22 L24 20 L20 34"/><path d="M24 20 L30 18 L26 30"/><path d="M16 38 L32 38"/></svg>',
        'Powerlifting'        => '<svg viewBox="0 0 48 48"><rect x="4" y="18" width="6" height="12" rx="2"/><rect x="38" y="18" width="6" height="12" rx="2"/><rect x="8" y="20" width="6" height="8" rx="1"/><rect x="34" y="20" width="6" height="8" rx="1"/><line x1="14" y1="24" x2="34" y2="24"/><circle cx="24" cy="14" r="3"/></svg>',
        'Endurance'           => '<svg viewBox="0 0 48 48"><circle cx="24" cy="8" r="3"/><path d="M20 12 Q16 18 18 24 L22 22 L20 34 L26 28 L28 34 L30 22 L34 24 Q36 18 32 12"/></svg>',
        'Functional Training' => '<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="14"/><path d="M24 10 L24 14"/><path d="M24 34 L24 38"/><path d="M10 24 L14 24"/><path d="M34 24 L38 24"/><circle cx="24" cy="24" r="4"/></svg>',
        'Hybrid Training'     => '<svg viewBox="0 0 48 48"><polygon points="24,6 28,18 40,18 30,26 34,38 24,30 14,38 18,26 8,18 20,18"/></svg>',
    ];
@endphp

<div class="dashboard-container">

    {{-- Welcome Header --}}
    <div class="welcome-header">
        <h1>
            Welcome back, <span>{{ explode(' ', $profile['name'] ?? auth()->user()->name)[0] }}</span>
        </h1>
        <p>Track your fitness journey and manage your subscription</p>
    </div>

    {{-- Expiration warnings (same expiration object as the Subscription card) --}}
    @if($exp && $exp->state === 'expiring')
    <div class="warning-banner">
        <span class="icon">⚠️</span>
        <div class="text">
            <strong>Subscription Expiring Soon</strong>
            <div class="sub">
                {{ $exp->message() }} Your {{ $snapshot->planType ?? $member->membership_type }} plan ends on
                <strong>{{ $exp->endDate->format('M d, Y') }}</strong>.
                <a href="{{ route('member.select-plan') }}">Renew now →</a>
            </div>
        </div>
    </div>
    @elseif($exp && $exp->state === 'expired')
    <div class="warning-banner" style="border-color:var(--danger);background:rgba(248,113,113,.08);">
        <span class="icon">⛔</span>
        <div class="text">
            <strong>Membership Expired</strong>
            <div class="sub">
                Your membership has expired. Please renew your membership to continue accessing member benefits.
                <a href="{{ route('member.select-plan') }}">Renew now →</a>
            </div>
        </div>
    </div>
    @elseif($member && $member->hasPaidInAdvance())
    <div class="warning-banner advance-payment-banner" style="border-color:var(--success);background:rgba(74,222,128,.08);">
        <span class="icon">✅</span>
        <div class="text">
            <strong>Advance Payment Recorded</strong>
            <div class="sub">
                This membership has already been paid beyond the current plan amount. The extra payment is recorded in your payment history.
            </div>
        </div>
        <button type="button" class="close-advance-banner" aria-label="Close notification">×</button>
    </div>
    @endif

    {{-- Top Row: Subscription + Profile --}}
    <div class="top-row">

        {{-- Current Subscription Card --}}
        <div class="card sub-card">
            <div class="card-header">
                <div class="title">Current Subscription</div>
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>

            @if($member && $snapshot)
                @php
                    $icon = $svgPlans[$member->fitness_plan] ?? '<svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="20"/></svg>';
                @endphp

                <div class="sub-plan">
                    <div class="icon-wrap">{!! $icon !!}</div>
                    <div class="info">
                        <div class="label">Fitness Plan</div>
                        <div class="value">{{ $member->fitness_plan }}</div>
                    </div>
                </div>

                <div class="sub-details">
                    <div class="item">
                        <div class="label">Duration</div>
                        <div class="value">{{ $snapshot->planType ?? $member->membership_type }}</div>
                    </div>
                    @if($snapshot->queuedRenewal)
                    <div class="item">
                        <div class="label">Next Renewal (Paid)</div>
                        <div class="value">{{ $snapshot->queuedRenewal['type'] }} · {{ $snapshot->queuedRenewal['period'] }}</div>
                    </div>
                    @endif
                </div>

                {{-- Current / Upcoming Coach --}}
                <div class="coach-section">
                    @if($coach['current'])
                        @php $c = $coach['current']; @endphp
                        <div class="coach-box current">
                            <div class="coach-tag">Current Coach</div>
                            <div class="coach-row"><span>Coach</span><strong>{{ $c['name'] }}</strong></div>
                            @if($c['start'] && $c['end'])
                            <div class="coach-row">
                                <span>Coaching Period</span>
                                <strong>{{ $c['start']->format('M j, Y') }} – {{ $c['end']->format('M j, Y') }}</strong>
                            </div>
                            @endif
                            <div class="coach-row">
                                <span>Status</span>
                                <span class="coach-status {{ strtolower($c['status']) }}">{{ $c['status'] }}</span>
                            </div>
                        </div>
                    @endif

                    @if($coach['upcoming'])
                        @php $u = $coach['upcoming']; @endphp
                        <div class="coach-box upcoming">
                            <div class="coach-tag">Upcoming Coach</div>
                            <div class="coach-row"><span>Coach</span><strong>{{ $u['name'] }}</strong></div>
                            <div class="coach-row"><span>Start Date</span><strong>{{ $u['start']->format('F j, Y') }}</strong></div>
                            @if($u['end'])
                            <div class="coach-row">
                                <span>Coaching Period</span>
                                <strong>{{ $u['start']->format('M j, Y') }} – {{ $u['end']->format('M j, Y') }}</strong>
                            </div>
                            @endif
                            <div class="coach-row">
                                <span>Status</span>
                                <span class="coach-status scheduled">{{ $u['status'] }}</span>
                            </div>
                        </div>
                    @elseif($coach['pending'])
                        @php $p = $coach['pending']; @endphp
                        <div class="coach-box upcoming">
                            <div class="coach-tag">Coach Request</div>
                            <div class="coach-row"><span>Coach</span><strong>{{ $p['name'] }}</strong></div>
                            @if($p['start'])
                            <div class="coach-row"><span>Requested Start</span><strong>{{ $p['start']->format('F j, Y') }}</strong></div>
                            @endif
                            <div class="coach-row">
                                <span>Status</span>
                                <span class="coach-status pending">{{ $p['status'] }}</span>
                            </div>
                        </div>
                    @endif

                    @if($coach['state'] === 'none')
                        <div class="coach-box"><div class="coach-none">No Personal Coach</div></div>
                    @endif
                </div>

                {{-- Status, active period, days remaining + progress: ALL from the snapshot --}}
                <div class="sub-status">
                    <x-membership-expiration :member="$member" :exp="$exp" :plan="false" :alert="false" />
                </div>

                <div class="sub-actions">
                    <a href="{{ route('member.select-plan') }}" class="btn btn-primary" style="flex:1;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        {{ $exp->dateExpired ? 'Renew Plan' : 'Change Plan' }}
                    </a>
                    <button type="button" class="btn btn-secondary"
                            onclick="document.getElementById('editSubModal').style.display='flex'"
                            style="padding:8px 12px;display:flex;align-items:center;justify-content:center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                </div>

            @else
                <div class="empty-state">
                    <div class="icon">🏋️</div>
                    <div class="title">No Active Plan</div>
                    <div class="sub">Choose a plan to get started</div>
                    <a href="{{ route('member.select-plan') }}" class="btn btn-primary">Choose a Plan</a>
                </div>
            @endif
        </div>

        {{-- Profile Card (live database values: snapshot first, then users, then members) --}}
        <div class="card profile-card">
            <div class="card-header">
                <div class="title">Profile</div>
                <svg viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>

            @php
                $pName    = $profile['name']  ?? auth()->user()->name;
                $pEmail   = $profile['email'] ?? auth()->user()->email;
                $pPhone   = $profile['phone'] ?? auth()->user()->phone;
                $pAddress = $profile['address'] ?? auth()->user()->address ?? $member?->address;
                $pSince   = $profile['member_since'] ?? auth()->user()->created_at;
                $pPhoto   = $profile['photo'] ?? auth()->user()->photo;
                $pStatus  = $profile['status'] ?? 'No Plan';
                $pUsable  = $profile['usable'] ?? false;
            @endphp

            <div class="pf-hero">
                @if($pPhoto)
                    <img class="pf-avatar" src="{{ asset('storage/'.$pPhoto) }}" alt="">
                @else
                    <div class="pf-avatar-ph">{{ strtoupper(mb_substr($pName, 0, 2)) }}</div>
                @endif
                <div class="pf-name">{{ $pName }}</div>
                <div class="pf-email">{{ $pEmail }}</div>
                <span class="status-badge {{ $pUsable ? '' : 'inactive' }}">{{ $pStatus }}</span>
            </div>

            <div class="pf-list">
                <div class="pf-row">
                    <div class="pf-icon">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="pf-text">
                        <div class="pf-label">Full Name</div>
                        <div class="pf-value">{{ $pName }}</div>
                    </div>
                </div>

                <div class="pf-row">
                    <div class="pf-icon">
                        <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/></svg>
                    </div>
                    <div class="pf-text">
                        <div class="pf-label">Email</div>
                        <div class="pf-value">{{ $pEmail }}</div>
                    </div>
                </div>

                <div class="pf-row">
                    <div class="pf-icon">
                        <svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.3a2 2 0 012.1-.4c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z"/></svg>
                    </div>
                    <div class="pf-text">
                        <div class="pf-label">Phone Number</div>
                        <div class="pf-value {{ $pPhone ? '' : 'empty' }}">{{ $pPhone ?: 'Not provided' }}</div>
                    </div>
                </div>

                <div class="pf-row">
                    <div class="pf-icon">
                        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div class="pf-text">
                        <div class="pf-label">Address</div>
                        <div class="pf-value {{ $pAddress ? '' : 'empty' }}">{{ $pAddress ?: 'Not provided' }}</div>
                    </div>
                </div>

                <div class="pf-row">
                    <div class="pf-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="pf-text">
                        <div class="pf-label">Member Since</div>
                        <div class="pf-value">{{ $pSince->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            <a href="{{ route('member.profile') }}" class="btn btn-secondary">Edit Profile</a>
        </div>

    </div>

    {{-- Explore Plans Banner --}}
    <div class="explore-banner">
        <div>
            <div class="title">Explore All Fitness Plans</div>
            <div class="sub">Choose from 7 specialized training programs designed to help you reach your goals</div>
        </div>
        <a href="{{ route('member.select-plan') }}" class="btn-dark">
            View All Plans →
        </a>
    </div>

    {{-- Recent Payments --}}
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="title">Recent Payments</div>
            <svg viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>

        @forelse($payments->take(5) as $payment)
        <div class="payment-item">
            <div class="payment-left">
                <svg viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <div class="payment-info">
                    <div class="plan">{{ $payment->fitness_plan }} - {{ $payment->membership_type }}</div>
                    <div class="date">{{ $payment->payment_date->format('M d, Y') }}</div>
                </div>
            </div>
            <div class="payment-right">
                <div>
                    <div class="payment-amount">₱{{ number_format($payment->amount, 0) }}</div>
                    <div class="payment-status">{{ $payment->status }}</div>
                </div>
                <a href="{{ route('member.receipt', $payment) }}" class="payment-receipt" title="View Receipt">
                    <svg viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state">No payments yet.</div>
        @endforelse

        @if($payments->count() > 0)
        <div class="view-all-link">
            <a href="{{ route('member.payments') }}">View All Payments →</a>
        </div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════
     Edit Subscription Modal
═══════════════════════════════════════════════ --}}
@if($member)
<div class="modal-overlay" id="editSubModal">
    <div class="modal">
        <button onclick="document.getElementById('editSubModal').style.display='none'" class="modal-close">✕</button>

        <div class="modal-title">Edit Subscription</div>
        <div class="modal-sub">No charge will be made.</div>

        <form method="POST" action="{{ route('member.subscription.update') }}">
            @csrf

            {{-- ── 1. FITNESS PLAN ── --}}
            <div class="modal-section-label">1. Fitness Plan</div>
            <div class="modal-plan-grid">
                @foreach($svgPlans as $planName => $planSvg)
                    @php $isPlan = $member->fitness_plan === $planName; @endphp
                    <label class="modal-plan-option">
                        <input type="radio" name="fitness_plan" value="{{ $planName }}" {{ $isPlan ? 'checked' : '' }}/>
                        <div class="modal-plan-card {{ $isPlan ? 'selected' : '' }}">
                            <div class="icon">{!! $planSvg !!}</div>
                            <div class="name">{{ $planName }}</div>
                            <div class="dot"></div>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- The controller validates these two as required: keep the current values. --}}
            <input type="hidden" name="membership_type" value="{{ $snapshot->planType ?? $member->membership_type }}">
            <input type="hidden" name="instructor_id" value="{{ $member->instructor_id }}">

            {{-- ── ACTIONS ── --}}
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">✓ Save Changes</button>
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editSubModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        // Radio → card selection handler
        document.querySelectorAll('.modal-plan-option input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var parent = this.closest('.modal-plan-grid');
                parent.querySelectorAll('.modal-plan-card').forEach(function(card) {
                    card.classList.remove('selected');
                });
                if (this.checked) {
                    var card = this.closest('.modal-plan-option').querySelector('.modal-plan-card');
                    card.classList.add('selected');
                }
            });
        });

        // Close modal on backdrop click
        var modal = document.getElementById('editSubModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) modal.style.display = 'none';
            });
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('editSubModal');
                if (modal && modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            }
        });

        var advanceBanner = document.querySelector('.advance-payment-banner');
        if (advanceBanner) {
            var advanceCloseButton = advanceBanner.querySelector('.close-advance-banner');
            if (advanceCloseButton) {
                advanceCloseButton.addEventListener('click', function() {
                    advanceBanner.style.display = 'none';
                });
            }
        }
    })();
</script>
@endif

@endsection