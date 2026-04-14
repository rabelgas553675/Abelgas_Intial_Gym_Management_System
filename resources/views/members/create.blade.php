@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'Add Member – IRONFORGE')
@section('page_title', 'Add New Member')

@section('content')
<div style="max-width:860px;margin:0 auto;">

  @if($errors->any())
    <div class="alert alert-danger">
      @foreach($errors->all() as $error)
        <div>✕ {{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
    @csrf

    {{-- Card 1: Personal Info --}}
    <div class="form-card">
      <div class="form-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="color:var(--accent);">👤</span> New Member Registration
      </div>

      <div style="display:grid;grid-template-columns:180px 1fr;gap:32px;align-items:start;">

        {{-- Photo Upload --}}
        <div style="text-align:center;">
          <div id="photo-preview"
               style="width:140px;height:140px;border-radius:50%;
                      border:2px dashed var(--border);background:var(--surface2);
                      display:flex;flex-direction:column;align-items:center;
                      justify-content:center;cursor:pointer;margin:0 auto 12px;
                      overflow:hidden;transition:border-color 0.15s;"
               onclick="document.getElementById('photo-input').click()"
               onmouseover="this.style.borderColor='var(--accent)'"
               onmouseout="this.style.borderColor='var(--border)'">
            <img id="photo-img" src="" alt=""
                 style="width:100%;height:100%;object-fit:cover;display:none;border-radius:50%;"/>
            <div id="photo-placeholder">
              <svg width="32" height="32" fill="none" stroke="var(--muted)" viewBox="0 0 24 24"
                   style="margin-bottom:6px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <div style="font-size:11px;color:var(--muted);">Click to upload</div>
            </div>
          </div>
          <input type="file" id="photo-input" name="photo"
                 accept="image/jpg,image/png,image/webp"
                 style="display:none;" onchange="previewPhoto(this)"/>
          <div style="font-size:11px;color:var(--muted);line-height:1.8;">
            JPG/PNG/WEBP · Max 3MB<br>
          </div>
        </div>

        {{-- Personal Fields --}}
        <div>
          <div style="font-size:10px;color:var(--muted);letter-spacing:2px;
                      text-transform:uppercase;margin-bottom:14px;">
            Personal Information
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">First Name *</label>
              <input type="text" name="first_name" class="form-control"
                     placeholder="e.g. Juan" value="{{ old('first_name') }}" required/>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name *</label>
              <input type="text" name="last_name" class="form-control"
                     placeholder="e.g. Dela Cruz" value="{{ old('last_name') }}" required/>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input type="email" name="email" class="form-control"
                     placeholder="member@email.com" value="{{ old('email') }}" required/>
            </div>
            <div class="form-group">
              <label class="form-label">Phone Number *</label>
              <input type="text" name="phone" class="form-control"
                     placeholder="09XXXXXXXXX" value="{{ old('phone') }}"/>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Gender *</label>
              <select name="gender" class="form-control" required>
                <option value="">Select Gender</option>
                <option value="Male"   {{ old('gender')=='Male'   ?'selected':'' }}>Male</option>
                <option value="Female" {{ old('gender')=='Female' ?'selected':'' }}>Female</option>
                <option value="Other"  {{ old('gender')=='Other'  ?'selected':'' }}>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Birthdate *</label>
              <input type="date" name="birthdate" class="form-control"
                     value="{{ old('birthdate') }}" required/>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Address *</label>
            <textarea name="address" class="form-control" rows="2"
                      placeholder="Complete address">{{ old('address') }}</textarea>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Membership Details --}}
    <div class="form-card">
      <div class="form-card-title" style="display:flex;align-items:center;gap:8px;">
        <span style="color:var(--accent);">📋</span> Membership Details
      </div>

      <div style="font-size:10px;color:var(--muted);letter-spacing:2px;
                  text-transform:uppercase;margin-bottom:14px;">
        Membership Details
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Membership Type *</label>
          <select name="membership_type" id="membership_type" class="form-control"
                  required onchange="computeEndDate()">
            <option value="">Select Type</option>
            <option value="Monthly"     {{ old('membership_type')=='Monthly'     ?'selected':'' }}>Monthly — ₱800</option>
            <option value="Quarterly"   {{ old('membership_type')=='Quarterly'   ?'selected':'' }}>Quarterly — ₱2,100</option>
            <option value="Semi-Annual" {{ old('membership_type')=='Semi-Annual' ?'selected':'' }}>Semi-Annual — ₱4,000</option>
            <option value="Annual"      {{ old('membership_type')=='Annual'      ?'selected':'' }}>Annual — ₱7,500</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Start Date *</label>
          <input type="date" name="start_date" id="start_date" class="form-control"
                 value="{{ old('start_date', date('Y-m-d')) }}"
                 required onchange="computeEndDate()"/>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">
            End Date
            <span style="font-weight:400;color:var(--muted);font-size:11px;">(auto-computed)</span>
          </label>
          <input type="text" id="end_date_display" class="form-control"
                 placeholder="Select type & date" readonly
                 style="color:var(--accent);cursor:not-allowed;"/>
          <input type="hidden" name="end_date" id="end_date_value"/>
        </div>

      </div>

      <div class="form-group">
        <label class="form-label">Membership Fee (₱) *</label>
        <input type="number" name="fee" id="fee" class="form-control"
               step="0.01" min="0" placeholder="0.00"
               value="{{ old('fee', '0.00') }}" style="max-width:240px;" required/>
        <div style="font-size:12px;color:var(--muted);margin-top:4px;">
          Auto-filled — you may adjust.
        </div>
      </div>
    </div>

    {{-- Action Buttons --}}
    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn btn-primary" style="padding:11px 28px;">
        👤 Register Member
      </button>
      <a href="{{ route('members.index') }}" class="btn btn-secondary" style="padding:11px 28px;">
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
    display.value = 'Select type & date';
    hidden.value  = '';
    return;
  }

  const plan  = plans[type];
  const start = new Date(startVal);
  const end   = new Date(start);
  end.setMonth(end.getMonth() + plan.months);

  const formatted = end.toLocaleDateString('en-US', {
    year: 'numeric', month: 'long', day: 'numeric'
  });

  display.value = formatted;
  hidden.value  = end.toISOString().slice(0, 10);
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

computeEndDate();
</script>
@endsection