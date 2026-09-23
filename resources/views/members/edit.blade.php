@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'Edit Member')
@section('page_title', 'Edit Member')

@section('topbar_actions')
  <a href="{{ route('members.index') }}" class="btn btn-secondary">← Back to Members</a>
@endsection

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .edit-page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* 2-Column Grid Layout */
    .form-grid-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        align-items: start;
    }

    .form-card {
        background: #0f0f0f;
        border: 1px solid #222;
        padding: 30px;
        border-radius: 12px;
        height: 100%;
    }

    .form-card-title {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 25px;
        border-bottom: 1px solid #222;
        padding-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Profile Pic Layout */
    .profile-header {
        display: flex;
        gap: 25px;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .profile-img-box {
        flex-shrink: 0;
    }

    .profile-img-box img,
    .no-photo-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid #333;
        background: #1a1a1a;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #555;
        font-size: 13px;
    }

    .profile-upload-info {
        flex: 1;
        min-width: 180px;
    }

    /* Input Styling */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    .form-group {
        min-width: 0;
    }

    .form-label {
        color: #888;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: bold;
        margin-bottom: 10px;
        display: block;
        letter-spacing: 1px;
    }

    .form-control {
        background: #181818;
        border: 1px solid #333;
        color: #fff;
        padding: 14px;
        border-radius: 8px;
        width: 100%;
        font-size: 14px;
        box-sizing: border-box;
        font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s;
        min-height: 48px;
    }

    .form-control:focus {
        border-color: #d4ff00;
        outline: none;
        box-shadow: 0 0 0 1px #d4ff00;
    }

    .form-control::placeholder {
        color: #555;
        opacity: 0.7;
    }

    /* Calendar Icon Fix */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
        opacity: 0.6;
        transition: 0.2s;
        padding: 4px;
    }

    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        filter: invert(86%) sepia(95%) saturate(2853%) hue-rotate(22deg) brightness(101%) contrast(101%);
        opacity: 1;
    }

    input[type="date"] {
        color-scheme: dark;
    }

    /* Dropdown Styling */
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888888' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 14px;
        padding-right: 40px;
        cursor: pointer;
    }

    select.form-control option {
        background: #181818;
        color: #fff;
        padding: 8px;
    }

    select.form-control:focus {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23d4ff00' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
    }

    /* File input */
    input[type="file"].form-control {
        padding: 10px 14px;
        min-height: 48px;
    }

    input[type="file"].form-control::-webkit-file-upload-button {
        background: #333;
        border: none;
        color: #fff;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-family: inherit;
        margin-right: 12px;
        transition: background 0.2s;
    }

    input[type="file"].form-control::-webkit-file-upload-button:hover {
        background: #444;
    }

    /* Buttons */
    .form-actions-row {
        display: flex;
        gap: 15px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .btn-primary-lime {
        flex: 2;
        min-width: 140px;
        background: #d4ff00;
        color: #000;
        font-weight: 800;
        border: none;
        padding: 16px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        text-transform: uppercase;
        transition: opacity 0.2s, transform 0.15s;
        min-height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary-lime:hover {
        opacity: 0.85;
        transform: translateY(-1px);
    }

    .btn-outline-dark {
        flex: 1;
        min-width: 100px;
        background: #1a1a1a;
        color: #fff;
        border: 1px solid #333;
        text-align: center;
        padding: 16px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        min-height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-outline-dark:hover {
        background: #222;
        border-color: #555;
    }

    /* Error Alert */
    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid #dc3545;
        color: #dc3545;
        padding: 15px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-danger div {
        padding: 3px 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1100px) {
        .form-grid-layout {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .edit-page-wrapper {
            padding: 0 12px;
        }

        .form-card {
            padding: 20px 16px;
            border-radius: 10px;
        }

        .form-card-title {
            font-size: 1rem;
            margin-bottom: 20px;
            padding-bottom: 12px;
        }

        .profile-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 16px;
        }

        .profile-img-box img,
        .no-photo-placeholder {
            width: 100px;
            height: 100px;
        }

        .profile-upload-info {
            width: 100%;
            text-align: center;
        }

        .profile-upload-info .form-label {
            text-align: center;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-label {
            font-size: 0.7rem;
            margin-bottom: 6px;
        }

        .form-control {
            font-size: 14px;
            padding: 12px;
            min-height: 44px;
        }

        input[type="file"].form-control {
            min-height: 44px;
            padding: 8px 10px;
        }

        select.form-control {
            padding-right: 36px;
            background-size: 12px;
            background-position: right 12px center;
        }

        .form-actions-row {
            flex-direction: column;
        }

        .btn-primary-lime,
        .btn-outline-dark {
            width: 100%;
            min-height: 48px;
            justify-content: center;
            font-size: 13px;
        }

        .btn-primary-lime {
            flex: none;
        }

        .btn-outline-dark {
            flex: none;
        }

        .alert-danger {
            padding: 12px 14px;
        }

        .alert-danger div {
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .edit-page-wrapper {
            padding: 0 8px;
        }

        .form-card {
            padding: 16px 12px;
            border-radius: 8px;
        }

        .form-card-title {
            font-size: 0.9rem;
            margin-bottom: 16px;
            padding-bottom: 10px;
        }

        .profile-img-box img,
        .no-photo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 8px;
        }

        .profile-header {
            gap: 12px;
        }

        .profile-upload-info small {
            font-size: 10px !important;
        }

        .form-row {
            gap: 12px;
            margin-bottom: 12px;
        }

        .form-label {
            font-size: 0.65rem;
            letter-spacing: 0.5px;
        }

        .form-control {
            font-size: 13px;
            padding: 10px 12px;
            min-height: 40px;
        }

        select.form-control {
            padding-right: 32px;
            background-size: 11px;
            background-position: right 10px center;
        }

        input[type="file"].form-control {
            font-size: 12px;
            min-height: 40px;
            padding: 6px 8px;
        }

        input[type="file"].form-control::-webkit-file-upload-button {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-primary-lime,
        .btn-outline-dark {
            min-height: 44px;
            font-size: 12px;
            padding: 12px 16px;
        }

        .alert-danger {
            padding: 10px 12px;
        }

        .alert-danger div {
            font-size: 12px;
        }
    }

    @media (max-width: 360px) {
        .profile-img-box img,
        .no-photo-placeholder {
            width: 70px;
            height: 70px;
        }

        .form-control {
            font-size: 12px;
            padding: 8px 10px;
            min-height: 36px;
        }

        .btn-primary-lime,
        .btn-outline-dark {
            min-height: 40px;
            font-size: 11px;
            padding: 10px 14px;
        }

        .form-card {
            padding: 12px 8px;
        }
    }

    /* Reduced motion preference */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>

<div class="edit-page-wrapper">
    @if($errors->any())
        <div class="alert-danger">
            @foreach($errors->all() as $e)
                <div>✕ {{ $e }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-grid-layout">
            <!-- LEFT COLUMN: Personal Info -->
            <div class="form-column">
                <div class="form-card">
                    <div class="form-card-title">Personal Information</div>

                    <div class="profile-header">
                        <div class="profile-img-box">
                            @if($member->photo)
                                <img src="{{ asset('storage/' . $member->photo) }}" id="imagePreview" alt="Profile">
                            @else
                                <div class="no-photo-placeholder" id="placeholderBox">No Photo</div>
                            @endif
                        </div>
                        <div class="profile-upload-info">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="photo" class="form-control" onchange="previewImage(this)" accept="image/jpg,image/png,image/webp">
                            <small style="color: #666; font-size: 11px; margin-top: 5px; display: block;">Accepted: JPG, PNG, WEBP. Max 3MB.</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $member->first_name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $member->last_name) }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}" placeholder="09XXXXXXXXX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Membership & Actions -->
            <div class="form-column">
                <div class="form-card">
                    <div class="form-card-title">Membership Details</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Membership Plan *</label>
                            <select name="membership_type" class="form-control" required>
                                @foreach(['Monthly', 'Quarterly', 'Semi-Annual', 'Annual'] as $plan)
                                    <option value="{{ $plan }}" {{ old('membership_type', $member->membership_type) == $plan ? 'selected' : '' }}>{{ $plan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                @foreach(['Active', 'Inactive', 'Suspended'] as $s)
                                    <option value="{{ $s }}" {{ old('status', $member->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $member->start_date) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Monthly Fee (₱) *</label>
                            <input type="number" step="0.01" name="fee" class="form-control" value="{{ old('fee', $member->fee) }}" required placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="form-actions-row">
                    <button type="submit" class="btn-primary-lime">Save Changes</button>
                    <a href="{{ route('members.index') }}" class="btn-outline-dark">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById('imagePreview');
                let placeholder = document.getElementById('placeholderBox');
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'imagePreview';
                    img.style.width = '120px';
                    img.style.height = '120px';
                    img.style.borderRadius = '10px';
                    img.style.objectFit = 'cover';
                    img.style.border = '2px solid #333';
                    if (placeholder) {
                        placeholder.parentNode.replaceChild(img, placeholder);
                    } else {
                        document.querySelector('.profile-img-box').appendChild(img);
                    }
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Ensure responsive image preview sizing
    function updatePreviewSize() {
        const img = document.getElementById('imagePreview');
        const placeholder = document.getElementById('placeholderBox');
        if (window.innerWidth <= 480) {
            if (img) { img.style.width = '80px';
                img.style.height = '80px'; }
            if (placeholder) { placeholder.style.width = '80px';
                placeholder.style.height = '80px'; }
        } else if (window.innerWidth <= 768) {
            if (img) { img.style.width = '100px';
                img.style.height = '100px'; }
            if (placeholder) { placeholder.style.width = '100px';
                placeholder.style.height = '100px'; }
        } else {
            if (img) { img.style.width = '120px';
                img.style.height = '120px'; }
            if (placeholder) { placeholder.style.width = '120px';
                placeholder.style.height = '120px'; }
        }
    }

    window.addEventListener('resize', updatePreviewSize);
    document.addEventListener('DOMContentLoaded', updatePreviewSize);
</script>

@endsection
