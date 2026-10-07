@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'Add Member – APEX')
@section('page_title', 'Add New Member')
@section('active_nav', 'members')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .add-member-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .custom-dropdown {
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

    .custom-dropdown option {
        background-color: #1a1a1a;
        color: white;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        width: 40px;
        height: 100%;
        cursor: pointer;
    }

    input[type="date"] {
        color-scheme: dark;
    }

    .strength-wrap {
        display: flex;
        gap: 4px;
        margin-top: 8px;
    }

    .strength-seg {
        flex: 1;
        height: 3px;
        border-radius: 2px;
        background: var(--border);
        transition: background .3s;
    }

    /* Form Cards */
    .form-card {
        background: var(--surface1);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 32px;
        margin-bottom: 20px;
    }

    .form-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 28px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--border);
    }

    .form-card-icon {
        width: 32px;
        height: 32px;
        background: rgba(200, 255, 0, 0.1);
        border: 1px solid rgba(200, 255, 0, 0.25);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .form-card-icon svg {
        width: 15px;
        height: 15px;
        stroke: var(--accent);
        fill: none;
        stroke-width: 2;
    }

    .form-card-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
    }

    .form-card-sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 1px;
    }

    /* Field styles */
    .field-label {
        display: block;
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    .field-input {
        width: 100%;
        padding: 11px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 13px;
        outline: none;
        transition: border-color .15s;
        box-sizing: border-box;
        font-family: inherit;
    }

    .field-input:focus {
        border-color: var(--accent);
    }

    .field-input::placeholder {
        color: var(--muted);
        opacity: 0.5;
    }

    textarea.field-input {
        resize: vertical;
        font-family: inherit;
        min-height: 60px;
    }

    /* Error alert */
    .error-alert {
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.3);
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 24px;
    }

    .error-item {
        color: #f87171;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 3px 0;
    }

    .error-item svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    /* Photo upload */
    .photo-upload {
        text-align: center;
    }

    .photo-preview {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        border: 2px dashed var(--border);
        background: var(--surface2);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin: 0 auto 12px;
        overflow: hidden;
        transition: border-color .2s, background .2s;
        position: relative;
    }

    .photo-preview:hover {
        border-color: var(--accent);
        background: rgba(200, 255, 0, 0.05);
    }

    .photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
        border-radius: 50%;
    }

    .photo-preview .placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .photo-preview .placeholder svg {
        width: 28px;
        height: 28px;
        stroke: var(--muted);
        fill: none;
    }

    .photo-preview .placeholder span {
        font-size: 11px;
        color: var(--muted);
        line-height: 1.4;
    }

    .photo-hint {
        font-size: 11px;
        color: var(--muted);
        line-height: 1.8;
    }

    /* Form rows */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    /* Password toggle */
    .pw-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        padding: 2px;
        transition: color .15s;
    }

    .pw-toggle:hover {
        color: var(--text);
    }

    .pw-toggle svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
    }

    .password-wrapper {
        position: relative;
    }

    .password-wrapper .field-input {
        padding-right: 44px;
    }

    /* Info note */
    .info-note {
        margin-top: 20px;
        background: rgba(96, 165, 250, 0.06);
        border: 1px solid rgba(96, 165, 250, 0.18);
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .info-note svg {
        width: 15px;
        height: 15px;
        flex-shrink: 0;
        margin-top: 1px;
        stroke: #60a5fa;
        fill: none;
        stroke-width: 2;
    }

    .info-note-text {
        font-size: 12px;
        color: rgba(96, 165, 250, 0.85);
        line-height: 1.6;
    }

    .info-note-text strong {
        color: #60a5fa;
    }

    /* Buttons */
    .form-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-submit {
        padding: 12px 28px;
        background: var(--accent);
        color: #000;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: .15s;
        min-height: 48px;
    }

    .btn-submit:hover {
        opacity: .88;
    }

    .btn-submit svg {
        width: 15px;
        height: 15px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2.5;
    }

    .btn-cancel {
        padding: 12px 24px;
        background: transparent;
        color: var(--muted);
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: .15s;
        min-height: 48px;
        display: inline-flex;
        align-items: center;
    }

    .btn-cancel:hover {
        border-color: var(--text);
        color: var(--text);
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 768px) {
        .add-member-container {
            padding: 0 12px;
        }

        .form-card {
            padding: 24px 16px;
            border-radius: 12px;
        }

        .form-card-header {
            margin-bottom: 20px;
            padding-bottom: 14px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .form-row:last-child {
            margin-bottom: 0;
        }

        .photo-upload {
            margin-bottom: 16px;
        }

        .photo-preview {
            width: 120px;
            height: 120px;
        }

        .field-label {
            font-size: 10px;
            margin-bottom: 6px;
        }

        .field-input {
            font-size: 14px;
            padding: 10px 12px;
            min-height: 44px;
        }

        textarea.field-input {
            min-height: 50px;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-submit,
        .btn-cancel {
            width: 100%;
            justify-content: center;
            min-height: 44px;
        }

        .btn-cancel {
            padding: 10px 20px;
        }

        .info-note {
            padding: 10px 12px;
            flex-wrap: wrap;
        }

        .info-note-text {
            font-size: 11px;
        }

        .error-alert {
            padding: 12px 14px;
        }

        .error-item {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .add-member-container {
            padding: 0 8px;
        }

        .form-card {
            padding: 16px 12px;
            border-radius: 10px;
            margin-bottom: 14px;
        }

        .form-card-header {
            gap: 8px;
            margin-bottom: 16px;
            padding-bottom: 10px;
        }

        .form-card-icon {
            width: 28px;
            height: 28px;
        }

        .form-card-icon svg {
            width: 13px;
            height: 13px;
        }

        .form-card-title {
            font-size: 13px;
        }

        .form-card-sub {
            font-size: 11px;
        }

        .photo-preview {
            width: 100px;
            height: 100px;
        }

        .photo-preview .placeholder svg {
            width: 22px;
            height: 22px;
        }

        .photo-preview .placeholder span {
            font-size: 10px;
        }

        .photo-hint {
            font-size: 10px;
            line-height: 1.6;
        }

        .field-label {
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .field-input {
            font-size: 13px;
            padding: 8px 10px;
            min-height: 38px;
        }

        .custom-dropdown {
            background-size: 12px !important;
            background-position: right 10px center !important;
            padding-right: 34px !important;
        }

        .btn-submit {
            font-size: 13px;
            padding: 10px 20px;
            min-height: 40px;
        }

        .btn-cancel {
            font-size: 12px;
            padding: 8px 16px;
            min-height: 40px;
        }

        .error-item {
            font-size: 11px;
        }

        .error-item svg {
            width: 12px;
            height: 12px;
        }

        .strength-wrap {
            gap: 3px;
        }

        .strength-seg {
            height: 2px;
        }

        #strength-label,
        #match-label {
            font-size: 10px;
        }

        .info-note {
            padding: 8px 10px;
        }

        .info-note svg {
            width: 13px;
            height: 13px;
        }

        .info-note-text {
            font-size: 10px;
        }

        .password-wrapper .field-input {
            padding-right: 38px;
        }

        .pw-toggle svg {
            width: 14px;
            height: 14px;
        }
    }

    @media (max-width: 360px) {
        .form-card {
            padding: 12px 8px;
        }

        .photo-preview {
            width: 80px;
            height: 80px;
        }

        .photo-preview .placeholder svg {
            width: 18px;
            height: 18px;
        }

        .photo-preview .placeholder span {
            font-size: 9px;
        }

        .field-input {
            font-size: 12px;
            padding: 6px 8px;
            min-height: 34px;
        }

        .btn-submit {
            font-size: 12px;
            padding: 8px 16px;
            min-height: 36px;
        }

        .btn-cancel {
            font-size: 11px;
            padding: 6px 14px;
            min-height: 36px;
        }

        .form-card-title {
            font-size: 12px;
        }

        .form-card-sub {
            font-size: 10px;
        }
    }
</style>

<div class="add-member-container">

    @if($errors->any())
        <div class="error-alert">
            @foreach($errors->all() as $error)
                <div class="error-item">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $error }}
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data" autocomplete="off">
        @csrf

        {{-- ── Card 1: Personal Info ── --}}
        <div class="form-card">

            <div class="form-card-header">
                <div class="form-card-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <div class="form-card-title">Personal Information</div>
                    <div class="form-card-sub">Basic details of the new member</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:160px 1fr; gap:32px; align-items:start;">

                {{-- Photo Upload --}}
                <div class="photo-upload">
                    <div class="photo-preview" id="photo-preview"
                         onclick="document.getElementById('photo-input').click()">
                        <img id="photo-img" src="" alt="">
                        <div class="placeholder" id="photo-placeholder">
                            <svg viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Click to<br>upload</span>
                        </div>
                    </div>
                    <input type="file" id="photo-input" name="photo"
                           accept="image/jpg,image/png,image/webp"
                           style="display:none;" onchange="previewPhoto(this)"/>
                    <div class="photo-hint">JPG / PNG / WEBP<br>Max 3MB</div>
                </div>

                {{-- Personal Fields --}}
                <div>
                    {{-- Row 1: First + Last --}}
                    <div class="form-row">
                        <div>
                            <label class="field-label">First Name <span style="color:#f87171;">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="e.g. Juan"
                                   class="field-input" required/>
                        </div>
                        <div>
                            <label class="field-label">Last Name <span style="color:#f87171;">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="e.g. Dela Cruz"
                                   class="field-input" required/>
                        </div>
                    </div>

                    {{-- Row 2: Email + Phone --}}
                    <div class="form-row">
                        <div>
                            <label class="field-label">Email Address <span style="color:#f87171;">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="member@email.com"
                                   class="field-input" required
                                   autocomplete="off"
                                   readonly
                                   onfocus="this.removeAttribute('readonly')"
                                   onblur="if(!this.value) this.setAttribute('readonly', true)"/>
                        </div>
                        <div>
                            <label class="field-label">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="09XXXXXXXXX"
                                   class="field-input"/>
                        </div>
                    </div>

                    {{-- Row 3: Gender + Birthdate --}}
                    <div class="form-row">
                        <div>
                            <label class="field-label">Gender <span style="color:#f87171;">*</span></label>
                            <select name="gender" class="custom-dropdown field-input" required>
                                <option value="">Select Gender</option>
                                <option value="Male"   {{ old('gender')=='Male'   ?'selected':'' }}>Male</option>
                                <option value="Female" {{ old('gender')=='Female' ?'selected':'' }}>Female</option>
                                <option value="Other"  {{ old('gender')=='Other'  ?'selected':'' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Birthdate <span style="color:#f87171;">*</span></label>
                            <div style="position:relative;">
                                <input type="date" name="birthdate" value="{{ old('birthdate') }}"
                                       class="field-input" style="padding-right:40px; color-scheme:dark;" required/>
                                <svg style="position:absolute; right:12px; top:50%; transform:translateY(-50%);
                                            pointer-events:none; color:#c8ff00;"
                                     width="16" height="16" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Row 4: Address --}}
                    <div>
                        <label class="field-label">Address</label>
                        <textarea name="address" rows="2" placeholder="Complete address"
                                  class="field-input">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Card 2: Account Credentials ── --}}
        <div class="form-card">

            <div class="form-card-header">
                <div class="form-card-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </div>
                <div>
                    <div class="form-card-title">Account Credentials</div>
                    <div class="form-card-sub">Login details for the member's portal account</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                {{-- Password --}}
                <div>
                    <label class="field-label">Password <span style="color:#f87171;">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password"
                               placeholder="Min. 8 characters"
                               autocomplete="new-password"
                               class="field-input" required
                               oninput="checkStrength(this.value)"/>
                        <button type="button" class="pw-toggle" onclick="togglePw('password','eye1')">
                            <svg id="eye1" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div class="strength-wrap">
                        <div class="strength-seg" id="seg1"></div>
                        <div class="strength-seg" id="seg2"></div>
                        <div class="strength-seg" id="seg3"></div>
                        <div class="strength-seg" id="seg4"></div>
                    </div>
                    <div id="strength-label" style="font-size:11px; color:var(--muted); margin-top:4px; height:14px;"></div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="field-label">Confirm Password <span style="color:#f87171;">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               placeholder="Repeat password"
                               autocomplete="new-password"
                               class="field-input" required
                               oninput="checkMatch()"/>
                        <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','eye2')">
                            <svg id="eye2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <div id="match-label" style="font-size:11px; margin-top:6px; height:14px;"></div>
                </div>

            </div>

            {{-- Info note --}}
            <div class="info-note">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div class="info-note-text">
                    A member portal account will be created automatically with the email and password above.
                    The member can log in at <strong>{{ config('app.url') }}</strong> using these credentials.
                </div>
            </div>

        </div>

        {{-- ── Action Buttons ── --}}
        <div class="form-actions">
            <button type="submit" id="submitBtn" class="btn-submit">
                <svg viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Register Member
            </button>
            <a href="{{ route('members.index') }}" class="btn-cancel">Cancel</a>
        </div>

    </form>
</div>

<script>
    function previewPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.getElementById('photo-img');
                img.src = e.target.result;
                img.style.display = 'block';
                document.getElementById('photo-placeholder').style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function togglePw(fieldId, eyeId) {
        const f = document.getElementById(fieldId);
        const e = document.getElementById(eyeId);
        if (f.type === 'password') {
            f.type = 'text';
            e.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>`;
        } else {
            f.type = 'password';
            e.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>`;
        }
    }

    function checkStrength(val) {
        let score = 0;
        if (val.length >= 8) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const colors = ['', '#f87171', '#fbbf24', '#60a5fa', '#4ade80'];
        const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

        for (let i = 1; i <= 4; i++) {
            document.getElementById('seg' + i).style.background =
                i <= score ? colors[score] : 'var(--border)';
        }
        const lbl = document.getElementById('strength-label');
        lbl.textContent = val.length ? labels[score] : '';
        lbl.style.color = colors[score] || 'var(--muted)';
        checkMatch();
    }

    function checkMatch() {
        const pw = document.getElementById('password').value;
        const conf = document.getElementById('password_confirmation').value;
        const label = document.getElementById('match-label');
        if (!conf) { label.textContent = '';
            return; }
        if (pw === conf) {
            label.textContent = '✓ Passwords match';
            label.style.color = '#4ade80';
        } else {
            label.textContent = '✕ Passwords do not match';
            label.style.color = '#f87171';
        }
    }
</script>

@endsection