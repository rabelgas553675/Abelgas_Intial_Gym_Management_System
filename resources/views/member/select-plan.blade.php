@extends('layouts.member')
@section('title', 'Choose Your Plan – IRONFORGE')
@section('active', 'plans')

@section('content')

{{-- Page Header --}}
<div style="text-align:center;margin-bottom:48px;">
  <h1 style="font-size:48px;font-weight:800;margin-bottom:12px;">
    Choose Your <span style="color:var(--accent);">Plan</span>
  </h1>
  <p style="color:var(--muted);font-size:16px;">Select a fitness program that matches your goals</p>
</div>

@if(session('success'))
  <div class="alert alert-success" style="margin-bottom:24px;">✓ {{ session('success') }}</div>
@endif

<form action="{{ route('member.subscribe') }}" method="POST" id="plan-form">
@csrf

{{-- ── 1. FITNESS PLAN ── --}}
<div style="margin-bottom:48px;">
  <h2 style="font-size:22px;font-weight:700;margin-bottom:20px;">Fitness Plan</h2>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
    @php
      $plans = [
        ['name'=>'Calisthenics',       'desc'=>'Build strength using bodyweight exercises',           'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><line x1="24" y1="11" x2="24" y2="24"/><line x1="24" y1="24" x2="14" y2="34"/><line x1="24" y1="24" x2="34" y2="34"/><line x1="24" y1="18" x2="14" y2="22"/><line x1="24" y1="18" x2="34" y2="22"/></svg>'],
        ['name'=>'Bodybuilding',        'desc'=>'Muscle hypertrophy and aesthetic development',        'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 28 Q10 24 14 20 Q18 16 22 20 L26 28 Q30 32 26 36 Q22 40 18 36 Z"/><path d="M26 28 Q30 24 34 20"/><path d="M6 22 L14 20"/><path d="M34 20 L42 18"/><path d="M6 26 L14 28"/><path d="M34 28 L42 26"/></svg>'],
        ['name'=>'Plyometrics',         'desc'=>'Explosive power and athletic performance',            'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><path d="M24 11 L18 22 L24 20 L20 34"/><path d="M24 20 L30 18 L26 30"/><path d="M16 38 L32 38"/></svg>'],
        ['name'=>'Powerlifting',        'desc'=>'Maximum strength in squat, bench, deadlift',         'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="18" width="6" height="12" rx="2"/><rect x="38" y="18" width="6" height="12" rx="2"/><rect x="8" y="20" width="6" height="8" rx="1"/><rect x="34" y="20" width="6" height="8" rx="1"/><line x1="14" y1="24" x2="34" y2="24"/><circle cx="24" cy="14" r="3"/></svg>'],
        ['name'=>'Endurance',           'desc'=>'Cardiovascular fitness and stamina',                  'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><path d="M20 12 Q16 18 18 24 L22 22 L20 34 L26 28 L28 34 L30 22 L34 24 Q36 18 32 12"/></svg>'],
        ['name'=>'Functional Training', 'desc'=>'Movement patterns for daily life',                    'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="24" r="14"/><path d="M24 10 L24 14"/><path d="M24 34 L24 38"/><path d="M10 24 L14 24"/><path d="M34 24 L38 24"/><circle cx="24" cy="24" r="4"/></svg>'],
        ['name'=>'Hybrid Training',     'desc'=>'Combines multiple training styles',                   'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="24,6 28,18 40,18 30,26 34,38 24,30 14,38 18,26 8,18 20,18"/></svg>'],
      ];
    @endphp

    @foreach($plans as $plan)
      @php $isCurrent = $member?->fitness_plan === $plan['name']; @endphp
      <label style="cursor:pointer;position:relative;">
        <input type="radio" name="fitness_plan" value="{{ $plan['name'] }}"
               style="display:none;" class="plan-radio"
               {{ old('fitness_plan', $member?->fitness_plan) == $plan['name'] ? 'checked' : '' }}/>
        <div class="plan-card {{ $isCurrent ? 'plan-selected' : '' }}"
             style="background:var(--surface);border:1.5px solid {{ $isCurrent ? 'var(--accent)' : 'var(--border)' }};
                    border-radius:14px;padding:28px 24px;transition:all 0.2s;height:100%;
                    {{ $isCurrent ? 'background:rgba(232,255,42,0.04);' : '' }}">

          @if($isCurrent)
            <div style="position:absolute;top:14px;right:14px;background:var(--accent);color:#111;
                        font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;
                        letter-spacing:0.5px;">Current Plan</div>
          @endif

          <div style="width:44px;height:44px;margin-bottom:16px;color:rgba(255,255,255,0.3);"
               class="plan-icon">
            {!! $plan['svg'] !!}
          </div>
          <div style="font-size:18px;font-weight:700;margin-bottom:8px;">{{ $plan['name'] }}</div>
          <div style="font-size:13px;color:var(--muted);line-height:1.5;">{{ $plan['desc'] }}</div>

          <div class="selected-indicator"
               style="margin-top:16px;font-size:13px;font-weight:600;color:var(--accent);
                      display:{{ $isCurrent ? 'flex' : 'none' }};align-items:center;gap:6px;">
            ✓ Selected
          </div>
        </div>
      </label>
    @endforeach
  </div>
  @error('fitness_plan')
    <div style="color:var(--danger);font-size:12px;margin-top:8px;">{{ $message }}</div>
  @enderror
</div>

{{-- ── 2. Coach Subscription ── --}}
<div style="margin-bottom:48px;">
  <h2 style="font-size:22px;font-weight:700;margin-bottom:20px;">Coach Subscription</h2>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
    @php
      $durations = [
        ['type'=>'Monthly',   'price'=>'₱300',   'sub'=>'₱300/Monthly'],
        ['type'=>'Quarterly', 'price'=>'₱1,200', 'sub'=>'₱1,200/Quarterly'],
        ['type'=>'Annually',  'price'=>'₱3,600', 'sub'=>'₱3,600/Annually'],
      ];
    @endphp
    @foreach($durations as $d)
      @php $isDur = old('membership_type', $member?->membership_type) == $d['type']; @endphp
      <label style="cursor:pointer;">
        <input type="radio" name="membership_type" value="{{ $d['type'] }}"
               style="display:none;" class="duration-radio"
               {{ $isDur ? 'checked' : '' }}/>
        <div class="duration-card {{ $isDur ? 'plan-selected' : '' }}"
             style="background:var(--surface);border:1.5px solid {{ $isDur ? 'var(--accent)' : 'var(--border)' }};
                    border-radius:14px;padding:28px 24px;transition:all 0.2s;
                    {{ $isDur ? 'background:rgba(232,255,42,0.04);' : '' }}">
          <div style="font-size:16px;font-weight:600;margin-bottom:10px;">{{ $d['type'] }}</div>
          <div style="font-size:36px;font-weight:800;margin-bottom:6px;">{{ $d['price'] }}</div>
          <div style="font-size:13px;color:var(--muted);margin-bottom:12px;">{{ $d['sub'] }}</div>
          <div class="selected-indicator"
               style="font-size:13px;font-weight:600;color:var(--accent);
                      display:{{ $isDur ? 'flex' : 'none' }};align-items:center;gap:6px;">
            ✓ Selected
          </div>
        </div>
      </label>
    @endforeach
  </div>
  @error('membership_type')
    <div style="color:var(--danger);font-size:12px;margin-top:8px;">{{ $message }}</div>
  @enderror
</div>

{{-- ── 3. SELECT INSTRUCTOR ── --}}
<div style="margin-bottom:48px;">
  <h2 style="font-size:22px;font-weight:700;margin-bottom:20px;">Select Instructor</h2>
  @if($instructors->isEmpty())
    <div style="color:var(--muted);padding:24px;background:var(--surface);border-radius:14px;
                border:1px solid var(--border);text-align:center;">
      No instructors available yet.
    </div>
  @else
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">

      {{-- No Instructor option --}}
      <label style="cursor:pointer;">
        <input type="radio" name="instructor_id" value=""
               style="display:none;" class="instructor-radio"
               {{ !old('instructor_id', $member?->instructor_id) ? 'checked' : '' }}/>
        <div class="instructor-card"
             style="background:var(--surface);border:1.5px solid var(--border);
                    border-radius:14px;padding:24px;transition:all 0.2s;">
          <div style="width:52px;height:52px;border-radius:50%;background:#2a2a2a;
                      display:flex;align-items:center;justify-content:center;
                      font-size:20px;margin-bottom:14px;">🚫</div>
          <div style="font-size:16px;font-weight:700;margin-bottom:4px;">No Instructor</div>
          <div style="font-size:13px;color:var(--muted);">Train independently</div>
        </div>
      </label>

      @foreach($instructors as $inst)
        @php $isInst = old('instructor_id', $member?->instructor_id) == $inst->id; @endphp
        <label style="cursor:pointer;">
          <input type="radio" name="instructor_id" value="{{ $inst->id }}"
                 style="display:none;" class="instructor-radio"
                 {{ $isInst ? 'checked' : '' }}/>
          <div class="instructor-card {{ $isInst ? 'plan-selected' : '' }}"
               style="background:var(--surface);border:1.5px solid {{ $isInst ? 'var(--accent)' : 'var(--border)' }};
                      border-radius:14px;padding:24px;transition:all 0.2s;
                      {{ $isInst ? 'background:rgba(232,255,42,0.04);' : '' }}">
            @if($inst->photo)
              <img src="{{ asset('storage/'.$inst->photo) }}"
                   style="width:52px;height:52px;border-radius:50%;object-fit:cover;margin-bottom:14px;"/>
            @else
              <div style="width:52px;height:52px;border-radius:50%;
                          background:rgba(100,120,50,0.4);
                          display:flex;align-items:center;justify-content:center;
                          font-size:20px;font-weight:700;color:#fff;
                          margin-bottom:14px;letter-spacing:0;">
                {{ strtoupper(substr($inst->name,0,1)) }}
              </div>
            @endif
            <div style="font-size:16px;font-weight:700;margin-bottom:4px;">{{ $inst->name }}</div>
            @if($inst->specialization)
              <div style="font-size:13px;color:var(--muted);margin-bottom:4px;">{{ $inst->specialization }}</div>
            @endif
            @if($inst->experience_years)
              <div style="font-size:13px;color:var(--muted);">{{ $inst->experience_years }} years</div>
            @endif
          </div>
        </label>
      @endforeach
    </div>
  @endif
</div>

{{-- ── 4. SUMMARY ── --}}
<div style="background:var(--surface);border:1.5px solid var(--border);border-radius:14px;padding:32px;margin-bottom:24px;">
  <h2 style="font-size:22px;font-weight:700;margin-bottom:24px;">Summary</h2>

  <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border);">
    <span style="color:var(--muted);font-size:15px;">Plan:</span>
    <span id="summary-plan" style="font-size:15px;font-weight:600;">
      {{ $member?->fitness_plan ?? 'Not selected' }}
    </span>
  </div>
  <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border);">
    <span style="color:var(--muted);font-size:15px;">Duration:</span>
    <span id="summary-duration" style="font-size:15px;font-weight:600;">
      {{ $member?->membership_type ?? 'Not selected' }}
    </span>
  </div>
  <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border);">
    <span style="color:var(--muted);font-size:15px;">Instructor:</span>
    <span id="summary-instructor" style="font-size:15px;font-weight:600;">Not selected</span>
  </div>
  <div style="display:flex;justify-content:space-between;padding:16px 0;margin-top:4px;">
    <span style="font-size:16px;font-weight:700;">Total:</span>
    <span id="summary-total" style="font-size:24px;font-weight:800;color:var(--accent);">
      {{ $member ? '₱'.number_format($member->fee, 0) : '—' }}
    </span>
  </div>

  <button type="submit" id="submit-btn"
          style="width:100%;padding:16px;border-radius:10px;border:none;cursor:pointer;
                 font-size:16px;font-weight:700;font-family:'DM Sans',sans-serif;
                 background:#2a2a2a;color:var(--muted);transition:all 0.2s;margin-top:8px;"
          disabled>
    Complete Subscription →
  </button>
</div>

</form>

<style>
.plan-card:hover, .duration-card:hover, .instructor-card:hover {
  border-color: rgba(232,255,42,0.4) !important;
  background: rgba(232,255,42,0.02) !important;
}
.plan-selected {
  border-color: var(--accent) !important;
  background: rgba(232,255,42,0.04) !important;
}
.plan-selected .plan-icon svg,
.plan-selected .plan-icon {
  color: var(--accent) !important;
}
</style>

<script>
const fees = { Monthly: 800, Quarterly: 2100, Annually: 7500 };
let selectedPlan = '{{ old("fitness_plan", $member?->fitness_plan) }}';
let selectedDuration = '{{ old("membership_type", $member?->membership_type) }}';
let selectedInstructor = 'Not selected';

function updateSummary() {
  document.getElementById('summary-plan').textContent = selectedPlan || 'Not selected';
  document.getElementById('summary-duration').textContent = selectedDuration || 'Not selected';
  document.getElementById('summary-instructor').textContent = selectedInstructor;

  const fee = fees[selectedDuration];
  document.getElementById('summary-total').textContent = fee ? '₱' + fee.toLocaleString() : '—';

  const ready = selectedPlan && selectedDuration;
  const btn = document.getElementById('submit-btn');
  btn.disabled = !ready;
  btn.style.background = ready ? 'var(--accent)' : '#2a2a2a';
  btn.style.color = ready ? '#111' : 'var(--muted)';
  btn.style.cursor = ready ? 'pointer' : 'not-allowed';
}

// Plan radios
document.querySelectorAll('.plan-radio').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.plan-card').forEach(c => {
      c.classList.remove('plan-selected');
      c.style.borderColor = 'var(--border)';
      c.style.background = 'var(--surface)';
      c.querySelector('.selected-indicator').style.display = 'none';
    });
    if (r.checked) {
      const card = r.closest('label').querySelector('.plan-card');
      card.classList.add('plan-selected');
      card.style.borderColor = 'var(--accent)';
      card.style.background = 'rgba(232,255,42,0.04)';
      card.querySelector('.selected-indicator').style.display = 'flex';
      selectedPlan = r.value;
      updateSummary();
    }
  });
});

// Duration radios
document.querySelectorAll('.duration-radio').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.duration-card').forEach(c => {
      c.classList.remove('plan-selected');
      c.style.borderColor = 'var(--border)';
      c.style.background = 'var(--surface)';
      c.querySelector('.selected-indicator').style.display = 'none';
    });
    if (r.checked) {
      const card = r.closest('label').querySelector('.duration-card');
      card.classList.add('plan-selected');
      card.style.borderColor = 'var(--accent)';
      card.style.background = 'rgba(232,255,42,0.04)';
      card.querySelector('.selected-indicator').style.display = 'flex';
      selectedDuration = r.value;
      updateSummary();
    }
  });
});

// Instructor radios
document.querySelectorAll('.instructor-radio').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.instructor-card').forEach(c => {
      c.classList.remove('plan-selected');
      c.style.borderColor = 'var(--border)';
      c.style.background = 'var(--surface)';
    });
    if (r.checked) {
      const card = r.closest('label').querySelector('.instructor-card');
      card.classList.add('plan-selected');
      card.style.borderColor = 'var(--accent)';
      card.style.background = 'rgba(232,255,42,0.04)';
      const nameEl = card.querySelector('div[style*="font-weight:700"]');
      selectedInstructor = r.value === '' ? 'Not selected' : (nameEl ? nameEl.textContent.trim() : 'Selected');
      updateSummary();
    }
  });
});

// Init
updateSummary();
</script>
@endsection