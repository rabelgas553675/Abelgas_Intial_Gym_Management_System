<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>My Profile – APEX FITNESS GYM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0d0d;
            --surface: #151515;
            --surface2: #1c1c1c;
            --surface3: #242424;
            --border: #2a2a2a;
            --accent: #ff2b3d;
            --accent-hover: #e0141f;
            --text: #f0f0f0;
            --muted: #888;
            --success: #4ade80;
            --danger: #f87171;
            --warning: #fbbf24;
            --info: #60a5fa;
            --radius: 10px;
            --topbar-height: 60px;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── TOP NAVBAR ── */
        .topnav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            height: var(--topbar-height);
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
            position: relative;
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
            background: rgba(255,43,61,0.1);
            font-weight: 600;
        }

        .nav-link.active svg { stroke: var(--accent); }

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
            background: rgba(255,43,61,0.12);
            border: 1px solid rgba(255,43,61,0.25);
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
            transition: all 0.15s;
            white-space: nowrap;
        }

        .btn-logout svg { width: 14px; height: 14px; stroke: var(--muted); transition: stroke 0.15s; flex-shrink: 0; }
        .btn-logout:hover { border-color: var(--danger); color: var(--danger); }
        .btn-logout:hover svg { stroke: var(--danger); }

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

        body.menu-open { overflow: hidden; }

        /* ── PAGE CONTENT ── */
        .page-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }

        /* ── Page Header ── */
        .page-header {
            margin-bottom: 28px;
        }
        .page-header h1 {
            font-size: clamp(1.5rem, 4vw, 2.2rem);
            font-weight: 700;
            margin-bottom: 4px;
            color: #fff;
        }
        .page-header p {
            color: var(--muted);
            font-size: clamp(0.8rem, 1.2vw, 1rem);
        }

        /* ── Alert ── */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: rgba(74,222,128,0.08);
            border: 1px solid rgba(74,222,128,0.2);
            color: var(--success);
        }
        .alert-danger {
            background: rgba(248,113,113,0.08);
            border: 1px solid rgba(248,113,113,0.2);
            color: var(--danger);
        }

        /* ── Profile Layout ── */
        .profile-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── Left Card ── */
        .profile-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px 28px;
            text-align: center;
        }

        .avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .avatar-preview {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            overflow: hidden;
            background: var(--surface2);
            border: 3px solid var(--border);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-preview .initials {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 48px;
            color: var(--accent);
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: 6px;
            right: 6px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid var(--bg);
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            transition: background 0.15s;
        }

        .avatar-upload-btn:hover {
            background: var(--accent-hover);
        }

        .avatar-upload-btn svg {
            width: 15px;
            height: 15px;
            stroke: #111;
            fill: none;
            stroke-width: 2.5;
        }

        .profile-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(251,191,36,0.15);
            color: var(--warning);
            border: 1px solid rgba(251,191,36,0.3);
            margin-bottom: 16px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            border-radius: 20px;
            background: rgba(74,222,128,0.12);
            border: 1px solid rgba(74,222,128,0.25);
            font-size: 13px;
            font-weight: 600;
            color: var(--success);
            margin-bottom: 24px;
        }

        .status-pill .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--success);
            display: inline-block;
        }

        .qr-container {
            margin-bottom: 24px;
            padding: 15px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--border);
            display: inline-block;
        }

        .qr-container img {
            width: 120px;
            height: 120px;
            display: block;
            margin: 0 auto;
        }

        .qr-container .qr-token {
            font-family: monospace;
            font-size: 10px;
            color: #999;
            margin-top: 8px;
            word-break: break-all;
        }

        .member-since {
            font-size: 12px;
            color: var(--muted);
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        /* ── Right Card ── */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
        }

        .form-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .form-card-header .title {
            font-size: 17px;
            font-weight: 700;
        }

        .form-card-header .indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent);
            display: inline-block;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-group .label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 8px 0;
            background: transparent;
            border: none;
            border-bottom: 1px solid var(--border);
            border-radius: 0;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 600;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-control:focus {
            border-bottom-color: var(--accent);
        }

        .form-control[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-control option {
            background: var(--surface2);
            color: var(--text);
        }

        .form-control-static {
            font-size: 15px;
            font-weight: 600;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
            color: var(--muted);
        }

        .form-control-static.warning {
            color: var(--warning);
        }

        .profile-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 0px center !important;
            background-size: 14px !important;
            padding-right: 24px !important;
            cursor: pointer;
        }

        .profile-select option {
            background-color: #1a1a1a;
            color: white;
        }

        .error-text {
            color: var(--danger);
            font-size: 12px;
            margin-top: 4px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 12px;
        }

        .btn {
            padding: 11px 28px;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
        }

        .btn-primary {
            background: var(--accent);
            color: #111;
            padding: 11px 32px;
            font-weight: 700;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border);
            color: white;
            padding: 11px 28px;
        }

        .btn-secondary:hover {
            background: var(--surface2);
            border-color: var(--border);
        }

        /* ===== RESPONSIVE ===== */

        @media (max-width: 1024px) {
            .topnav { padding: 0 20px; }
            .topnav-links { gap: 0; }
            .nav-link { padding: 8px 10px; font-size: 13px; }
            .nav-link svg { width: 15px; height: 15px; }
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .profile-card {
                max-width: 400px;
                margin: 0 auto;
            }
        }

        @media (max-width: 860px) {
            .topnav { height: 56px; padding: 0 16px; }
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
            .user-chip { font-size: 0; gap: 0; }
            .user-avatar { font-size: 13px; }
            .staff-badge { display: none; }
            .btn-logout { padding: 8px; width: 38px; height: 38px; }
            .btn-logout span { display: none; }

            .page-content { padding: 20px 14px 40px; }
            .profile-card { padding: 24px 20px; }
            .avatar-preview { width: 120px; height: 120px; }
            .avatar-preview .initials { font-size: 40px; }
            .form-card { padding: 24px 20px; }
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
                margin-bottom: 16px;
            }
            .form-actions {
                flex-direction: column;
            }
            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .qr-container img {
                width: 100px;
                height: 100px;
            }
            .profile-card {
                max-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .topnav { padding: 0 12px; height: 52px; }
            .topnav-name { font-size: 15px; max-width: 38vw; }
            .topnav-logo { width: 28px; height: 28px; }
            .topnav-links { width: 84vw; }

            .page-content { padding: 14px 10px 30px; }
            .profile-card { padding: 20px 16px; }
            .avatar-preview { width: 100px; height: 100px; }
            .avatar-preview .initials { font-size: 34px; }
            .avatar-upload-btn { width: 28px; height: 28px; bottom: 4px; right: 4px; }
            .avatar-upload-btn svg { width: 12px; height: 12px; }
            .form-card { padding: 18px 14px; }
            .profile-name { font-size: 18px; }
            .btn { font-size: 13px; padding: 10px 20px; }
            .qr-container img { width: 80px; height: 80px; }
            .qr-container { padding: 10px; }
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
            <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('members.index') }}" class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                             M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                             m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Members
            </a>

            <a href="{{ route('attendance.scan') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 17h3m3 0h-3m0 0v-3m0 3v3"/>
                </svg>
                Attendance
            </a>

            <a href="{{ route('staff.payments') }}" class="nav-link {{ request()->routeIs('staff.payments') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Payments
            </a>

            <a href="{{ route('staff.profile') }}" class="nav-link active">
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
    <div class="page-content">

        {{-- Page Header --}}
        <div class="page-header">
            <h1>My Profile</h1>
            <p>View and update your personal information</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif

        <form action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="profile-grid">

                {{-- LEFT CARD — Avatar + identity --}}
                <div class="profile-card">

                    {{-- Avatar with camera button --}}
                    <div class="avatar-wrapper">
                        <div class="avatar-preview" id="avatarPreview">
                            @if($user->photo)
                                <img src="{{ asset('storage/'.$user->photo) }}" alt="Profile Photo"/>
                            @else
                                <span class="initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <label class="avatar-upload-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
                        </label>
                    </div>

                    {{-- Name --}}
                    <div class="profile-name">{{ $user->name }}</div>

                    {{-- Role badge --}}
                    <div class="role-badge">Staff</div>

                    {{-- Status pill --}}
                    <div class="status-pill">
                        <span class="dot"></span>
                        Active Staff
                    </div>

                    {{-- QR CODE SECTION --}}
                    @php $qr = \App\Models\UserQrToken::where('user_id', $user->id)->first(); @endphp
                    @if($qr && $qr->qr_code_path)
                        <div class="qr-container">
                            <img src="{{ asset('storage/' . $qr->qr_code_path) }}" alt="QR Code"/>
                            <div class="qr-token">{{ $qr->qr_token }}</div>
                        </div>
                    @endif

                    <div class="member-since">
                        Member since {{ $user->created_at->format('M d, Y') }}
                    </div>
                </div>

                {{-- RIGHT CARD — Bio & form fields --}}
                <div class="form-card">

                    <div class="form-card-header">
                        <div class="title">Bio &amp; other details</div>
                        <span class="indicator"></span>
                    </div>

                    {{-- Row 1: Full Name + Email --}}
                    <div class="form-row">
                        <div class="form-group">
                            <span class="label">Full Name</span>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $user->name) }}" required/>
                            @error('name')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <span class="label">Email Address</span>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled/>
                        </div>
                    </div>

                    {{-- Row 2: Phone + DOB --}}
                    <div class="form-row">
                        <div class="form-group">
                            <span class="label">Phone Number</span>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', $user->phone) }}" placeholder="09..."/>
                            @error('phone')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <span class="label">Date of Birth</span>
                            <input type="date" name="birthdate" class="form-control"
                                   value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"
                                   style="color-scheme:dark;"/>
                            @error('birthdate')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 3: Gender + Address --}}
                    <div class="form-row">
                        <div class="form-group">
                            <span class="label">Gender</span>
                            <select name="gender" class="form-control profile-select">
                                <option value="">— Select —</option>
                                @foreach(['Male','Female','Other'] as $g)
                                    <option value="{{ $g }}" {{ old('gender', $user->gender) === $g ? 'selected' : '' }}>
                                        {{ $g }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <span class="label">City / Address</span>
                            <input type="text" name="address" class="form-control"
                                   value="{{ old('address', $user->address) }}" placeholder="Your city or address..."/>
                            @error('address')
                                <div class="error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 4: Account info (read-only) --}}
                    <div class="form-row" style="margin-bottom: 12px;">
                        <div class="form-group">
                            <span class="label">Role</span>
                            <div class="form-control-static warning">Staff</div>
                        </div>
                        <div class="form-group">
                            <span class="label">Account Created</span>
                            <div class="form-control-static">{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="form-actions">
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>

                </div>
            </div>

        </form>

    </div>

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

        function previewAvatar(input) {
            if (!input.files[0]) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').innerHTML =
                    `<img src="${e.target.result}" alt="Profile Photo"/>`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    </script>

</body>
</html>