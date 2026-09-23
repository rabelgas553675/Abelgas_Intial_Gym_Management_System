@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'QR Attendance Scanner – APEX')
@section('active_nav', 'attendance')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .scanner-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Header */
    .scanner-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .scanner-header-left h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .scanner-header-left p {
        color: var(--muted);
        font-size: 14px;
    }

    .scanner-header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Grid */
    .scanner-grid {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 20px;
        align-items: start;
    }

    /* Scanner Panel */
    .scanner-panel {
        background: linear-gradient(135deg, #0f0f1a, #1a1a2e);
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .25);
    }

    .live-clock {
        text-align: center;
        margin-bottom: 24px;
    }

    .live-clock-time {
        font-size: 36px;
        font-weight: 700;
        color: #fff;
        letter-spacing: 2px;
    }

    .live-clock-date {
        color: rgba(255, 255, 255, .55);
        font-size: 13px;
        margin-top: 2px;
    }

    /* Cooldown */
    .cooldown-overlay {
        display: none;
        position: relative;
        border-radius: 14px;
        overflow: hidden;
        background: rgba(15, 15, 26, 0.92);
        border: 3px solid rgba(200, 255, 0, 0.5);
        margin-bottom: 12px;
        padding: 40px 20px;
        text-align: center;
        z-index: 10;
    }

    .cooldown-overlay.active {
        display: block;
    }

    .cooldown-number {
        font-size: 42px;
        font-weight: 800;
        color: var(--accent);
    }

    .cooldown-text {
        color: rgba(255, 255, 255, 0.6);
        font-size: 13px;
        margin-top: 6px;
    }

    .cooldown-bar-track {
        margin-top: 16px;
        height: 4px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        overflow: hidden;
    }

    .cooldown-bar {
        height: 100%;
        width: 100%;
        background: var(--accent);
        transition: width 3s linear;
        border-radius: 4px;
    }

    /* Reader */
    .reader-wrapper {
        border-radius: 14px;
        overflow: hidden;
        border: 3px solid rgba(200, 255, 0, .4);
        margin-bottom: 12px;
        position: relative;
        background: #0a0a15;
        min-height: 200px;
    }

    .reader-wrapper #reader {
        width: 100%;
        min-height: 200px;
    }

    .reader-wrapper #reader video {
        width: 100% !important;
        height: auto !important;
    }

    .scan-line {
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--accent), transparent);
        animation: scanMove 2s linear infinite;
        border-radius: 2px;
        margin-bottom: 12px;
    }

    /* Status */
    .scanner-status {
        text-align: center;
        margin-bottom: 12px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(74, 222, 128, 0.12);
        color: #4ade80;
        border: 1px solid rgba(74, 222, 128, 0.3);
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #4ade80;
        animation: pulse 1.5s ease-in-out infinite;
        display: inline-block;
    }

    .status-dot.locked {
        background: #f87171;
        animation: none;
    }

    .scanner-hint {
        text-align: center;
        color: rgba(255, 255, 255, .45);
        font-size: 12px;
        margin-bottom: 12px;
    }

    /* Manual Input */
    .manual-input-group {
        display: flex;
        gap: 8px;
        margin-bottom: 6px;
    }

    .manual-input-group input {
        flex: 1;
        padding: 10px 14px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 9px 0 0 9px;
        font-size: 13px;
        outline: none;
        font-family: 'DM Sans', sans-serif;
        min-width: 0;
        min-height: 44px;
    }

    .manual-input-group input:focus {
        border-color: var(--accent);
    }

    .manual-submit-btn {
        background: var(--accent);
        color: #111;
        border: none;
        border-radius: 0 9px 9px 0;
        padding: 0 16px;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.2s;
        flex-shrink: 0;
        min-height: 44px;
        min-width: 44px;
        font-size: 18px;
    }

    .manual-submit-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .manual-hint {
        color: rgba(255, 255, 255, .3);
        font-size: 11px;
        display: block;
        margin-bottom: 14px;
    }

    /* Manual Entry Panel */
    .manual-entry-panel {
        background: rgba(255, 255, 255, .05);
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 12px;
        padding: 18px;
    }

    .manual-entry-panel .panel-label {
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, .5);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .manual-entry-panel select {
        width: 100%;
        padding: 9px 12px;
        background: #1a1a2e;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 10px;
        font-family: 'DM Sans', sans-serif;
        appearance: auto;
        min-height: 44px;
    }

    .manual-entry-panel select option {
        background: #1a1a2e;
        color: #fff;
    }

    .manual-entry-panel select optgroup {
        background: #0f0f1a;
    }

    .manual-btn-group {
        display: flex;
        gap: 8px;
    }

    .manual-btn-group button {
        flex: 1;
        padding: 9px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        font-size: 13px;
        transition: opacity 0.2s;
        min-height: 44px;
    }

    .manual-btn-group button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .btn-timein {
        background: #4ade80;
        color: #111;
    }

    .btn-timeout {
        background: #60a5fa;
        color: #111;
    }

    .manual-msg {
        margin-top: 8px;
        font-size: 12px;
        min-height: 16px;
    }

    /* Counters */
    .scanner-counters {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, .1);
        gap: 8px;
        flex-wrap: wrap;
    }

    .counter-item {
        text-align: center;
        flex: 1;
        min-width: 60px;
    }

    .counter-value {
        font-size: 22px;
        font-weight: 700;
    }

    .counter-value.green {
        color: #4ade80;
    }
    .counter-value.yellow {
        color: #fbbf24;
    }
    .counter-value.white {
        color: #fff;
    }

    .counter-label {
        color: rgba(255, 255, 255, .45);
        font-size: 11px;
    }

    /* Result Card */
    .result-card {
        display: none;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        margin-top: 16px;
        animation: popIn .35s ease;
    }

    .result-card.visible {
        display: block;
    }

    .result-avatar {
        margin-bottom: 10px;
    }

    .result-avatar img,
    .result-avatar div {
        margin: 0 auto;
        display: block;
    }

    .result-name {
        font-weight: 700;
        margin-bottom: 4px;
        font-size: 18px;
        color: #111;
    }

    .result-membership {
        color: var(--muted);
        font-size: 13px;
        margin-bottom: 8px;
    }

    .result-times {
        margin-bottom: 8px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
    }

    .result-times .time-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(0, 0, 0, .08);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 600;
        margin: 3px;
    }

    .result-message {
        font-weight: 600;
        font-size: 14px;
        margin: 0;
    }

    .result-timein {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border: 2px solid #28a745;
        color: #111;
    }
    .result-timeout {
        background: linear-gradient(135deg, #cce5ff, #b8daff);
        border: 2px solid #007bff;
        color: #111;
    }
    .result-error {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        border: 2px solid #dc3545;
        color: #111;
    }
    .result-expired,
    .result-suspended {
        background: linear-gradient(135deg, #fff3cd, #ffeeba);
        border: 2px solid #ffc107;
        color: #111;
    }

    /* Table Section */
    .table-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
    }

    .table-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }

    .table-header .title {
        font-size: 15px;
        font-weight: 700;
    }

    .table-header .live-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--muted);
    }

    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4ade80;
        animation: pulse 2s ease-in-out infinite;
        display: inline-block;
    }

    .live-dot.synced {
        background: #fbbf24;
        animation: none;
    }

    .table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .attendance-table th {
        padding: 11px 16px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 2px;
        background: var(--surface2);
        white-space: nowrap;
    }

    .attendance-table td {
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        vertical-align: middle;
    }

    .attendance-table tr:first-child td {
        border-top: none;
    }

    .attendance-table .user-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .attendance-table .user-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }

    .attendance-table .user-avatar-placeholder {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .attendance-table .user-name {
        font-size: 13px;
        font-weight: 600;
    }

    .attendance-table .user-role {
        font-size: 11px;
        color: var(--muted);
    }

    .status-inside {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(74, 222, 128, 0.15);
        color: #4ade80;
        white-space: nowrap;
    }

    .status-done {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        background: var(--surface2);
        color: var(--muted);
        white-space: nowrap;
    }

    .method-manual {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
        white-space: nowrap;
    }

    .method-qr {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        background: var(--surface2);
        color: var(--muted);
        white-space: nowrap;
    }

    .empty-row td {
        padding: 48px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
    }

    .row-new {
        animation: slideIn 0.4s ease;
    }

    /* Animations */
    @keyframes scanMove {
        0% {
            opacity: 0;
            transform: translateY(-60px);
        }
        50% {
            opacity: 1;
        }
        100% {
            opacity: 0;
            transform: translateY(60px);
        }
    }

    @keyframes popIn {
        from {
            opacity: 0;
            transform: scale(.88);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes pulse {
        0%,
        100% {
            opacity: 1;
        }
        50% {
            opacity: 0.3;
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Buttons */
    .btn-sm {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        min-height: 40px;
        white-space: nowrap;
    }

    .btn-secondary {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--border);
    }

    .btn-primary {
        background: var(--accent);
        color: #000;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .scanner-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .scanner-panel {
            max-width: 600px;
            margin: 0 auto;
        }

        .scanner-header-left h1 {
            font-size: 24px;
        }
    }

    @media (max-width: 768px) {
        .scanner-container {
            padding: 0 12px;
        }

        .scanner-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .scanner-header-left h1 {
            font-size: 22px;
        }

        .scanner-header-left p {
            font-size: 13px;
        }

        .scanner-header-actions {
            width: 100%;
        }

        .scanner-header-actions .btn-sm {
            flex: 1;
            justify-content: center;
            min-width: 60px;
            font-size: 12px;
            padding: 6px 12px;
            min-height: 36px;
        }

        .scanner-panel {
            padding: 20px 16px;
            border-radius: 16px;
        }

        .live-clock-time {
            font-size: 28px;
        }

        .live-clock-date {
            font-size: 12px;
        }

        .cooldown-overlay {
            padding: 30px 16px;
        }

        .cooldown-number {
            font-size: 32px;
        }

        .reader-wrapper {
            min-height: 180px;
        }

        .reader-wrapper #reader {
            min-height: 180px;
        }

        .manual-input-group input {
            font-size: 14px;
            padding: 8px 12px;
            min-height: 40px;
        }

        .manual-submit-btn {
            min-height: 40px;
            min-width: 40px;
            font-size: 16px;
            padding: 0 14px;
        }

        .manual-entry-panel {
            padding: 14px;
        }

        .manual-entry-panel select {
            font-size: 14px;
            padding: 8px 10px;
            min-height: 40px;
        }

        .manual-btn-group button {
            font-size: 12px;
            padding: 8px;
            min-height: 40px;
        }

        .scanner-counters {
            flex-wrap: wrap;
            gap: 8px;
        }

        .counter-item {
            flex: 0 0 33.33%;
        }

        .counter-value {
            font-size: 18px;
        }

        .result-card {
            padding: 18px 16px;
        }

        .result-name {
            font-size: 16px;
        }

        .table-header {
            padding: 14px 16px;
        }

        .table-header .title {
            font-size: 14px;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 10px 12px;
            font-size: 12px;
        }

        .attendance-table .user-avatar,
        .attendance-table .user-avatar-placeholder {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }

        .attendance-table .user-name {
            font-size: 12px;
        }

        .attendance-table .user-role {
            font-size: 10px;
        }

        .empty-row td {
            padding: 32px 16px;
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .scanner-container {
            padding: 0 8px;
        }

        .scanner-header-left h1 {
            font-size: 20px;
        }

        .scanner-header-left p {
            font-size: 12px;
        }

        .scanner-header-actions .btn-sm {
            font-size: 11px;
            padding: 4px 10px;
            min-width: 50px;
            min-height: 32px;
        }

        .scanner-panel {
            padding: 16px 12px;
            border-radius: 12px;
        }

        .live-clock-time {
            font-size: 24px;
        }

        .live-clock-date {
            font-size: 11px;
        }

        .cooldown-overlay {
            padding: 20px 12px;
        }

        .cooldown-number {
            font-size: 28px;
        }

        .cooldown-text {
            font-size: 12px;
        }

        .reader-wrapper {
            min-height: 150px;
            border-radius: 10px;
            border-width: 2px;
        }

        .reader-wrapper #reader {
            min-height: 150px;
        }

        .scanner-status .status-badge {
            font-size: 11px;
            padding: 4px 10px;
        }

        .manual-input-group input {
            font-size: 13px;
            padding: 6px 10px;
            border-radius: 6px 0 0 6px;
            min-height: 36px;
        }

        .manual-submit-btn {
            min-height: 36px;
            min-width: 36px;
            font-size: 14px;
            padding: 0 12px;
            border-radius: 0 6px 6px 0;
        }

        .manual-hint {
            font-size: 10px;
        }

        .manual-entry-panel {
            padding: 12px;
        }

        .manual-entry-panel .panel-label {
            font-size: 10px;
        }

        .manual-entry-panel select {
            font-size: 13px;
            padding: 6px 8px;
            min-height: 36px;
        }

        .manual-btn-group button {
            font-size: 11px;
            padding: 6px;
            min-height: 36px;
        }

        .manual-msg {
            font-size: 11px;
        }

        .counter-value {
            font-size: 16px;
        }

        .counter-label {
            font-size: 10px;
        }

        .result-card {
            padding: 14px 12px;
            border-radius: 12px;
        }

        .result-name {
            font-size: 15px;
        }

        .result-membership {
            font-size: 12px;
        }

        .result-times .time-badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .result-message {
            font-size: 13px;
        }

        .table-header {
            padding: 10px 12px;
        }

        .table-header .title {
            font-size: 13px;
        }

        .table-header .live-indicator {
            font-size: 11px;
        }

        .attendance-table {
            min-width: 550px;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .attendance-table .user-avatar,
        .attendance-table .user-avatar-placeholder {
            width: 24px;
            height: 24px;
            font-size: 9px;
        }

        .attendance-table .user-name {
            font-size: 11px;
        }

        .attendance-table .user-role {
            font-size: 9px;
        }

        .status-inside,
        .status-done,
        .method-manual,
        .method-qr {
            font-size: 9px;
            padding: 1px 6px;
        }

        .empty-row td {
            padding: 24px 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 360px) {
        .scanner-panel {
            padding: 12px 8px;
        }

        .live-clock-time {
            font-size: 20px;
        }

        .cooldown-number {
            font-size: 24px;
        }

        .reader-wrapper {
            min-height: 120px;
        }

        .reader-wrapper #reader {
            min-height: 120px;
        }

        .manual-input-group input {
            font-size: 12px;
            padding: 4px 8px;
            min-height: 32px;
        }

        .manual-submit-btn {
            min-height: 32px;
            min-width: 32px;
            font-size: 12px;
            padding: 0 10px;
        }

        .manual-entry-panel select {
            font-size: 12px;
            min-height: 32px;
        }

        .manual-btn-group button {
            font-size: 10px;
            min-height: 32px;
        }

        .counter-value {
            font-size: 14px;
        }

        .attendance-table {
            min-width: 450px;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 6px 8px;
            font-size: 10px;
        }

        .attendance-table .user-avatar,
        .attendance-table .user-avatar-placeholder {
            width: 20px;
            height: 20px;
            font-size: 8px;
        }

        .attendance-table .user-name {
            font-size: 10px;
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
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
                    <button class="manual-submit-btn" id="manualSubmitBtn" onclick="processManual()">
                        →
                    </button>
                </div>
                <small class="manual-hint">
                    Type a numeric ID for quick lookup · 3-second cooldown between scans
                </small>

                {{-- Manual Entry Panel --}}
                <div class="manual-entry-panel">
                    <div class="panel-label">Manual Attendance Entry</div>

                    <select id="manualMemberId">
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
                                <option value="staff-{{ $s->id }}">
                                    {{ $s->name }} ({{ ucfirst($s->role) }})
                                </option>
                            @empty
                                <option disabled>No Staff Records Found</option>
                            @endforelse
                        </optgroup>
                    </select>

                    <div class="manual-btn-group">
                        <button class="btn-timein" id="timeInBtn" onclick="manualRecord('timein')">
                            ↓ Time In
                        </button>
                        <button class="btn-timeout" id="timeOutBtn" onclick="manualRecord('timeout')">
                            ↑ Time Out
                        </button>
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
                        <tr id="log-{{ $log->id }}" data-member="{{ $dataKey }}" style="border-top:1px solid var(--border);">
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
                        <tr id="emptyRow">
                            <td colspan="7" class="empty-row">
                                No scans today yet. Start scanning!
                            </td>
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

// ── Live Clock ─────────────────────────────────────────────────────────────
function updateClock() {
  const now = new Date();
  document.getElementById('liveClock').textContent =
    now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
  document.getElementById('liveDate').textContent =
    now.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
}
setInterval(updateClock, 1000);
updateClock();

// ── Cooldown state ──────────────────────────────────────────────────────────
let scanning     = false;
let lastScanned  = '';
let cooldownTimer = null;
const COOLDOWN_MS = 3000;

function startCooldown() {
  scanning = true;
  setStatus('locked');

  document.getElementById('cooldownOverlay').style.display = 'block';
  document.getElementById('reader').style.opacity = '0.15';
  setButtonsEnabled(false);

  let remaining = COOLDOWN_MS / 1000;
  document.getElementById('cooldownNumber').textContent = remaining;
  document.getElementById('cooldownSec').textContent    = remaining;

  const bar = document.getElementById('cooldownBar');
  bar.style.transition = 'none';
  bar.style.width      = '100%';
  bar.getBoundingClientRect();
  bar.style.transition = `width ${COOLDOWN_MS}ms linear`;
  bar.style.width      = '0%';

  cooldownTimer = setInterval(() => {
    remaining -= 1;
    if (remaining <= 0) remaining = 0;
    document.getElementById('cooldownNumber').textContent = remaining;
    document.getElementById('cooldownSec').textContent    = remaining;
  }, 1000);

  setTimeout(() => {
    clearInterval(cooldownTimer);
    scanning = false;
    lastScanned = '';
    document.getElementById('cooldownOverlay').style.display = 'none';
    document.getElementById('reader').style.opacity = '1';
    setButtonsEnabled(true);
    setStatus('ready');
  }, COOLDOWN_MS);
}

function setStatus(state) {
  const badge = document.getElementById('statusBadge');
  if (state === 'ready') {
    badge.innerHTML = `<span class="status-dot"></span> Scanner ready`;
    badge.style.background = 'rgba(74,222,128,0.12)';
    badge.style.color      = '#4ade80';
    badge.style.border     = '1px solid rgba(74,222,128,0.3)';
  } else {
    badge.innerHTML = `<span class="status-dot" style="background:#f87171;animation:none;"></span> Cooldown — next scan in 3s`;
    badge.style.background = 'rgba(248,113,113,0.12)';
    badge.style.color      = '#f87171';
    badge.style.border     = '1px solid rgba(248,113,113,0.3)';
  }
}

function setButtonsEnabled(enabled) {
  const ids = ['manualSubmitBtn', 'timeInBtn', 'timeOutBtn'];
  ids.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.disabled      = !enabled;
    el.style.opacity = enabled ? '1' : '0.4';
    el.style.cursor  = enabled ? 'pointer' : 'not-allowed';
  });
  const input = document.getElementById('manualInput');
  if (input) {
    input.disabled = !enabled;
    input.style.opacity = enabled ? '1' : '0.5';
  }
}

// ── QR Scanner init ─────────────────────────────────────────────────────────
function showCameraError(message) {
  const readerEl = document.getElementById('reader');
  if (!readerEl) return;

  readerEl.innerHTML = `
    <div style="color:rgba(255,255,255,.72);text-align:center;padding:32px 20px;font-size:13px;line-height:1.6;">
      ${message}<br>
      <span style="color:rgba(255,255,255,.5);">Use Manual Entry below.</span>
    </div>
  `;
}

function getCameraErrorMessage(error) {
  if (!error) {
    return 'Camera access is unavailable right now. Please allow camera access to continue.';
  }

  const name = (error.name || '').toString();
  const message = ((error.message || '') + '').toLowerCase();

  if (name === 'NotAllowedError' || message.includes('permission')) {
    return 'Camera permission was blocked. Please allow access to your camera and refresh the page.';
  }

  if (name === 'NotFoundError' || message.includes('no camera') || message.includes('device not found')) {
    return 'No camera was detected on this device. Please connect a camera or use Manual Entry.';
  }

  if (name === 'NotReadableError' || message.includes('in use') || message.includes('already in use')) {
    return 'Your camera is already in use by another app. Close it and try again.';
  }

  if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError' || message.includes('overconstrained') || message.includes('constraint')) {
    return 'This browser/device is rejecting the camera mode requested by the app. Please use a normal browser tab, allow camera access, and retry. Your permission is already enabled.';
  }

  if (name === 'NotSupportedError' || message.includes('secure context')) {
    return 'This page must be loaded with a secure connection or localhost for camera access.';
  }

  if (message) {
    return `Camera access failed: ${error.message}`;
  }

  return 'Camera is unavailable right now. Check your device permissions and try again.';
}

async function startQrScanner() {
  if (typeof Html5Qrcode === 'undefined') {
    showCameraError('The QR scanner library failed to load. Refresh the page and try again.');
    return;
  }

  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    showCameraError('This browser does not support camera access. Please use a modern browser with camera permissions enabled.');
    return;
  }

  const cameraConfigs = [
    // Html5Qrcode.start() accepts MediaTrackConstraints directly and wraps
    // them in its own `video` constraint. Passing `{ video: ... }` here nests
    // the constraint and makes browsers reject camera initialization.
    {},
    { facingMode: { ideal: 'environment' } },
    { facingMode: 'user' }
  ];

  let lastError = null;

  for (const config of cameraConfigs) {
    const qrInstance = new Html5Qrcode('reader');

    try {
      await qrInstance.start(
        config,
        { fps: 10, qrbox: { width: 240, height: 240 } },
        (decodedText) => {
          if (scanning) return;
          if (decodedText === lastScanned) return;
          lastScanned = decodedText;
          startCooldown();
          processQR(decodedText);
        },
        () => {}
      );
      return;
    } catch (error) {
      lastError = error;
      console.warn('Camera start attempt failed:', config, error);
      try {
        await qrInstance.stop();
      } catch (e) {
        // ignore stop errors while retrying
      }
    }
  }

  console.error('Html5Qrcode failed to start:', lastError);
  showCameraError(getCameraErrorMessage(lastError));
}

startQrScanner();

// ── Manual input ─────────────────────────────────────────────────────────────
function processManual() {
  if (scanning) return;
  const val = document.getElementById('manualInput').value.trim();
  if (!val) return;
  startCooldown();
  processQR(val);
  document.getElementById('manualInput').value = '';
}
document.getElementById('manualInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') processManual();
});

// ── Core AJAX ────────────────────────────────────────────────────────────────
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
  .catch(error => showResult({ success: false, message: error.message || 'Connection error. Attendance was not saved.' }));
}

// ── Manual entry panel ──────────────────────────────────────────────────────
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
          '&manual_action='   + action
  })
  .then(r => { if (!r.ok) throw new Error('Server error'); return r.json(); })
  .then(data => {
    msg.innerHTML = data.success
      ? `<span style="color:#4ade80;">✅ ${data.message}</span>`
      : `<span style="color:#f87171;">❌ ${data.message}</span>`;

    if (data.success) {
      if (action === 'timein') {
        appendLogRow(data, 'timein');
      } else {
        updateLogRowTimeout(data);
      }
      playBeep(true);
      document.getElementById('manualMemberId').value = '';
    } else {
      playBeep(false);
    }
  })
  .catch(() => {
    msg.innerHTML = '<span style="color:#f87171;">❌ Connection error. Check console.</span>';
  });
}

// ── Show result card ────────────────────────────────────────────────────────
function showResult(data) {
  const card    = document.getElementById('resultCard');
  const isStaff = data.is_staff || false;

  const avatar = data.photo
    ? `<img src="${data.photo}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:4px solid #fff;margin:0 auto;display:block;">`
    : `<div style="width:80px;height:80px;border-radius:50%;
        background:${isStaff ? 'linear-gradient(135deg,#60a5fa,#3b82f6)' : 'linear-gradient(135deg,#c8ff00,#4ade80)'};
        display:flex;align-items:center;justify-content:center;font-size:28px;
        font-weight:700;color:#111;margin:0 auto;">
        ${(data.member || '?').charAt(0).toUpperCase()}</div>`;

  document.getElementById('resultAvatar').innerHTML       = avatar;
  document.getElementById('resultName').textContent       = data.member     || 'Unknown';
  document.getElementById('resultMembership').textContent = data.membership || '';

  let cls = 'result-error';
  if (data.success && data.action === 'timein')  { cls = 'result-timein';  appendLogRow(data, 'timein'); }
  if (data.success && data.action === 'timeout') { cls = 'result-timeout'; updateLogRowTimeout(data); }
  if (data.status === 'expired')                  cls = 'result-expired';
  if (data.status === 'suspended')                cls = 'result-suspended';

  card.className = cls + ' visible';

  if (data.success && data.action === 'timein') {
    document.getElementById('resultTimes').innerHTML =
      `<span class="time-badge">Time In: <strong>${data.time_in}</strong></span>
       ${!isStaff ? `<span class="time-badge">Valid Until: <strong>${data.end_date || 'N/A'}</strong></span>` : ''}`;
  } else if (data.success && data.action === 'timeout') {
    document.getElementById('resultTimes').innerHTML =
      `<span class="time-badge">In: <strong>${data.time_in}</strong></span>
       <span class="time-badge">Out: <strong>${data.time_out}</strong></span>
       <span class="time-badge">Duration: <strong>${data.duration}</strong></span>`;
  } else {
    document.getElementById('resultTimes').innerHTML = '';
  }

  document.getElementById('resultMessage').textContent = data.message || '';
  card.style.display = 'block';
  playBeep(data.success);
}

// ── Inject a new Time-In row ───────────────────────────────────────────────
function appendLogRow(data, action) {
  const tbody    = document.getElementById('todayLogBody');
  const emptyRow = document.getElementById('emptyRow');
  if (emptyRow) emptyRow.remove();

  const existing = tbody.querySelector(`[data-member="${data.member_id}"]`);
  if (existing) existing.remove();

  const isStaff = data.is_staff ||
                  (typeof data.member_id === 'string' && data.member_id.toString().startsWith('staff-'));

  const avatar = data.photo
    ? `<img src="${data.photo}" class="user-avatar">`
    : `<div class="user-avatar-placeholder" style="background:${isStaff ? 'rgba(96,165,250,0.08)' : 'rgba(200,255,0,0.08)'};border:1px solid ${isStaff ? 'rgba(96,165,250,0.15)' : 'rgba(200,255,0,0.15)'};color:${isStaff ? '#60a5fa' : 'var(--accent)'};">${(data.member || '?').charAt(0).toUpperCase()}</div>`;

  const methodBadge = data.entry_method === 'manual'
    ? '<span class="method-manual">Manual</span>'
    : '<span class="method-qr">QR Scan</span>';

  const insideBadge = '<span class="status-inside">Inside</span>';

  const row = document.createElement('tr');
  row.setAttribute('data-member', data.member_id);
  row.classList.add('row-new');
  row.style.borderTop = '1px solid var(--border)';
  row.innerHTML = `
    <td style="padding:12px 16px;">${avatar}</td>
    <td style="padding:12px 16px;">
      <div class="user-name">${data.member}</div>
      <div class="user-role">${data.membership || ''}</div>
    </td>
    <td style="padding:12px 16px;font-size:13px;">${data.time_in}</td>
    <td style="padding:12px 16px;">${insideBadge}</td>
    <td style="padding:12px 16px;font-size:13px;color:var(--muted);">—</td>
    <td style="padding:12px 16px;">${methodBadge}</td>
    <td style="padding:12px 16px;">${insideBadge}</td>`;

  tbody.prepend(row);

  document.getElementById('insideCount').textContent =
    parseInt(document.getElementById('insideCount').textContent || 0) + 1;
  document.getElementById('todayTotal').textContent =
    parseInt(document.getElementById('todayTotal').textContent  || 0) + 1;
}

// ── Update an existing row on Time Out ─────────────────────────────────────
function updateLogRowTimeout(data) {
  const row = document.querySelector(`[data-member="${data.member_id}"]`);
  if (row) {
    const cells = row.querySelectorAll('td');
    if (cells.length >= 7) {
      cells[3].innerHTML = data.time_out;
      cells[4].textContent = data.duration;
      cells[6].innerHTML = '<span class="status-done">Done</span>';
    }
    row.classList.add('row-new');
    setTimeout(() => row.classList.remove('row-new'), 500);
  }
  document.getElementById('insideCount').textContent =
    Math.max(0, parseInt(document.getElementById('insideCount').textContent || 1) - 1);
}

// ── Beep ────────────────────────────────────────────────────────────────────
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
  } catch (e) {}
}

// ── Auto-refresh polling ────────────────────────────────────────────────────
const POLL_INTERVAL_MS = 10000;

function getRenderedKeys() {
  return Array.from(
    document.querySelectorAll('#todayLogBody tr[data-member]')
  ).map(r => r.getAttribute('data-member'));
}

function pollAttendance() {
  const known = getRenderedKeys();

  fetch('{{ route("attendance.live") }}', {
    method:  'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ known_keys: known })
  })
  .then(r => r.ok ? r.json() : null)
  .then(data => {
    if (!data || !data.rows || data.rows.length === 0) {
      flashLiveIndicator();
      return;
    }

    data.rows.forEach(row => {
      if (document.querySelector(`[data-member="${row.key}"]`)) return;

      const tbody    = document.getElementById('todayLogBody');
      const emptyRow = document.getElementById('emptyRow');
      if (emptyRow) emptyRow.remove();

      const el = document.createElement('tr');
      el.setAttribute('data-member', row.key);
      el.classList.add('row-new');
      el.style.borderTop = '1px solid var(--border)';
      el.innerHTML = row.html;
      tbody.prepend(el);
    });

    if (data.inside_count !== undefined) {
      document.getElementById('insideCount').textContent = data.inside_count;
    }
    if (data.today_total !== undefined) {
      document.getElementById('todayTotal').textContent = data.today_total;
    }

    flashLiveIndicator();
  })
  .catch(() => {});
}

function flashLiveIndicator() {
  const dot   = document.getElementById('liveDot');
  const label = document.getElementById('liveLabel');
  if (!dot) return;
  dot.style.background   = '#fbbf24';
  label.textContent      = 'Synced';
  setTimeout(() => {
    dot.style.background = '#4ade80';
    label.textContent    = 'Live';
  }, 600);
}

setInterval(pollAttendance, POLL_INTERVAL_MS);
</script>

@endsection
