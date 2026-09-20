<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
    <title>Choose Your Plan – IRONFORGE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        /* ── reset & base ── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #0a0a0a;
            color: #f0f0f0;
            padding: 20px 16px 40px;
        }
        .member-wrapper {
            max-width: 1280px;
            margin: 0 auto;
        }
        :root {
            --accent: #dc2626;
            --accent-hover: #b91c1c;
            --accent-glow: rgba(220, 38, 38, 0.15);
            --surface: #111111;
            --surface2: #1a1a1a;
            --border: #2a2a2a;
            --border-hover: #3a3a3a;
            --muted: #777;
            --radius: 14px;
        }
        .page-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .page-header h1 {
            font-size: 2.4rem;
            font-weight: 800;
            margin-bottom: 8px;
            color: #fff;
            letter-spacing: -0.02em;
        }
        .page-header p {
            color: var(--muted);
            font-size: 1rem;
        }
        .text-accent {
            color: var(--accent);
        }

        /* ── alerts ── */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            flex-wrap: wrap;
            gap: 4px 8px;
        }
        .alert-success {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid #dc2626;
            color: #dc2626;
        }
        .alert-error {
            background: rgba(220, 38, 38, 0.15);
            border: 1px solid #dc2626;
            color: #dc2626;
        }

        /* ── sections ── */
        .section-group {
            margin-bottom: 44px;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: #fff;
            letter-spacing: -0.01em;
        }
        .section-title small {
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--muted);
            margin-left: 8px;
        }

        /* ── grids ── */
        .grid-plans {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        /* ── cards ── */
        .selectable-label {
            display: block;
            height: 100%;
            cursor: pointer;
        }
        .card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 16px;
            transition: border-color 0.2s, background 0.2s, transform 0.1s;
            height: 100%;
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }
        .card:hover {
            border-color: rgba(220, 38, 38, 0.4);
        }
        .card.selected {
            border-color: var(--accent) !important;
            background: rgba(220, 38, 38, 0.06) !important;
            box-shadow: 0 0 30px rgba(220, 38, 38, 0.05);
        }
        .badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--accent);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 10px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }
        .icon-box {
            width: 44px;
            height: 44px;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.2);
            flex-shrink: 0;
        }
        .card.selected .icon-box {
            color: var(--accent);
        }
        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #fff;
        }
        .card-subtitle {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #fff;
        }
        .card-price {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 4px;
            color: #fff;
        }
        .card.selected .card-price {
            color: var(--accent);
        }
        .card-desc {
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.5;
        }
        .card-desc-sm {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 2px;
        }
        .selected-indicator {
            margin-top: 14px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--accent);
            display: none;
            align-items: center;
            gap: 4px;
        }
        .avatar-placeholder,
        .avatar-img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            background: #1a1a1a;
            margin-bottom: 12px;
            flex-shrink: 0;
            object-fit: cover;
            color: #fff;
        }
        .avatar-img {
            background: transparent;
        }

        /* ── summary ── */
        .summary-container {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            padding: 24px 20px;
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            color: var(--muted);
            font-size: 0.95rem;
        }
        .summary-row .fw-600 {
            color: #fff;
            font-weight: 600;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 18px 0 10px;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--accent);
        }
        .btn-submit {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: none;
            font-size: 1rem;
            font-weight: 700;
            background: #1a1a1a;
            color: var(--muted);
            transition: all 0.2s;
            cursor: not-allowed;
        }
        .btn-submit.active {
            background: var(--accent);
            color: #fff;
            cursor: pointer;
        }
        .btn-submit.active:hover {
            background: var(--accent-hover);
            transform: scale(1.01);
        }

        /* ── responsive breakpoints ── */
        @media (max-width: 1024px) {
            .grid-plans {
                grid-template-columns: repeat(3, 1fr);
            }
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
            .grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            body {
                padding: 16px 12px;
            }
            .page-header h1 {
                font-size: 2rem;
            }
            .grid-plans {
                grid-template-columns: repeat(2, 1fr);
            }
            .grid-4 {
                grid-template-columns: 1fr 1fr;
            }
            .grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 500px) {
            .grid-plans {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .grid-4 {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .grid-3 {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .card {
                padding: 16px 12px;
            }
            .card-price {
                font-size: 1.6rem;
            }
            .summary-total {
                font-size: 1.4rem;
            }
            .page-header h1 {
                font-size: 1.8rem;
            }
            .section-title {
                font-size: 1.3rem;
            }
        }
        @media (max-width: 400px) {
            .grid-plans {
                grid-template-columns: 1fr;
            }
            .grid-4 {
                grid-template-columns: 1fr;
            }
            .grid-3 {
                grid-template-columns: 1fr;
            }
            .card {
                align-items: center;
                text-align: center;
            }
            .icon-box {
                margin-left: auto;
                margin-right: auto;
            }
            .avatar-placeholder,
            .avatar-img {
                margin-left: auto;
                margin-right: auto;
            }
        }

        /* small helper */
        .mt-2 {
            margin-top: 8px;
        }
        .fw-600 {
            font-weight: 600;
        }
        .text-muted {
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="member-wrapper">
        <!-- page header -->
        <div class="page-header">
            <h1>Choose Your <span class="text-accent">Plan</span></h1>
            <p>Select a fitness program and subscription that matches your goals</p>
        </div>

        <!-- alerts (demo) -->
        <div class="alert alert-success">✓ Your current plan is active</div>
        <div class="alert alert-error" style="display:none;">✕ Please fix errors</div>

        <!--
            ─── FIX: Form action updated ───
            The error showed POST to /my/select-plan is not allowed.
            Make sure your route in routes/web.php accepts POST.
            Example route:
            Route::post('/my/select-plan', [PlanController::class, 'store'])->name('member.subscribe');
        -->
        <form action="{{ route('member.subscribe') }}" method="POST" id="plan-form">
            @csrf

            <!-- ─── 1. FITNESS PLAN ─── -->
            <div class="section-group">
                <h2 class="section-title">1. Fitness Plan</h2>
                <div class="grid-plans">
                    @php
                        $plans = [
                            ['name'=>'Calisthenics', 'desc'=>'Build strength using bodyweight exercises', 'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><line x1="24" y1="11" x2="24" y2="24"/><line x1="24" y1="24" x2="14" y2="34"/><line x1="24" y1="24" x2="34" y2="34"/><line x1="24" y1="18" x2="14" y2="22"/><line x1="24" y1="18" x2="34" y2="22"/></svg>'],
                            ['name'=>'Bodybuilding', 'desc'=>'Muscle hypertrophy and aesthetic development', 'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 28 Q10 24 14 20 Q18 16 22 20 L26 28 Q30 32 26 36 Q22 40 18 36 Z"/><path d="M26 28 Q30 24 34 20"/><path d="M6 22 L14 20"/><path d="M34 20 L42 18"/><path d="M6 26 L14 28"/><path d="M34 28 L42 26"/></svg>'],
                            ['name'=>'Plyometrics', 'desc'=>'Explosive power and athletic performance', 'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><path d="M24 11 L18 22 L24 20 L20 34"/><path d="M24 20 L30 18 L26 30"/><path d="M16 38 L32 38"/></svg>'],
                            ['name'=>'Powerlifting', 'desc'=>'Maximum strength in squat, bench, deadlift', 'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="18" width="6" height="12" rx="2"/><rect x="38" y="18" width="6" height="12" rx="2"/><rect x="8" y="20" width="6" height="8" rx="1"/><rect x="34" y="20" width="6" height="8" rx="1"/><line x1="14" y1="24" x2="34" y2="24"/><circle cx="24" cy="14" r="3"/></svg>'],
                            ['name'=>'Endurance', 'desc'=>'Cardiovascular fitness and stamina', 'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="8" r="3"/><path d="M20 12 Q16 18 18 24 L22 22 L20 34 L26 28 L28 34 L30 22 L34 24 Q36 18 32 12"/></svg>'],
                            ['name'=>'Functional Training', 'desc'=>'Movement patterns for everyday life', 'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="24" cy="24" r="14"/><path d="M24 10 L24 14"/><path d="M24 34 L24 38"/><path d="M10 24 L14 24"/><path d="M34 24 L38 24"/><circle cx="24" cy="24" r="4"/></svg>'],
                            ['name'=>'Hybrid Training', 'desc'=>'Combined strength and cardio training', 'svg'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="24,6 28,18 40,18 30,26 34,38 24,30 14,38 18,26 8,18 20,18"/></svg>'],
                        ];
                        $selectedPlan = old('fitness_plan') ?? $member?->fitness_plan ?? '';
                    @endphp

                    @foreach($plans as $plan)
                        @php $isCurrent = $selectedPlan === $plan['name']; @endphp
                        <label class="selectable-label">
                            <input type="radio" name="fitness_plan" value="{{ $plan['name'] }}"
                                   class="plan-radio" style="display:none;" {{ $isCurrent ? 'checked' : '' }}/>
                            <div class="card plan-card {{ $isCurrent ? 'selected' : '' }}">
                                @if($member && $member->fitness_plan === $plan['name'] && !old('fitness_plan'))
                                    <div class="badge">Current Plan</div>
                                @endif
                                <div class="icon-box">{!! $plan['svg'] !!}</div>
                                <div class="card-title">{{ $plan['name'] }}</div>
                                <div class="card-desc">{{ $plan['desc'] }}</div>
                                <div class="selected-indicator" style="{{ $isCurrent ? 'display:flex' : 'display:none' }}">✓ Selected</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- ─── 2. GYM SUBSCRIPTION ─── -->
            <div class="section-group">
                <h2 class="section-title">2. Gym Subscription</h2>
                <div class="grid-4">
                    @php
                        $gymDurations = [
                            ['type'=>'Monthly', 'price'=>'800', 'display'=>'₱800', 'label'=>'₱800 / Month', 'days'=>'30 days'],
                            ['type'=>'Quarterly', 'price'=>'2100', 'display'=>'₱2,100', 'label'=>'₱2,100 / Quarter', 'days'=>'90 days'],
                            ['type'=>'Semi-Annual', 'price'=>'4500', 'display'=>'₱4,500', 'label'=>'₱4,500 / 6 Months', 'days'=>'180 days'],
                            ['type'=>'Annually', 'price'=>'7500', 'display'=>'₱7,500', 'label'=>'₱7,500 / Year', 'days'=>'365 days'],
                        ];
                        $selectedGym = old('membership_type') ?? $member?->membership_type ?? '';
                    @endphp
                    @foreach($gymDurations as $d)
                        @php $isSelected = $selectedGym === $d['type']; @endphp
                        <label class="selectable-label">
                            <input type="radio" name="membership_type" value="{{ $d['type'] }}" data-price="{{ $d['price'] }}"
                                   class="gym-radio" style="display:none;" {{ $isSelected ? 'checked' : '' }}/>
                            <div class="card gym-card {{ $isSelected ? 'selected' : '' }}">
                                <div class="card-subtitle">{{ $d['type'] }}</div>
                                <div class="card-price">{{ $d['display'] }}</div>
                                <div class="card-desc">{{ $d['label'] }}</div>
                                <div style="font-size:12px;color:var(--muted);margin-top:4px;">{{ $d['days'] }}</div>
                                <div class="selected-indicator" style="{{ $isSelected ? 'display:flex' : 'display:none' }}">✓ Selected</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- ─── 3. PERSONAL COACHING ─── -->
            <div class="section-group">
                <h2 class="section-title">3. Personal Coaching <small>(Optional)</small></h2>

                @php
                    $selectedInst = old('instructor_id') ?? ($member?->instructor_id ?? '');
                    $noInstSelected = ($selectedInst === '' || $selectedInst === null);
                @endphp

                <div class="grid-3" style="margin-bottom: 20px;">
                    <!-- No Instructor -->
                    <label class="selectable-label">
                        <input type="radio" name="instructor_id" value="" class="instructor-radio"
                               style="display:none;" {{ $noInstSelected ? 'checked' : '' }}/>
                        <div class="card instructor-card {{ $noInstSelected ? 'selected' : '' }}">
                            <div class="avatar-placeholder">🚫</div>
                            <div class="card-title">No Instructor</div>
                            <div class="card-desc">Train independently</div>
                            <div class="selected-indicator" style="{{ $noInstSelected ? 'display:flex' : 'display:none' }}">✓ Selected</div>
                        </div>
                    </label>

                    @foreach($instructors ?? [] as $inst)
                        @php $isInst = (string)$selectedInst === (string)$inst->id; @endphp
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

                <!-- Coach duration container -->
                <div id="coach-duration-container"
                     style="{{ $noInstSelected ? 'opacity:0.4;pointer-events:none;' : 'opacity:1;pointer-events:auto;' }}">
                    <h3 style="font-size:1rem; font-weight:700; color:#fff; margin-bottom: 14px;">
                        Coach Subscription Duration
                    </h3>
                    @php
                        $coachDurations = [
                            ['type'=>'Monthly', 'price'=>'300', 'display'=>'₱300', 'label'=>'₱300 / Month', 'days'=>'30 days'],
                            ['type'=>'Quarterly', 'price'=>'1200', 'display'=>'₱1,200', 'label'=>'₱1,200 / Quarter', 'days'=>'90 days'],
                            ['type'=>'Semi-Annual', 'price'=>'1800', 'display'=>'₱1,800', 'label'=>'₱1,800 / 6 Months', 'days'=>'180 days'],
                            ['type'=>'Annually', 'price'=>'3600', 'display'=>'₱3,600', 'label'=>'₱3,600 / Year', 'days'=>'365 days'],
                        ];
                        $selectedCoach = old('coach_membership_type') ?? $member?->coach_membership_type ?? '';
                    @endphp
                    <div class="grid-4">
                        @foreach($coachDurations as $d)
                            @php $isCSelected = $selectedCoach === $d['type']; @endphp
                            <label class="selectable-label">
                                <input type="radio" name="coach_membership_type" value="{{ $d['type'] }}" data-price="{{ $d['price'] }}"
                                       class="coach-radio" style="display:none;" {{ $isCSelected ? 'checked' : '' }}/>
                                <div class="card coach-card {{ $isCSelected ? 'selected' : '' }}">
                                    <div class="card-subtitle">{{ $d['type'] }} Coach</div>
                                    <div class="card-price">{{ $d['display'] }}</div>
                                    <div class="card-desc">{{ $d['label'] }}</div>
                                    <div style="font-size:12px;color:var(--muted);margin-top:4px;">{{ $d['days'] }}</div>
                                    <div class="selected-indicator" style="{{ $isCSelected ? 'display:flex' : 'display:none' }}">✓ Selected</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ─── 4. SUMMARY ─── -->
            <div class="summary-container">
                <h2 class="section-title" style="margin-bottom: 8px;">Summary</h2>
                <div class="summary-row">
                    <span>Fitness Plan</span>
                    <span id="summary-plan" class="fw-600">{{ $selectedPlan ?? '--' }}</span>
                </div>
                <div class="summary-row">
                    <span>Gym Membership</span>
                    <span id="summary-duration" class="fw-600">{{ $selectedGym ?? '--' }}</span>
                </div>
                <div class="summary-row">
                    <span>Instructor</span>
                    <span id="summary-instructor" class="fw-600">
                        {{ $noInstSelected ? 'None' : ($selectedInst ? ($instructors->firstWhere('id', $selectedInst)->name ?? '--') : 'None') }}
                    </span>
                </div>
                <div class="summary-row">
                    <span>Coach Plan</span>
                    <span id="summary-coach-duration" class="fw-600">{{ $selectedCoach ?? 'None' }}</span>
                </div>
                <div class="summary-total">
                    <span>Total Fee</span>
                    <span id="summary-total-value">₱0</span>
                </div>
                <button type="submit" id="submit-btn" class="btn-submit active">
                    Complete Subscription →
                </button>
            </div>
        </form>
    </div>

    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                const coachContainer = document.getElementById('coach-duration-container');

                function updateSummary() {
                    const planRadio = document.querySelector('.plan-radio:checked');
                    const gymRadio = document.querySelector('.gym-radio:checked');
                    const instructorRadio = document.querySelector('.instructor-radio:checked');
                    const coachRadio = document.querySelector('.coach-radio:checked');

                    const hasInstructor = instructorRadio && instructorRadio.value !== '';

                    // lock/unlock coach section
                    coachContainer.style.opacity = hasInstructor ? '1' : '0.4';
                    coachContainer.style.pointerEvents = hasInstructor ? 'auto' : 'none';

                    // clear coach selection if no instructor
                    if (!hasInstructor) {
                        document.querySelectorAll('.coach-radio').forEach(r => { r.checked = false; });
                        document.querySelectorAll('.coach-card').forEach(c => {
                            c.classList.remove('selected');
                            const ind = c.querySelector('.selected-indicator');
                            if (ind) ind.style.display = 'none';
                        });
                    }

                    // set summary texts
                    document.getElementById('summary-plan').textContent = planRadio ? planRadio.value : '--';
                    document.getElementById('summary-duration').textContent = gymRadio ? gymRadio.value : '--';

                    if (hasInstructor) {
                        const label = instructorRadio.closest('label');
                        const titleEl = label ? label.querySelector('.card-title') : null;
                        document.getElementById('summary-instructor').textContent = titleEl ? titleEl.textContent.trim() :
                        '--';
                        document.getElementById('summary-coach-duration').textContent = coachRadio ? coachRadio.value :
                            'Select Duration';
                    } else {
                        document.getElementById('summary-instructor').textContent = 'None';
                        document.getElementById('summary-coach-duration').textContent = 'None';
                    }

                    // totals
                    const gymFee = gymRadio ? parseInt(gymRadio.dataset.price, 10) : 0;
                    const coachFee = (hasInstructor && coachRadio) ? parseInt(coachRadio.dataset.price, 10) : 0;
                    document.getElementById('summary-total-value').textContent = '₱' + (gymFee + coachFee).toLocaleString();

                    // submit button
                    const ready = planRadio && gymRadio && (!hasInstructor || (hasInstructor && coachRadio));
                    const btn = document.getElementById('submit-btn');
                    btn.disabled = !ready;
                    btn.classList.toggle('active', !!ready);
                }

                function wireRadios(radioSel, cardClass) {
                    document.querySelectorAll(radioSel).forEach(function(radio) {
                        radio.addEventListener('change', function() {
                            document.querySelectorAll('.' + cardClass).forEach(function(card) {
                                card.classList.remove('selected');
                                const ind = card.querySelector('.selected-indicator');
                                if (ind) ind.style.display = 'none';
                            });
                            if (radio.checked) {
                                const card = radio.closest('label').querySelector('.card');
                                if (card) {
                                    card.classList.add('selected');
                                    const ind = card.querySelector('.selected-indicator');
                                    if (ind) ind.style.display = 'flex';
                                }
                            }
                            updateSummary();
                        });
                    });
                }

                wireRadios('.plan-radio', 'plan-card');
                wireRadios('.gym-radio', 'gym-card');
                wireRadios('.instructor-radio', 'instructor-card');
                wireRadios('.coach-radio', 'coach-card');

                // run once
                updateSummary();
            });
        })();
    </script>
</body>
</html>