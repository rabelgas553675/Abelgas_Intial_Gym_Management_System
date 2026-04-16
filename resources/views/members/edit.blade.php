@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'Edit Member')
@section('page_title', 'Edit Member')

@section('topbar_actions')
  <a href="{{ route('members.index') }}" class="btn btn-secondary">← Back to Members</a>
@endsection

@section('content')
<div class="edit-page-wrapper">
    @if($errors->any())
        <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.1); border: 1px solid #dc3545; color: #dc3545; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
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
                            <input type="file" name="photo" class="form-control" onchange="previewImage(this)">
                            <small style="color: #666; font-size: 11px; margin-top: 5px; display: block;">Accepted: JPG, PNG. Max 3MB.</small>
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
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
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
                            <select name="membership_type" class="form-control custom-select" required>
                                @foreach(['Monthly', 'Quarterly', 'Semi-Annual', 'Annual'] as $plan)
                                    <option value="{{ $plan }}" {{ old('membership_type', $member->membership_type) == $plan ? 'selected' : '' }}>{{ $plan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control custom-select">
                                @foreach(['Active', 'Expired', 'Inactive'] as $s)
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
                            <input type="number" step="0.01" name="fee" class="form-control" value="{{ old('fee', $member->fee) }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-actions-row">
                    <button type="submit" class="btn btn-primary-lime">Save Changes</button>
                    <a href="{{ route('members.index') }}" class="btn btn-outline-dark">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
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
    }
    .profile-img-box img, .no-photo-placeholder {
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

    /* Input Styling */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
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
    }

    /* Calendar Icon Visibility Fix */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1); /* Turns the dark icon white */
        cursor: pointer;
        opacity: 0.6;
        transition: 0.2s;
    }
    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        /* Filter to make it Lime (#d4ff00) on hover */
        filter: invert(86%) sepia(95%) saturate(2853%) hue-rotate(22deg) brightness(101%) contrast(101%);
        opacity: 1;
    }

    /* Dropdown Icon Styling */
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

    .form-control:focus {
        border-color: #d4ff00;
        outline: none;
        box-shadow: 0 0 0 1px #d4ff00;
    }

    select.form-control:focus {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23d4ff00' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
    }

    /* Buttons */
    .form-actions-row {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }
    .btn-primary-lime {
        flex: 2;
        background: #d4ff00;
        color: #000;
        font-weight: 800;
        border: none;
        padding: 16px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        text-transform: uppercase;
    }
    .btn-outline-dark {
        flex: 1;
        background: #1a1a1a;
        color: #fff;
        border: 1px solid #333;
        text-align: center;
        padding: 16px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }

    @media (max-width: 1100px) {
        .form-grid-layout { grid-template-columns: 1fr; }
    }
</style>

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
                    placeholder.parentNode.replaceChild(img, placeholder);
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection