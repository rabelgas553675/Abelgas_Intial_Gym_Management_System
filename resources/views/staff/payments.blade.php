<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Payment Transactions – APEX FITNESS GYM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0a0a;
            --surface: #111111;
            --surface2: #1a1a1a;
            --surface3: #222222;
            --border: #2a2a2a;
            --accent: #ff2222;
            --accent-hover: #cc0000;
            --accent-glow: rgba(255,0,0,0.15);
            --text: #f0f0f0;
            --muted: #888888;
            --success: #ff4444;
            --danger: #ff0000;
            --warning: #ff6b35;
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
            border-bottom: 2px solid var(--accent);
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 36px;
            gap: 12px;
            box-shadow: 0 2px 20px rgba(255,0,0,0.1);
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
            box-shadow: 0 0 20px rgba(255,0,0,0.3);
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
            transition: all 0.2s ease;
            white-space: nowrap;
            position: relative;
        }

        .nav-link svg {
            width: 16px; height: 16px;
            stroke: var(--muted);
            fill: none;
            flex-shrink: 0;
            transition: stroke 0.2s;
        }

        .nav-link:hover {
            color: var(--text);
            background: var(--surface2);
        }

        .nav-link:hover svg { stroke: var(--text); }

        .nav-link.active {
            color: var(--accent);
            background: rgba(255,0,0,0.1);
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
            box-shadow: 0 0 10px rgba(255,0,0,0.5);
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
            background: rgba(255,0,0,0.15);
            color: var(--accent);
            border: 1px solid rgba(255,0,0,0.3);
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
            background: rgba(255,0,0,0.15);
            border: 2px solid var(--accent);
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
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-logout svg { width: 14px; height: 14px; stroke: var(--muted); transition: stroke 0.2s; flex-shrink: 0; }
        .btn-logout:hover { 
            border-color: var(--accent); 
            color: var(--accent); 
            background: rgba(255,0,0,0.05);
        }
        .btn-logout:hover svg { stroke: var(--accent); }

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
        .topnav-toggle:hover {
            border-color: var(--accent);
        }
        .topnav-toggle svg { width: 20px; height: 20px; stroke: var(--text); fill: none; }

        .topnav-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
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
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        .page-header h1 {
            font-size: clamp(1.5rem, 4vw, 2.2rem);
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--text);
        }
        .page-header h1 span {
            color: var(--accent);
        }
        .page-header p {
            color: var(--muted);
            font-size: clamp(0.8rem, 1.2vw, 1rem);
        }

        /* ── Stat Grid ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 22px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--accent);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--accent);
            box-shadow: 0 8px 30px rgba(255,0,0,0.1);
        }
        .stat-card .stat-label {
            font-size: 10px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .stat-card .stat-value {
            font-size: clamp(1.5rem, 3vw, 2.4rem);
            font-weight: 800;
            color: var(--accent);
            line-height: 1;
        }
        .stat-card .stat-sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }
        .stat-card.green .stat-value {
            color: var(--success);
        }
        .stat-card.blue .stat-value {
            color: var(--info);
        }
        .stat-card.yellow .stat-value {
            color: var(--warning);
        }

        /* ── Two Column Layout ── */
        .two-col {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── Form Card ── */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            transition: border-color 0.3s ease;
        }
        .form-card:hover {
            border-color: var(--accent);
        }
        .form-card-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 2px solid var(--accent);
            color: var(--accent);
        }

        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 20px rgba(255,0,0,0.1);
        }
        .form-control option {
            background: var(--surface2);
            color: var(--text);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .custom-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 14px center !important;
            background-size: 14px !important;
            padding-right: 40px !important;
            cursor: pointer;
        }

        /* Date picker */
        .date-picker-wrapper {
            position: relative;
            display: flex;
        }
        .date-picker-wrapper input[type="date"] {
            padding-right: 48px;
            flex: 1;
            border-radius: 8px;
            color-scheme: dark;
        }
        .date-picker-wrapper input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
            width: 0;
            padding: 0;
            margin: 0;
        }
        .date-picker-btn {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 44px;
            background: rgba(255,0,0,0.08);
            border: none;
            border-left: 1px solid var(--border);
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: var(--accent);
        }
        .date-picker-btn:hover {
            background: rgba(255,0,0,0.2);
        }
        .date-picker-btn svg {
            width: 18px;
            height: 18px;
            stroke: var(--accent);
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: var(--accent);
            color: #000000;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(255,0,0,0.3);
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(255,0,0,0.4);
        }
        .btn-danger-soft {
            background: rgba(255,0,0,0.1);
            color: var(--danger);
            border: 1px solid rgba(255,0,0,0.2);
            padding: 4px 10px;
            font-size: 14px;
        }
        .btn-danger-soft:hover {
            background: rgba(255,0,0,0.2);
        }
        .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
        }
        .btn-block {
            width: 100%;
            justify-content: center;
            padding: 12px;
            font-weight: 700;
        }

        .alert {
            padding: 10px 14px;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alert-success {
            background: rgba(255,68,68,0.08);
            border: 1px solid rgba(255,68,68,0.2);
            color: var(--success);
        }
        .alert-danger {
            background: rgba(255,0,0,0.08);
            border: 1px solid rgba(255,0,0,0.2);
            color: var(--danger);
        }
        .error-text {
            color: var(--danger);
            font-size: 12px;
            margin-top: 4px;
        }

        /* ── Section Header ── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            gap: 12px;
            flex-wrap: wrap;
        }
        .section-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--accent);
        }

        /* ── Table ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: border-color 0.3s ease;
        }
        .card:hover {
            border-color: var(--accent);
        }
        .table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        thead {
            background: var(--surface2);
            border-bottom: 2px solid var(--accent);
        }
        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 2px;
            white-space: nowrap;
        }
        td {
            padding: 12px 16px;
            font-size: 13px;
            border-top: 1px solid var(--border);
            vertical-align: middle;
        }
        tr:hover td {
            background: rgba(255,0,0,0.03);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-paid,
        .badge-completed,
        .badge-success {
            background: rgba(255,68,68,0.15);
            color: var(--success);
            border: 1px solid rgba(255,68,68,0.2);
        }
        .badge-pending {
            background: rgba(255,107,53,0.15);
            color: var(--warning);
            border: 1px solid rgba(255,107,53,0.2);
        }
        .badge-failed,
        .badge-cancelled {
            background: rgba(255,0,0,0.15);
            color: var(--danger);
            border: 1px solid rgba(255,0,0,0.2);
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 48px 20px;
        }

        .pagination-wrap {
            padding: 16px 18px;
            border-top: 1px solid var(--border);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--surface);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--accent);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-hover);
        }

        /* ===== RESPONSIVE ===== */

        @media (max-width: 1024px) {
            .topnav { padding: 0 20px; }
            .topnav-links { gap: 0; }
            .nav-link { padding: 8px 10px; font-size: 13px; }
            .nav-link svg { width: 15px; height: 15px; }
            .two-col {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 860px) {
            .topnav { height: 56px; padding: 0 16px; border-bottom-width: 2px; }
            .topnav-name { font-size: 17px; max-width: 46vw; }

            .topnav-toggle { display: flex; order: 3; }

            .topnav-links {
                position: fixed;
                top: 0;
                right: 0;
                height: 100vh;
                width: min(80vw, 300px);
                background: var(--surface);
                border-left: 2px solid var(--accent);
                flex-direction: column;
                align-items: stretch;
                gap: 3px;
                padding: 74px 14px 20px;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                z-index: 95;
                overflow-y: auto;
                box-shadow: -10px 0 40px rgba(0,0,0,0.5);
            }
            .topnav-links.open { transform: translateX(0); }

            .nav-link { 
                width: 100%; 
                padding: 13px 14px; 
                font-size: 15px; 
                border-radius: 10px; 
            }
            .nav-link svg { width: 18px; height: 18px; }
            .nav-link.active::after { display: none; }
            .nav-link.active { 
                border-left: 3px solid var(--accent); 
                background: rgba(255,0,0,0.05);
            }

            .topnav-right { gap: 8px; order: 2; }
            .user-chip span,
            .user-chip { font-size: 0; gap: 0; }
            .user-avatar { 
                font-size: 13px; 
                border-width: 2px;
            }
            .staff-badge { display: none; }
            .btn-logout { 
                padding: 8px; 
                width: 38px; 
                height: 38px; 
                border-radius: 50%;
            }
            .btn-logout span { display: none; }

            .page-content { padding: 20px 14px 40px; }
            .stat-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .stat-card { padding: 16px 18px; }
            .stat-card .stat-value { font-size: clamp(1.2rem, 3vw, 1.8rem); }
            .form-card { padding: 18px; }
            table { min-width: 500px; }
            th, td { padding: 10px 12px; font-size: 12px; }
            .two-col { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .topnav { padding: 0 12px; height: 52px; }
            .topnav-name { font-size: 15px; max-width: 38vw; }
            .topnav-logo { width: 28px; height: 28px; }
            .topnav-links { width: 84vw; }

            .page-content { padding: 14px 10px 30px; }
            .stat-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .stat-card { padding: 12px 14px; }
            .stat-card .stat-value { font-size: 1.2rem; }
            .stat-card .stat-label { font-size: 8px; letter-spacing: 1px; }
            .stat-card .stat-sub { font-size: 10px; }
            .form-card { padding: 14px; }
            .form-card-title { font-size: 14px; }
            table { min-width: 400px; }
            th, td { padding: 8px 10px; font-size: 11px; }
            .badge { font-size: 9px; padding: 2px 8px; }
            .section-title { font-size: 15px; }
            .btn { font-size: 12px; padding: 8px 14px; }
        }

        @media (max-width: 360px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
            .stat-card { padding: 12px 14px; }
            table { min-width: 350px; }
            th, td { padding: 6px 8px; font-size: 10px; }
        }

        /* Animation for stat cards */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .stat-card {
            animation: fadeInUp 0.5s ease forwards;
        }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
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

            <a href="{{ route('staff.payments') }}" class="nav-link active">
                <svg viewBox="0 0 24 24" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                Payments
            </a>

            <a href="{{ route('staff.profile') }}" class="nav-link {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
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
            <h1>💳 Payment <span>Transactions</span></h1>
            <p>Record and view all member payment records.</p>
        </div>

        {{-- Summary Cards --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Total Transactions</div>
                <div class="stat-value">{{ $totalCount ?? 0 }}</div>
                <div class="stat-sub">All time</div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-label">This Month</div>
                <div class="stat-value" style="color:var(--warning);font-size:clamp(1.2rem, 2.5vw, 1.8rem);">
                    ₱{{ number_format($thisMonth ?? 0, 0) }}
                </div>
                <div class="stat-sub">Current month collections</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Total Collected</div>
                <div class="stat-value" style="color:var(--success);font-size:clamp(1.2rem, 2.5vw, 1.8rem);">
                    ₱{{ number_format($totalCollected ?? 0, 0) }}
                </div>
                <div class="stat-sub">All time revenue</div>
            </div>
        </div>

        {{-- Two Column Layout --}}
        <div class="two-col">

            {{-- LEFT: Record Payment Form --}}
            <div class="form-card">
                <div class="form-card-title">+ Record Payment</div>

                @if(session('success'))
                    <div class="alert alert-success">✓ {{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">✕ {{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Member</label>
                        <select name="member_id" class="form-control custom-select" required>
                            <option value="" disabled selected>— Select Member —</option>
                            @foreach($members ?? [] as $member)
                                <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('member_id')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount (₱)</label>
                        <input type="number" name="amount" class="form-control"
                               step="0.01" min="0" placeholder="0.00"
                               value="{{ old('amount') }}" required/>
                        @error('amount')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Date</label>
                        <div class="date-picker-wrapper">
                            <input type="date" name="payment_date" id="staff_payment_date" class="form-control"
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required/>
                            <button type="button" class="date-picker-btn"
                                    onclick="document.getElementById('staff_payment_date').showPicker()"
                                    title="Open calendar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </button>
                        </div>
                        @error('payment_date')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-control custom-select" required>
                            <option value="" disabled selected>— Select Method —</option>
                            @foreach(['Cash','GCash','Bank Transfer','Card'] as $m)
                                <option value="{{ $m }}" {{ old('method') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                        @error('method')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        ✓ Record Payment
                    </button>
                </form>
            </div>

            {{-- RIGHT: Transactions Table --}}
            <div>
                <div class="section-header">
                    <div class="section-title">All Transactions</div>
                </div>

                <div class="card">
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Member</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    @if(auth()->user()->role === 'admin')
                                    <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments ?? [] as $p)
                                @php
                                    $pPhoto = $p->member?->user?->photo ?? $p->member?->photo ?? null;
                                @endphp
                                <tr>
                                    <td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--muted);">
                                        {{ $p->receipt_number ?? 'TXN-'.str_pad($p->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            @if($pPhoto)
                                                <img src="{{ asset('storage/'.$pPhoto) }}"
                                                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;
                                                            flex-shrink:0;border:2px solid var(--accent);"/>
                                            @else
                                                <div style="width:28px;height:28px;border-radius:50%;
                                                            background:rgba(255,0,0,0.12);border:2px solid rgba(255,0,0,0.3);
                                                            display:flex;align-items:center;justify-content:center;
                                                            font-size:10px;font-weight:700;color:var(--accent);flex-shrink:0;">
                                                    {{ strtoupper(substr($p->member?->name ?? '?', 0, 2)) }}
                                                </div>
                                            @endif
                                            <span style="font-weight:600;font-size:13px;color:var(--text);">{{ $p->member?->name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td style="font-weight:700;color:var(--accent);font-size:14px;">
                                        ₱{{ number_format($p->amount ?? 0, 0) }}
                                    </td>
                                    <td style="color:var(--muted);font-size:12px;">
                                        {{ isset($p->payment_date) ? (\Carbon\Carbon::parse($p->payment_date)->format('M d, Y')) : '—' }}
                                    </td>
                                    <td>
                                        <span style="background:var(--surface2);border:1px solid var(--border);
                                                     padding:2px 10px;border-radius:6px;font-size:11px;color:var(--text);">
                                            {{ $p->method ?? 'Cash' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $status = strtolower($p->status ?? 'paid');
                                            $badgeClass = in_array($status, ['paid', 'completed', 'success']) ? 'badge-paid' :
                                                          ($status === 'pending' ? 'badge-pending' : 'badge-failed');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst($p->status ?? 'Paid') }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->role === 'admin')
                                    <td>
                                        <form method="POST" action="{{ route('payments.destroy', $p) }}"
                                              onsubmit="return confirm('Delete this transaction?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger-soft btn-sm" title="Delete">🗑</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->role === 'admin' ? '7' : '6' }}" class="empty-state">
                                        No transactions yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($payments) && $payments->hasPages())
                    <div class="pagination-wrap">
                        {{ $payments->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

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
    </script>

</body>
</html>