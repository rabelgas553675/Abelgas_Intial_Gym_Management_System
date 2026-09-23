@extends('layouts.instructor')
@section('title','My Profile – APEX')
@section('active', 'profile')

@section('content')

<style>
    :root {
        --bg: #0a0a0a; 
        --surface: #111111;
        --surface2: #1a1a1a;
        --border: #2a2a2a;
        --accent: #ff2222;
        --accent-hover: #cc0000;
        --accent-glow: rgba(255,0,0,0.15);
        --text: #f0f0f0;
        --muted: #888888;
        --success: #ff4444;
        --danger: #ff0000;
        --warning: #ff6b35;
    }

    /* Profile Container */
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 28px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--accent);
    }
    .page-header h1 {
        font-size: clamp(1.5rem, 4vw, 2.2rem);
        font-weight: 700;
        margin-bottom: 6px;
        color: var(--text);
    }
    .page-header h1 span {
        color: var(--accent);
    }
    .page-header p {
        color: var(--muted);
        font-size: clamp(0.8rem, 1.2vw, 1rem);
    }

    /* Profile Layout */
    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* Left Card - Photo & Info */
    .profile-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 36px 24px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .profile-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    .profile-card:hover::before {
        transform: scaleX(1);
    }
    .profile-card:hover {
        border-color: var(--accent);
        box-shadow: 0 8px 30px rgba(255,0,0,0.1);
    }

    /* Avatar */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }
    .avatar-preview {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        overflow: hidden;
        background: var(--surface2);
        border: 3px solid var(--accent);
        margin: 0 auto;
        box-shadow: 0 0 40px rgba(255,0,0,0.15);
        transition: all 0.3s ease;
    }
    .avatar-preview:hover {
        box-shadow: 0 0 60px rgba(255,0,0,0.3);
        transform: scale(1.02);
    }
    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(255,0,0,0.05), rgba(255,0,0,0.1));
    }
    .avatar-placeholder svg {
        width: 56px;
        height: 56px;
        stroke: var(--muted);
    }

    /* Upload Button */
    .upload-btn {
        position: absolute;
        bottom: 6px;
        right: 6px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--accent);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--bg);
        box-shadow: 0 2px 12px rgba(255,0,0,0.4);
        transition: all 0.3s ease;
    }
    .upload-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 20px rgba(255,0,0,0.6);
    }
    .upload-btn svg {
        width: 15px;
        height: 15px;
        stroke: #000;
    }

    /* Profile Name */
    .profile-name {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text);
    }
    .profile-role {
        font-size: 13px;
        color: var(--accent);
        font-weight: 600;
        margin-bottom: 16px;
    }

    /* QR Code */
    .qr-section {
        margin-bottom: 20px;
        padding: 15px;
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid var(--border);
        display: inline-block;
        transition: all 0.3s ease;
    }
    .qr-section:hover {
        border-color: var(--accent);
        box-shadow: 0 4px 20px rgba(255,0,0,0.1);
    }
    .qr-section img {
        width: 100px;
        height: 100px;
        display: block;
        margin: 0 auto;
    }
    .qr-token {
        font-family: 'JetBrains Mono', monospace;
        font-size: 9px;
        color: #666;
        margin-top: 8px;
        word-break: break-all;
    }

    /* Specialization Badge */
    .specialization-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        background: rgba(255,0,0,0.1);
        border: 1px solid rgba(255,0,0,0.3);
        color: var(--accent);
        margin-bottom: 16px;
        transition: all 0.3s ease;
    }
    .specialization-badge:hover {
        background: rgba(255,0,0,0.2);
        border-color: var(--accent);
    }

    .experience-text {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 16px;
    }

    .member-since {
        font-size: 12px;
        color: var(--muted);
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    /* Right Card - Details */
    .details-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 32px;
        transition: all 0.3s ease;
    }
    .details-card:hover {
        border-color: var(--accent);
        box-shadow: 0 8px 30px rgba(255,0,0,0.1);
    }

    .details-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--accent);
    }
    .details-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--accent);
    }
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--accent);
        box-shadow: 0 0 16px rgba(255,0,0,0.5);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.8); }
    }

    /* Form Fields */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px 40px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
    }
    .form-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .form-control {
        background: transparent;
        border: none;
        border-bottom: 2px solid var(--border);
        border-radius: 0;
        padding: 4px 0 8px 0;
        font-size: 15px;
        font-weight: 600;
        color: var(--text);
        transition: all 0.3s ease;
        outline: none;
        width: 100%;
    }
    .form-control:focus {
        border-bottom-color: var(--accent);
        box-shadow: 0 2px 12px rgba(255,0,0,0.1);
    }
    .form-control::placeholder {
        color: var(--muted);
        font-weight: 400;
    }
    .form-control.readonly {
        color: var(--muted);
        cursor: not-allowed;
    }

    .form-text {
        font-size: 15px;
        font-weight: 600;
        padding: 4px 0 8px 0;
        border-bottom: 2px solid var(--border);
        color: var(--muted);
    }

    /* Error Text */
    .error-text {
        color: var(--danger);
        font-size: 11px;
        margin-top: 3px;
    }

    /* Action Buttons */
    .action-buttons {
        margin-top: 36px;
        padding-top: 24px;
        border-top: 2px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 11px 24px;
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: var(--accent);
        color: #000;
        box-shadow: 0 4px 20px rgba(255,0,0,0.3);
    }
    .btn-primary:hover {
        background: var(--accent-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(255,0,0,0.4);
    }
    .btn-secondary {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
    }
    .btn-secondary:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: rgba(255,0,0,0.05);
    }

    /* ===== RESPONSIVE ===== */

    @media (max-width: 1024px) {
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .profile-card {
            max-width: 400px;
            margin: 0 auto;
        }
        .form-grid {
            grid-template-columns: 1fr 1fr;
            gap: 20px 30px;
        }
    }

    @media (max-width: 768px) {
        .profile-container {
            padding: 0 16px;
        }
        .profile-card {
            padding: 28px 20px;
            max-width: 100%;
        }
        .avatar-preview {
            width: 140px;
            height: 140px;
        }
        .details-card {
            padding: 24px;
        }
        .form-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }
        .action-buttons {
            justify-content: center;
        }
        .btn {
            padding: 10px 20px;
            font-size: 13px;
            flex: 1;
            justify-content: center;
            min-width: 120px;
        }
    }

    @media (max-width: 480px) {
        .profile-container {
            padding: 0 12px;
        }
        .profile-card {
            padding: 20px 16px;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
        }
        .details-card {
            padding: 18px;
        }
        .details-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .details-title {
            font-size: 15px;
        }
        .form-control {
            font-size: 14px;
        }
        .action-buttons {
            flex-direction: column;
            gap: 10px;
        }
        .btn {
            width: 100%;
            justify-content: center;
        }
        .qr-section img {
            width: 80px;
            height: 80px;
        }
        .profile-name {
            font-size: 18px;
        }
    }

    @media (max-width: 360px) {
        .profile-card {
            padding: 16px 12px;
        }
        .avatar-preview {
            width: 100px;
            height: 100px;
        }
        .upload-btn {
            width: 28px;
            height: 28px;
            bottom: 4px;
            right: 4px;
        }
        .upload-btn svg {
            width: 12px;
            height: 12px;
        }
        .form-control {
            font-size: 13px;
            padding: 4px 0 6px 0;
        }
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

    /* Animation for cards */
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
    .profile-card {
        animation: fadeInUp 0.5s ease forwards;
    }
    .details-card {
        animation: fadeInUp 0.5s ease 0.1s forwards;
        opacity: 0;
    }
</style>

<div class="profile-container">

    {{-- Page Header --}}
    <div class="page-header">
        <h1> My <span>Profile</span></h1>
        <p>View and update your instructor information</p>
    </div>

    <form action="{{ route('instructor.profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="profile-grid">

        {{-- LEFT: Photo Card --}}
        <div class="profile-card">
            <div class="avatar-wrapper">
                <div id="avatar-preview" class="avatar-preview">
                    @if($instructor->photo)
                        <img src="{{ asset('storage/'.$instructor->photo) }}" alt="Profile Photo"/>
                    @else
                        <div class="avatar-placeholder">
                            <svg fill="none" stroke="var(--muted)" stroke-width="1.2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <label class="upload-btn" title="Upload Photo">
                    <svg fill="none" stroke="#000" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <input type="file" name="photo" accept="image/*" style="display:none;"
                           onchange="previewAvatar(this)"/>
                </label>
            </div>

            <div class="profile-name">{{ $instructor->name }}</div>
            <div class="profile-role"> Instructor</div>

            {{-- QR Code Section --}}
            @php $qr = \App\Models\UserQrToken::where('user_id', auth()->id())->first(); @endphp
            @if($qr && $qr->qr_code_path)
            <div class="qr-section">
                <img src="{{ asset('storage/' . $qr->qr_code_path) }}" alt="QR Code">
                <div class="qr-token">{{ $qr->qr_token }}</div>
            </div>
            @endif

            {{-- Specialization Badge --}}
            @if($instructor->specialization)
            <div class="specialization-badge">
                 {{ $instructor->specialization }}
            </div>
            @endif

            @if($instructor->experience_years)
            <div class="experience-text">
                 {{ $instructor->experience_years }} years of experience
            </div>
            @endif

            <div class="member-since">
                 Member since {{ auth()->user()->created_at->format('M d, Y') }}
            </div>
        </div>

        {{-- RIGHT: Details Card --}}
        <div class="details-card">

            <div class="details-header">
                <div class="details-title">Bio &amp; Other Details</div>
                <div class="status-dot"></div>
            </div>

            <div class="form-grid">

                {{-- Full Name --}}
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $instructor->name) }}" required/>
                    @error('name')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email (read-only) --}}
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="form-text">{{ $instructor->email }}</div>
                </div>

                {{-- Phone --}}
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone', $instructor->phone) }}" placeholder="Not set"/>
                </div>

                {{-- Specialization --}}
                <div class="form-group">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specialization" class="form-control"
                           value="{{ old('specialization', $instructor->specialization) }}"
                           placeholder="e.g. Bodybuilding, Calisthenics"/>
                </div>

                {{-- Experience Years --}}
                <div class="form-group">
                    <label class="form-label">Years of Experience</label>
                    <input type="number" name="experience_years" class="form-control"
                           min="0" max="50"
                           value="{{ old('experience_years', $instructor->experience_years) }}"
                           placeholder="0"/>
                </div>

                {{-- Address --}}
                <div class="form-group">
                    <label class="form-label">City / Address</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address', $instructor->address) }}" placeholder="Not set"/>
                </div>

                {{-- Assigned Members (read-only) --}}
                <div class="form-group">
                    <label class="form-label">Assigned Members</label>
                    <div class="form-text" style="color:var(--accent);font-weight:700;">
                        {{ auth()->user()->assignedMembers()->count() }} members
                    </div>
                </div>

            </div>

            {{-- Actions --}}
            <div class="action-buttons">
                <a href="{{ route('instructor.dashboard') }}" class="btn btn-secondary">
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
function previewAvatar(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatar-preview').innerHTML =
            `<img src="${e.target.result}" alt="Profile Photo"/>`;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>

@endsection