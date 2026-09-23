@extends('layouts.member')
@section('title', 'My Profile – APEX')
@section('page_title', 'My Profile')
@section('active_nav', 'member.profile')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Fix for the minimalist dropdown icon */
    select.staff-select {
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

    select.staff-select option {
        background-color: #1a1a1a;
        color: white;
    }

    .page-header {
        margin-bottom: 28px;
    }

    .page-header h1 {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .page-header p {
        color: var(--muted);
        font-size: 14px;
    }

    /* Profile Grid */
    .profile-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* Avatar Card */
    .avatar-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 36px 28px;
        text-align: center;
        position: sticky;
        top: 20px;
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
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-preview .placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
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
        transition: transform 0.2s;
    }

    .avatar-upload-btn:hover {
        transform: scale(1.1);
    }

    .avatar-upload-btn svg {
        width: 15px;
        height: 15px;
        fill: none;
        stroke: #111;
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
        background: rgba(234, 179, 8, 0.12);
        color: #eab308;
        border: 1px solid rgba(234, 179, 8, 0.25);
        margin-bottom: 16px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 18px;
        border-radius: 20px;
        background: rgba(74, 222, 128, 0.12);
        border: 1px solid rgba(74, 222, 128, 0.25);
        font-size: 13px;
        font-weight: 600;
        color: var(--success);
        margin-bottom: 24px;
    }

    .status-badge .dot {
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

    .qr-token {
        font-family: monospace;
        font-size: 10px;
        color: #999;
        margin-top: 8px;
    }

    .member-since {
        font-size: 12px;
        color: var(--muted);
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    /* Form Card */
    .form-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px;
    }

    .form-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
    }

    .form-header .title {
        font-size: 17px;
        font-weight: 700;
    }

    .form-header .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--accent);
        display: inline-block;
        flex-shrink: 0;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    .form-group {
        min-width: 0;
    }

    .form-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        display: block;
    }

    .form-control-custom {
        background: transparent;
        border: none;
        border-bottom: 1px solid var(--border);
        border-radius: 0;
        padding: 8px 0;
        font-size: 15px;
        font-weight: 600;
        width: 100%;
        color: var(--text);
        transition: border-color 0.2s;
        font-family: inherit;
    }

    .form-control-custom:focus {
        outline: none;
        border-bottom-color: var(--accent);
    }

    .form-control-custom:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .form-control-custom[type="date"] {
        color-scheme: dark;
    }

    .form-control-custom[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
        opacity: 0.6;
    }

    .form-control-custom[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }

    select.form-control-custom {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0px center;
        background-size: 14px;
        padding-right: 24px;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }

    .readonly-value {
        font-size: 15px;
        font-weight: 600;
        padding: 8px 0;
        border-bottom: 1px solid var(--border);
    }

    .readonly-value.member {
        color: #eab308;
    }

    .readonly-value.muted {
        color: var(--muted);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 11px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        min-height: 48px;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-secondary {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text);
    }

    .btn-secondary:hover {
        background: var(--surface2);
    }

    .btn-primary {
        background: var(--accent);
        color: #000;
    }

    .btn-primary:hover {
        opacity: .88;
        transform: translateY(-1px);
    }

    .alert-success {
        padding: 14px 18px;
        border-radius: 8px;
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.2);
        color: #4ade80;
        margin-bottom: 20px;
        font-weight: 500;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .profile-grid {
            grid-template-columns: 280px 1fr;
            gap: 20px;
        }
    }

    @media (max-width: 900px) {
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .avatar-card {
            position: relative;
            top: 0;
            max-width: 400px;
            margin: 0 auto;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
        }
    }

    @media (max-width: 768px) {
        .profile-container {
            padding: 0 12px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .page-header p {
            font-size: 13px;
        }

        .form-card {
            padding: 20px 16px;
            border-radius: 12px;
        }

        .form-header {
            margin-bottom: 20px;
        }

        .form-header .title {
            font-size: 15px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-control-custom {
            font-size: 14px;
            padding: 6px 0;
        }

        .readonly-value {
            font-size: 14px;
        }

        .avatar-card {
            padding: 24px 16px;
            border-radius: 12px;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
        }

        .avatar-upload-btn {
            width: 30px;
            height: 30px;
            bottom: 4px;
            right: 4px;
        }

        .avatar-upload-btn svg {
            width: 13px;
            height: 13px;
        }

        .profile-name {
            font-size: 18px;
        }

        .qr-container img {
            width: 100px;
            height: 100px;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .form-label {
            font-size: 10px;
        }

        .status-badge {
            font-size: 12px;
            padding: 5px 14px;
        }

        .role-badge {
            font-size: 11px;
            padding: 3px 12px;
        }

        select.form-control-custom {
            background-position: right 0px center;
            background-size: 12px;
            padding-right: 20px;
        }
    }

    @media (max-width: 480px) {
        .profile-container {
            padding: 0 8px;
        }

        .page-header h1 {
            font-size: 20px;
        }

        .page-header p {
            font-size: 12px;
        }

        .form-card {
            padding: 16px 12px;
            border-radius: 10px;
        }

        .form-header .title {
            font-size: 14px;
        }

        .form-header .dot {
            width: 8px;
            height: 8px;
        }

        .form-row {
            gap: 12px;
            margin-bottom: 12px;
        }

        .form-control-custom {
            font-size: 13px;
            padding: 5px 0;
        }

        .readonly-value {
            font-size: 13px;
        }

        .avatar-card {
            padding: 20px 12px;
            border-radius: 10px;
        }

        .avatar-preview {
            width: 80px;
            height: 80px;
            border-width: 2px;
        }

        .avatar-upload-btn {
            width: 26px;
            height: 26px;
        }

        .avatar-upload-btn svg {
            width: 11px;
            height: 11px;
        }

        .profile-name {
            font-size: 16px;
        }

        .qr-container {
            padding: 10px;
        }

        .qr-container img {
            width: 80px;
            height: 80px;
        }

        .qr-token {
            font-size: 9px;
        }

        .form-actions .btn {
            font-size: 13px;
            padding: 10px 20px;
            min-height: 44px;
        }

        .btn-secondary {
            border-radius: 8px;
        }

        .btn-primary {
            border-radius: 8px;
        }

        .form-label {
            font-size: 9px;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .status-badge {
            font-size: 11px;
            padding: 4px 12px;
            margin-bottom: 18px;
        }

        .role-badge {
            font-size: 10px;
            padding: 3px 10px;
        }

        .member-since {
            font-size: 11px;
            padding-top: 12px;
        }

        select.form-control-custom {
            background-size: 11px;
            padding-right: 18px;
        }

        .alert-success {
            font-size: 13px;
            padding: 12px 14px;
        }
    }

    @media (max-width: 360px) {
        .avatar-preview {
            width: 70px;
            height: 70px;
        }

        .avatar-upload-btn {
            width: 22px;
            height: 22px;
            bottom: 2px;
            right: 2px;
        }

        .avatar-upload-btn svg {
            width: 10px;
            height: 10px;
        }

        .profile-name {
            font-size: 14px;
        }

        .form-control-custom {
            font-size: 12px;
        }

        .readonly-value {
            font-size: 12px;
        }

        .form-actions .btn {
            font-size: 12px;
            padding: 8px 16px;
            min-height: 40px;
        }

        .qr-container img {
            width: 70px;
            height: 70px;
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

<div class="profile-container">

    {{-- Header --}}
    <div class="page-header">
        <h1>My Profile</h1>
        <p>View and update your personal information.</p>
    </div>

    @if(session('success'))
        <div class="alert-success">✓ {{ session('success') }}</div>
    @endif

    <form action="{{ route('member.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="profile-grid">

            {{-- LEFT: Avatar card --}}
            <div class="avatar-card">
                <div class="avatar-wrapper">
                    <div class="avatar-preview" id="avatar-preview">
                        @if($user->photo)
                            <img src="{{ asset('storage/'.$user->photo) }}" alt="Profile photo"/>
                        @else
                            <div class="placeholder">
                                {{ strtoupper(substr($user->name,0,2)) }}
                            </div>
                        @endif
                    </div>
                    <label class="avatar-upload-btn">
                        <svg viewBox="0 0 24 24">
                            <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        <input type="file" name="photo" accept="image/*" style="display:none;" onchange="previewAvatar(this)"/>
                    </label>
                </div>

                <div class="profile-name">{{ $user->name }}</div>

                <div class="role-badge">Member</div>

                <div class="status-badge">
                    <span class="dot"></span>
                    Active Member
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

            {{-- RIGHT: Bio form --}}
            <div class="form-card">
                <div class="form-header">
                    <div class="title">Bio &amp; other details</div>
                    <span class="dot"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-label">Full Name</div>
                        <input type="text" name="name" class="form-control-custom"
                               value="{{ old('name', $user->name) }}" required/>
                    </div>
                    <div class="form-group">
                        <div class="form-label">Email Address</div>
                        <input type="email" class="form-control-custom" value="{{ $user->email }}" disabled/>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-label">Phone Number</div>
                        <input type="text" name="phone" class="form-control-custom"
                               value="{{ old('phone', $user->phone) }}" placeholder="09..."/>
                    </div>
                    <div class="form-group">
                        <div class="form-label">Date of Birth</div>
                        <input type="date" name="birthdate" class="form-control-custom"
                               value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}"/>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-label">Gender</div>
                        <select name="gender" class="form-control-custom staff-select">
                            <option value="">— Select —</option>
                            @foreach(['Male','Female','Other'] as $g)
                                <option value="{{ $g }}" {{ old('gender', $user->gender) === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-label">City / Address</div>
                        <input type="text" name="address" class="form-control-custom"
                               value="{{ old('address', $user->address) }}" placeholder="Your city or address..."/>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <div class="form-label">Role</div>
                        <div class="readonly-value member">Member</div>
                    </div>
                    <div class="form-group">
                        <div class="form-label">Account Created</div>
                        <div class="readonly-value muted">
                            {{ $user->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewAvatar(input) {
        if (!input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('avatar-preview');
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;"/>`;
        };
        reader.readAsDataURL(input.files[0]);
    }

    // Handle responsive avatar sizing
    function updateAvatarSizes() {
        const preview = document.getElementById('avatar-preview');
        const img = preview.querySelector('img');
        const placeholder = preview.querySelector('.placeholder');
        let size = 140;

        if (window.innerWidth <= 480) {
            size = 80;
        } else if (window.innerWidth <= 768) {
            size = 100;
        } else if (window.innerWidth <= 900) {
            size = 120;
        }

        // Avatar sizes are handled by CSS, but we ensure images fill properly
        if (img) {
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
        }
    }

    // Update on resize and load
    window.addEventListener('resize', updateAvatarSizes);
    document.addEventListener('DOMContentLoaded', updateAvatarSizes);
</script>

@endsection