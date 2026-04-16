@extends('layouts.member')
@section('title', 'Choose Your Plan – IRONFORGE')
@section('active', 'plans')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <h1>Choose Your <span class="text-accent">Plan</span></h1>
  <p>Select a fitness program and subscription that matches your goals</p>
</div>

@if(session('success'))
  <div class="alert alert-success" style="background: rgba(40, 167, 69, 0.1); border: 1px solid #28a745; color: #28a745; padding: 15px; border-radius: 10px; margin-bottom: 25px;">
    ✓ {{ session('success') }}
  </div>
@endif

<form action="{{ route('member.subscribe') }}" method="POST" id="plan-form">
@csrf

{{-- ── 1. FITNESS PLAN ── --}}
<div class="section-group">
  <h2 class="section-title">1. Fitness Plan</h2>
  <div class="grid-3">
    @php
      $plans = [
        ['name'=>'Calisthenics',       'desc'=>'Build strength using bodyweight exercises',           'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><line x1="24" y1="11" x2="24" y2="24"/><line x1="24" y1="24" x2="14" y2="34"/><line x1="24" y1="24" x2="34" y2="34"/><line x1="24" y1="18" x2="14" y2="22"/><line x1="24" y1="18" x2="34" y2="22"/></svg>'],
        ['name'=>'Bodybuilding',        'desc'=>'Muscle hypertrophy and aesthetic development',        'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 28 Q10 24 14 20 Q18 16 22 20 L26 28 Q30 32 26 36 Q22 40 18 36 Z"/><path d="M26 28 Q30 24 34 20"/><path d="M6 22 L14 20"/><path d="M34 20 L42 18"/><path d="M6 26 L14 28"/><path d="M34 28 L42 26"/></svg>'],
        ['name'=>'Plyometrics',         'desc'=>'Explosive power and athletic performance',            'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><path d="M24 11 L18 22 L24 20 L20 34"/><path d="M24 20 L30 18 L26 30"/><path d="M16 38 L32 38"/></svg>'],
        ['name'=>'Powerlifting',        'desc'=>'Maximum strength in squat, bench, deadlift',         'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="18" width="6" height="12" rx="2"/><rect x="38" y="18" width="6" height="12" rx="2"/><rect x="8" y="20" width="6" height="8" rx="1"/><rect x="34" y="20" width="6" height="8" rx="1"/><line x1="14" y1="24" x2="34" y2="24"/><circle cx="24" cy="14" r="3"/></svg>'],
        ['name'=>'Endurance',           'desc'=>'Cardiovascular fitness and stamina',                  'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><path d="M20 12 Q16 18 18 24 L22 22 L20 34 L26 28 L28 34 L30 22 L34 24 Q36 18 32 12"/></svg>'],
        ['name'=>'Functional Training', 'desc'=>'Movement patterns for daily life',                    'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="24" r="14"/><path d="M24 10 L24 14"/><path d="M24 34 L24 38"/><path d="M10 24 L14 24"/><path d="M34 24 L38 24"/><circle cx="24" cy="24" r="4"/></svg>'],
      ];
    @endphp

    @foreach($plans as $plan)
      @php $isCurrent = old('fitness_plan', $member?->fitness_plan) === $plan['name']; @endphp
      <label class="selectable-label">
        <input type="radio" name="fitness_plan" value="{{ $plan['name'] }}"
               class="plan-radio" style="display:none;" {{ $isCurrent ? 'checked' : '' }}/>
        <div class="card plan-card {{ $isCurrent ? 'selected' : '' }}">
          @if($isCurrent) <div class="badge">Current Plan</div> @endif
          <div class="icon-box">{!! $plan['svg'] !!}</div>
          <div class="card-title">{{ $plan['name'] }}</div>
          <div class="card-desc">{{ $plan['desc'] }}</div>
          <div class="selected-indicator" style="{{ $isCurrent ? 'display:flex' : 'display:none' }}">✓ Selected</div>
        </div>
      </label>
    @endforeach
  </div>
</div>

{{-- ── 2. GYM SUBSCRIPTION ── --}}
<div class="section-group">
  <h2 class="section-title">2. Gym Subscription</h2>
  <div class="grid-3">
    @php
      $gymDurations = [
        ['type'=>'Monthly',   'price'=>'800',   'label'=>'₱800/Month'],
        ['type'=>'Quarterly', 'price'=>'3200',  'label'=>'₱3,200/Quarter'],
        ['type'=>'Annually',  'price'=>'9600',  'label'=>'₱9,600/Year'],
      ];
    @endphp
    @foreach($gymDurations as $d)
      @php $isSelected = old('membership_type', $member?->membership_type) == $d['type']; @endphp
      <label class="selectable-label">
        <input type="radio" name="membership_type" value="{{ $d['type'] }}" data-price="{{ $d['price'] }}"
               class="gym-radio" style="display:none;" {{ $isSelected ? 'checked' : '' }}/>
        <div class="card gym-card {{ $isSelected ? 'selected' : '' }}">
          <div class="card-subtitle">{{ $d['type'] }}</div>
          <div class="card-price">₱{{ number_format($d['price']) }}</div>
          <div class="card-desc">{{ $d['label'] }}</div>
          <div class="selected-indicator" style="{{ $isSelected ? 'display:flex' : 'display:none' }}">✓ Selected</div>
        </div>
      </label>
    @endforeach
  </div>
</div>

{{-- ── 3. SELECT INSTRUCTOR & COACH PLAN ── --}}
<div class="section-group">
  <h2 class="section-title">3. Personal Coaching (Optional)</h2>
  
  <div class="grid-3" style="margin-bottom: 24px;">
    {{-- No Instructor option --}}
    @php $noInst = !old('instructor_id', $member?->instructor_id); @endphp
    <label class="selectable-label">
      <input type="radio" name="instructor_id" value="" class="instructor-radio"
             style="display:none;" {{ $noInst ? 'checked' : '' }}/>
      <div class="card instructor-card {{ $noInst ? 'selected' : '' }}">
        <div class="avatar-placeholder">🚫</div>
        <div class="card-title">No Instructor</div>
        <div class="card-desc">Train independently</div>
        <div class="selected-indicator" style="{{ $noInst ? 'display:flex' : 'display:none' }}">✓ Selected</div>
      </div>
    </label>

    @foreach($instructors as $inst)
      @php $isInst = old('instructor_id', $member?->instructor_id) == $inst->id; @endphp
      <label class="selectable-label">
        <input type="radio" name="instructor_id" value="{{ $inst->id }}" class="instructor-radio"
               style="display:none;" {{ $isInst ? 'checked' : '' }}/>
        <div class="card instructor-card {{ $isInst ? 'selected' : '' }}">
          @if($inst->photo)
            <img src="{{ asset('storage/'.$inst->photo) }}" class="avatar-img"/>
          @else
            <div class="avatar-placeholder">{{ strtoupper(substr($inst->name,0,1)) }}</div>
          @endif
          <div class="card-title">{{ $inst->name }}</div>
          <div class="card-desc">{{ $inst->specialization ?? 'Professional Coach' }}</div>
          <div class="selected-indicator" style="{{ $isInst ? 'display:flex' : 'display:none' }}">✓ Selected</div>
        </div>
      </label>
    @endforeach
  </div>

  <div id="coach-duration-container">
    <h3 class="card-subtitle" style="margin-bottom:15px;">Coach Subscription Duration</h3>
    <div class="grid-3">
      @php
        $coachDurations = [
          ['type'=>'Monthly',   'price'=>'300',   'label'=>'₱300/Month'],
          ['type'=>'Quarterly', 'price'=>'1200',  'label'=>'₱1,200/Quarter'],
          ['type'=>'Annually',  'price'=>'3600',  'label'=>'₱3,600/Year'],
        ];
      @endphp
      @foreach($coachDurations as $d)
        @php $isCSelected = old('coach_membership_type') == $d['type']; @endphp
        <label class="selectable-label">
          <input type="radio" name="coach_membership_type" value="{{ $d['type'] }}" data-price="{{ $d['price'] }}"
                 class="coach-radio" style="display:none;" {{ $isCSelected ? 'checked' : '' }}/>
          <div class="card coach-card {{ $isCSelected ? 'selected' : '' }}">
            <div class="card-subtitle">{{ $d['type'] }} Coach</div>
            <div class="card-price">₱{{ number_format($d['price']) }}</div>
            <div class="card-desc">{{ $d['label'] }}</div>
            <div class="selected-indicator" style="{{ $isCSelected ? 'display:flex' : 'display:none' }}">✓ Selected</div>
          </div>
        </label>
      @endforeach
    </div>
  </div>
</div>

{{-- ── 4. SUMMARY ── --}}
<div class="summary-container">
  <h2 class="section-title">Summary</h2>

  <div class="summary-row">
    <span>Fitness Plan:</span>
    <span id="summary-plan" class="fw-600">--</span>
  </div>
  <div class="summary-row">
    <span>Gym Membership:</span>
    <span id="summary-duration" class="fw-600">--</span>
  </div>
  <div class="summary-row">
    <span>Instructor:</span>
    <span id="summary-instructor" class="fw-600">None</span>
  </div>
  <div class="summary-row">
    <span>Coach Plan:</span>
    <span id="summary-coach-duration" class="fw-600">None</span>
  </div>
  
  <div class="summary-total">
    <span>Total Fee:</span>
    <span id="summary-total-value">₱0</span>
  </div>

  <button type="submit" id="submit-btn" class="btn-submit" disabled>
    Complete Subscription →
  </button>
</div>

</form>

<style>
:root {
    --accent: #e8ff2a;
    --surface: #1a1a1a;
    --border: #333;
    --muted: #888;
    --danger: #ff4d4d;
}

.page-header { text-align: center; margin-bottom: 48px; }
.page-header h1 { font-size: 48px; font-weight: 800; margin-bottom: 12px; color: #fff; }
.page-header p { color: var(--muted); font-size: 16px; }
.text-accent { color: var(--accent); }

.section-group { margin-bottom: 48px; }
.section-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; color: #fff; }

.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }

.card {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: 14px;
    padding: 24px;
    transition: all 0.2s;
    height: 100%;
    position: relative;
    cursor: pointer;
}

.card:hover { border-color: rgba(232, 255, 42, 0.4); }
.card.selected {
    border-color: var(--accent) !important;
    background: rgba(232, 255, 42, 0.04) !important;
}

.badge {
    position: absolute; top: 14px; right: 14px; background: var(--accent);
    color: #111; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px;
}

.icon-box { width: 44px; height: 44px; margin-bottom: 16px; color: rgba(255, 255, 255, 0.3); }
.card.selected .icon-box { color: var(--accent); }

.card-title { font-size: 18px; font-weight: 700; margin-bottom: 8px; color: #fff;}
.card-subtitle { font-size: 16px; font-weight: 600; margin-bottom: 10px; color: #fff;}
.card-price { font-size: 32px; font-weight: 800; margin-bottom: 6px; color: #fff;}
.card-desc { font-size: 13px; color: var(--muted); line-height: 1.5; }

.selected-indicator { margin-top: 16px; font-size: 13px; font-weight: 600; color: var(--accent); display: none; align-items: center; gap: 6px; }

.avatar-placeholder {
    width: 52px; height: 52px; border-radius: 50%; background: #2a2a2a;
    display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 14px;
}
.avatar-img { width: 52px; height: 52px; border-radius: 50%; object-fit: cover; margin-bottom: 14px; }

.summary-container { background: var(--surface); border: 1.5px solid var(--border); border-radius: 14px; padding: 32px; margin-bottom: 24px; }
.summary-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border); color: var(--muted); }
.summary-total { display: flex; justify-content: space-between; padding: 16px 0; font-size: 24px; font-weight: 800; color: var(--accent); }
.fw-600 { font-weight: 600; color: #fff; }

.btn-submit {
    width: 100%; padding: 16px; border-radius: 10px; border: none;
    font-size: 16px; font-weight: 700; background: #2a2a2a; color: var(--muted);
    transition: all 0.2s; cursor: not-allowed;
}
.btn-submit.active { background: var(--accent); color: #111; cursor: pointer; }

@media (max-width: 768px) { .grid-3 { grid-template-columns: 1fr; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const coachContainer = document.getElementById('coach-duration-container');

    function updateSummary() {
        const plan = document.querySelector('.plan-radio:checked');
        const gym = document.querySelector('.gym-radio:checked');
        const instructor = document.querySelector('.instructor-radio:checked');
        const coach = document.querySelector('.coach-radio:checked');

        const hasInstructor = (instructor && instructor.value !== "");
        
        // Visual indication that Coach plan is locked/unlocked
        coachContainer.style.opacity = hasInstructor ? "1" : "0.4";
        coachContainer.style.pointerEvents = hasInstructor ? "auto" : "none";

        // Summary Text Updates
        document.getElementById('summary-plan').textContent = plan ? plan.value : '--';
        document.getElementById('summary-duration').textContent = gym ? gym.value : '--';
        
        if (hasInstructor) {
            const name = instructor.closest('label').querySelector('.card-title').textContent;
            document.getElementById('summary-instructor').textContent = name;
            document.getElementById('summary-coach-duration').textContent = coach ? coach.value : 'Select Duration';
        } else {
            document.getElementById('summary-instructor').textContent = 'None';
            document.getElementById('summary-coach-duration').textContent = 'None';
        }

        // Calculation
        let gymFee = gym ? parseInt(gym.dataset.price) : 0;
        let coachFee = (hasInstructor && coach) ? parseInt(coach.dataset.price) : 0;
        
        const total = gymFee + coachFee;
        document.getElementById('summary-total-value').textContent = '₱' + total.toLocaleString();

        // Submit Button State
        const gymReady = (plan && gym);
        const coachReady = !hasInstructor || (hasInstructor && coach);

        const btn = document.getElementById('submit-btn');
        if (gymReady && coachReady) {
            btn.disabled = false;
            btn.classList.add('active');
        } else {
            btn.disabled = true;
            btn.classList.remove('active');
        }
    }

    function handleRadioChange(selector, cardClass) {
        document.querySelectorAll(selector).forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.' + cardClass).forEach(card => {
                    card.classList.remove('selected');
                    const indicator = card.querySelector('.selected-indicator');
                    if (indicator) indicator.style.display = 'none';
                });
                
                if (radio.checked) {
                    const card = radio.closest('label').querySelector('.card');
                    card.classList.add('selected');
                    const indicator = card.querySelector('.selected-indicator');
                    if (indicator) indicator.style.display = 'flex';
                }
                updateSummary();
            });
        });
    }

    handleRadioChange('.plan-radio', 'plan-card');
    handleRadioChange('.gym-radio', 'gym-card');
    handleRadioChange('.instructor-radio', 'instructor-card');
    handleRadioChange('.coach-radio', 'coach-card');

    updateSummary();
});
</script>
@endsection