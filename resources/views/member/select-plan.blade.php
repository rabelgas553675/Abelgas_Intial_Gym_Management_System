<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
    <title>Choose Your Plan – APEX</title>

    {{-- Apply the saved theme BEFORE first paint (prevents a dark flash in light mode).
         THEME_KEY must match the localStorage key used by layouts/member.blade.php
         so the dashboard toggle and this page stay in sync. --}}
    <script>
        window.APEX_THEME_KEY = 'theme';
        (function () {
            var t = 'dark';
            try {
                var v = localStorage.getItem(window.APEX_THEME_KEY);
                if (v && /light/i.test(v)) t = 'light';
            } catch (e) {}
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet" />
    <style>
        /* ── theme tokens (charcoal & gold) — keep in sync with layouts/member.blade.php ── */
        :root,
        [data-theme="dark"] {
            color-scheme: dark;
            --bg:#0d0d0d;
            --text:#f0f0f0;
            --surface:#151515;
            --surface2:#1c1c1c;
            --border:#2a2a2a;
            --border-hover:#3a3a3a;
            --muted:#888;
            --accent:#e0a93b;
            --accent-2:#f0c060;
            --accent-dark:#b8862a;
            --accent-hover:#b8862a;
            --accent-soft:rgba(224,169,59,0.12);
            --accent-text:#e0a93b;           /* gold used for small text */
            --selected-bg:rgba(224,169,59,0.07);
            --selected-shadow:0 0 30px rgba(224,169,59,0.08);
            --icon-idle:rgba(255,255,255,0.2);
            --danger:#f87171;
            --danger-soft:rgba(248,113,113,0.12);
            --toggle-shadow:0 4px 14px rgba(0,0,0,0.35);
            --radius:14px;
        }
        [data-theme="light"] {
            color-scheme: light;
            --bg:#f3f0ea;
            --text:#1c1c1c;
            --surface:#fdfcf9;
            --surface2:#f1ede5;
            --border:#e3ddd0;
            --border-hover:#d3ccbb;
            --muted:#6f6a60;
            --accent:#cf9a28;
            --accent-2:#f0c060;
            --accent-dark:#b8862a;
            --accent-hover:#b8862a;
            --accent-soft:rgba(207,154,40,0.14);
            --accent-text:#8f620c;           /* darker gold = readable on cream */
            --selected-bg:rgba(207,154,40,0.10);
            --selected-shadow:0 4px 18px rgba(207,154,40,0.18);
            --icon-idle:#b9b1a2;
            --danger:#dc2626;
            --danger-soft:rgba(220,38,38,0.08);
            --toggle-shadow:0 4px 14px rgba(0,0,0,0.12);
        }

        /* ── reset & base ── */
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background:var(--bg);
            color:var(--text);
            padding:20px 16px 40px;
            transition:background .25s, color .25s;
        }
        .member-wrapper { max-width:1280px; margin:0 auto; }

        /* ── back button ── */
        .top-bar { margin-bottom:20px; }
        .btn-back {
            display:inline-flex; align-items:center; gap:8px;
            padding:10px 16px; min-height:44px; border-radius:10px;
            background:var(--surface2); border:1px solid var(--border);
            color:var(--text); font-size:0.9rem; font-weight:600; text-decoration:none;
            transition:border-color .2s, color .2s, transform .1s;
        }
        .btn-back:hover { border-color:var(--accent); color:var(--accent-text); transform:translateX(-2px); }
        .btn-back svg { flex-shrink:0; }

        .page-header { text-align:center; margin-bottom:36px; }
        .page-header h1 { font-size:2.4rem; font-weight:800; margin-bottom:8px; color:var(--text); letter-spacing:-0.02em; }
        .page-header p { color:var(--muted); font-size:1rem; }
        .text-accent { color:var(--accent); }

        /* ── alerts ── */
        .alert { padding:14px 18px; border-radius:12px; margin-bottom:24px; font-weight:500; display:flex; flex-wrap:wrap; gap:4px 8px; }
        .alert-success { background:var(--accent-soft); border:1px solid var(--accent); color:var(--accent-text); }
        .alert-error { background:var(--danger-soft); border:1px solid var(--danger); color:var(--danger); }

        /* ── sections ── */
        .section-group { margin-bottom:44px; }
        .section-title { font-size:1.5rem; font-weight:700; margin-bottom:18px; color:var(--text); letter-spacing:-0.01em; }

        /* ── grids ── */
        .grid-plans { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; }

        /* ── cards ── */
        .selectable-label { display:block; height:100%; cursor:pointer; }
        .card {
            background:var(--surface); border:1.5px solid var(--border); border-radius:var(--radius);
            padding:20px 16px; transition:border-color .2s, background .2s, transform .1s;
            height:100%; position:relative; box-sizing:border-box;
            display:flex; flex-direction:column; align-items:flex-start; text-align:left;
        }
        .card:hover { border-color:rgba(224,169,59,0.5); }
        .card.selected { border-color:var(--accent) !important; background:var(--selected-bg) !important; box-shadow:var(--selected-shadow); }
        .badge {
            position:absolute; top:12px; right:12px; background:linear-gradient(135deg, var(--accent-2), var(--accent-dark));
            color:#1a1a1a; font-size:0.65rem; font-weight:700; text-transform:uppercase;
            padding:2px 10px; border-radius:20px; letter-spacing:0.3px;
        }
        .icon-box { width:44px; height:44px; margin-bottom:12px; color:var(--icon-idle); flex-shrink:0; }
        .card.selected .icon-box { color:var(--accent); }
        .card-title { font-size:1.15rem; font-weight:700; margin-bottom:6px; color:var(--text); }
        .card-desc { font-size:0.8rem; color:var(--muted); line-height:1.5; }
        .selected-indicator { margin-top:14px; font-size:0.75rem; font-weight:600; color:var(--accent-text); display:none; align-items:center; gap:4px; }

        /* ── save action ── */
        .form-actions { display:flex; flex-direction:column; align-items:center; gap:12px; margin-top:8px; }
        .form-actions .hint { color:var(--muted); font-size:0.85rem; text-align:center; }
        .btn-submit {
            min-width:260px; max-width:100%; padding:16px 32px; border-radius:12px; border:none; font-family:inherit;
            font-size:1rem; font-weight:700; background:var(--surface2); color:var(--muted);
            transition:all .2s; cursor:not-allowed;
        }
        .btn-submit.active { background:linear-gradient(135deg, var(--accent-2), var(--accent-dark)); color:#1a1a1a; cursor:pointer; }
        .btn-submit.active:hover { box-shadow:0 6px 20px rgba(224,169,59,0.3); transform:scale(1.01); }

        /* ── theme toggle (bottom-right pill, same spot as the dashboard) ── */
        .theme-toggle {
            position:fixed; right:14px; bottom:14px; z-index:50;
            display:inline-flex; align-items:center; gap:8px;
            padding:10px 16px; border-radius:999px;
            background:var(--surface); border:1px solid var(--border); color:var(--text);
            font-family:inherit; font-size:0.85rem; font-weight:600; cursor:pointer;
            box-shadow:var(--toggle-shadow); transition:border-color .2s, background .25s, color .25s;
        }
        .theme-toggle:hover { border-color:var(--accent); }
        .theme-toggle svg { flex-shrink:0; }

        /* ── responsive breakpoints ── */
        @media (max-width:1024px) {
            .grid-plans { grid-template-columns:repeat(3, 1fr); }
        }
        @media (max-width:768px) {
            body { padding:16px 12px; }
            .page-header h1 { font-size:2rem; }
            .grid-plans { grid-template-columns:repeat(2, 1fr); }
        }
        @media (max-width:500px) {
            .grid-plans { grid-template-columns:1fr 1fr; gap:10px; }
            .card { padding:16px 12px; }
            .page-header h1 { font-size:1.8rem; }
            .section-title { font-size:1.3rem; }
            .btn-back { padding:8px 14px; min-height:40px; font-size:0.85rem; }
            .btn-submit { width:100%; min-width:0; }
            .theme-toggle { padding:8px 14px; font-size:0.8rem; }
        }
        @media (max-width:400px) {
            .grid-plans { grid-template-columns:1fr; }
            .card { align-items:center; text-align:center; }
            .icon-box { margin-left:auto; margin-right:auto; }
        }

        @media (prefers-reduced-motion:reduce) { * { transition-duration:.01ms !important; } }
    </style>
</head>
<body>
    <div class="member-wrapper">

        <!-- back button -->
        <div class="top-bar">
            <a href="{{ route('member.dashboard') }}" class="btn-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- page header -->
        <div class="page-header">
            <h1>Choose Your <span class="text-accent">Plan</span></h1>
            <p>Select a fitness program that matches your goals</p>
        </div>

        {{-- Current plan status + feedback from the last save --}}
        @php
            $currentPlan  = $member?->fitness_plan;
            $selectedPlan = old('fitness_plan') ?? $currentPlan ?? '';
        @endphp

        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">✕ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">✕ {{ $errors->first() }}</div>
        @endif

        @if($member)
            <div class="alert alert-success">
                <span>Current plan:</span>
                <strong>{{ $currentPlan ?: 'No fitness plan selected yet' }}</strong>
            </div>

            {{-- Changing the fitness plan only updates the plan. It never creates a payment,
                 renews the membership or changes the coach. --}}
            <form action="{{ route('member.subscription.update') }}" method="POST" id="plan-form">
                @csrf

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
                        @endphp

                        @foreach($plans as $plan)
                            @php $isSelected = $selectedPlan === $plan['name']; @endphp
                            <label class="selectable-label">
                                <input type="radio" name="fitness_plan" value="{{ $plan['name'] }}"
                                       class="plan-radio" style="display:none;" {{ $isSelected ? 'checked' : '' }}/>
                                <div class="card plan-card {{ $isSelected ? 'selected' : '' }}">
                                    @if($currentPlan === $plan['name'])
                                        <div class="badge">Current Plan</div>
                                    @endif
                                    <div class="icon-box">{!! $plan['svg'] !!}</div>
                                    <div class="card-title">{{ $plan['name'] }}</div>
                                    <div class="card-desc">{{ $plan['desc'] }}</div>
                                    <div class="selected-indicator" style="{{ $isSelected ? 'display:flex' : 'display:none' }}">✓ Selected</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submit-btn" class="btn-submit" disabled>Change Plan</button>
                    <p class="hint">Changing your fitness plan doesn't affect your membership, coach or payments.</p>
                </div>
            </form>
        @else
            <div class="alert alert-error">You don't have a membership yet, so there's no fitness plan to change. Ask the front desk to set up your membership.</div>
        @endif
    </div>

    <!-- theme toggle -->
    <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Toggle light / dark mode"></button>

    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                /* ───────── Theme toggle ───────── */
                var toggleBtn = document.getElementById('theme-toggle');
                var root = document.documentElement;

                var syncToggle = function() {
                    var isDark = root.getAttribute('data-theme') !== 'light';
                    toggleBtn.textContent = isDark ? '☀ Light mode' : '☾ Dark mode';
                    toggleBtn.setAttribute('aria-label', 'Switch to ' + (isDark ? 'light' : 'dark') + ' mode');
                };

                toggleBtn.addEventListener('click', function() {
                    if (window.theme && typeof window.theme.toggle === 'function') {
                        window.theme.toggle();
                    } else {
                        var next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                        root.classList.toggle('dark', next === 'dark');
                        root.setAttribute('data-theme', next);
                        try { localStorage.setItem(window.APEX_THEME_KEY, next); } catch (e) {}
                    }
                    syncToggle();
                });
                syncToggle();

                /* ───────── Fitness plan selection ───────── */
                var form = document.getElementById('plan-form');
                if (!form) return; // member has no membership: nothing to select

                var btn = document.getElementById('submit-btn');
                var currentPlan = @json($currentPlan);

                function updateButton() {
                    var checked = form.querySelector('.plan-radio:checked');
                    // Only allow saving when a plan different from the current one is selected.
                    var canSave = !!checked && checked.value !== currentPlan;
                    btn.disabled = !canSave;
                    btn.classList.toggle('active', canSave);
                }

                form.querySelectorAll('.plan-radio').forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        form.querySelectorAll('.plan-card').forEach(function(card) {
                            card.classList.remove('selected');
                            var ind = card.querySelector('.selected-indicator');
                            if (ind) ind.style.display = 'none';
                        });
                        var card = radio.closest('label').querySelector('.card');
                        card.classList.add('selected');
                        var ind = card.querySelector('.selected-indicator');
                        if (ind) ind.style.display = 'flex';
                        updateButton();
                    });
                });

                updateButton();
            });
        })();
    </script>
</body>
</html>