@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'Add Member – IRONFORGE')
@section('page_title', 'Add New Member')
@section('active_nav', 'members')

@section('content')

<style>
  /* Fix for custom dropdown icons */
  .custom-dropdown {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    /* Custom Chevron SVG Icon */
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    background-size: 14px !important;
    padding-right: 40px !important;
    cursor: pointer;
  }

  /* Styling for the options menu background */
  .custom-dropdown option {
    background-color: #1a1a1a;
    color: white;
  }
</style>

<div style="max-width:900px; margin:0 auto;">

  @if($errors->any())
    <div style="background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.3);
                border-radius:10px; padding:14px 18px; margin-bottom:24px;">
      @foreach($errors->all() as $error)
        <div style="color:#f87171; font-size:13px; display:flex; align-items:center; gap:8px; padding:3px 0;">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ $error }}
        </div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- ── Card 1: Personal Info ──────────────────────────────────────────── --}}
    <div style="background:var(--surface1); border:1px solid var(--border); border-radius:14px;
                padding:32px; margin-bottom:20px;">

      {{-- Card header --}}
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:28px;
                  padding-bottom:18px; border-bottom:1px solid var(--border);">
        <div style="width:32px; height:32px; background:rgba(200,255,0,0.1); border:1px solid rgba(200,255,0,0.25);
                    border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <svg width="15" height="15" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <div>
          <div style="font-size:15px; font-weight:700; color:var(--text);">Personal Information</div>
          <div style="font-size:12px; color:var(--muted); margin-top:1px;">Basic details of the new member</div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:160px 1fr; gap:32px; align-items:start;">

        {{-- Photo Upload --}}
        <div style="text-align:center;">
          <div id="photo-preview"
               style="width:140px; height:140px; border-radius:50%;
                      border:2px dashed var(--border); background:var(--surface2);
                      display:flex; flex-direction:column; align-items:center;
                      justify-content:center; cursor:pointer; margin:0 auto 12px;
                      overflow:hidden; transition:border-color .2s, background .2s;"
               onclick="document.getElementById('photo-input').click()"
               onmouseover="this.style.borderColor='var(--accent)'; this.style.background='rgba(200,255,0,0.05)'"
               onmouseout="this.style.borderColor='var(--border)'; this.style.background='var(--surface2)'">
            <img id="photo-img" src="" alt=""
                 style="width:100%; height:100%; object-fit:cover; display:none; border-radius:50%;"/>
            <div id="photo-placeholder" style="display:flex; flex-direction:column; align-items:center; gap:6px;">
              <svg width="28" height="28" fill="none" stroke="var(--muted)" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span style="font-size:11px; color:var(--muted); line-height:1.4;">Click to<br>upload</span>
            </div>
          </div>
          <input type="file" id="photo-input" name="photo"
                 accept="image/jpg,image/png,image/webp"
                 style="display:none;" onchange="previewPhoto(this)"/>
          <div style="font-size:11px; color:var(--muted); line-height:1.8;">
            JPG / PNG / WEBP<br>Max 3MB
          </div>
        </div>

        {{-- Personal Fields --}}
        <div>

          {{-- Row 1: First + Last Name --}}
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
            <div>
              <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                First Name <span style="color:#f87171;">*</span>
              </label>
              <input type="text" name="first_name" value="{{ old('first_name') }}"
                     placeholder="e.g. Juan"
                     style="width:100%; padding:11px 14px; background:var(--surface2);
                            border:1px solid var(--border); border-radius:8px;
                            color:var(--text); font-size:13px; outline:none; transition:.15s; box-sizing:border-box;"
                     onfocus="this.style.borderColor='var(--accent)'"
                     onblur="this.style.borderColor='var(--border)'" required/>
            </div>
            <div>
              <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                Last Name <span style="color:#f87171;">*</span>
              </label>
              <input type="text" name="last_name" value="{{ old('last_name') }}"
                     placeholder="e.g. Dela Cruz"
                     style="width:100%; padding:11px 14px; background:var(--surface2);
                            border:1px solid var(--border); border-radius:8px;
                            color:var(--text); font-size:13px; outline:none; transition:.15s; box-sizing:border-box;"
                     onfocus="this.style.borderColor='var(--accent)'"
                     onblur="this.style.borderColor='var(--border)'" required/>
            </div>
          </div>

          {{-- Row 2: Email + Phone --}}
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
            <div>
              <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                Email Address <span style="color:#f87171;">*</span>
              </label>
              <input type="email" name="email" value="{{ old('email') }}"
                     placeholder="member@email.com"
                     style="width:100%; padding:11px 14px; background:var(--surface2);
                            border:1px solid var(--border); border-radius:8px;
                            color:var(--text); font-size:13px; outline:none; transition:.15s; box-sizing:border-box;"
                     onfocus="this.style.borderColor='var(--accent)'"
                     onblur="this.style.borderColor='var(--border)'" required/>
            </div>
            <div>
              <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                Phone Number
              </label>
              <input type="text" name="phone" value="{{ old('phone') }}"
                     placeholder="09XXXXXXXXX"
                     style="width:100%; padding:11px 14px; background:var(--surface2);
                            border:1px solid var(--border); border-radius:8px;
                            color:var(--text); font-size:13px; outline:none; transition:.15s; box-sizing:border-box;"
                     onfocus="this.style.borderColor='var(--accent)'"
                     onblur="this.style.borderColor='var(--border)'"/>
            </div>
          </div>

          {{-- Row 3: Gender + Birthdate --}}
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
            <div>
              <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                Gender <span style="color:#f87171;">*</span>
              </label>
              <select name="gender" class="custom-dropdown"
                      style="width:100%; padding:11px 14px; background:var(--surface2);
                             border:1px solid var(--border); border-radius:8px;
                             color:var(--text); font-size:13px; outline:none; cursor:pointer; transition:.15s; box-sizing:border-box;"
                      onfocus="this.style.borderColor='var(--accent)'"
                      onblur="this.style.borderColor='var(--border)'" required>
                <option value="">Select Gender</option>
                <option value="Male"   {{ old('gender')=='Male'   ?'selected':'' }}>Male</option>
                <option value="Female" {{ old('gender')=='Female' ?'selected':'' }}>Female</option>
                <option value="Other"  {{ old('gender')=='Other'  ?'selected':'' }}>Other</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                            text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                Birthdate <span style="color:#f87171;">*</span>
              </label>
              <input type="date" name="birthdate" value="{{ old('birthdate') }}"
                     style="width:100%; padding:11px 14px; background:var(--surface2);
                            border:1px solid var(--border); border-radius:8px;
                            color:var(--text); font-size:13px; outline:none; transition:.15s; box-sizing:border-box;"
                     onfocus="this.style.borderColor='var(--accent)'"
                     onblur="this.style.borderColor='var(--border)'" required/>
            </div>
          </div>

          {{-- Row 4: Address --}}
          <div>
            <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                          text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
              Address
            </label>
            <textarea name="address" rows="2"
                      placeholder="Complete address"
                      style="width:100%; padding:11px 14px; background:var(--surface2);
                             border:1px solid var(--border); border-radius:8px;
                             color:var(--text); font-size:13px; outline:none; transition:.15s;
                             resize:vertical; box-sizing:border-box; font-family:inherit;"
                      onfocus="this.style.borderColor='var(--accent)'"
                      onblur="this.style.borderColor='var(--border)'">{{ old('address') }}</textarea>
          </div>

        </div>
      </div>
    </div>

    {{-- ── Card 2: Membership Details ────────────────────────────────────── --}}
    <div style="background:var(--surface1); border:1px solid var(--border); border-radius:14px;
                padding:32px; margin-bottom:24px;">

      {{-- Card header --}}
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:28px;
                  padding-bottom:18px; border-bottom:1px solid var(--border);">
        <div style="width:32px; height:32px; background:rgba(200,255,0,0.1); border:1px solid rgba(200,255,0,0.25);
                    border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <svg width="15" height="15" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
          </svg>
        </div>
        <div>
          <div style="font-size:15px; font-weight:700; color:var(--text);">Membership Details</div>
          <div style="font-size:12px; color:var(--muted); margin-top:1px;">Plan, dates and fee</div>
        </div>
      </div>

      {{-- Row: Type + Start + End --}}
      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:18px;">

        <div>
          <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                        text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
            Membership Type <span style="color:#f87171;">*</span>
          </label>
          <select name="membership_type" id="membership_type" class="custom-dropdown"
                  style="width:100%; padding:11px 14px; background:var(--surface2);
                         border:1px solid var(--border); border-radius:8px;
                         color:var(--text); font-size:13px; outline:none; cursor:pointer; transition:.15s; box-sizing:border-box;"
                  onfocus="this.style.borderColor='var(--accent)'"
                  onblur="this.style.borderColor='var(--border)'"
                  onchange="computeEndDate()" required>
            <option value="">Select Type</option>
            <option value="Monthly"     {{ old('membership_type')=='Monthly'     ?'selected':'' }}>Monthly — ₱800</option>
            <option value="Quarterly"   {{ old('membership_type')=='Quarterly'   ?'selected':'' }}>Quarterly — ₱2,100</option>
            <option value="Semi-Annual" {{ old('membership_type')=='Semi-Annual' ?'selected':'' }}>Semi-Annual — ₱4,000</option>
            <option value="Annual"      {{ old('membership_type')=='Annual'      ?'selected':'' }}>Annual — ₱7,500</option>
          </select>
        </div>

        <div>
          <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                        text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
            Start Date <span style="color:#f87171;">*</span>
          </label>
          <input type="date" name="start_date" id="start_date"
                 value="{{ old('start_date', date('Y-m-d')) }}"
                 style="width:100%; padding:11px 14px; background:var(--surface2);
                        border:1px solid var(--border); border-radius:8px;
                        color:var(--text); font-size:13px; outline:none; transition:.15s; box-sizing:border-box;"
                 onfocus="this.style.borderColor='var(--accent)'"
                 onblur="this.style.borderColor='var(--border)'"
                 onchange="computeEndDate()" required/>
        </div>

        <div>
          <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                        text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
            End Date
            <span style="font-weight:400; color:var(--muted); font-size:10px; text-transform:none; letter-spacing:0;">(auto-computed)</span>
          </label>
          <input type="text" id="end_date_display"
                 placeholder="Select type & date" readonly
                 style="width:100%; padding:11px 14px; background:rgba(200,255,0,0.04);
                        border:1px solid rgba(200,255,0,0.2); border-radius:8px;
                        color:var(--accent); font-size:13px; font-weight:600;
                        cursor:not-allowed; box-sizing:border-box;"/>
          <input type="hidden" name="end_date" id="end_date_value"/>
        </div>
      </div>

      {{-- Fee --}}
      <div>
        <label style="display:block; font-size:11px; color:var(--muted); font-weight:600;
                      text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
          Membership Fee (₱) <span style="color:#f87171;">*</span>
        </label>
        <div style="display:flex; align-items:center; gap:12px;">
          <div style="position:relative; display:inline-block;">
            <span style="position:absolute; left:13px; top:50%; transform:translateY(-50%);
                         color:var(--accent); font-weight:700; font-size:14px; pointer-events:none;">₱</span>
            <input type="number" name="fee" id="fee"
                   step="0.01" min="0" placeholder="0.00"
                   value="{{ old('fee', '0.00') }}"
                   style="padding:11px 14px 11px 30px; background:var(--surface2);
                          border:1px solid var(--border); border-radius:8px;
                          color:var(--text); font-size:13px; outline:none; transition:.15s; width:200px;"
                   onfocus="this.style.borderColor='var(--accent)'"
                   onblur="this.style.borderColor='var(--border)'" required/>
          </div>
          <span style="font-size:12px; color:var(--muted);">Auto-filled — you may adjust if needed.</span>
        </div>
      </div>

    </div>

    {{-- ── Action Buttons ─────────────────────────────────────────────────── --}}
    <div style="display:flex; gap:12px; align-items:center;">
      <button type="submit"
              style="padding:12px 28px; background:var(--accent); color:#000; border:none;
                     border-radius:8px; font-size:14px; font-weight:700; cursor:pointer;
                     display:inline-flex; align-items:center; gap:8px; transition:.15s;"
              onmouseover="this.style.opacity='.88'"
              onmouseout="this.style.opacity='1'">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        Register Member
      </button>
      <a href="{{ route('members.index') }}"
         style="padding:12px 24px; background:transparent; color:var(--muted);
                border:1px solid var(--border); border-radius:8px; font-size:13px;
                font-weight:600; text-decoration:none; transition:.15s;"
         onmouseover="this.style.borderColor='var(--text)'; this.style.color='var(--text)'"
         onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--muted)'">
        Cancel
      </a>
    </div>

  </form>
</div>

<script>
const plans = {
  'Monthly':     { months: 1,  fee: 800   },
  'Quarterly':   { months: 3,  fee: 2100  },
  'Semi-Annual': { months: 6,  fee: 4000  },
  'Annual':      { months: 12, fee: 7500  },
};

function computeEndDate() {
  const type     = document.getElementById('membership_type').value;
  const startVal = document.getElementById('start_date').value;
  const display  = document.getElementById('end_date_display');
  const hidden   = document.getElementById('end_date_value');
  const feeInput = document.getElementById('fee');

  if (!type || !startVal) {
    display.value = '';
    display.placeholder = 'Select type & date';
    hidden.value  = '';
    return;
  }

  const plan  = plans[type];
  const start = new Date(startVal + 'T00:00:00');
  const end   = new Date(start);
  end.setMonth(end.getMonth() + plan.months);

  display.value = end.toLocaleDateString('en-US', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
  hidden.value   = end.toISOString().slice(0, 10);
  feeInput.value = plan.fee.toFixed(2);
}

function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img         = document.getElementById('photo-img');
      const placeholder = document.getElementById('photo-placeholder');
      img.src           = e.target.result;
      img.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Run on load to restore old() values
computeEndDate();
</script>
@endsection