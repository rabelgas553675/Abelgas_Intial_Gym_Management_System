@extends('layouts.member')
@section('title', 'My Schedule – IRONFORGE')
@section('active', 'schedule')

@section('content')

<style>
    /* ===== RESPONSIVE STYLES ===== */
    .schedule-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .page-header {
        margin-bottom: 28px;
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .page-header p {
        color: var(--muted);
        font-size: 14px;
    }

    /* Navigation */
    .nav-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .nav-center {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
        flex: 1;
    }

    .nav-month {
        font-size: 20px;
        font-weight: 700;
        min-width: 160px;
        text-align: center;
    }

    /* Buttons */
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 13px;
        min-height: 40px;
        text-decoration: none;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-secondary {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: var(--border);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
        min-height: 36px;
    }

    /* Empty State */
    .empty-state {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 64px;
        text-align: center;
    }

    .empty-state .icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .empty-state .title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .empty-state .sub {
        color: var(--muted);
        font-size: 14px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px 24px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-card .label {
        font-size: 10px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 8px;
    }

    .stat-card .value {
        font-size: 32px;
        font-weight: 800;
    }

    .stat-card .sub {
        font-size: 12px;
        color: var(--muted);
        margin-top: 4px;
    }

    .stat-card.accent-border {
        border-left: 3px solid var(--accent);
    }

    .stat-card .value.accent {
        color: var(--accent);
    }

    .stat-card .value.success {
        color: var(--success);
    }

    .stat-card .value.info {
        color: var(--info);
    }

    /* Date Header */
    .date-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .date-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--surface2);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .date-box.today {
        background: var(--accent);
    }

    .date-box .day-name {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
    }

    .date-box.today .day-name {
        color: #111;
    }

    .date-box .day-number {
        font-size: 16px;
        font-weight: 800;
        color: var(--text);
        line-height: 1;
    }

    .date-box.today .day-number {
        color: #111;
    }

    .date-info .date-label {
        font-weight: 700;
        font-size: 15px;
        color: var(--text);
    }

    .date-info .date-label.today {
        color: var(--accent);
    }

    .date-info .date-label.past {
        color: var(--muted);
    }

    .date-info .date-sub {
        font-size: 12px;
        color: var(--muted);
    }

    .today-badge {
        background: rgba(200, 255, 0, 0.15);
        color: var(--accent);
        border: 1px solid rgba(200, 255, 0, 0.3);
        padding: 3px 10px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Plan Cards */
    .plan-card {
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 10px;
        margin-left: 56px;
        transition: all 0.2s;
    }

    .plan-card:hover {
        transform: translateX(4px);
    }

    .plan-card.past {
        opacity: 0.6;
    }

    .plan-card .plan-header {
        display: flex;
        align-items: start;
        justify-content: space-between;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .plan-card .plan-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .plan-card .plan-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .plan-card .plan-meta .tag {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 5px;
        display: inline-block;
    }

    .plan-card .plan-meta .tag-category {
        background: var(--surface2);
        color: var(--muted);
        border: 1px solid var(--border);
    }

    .plan-card .plan-right {
        font-size: 12px;
        color: var(--muted);
        text-align: right;
        flex-shrink: 0;
    }

    .plan-card .plan-right .completed {
        color: var(--success);
        font-weight: 600;
    }

    .plan-card .plan-description {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 12px;
        line-height: 1.6;
    }

    .plan-card .exercises-section {
        border-top: 1px solid var(--border);
        padding-top: 12px;
    }

    .plan-card .exercises-section .exercises-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .plan-card .exercise-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }

    .plan-card .exercise-item:last-child {
        border-bottom: none;
    }

    .plan-card .exercise-item .num {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--surface2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* ===== RESPONSIVE BREAKPOINTS ===== */

    @media (max-width: 1024px) {
        .schedule-container {
            padding: 0 12px;
        }

        .stats-grid {
            gap: 10px;
        }

        .stat-card .value {
            font-size: 28px;
        }
    }

    @media (max-width: 768px) {
        .schedule-container {
            padding: 0 12px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .page-header p {
            font-size: 13px;
        }

        .nav-wrapper {
            flex-direction: column;
            align-items: stretch;
        }

        .nav-center {
            gap: 8px;
        }

        .nav-month {
            font-size: 18px;
            min-width: 120px;
        }

        .nav-wrapper .btn-sm {
            font-size: 11px;
            padding: 5px 12px;
            min-height: 32px;
        }

        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .stat-card {
            padding: 14px 16px;
            border-radius: 10px;
        }

        .stat-card .value {
            font-size: 24px;
        }

        .stat-card .label {
            font-size: 9px;
        }

        .stat-card .sub {
            font-size: 10px;
        }

        .empty-state {
            padding: 40px 20px;
            border-radius: 12px;
        }

        .empty-state .icon {
            font-size: 36px;
        }

        .empty-state .title {
            font-size: 16px;
        }

        .empty-state .sub {
            font-size: 13px;
        }

        .date-box {
            width: 38px;
            height: 38px;
        }

        .date-box .day-number {
            font-size: 14px;
        }

        .date-info .date-label {
            font-size: 14px;
        }

        .plan-card {
            padding: 16px 18px;
            margin-left: 48px;
        }

        .plan-card .plan-title {
            font-size: 15px;
        }

        .plan-card .plan-meta .tag {
            font-size: 10px;
            padding: 1px 6px;
        }

        .plan-card .plan-description {
            font-size: 12px;
        }

        .plan-card .exercise-item {
            font-size: 12px;
            padding: 4px 0;
        }

        .plan-card .exercise-item .num {
            width: 18px;
            height: 18px;
            font-size: 9px;
        }

        .plan-card .plan-right {
            font-size: 11px;
        }
    }

    @media (max-width: 480px) {
        .schedule-container {
            padding: 0 8px;
        }

        .page-header h1 {
            font-size: 20px;
        }

        .page-header p {
            font-size: 12px;
        }

        .nav-month {
            font-size: 16px;
            min-width: 100px;
        }

        .nav-center {
            gap: 6px;
        }

        .nav-wrapper .btn-sm {
            font-size: 10px;
            padding: 4px 10px;
            min-height: 28px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .stat-card {
            padding: 12px 14px;
        }

        .stat-card .value {
            font-size: 20px;
        }

        .stat-card .label {
            font-size: 8px;
            letter-spacing: 1px;
        }

        .stat-card .sub {
            font-size: 9px;
        }

        .empty-state {
            padding: 32px 16px;
            border-radius: 10px;
        }

        .empty-state .icon {
            font-size: 32px;
        }

        .empty-state .title {
            font-size: 15px;
        }

        .empty-state .sub {
            font-size: 12px;
        }

        .date-header {
            gap: 8px;
        }

        .date-box {
            width: 34px;
            height: 34px;
        }

        .date-box .day-name {
            font-size: 8px;
        }

        .date-box .day-number {
            font-size: 12px;
        }

        .date-info .date-label {
            font-size: 13px;
        }

        .date-info .date-sub {
            font-size: 11px;
        }

        .today-badge {
            font-size: 9px;
            padding: 2px 8px;
        }

        .plan-card {
            padding: 14px 12px;
            margin-left: 0;
            border-radius: 10px;
        }

        .plan-card .plan-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .plan-card .plan-title {
            font-size: 14px;
        }

        .plan-card .plan-meta .tag {
            font-size: 9px;
            padding: 1px 5px;
        }

        .plan-card .plan-right {
            text-align: left;
            width: 100%;
        }

        .plan-card .plan-description {
            font-size: 12px;
        }

        .plan-card .exercise-item {
            font-size: 12px;
            padding: 4px 0;
        }

        .plan-card .exercise-item .num {
            width: 16px;
            height: 16px;
            font-size: 8px;
        }

        .plan-card .exercises-section .exercises-label {
            font-size: 10px;
        }

        .date-header-wrapper {
            margin-bottom: 10px;
        }
    }

    @media (max-width: 360px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }

        .stat-card {
            padding: 10px 10px;
            border-radius: 8px;
        }

        .stat-card .value {
            font-size: 18px;
        }

        .plan-card {
            padding: 10px 10px;
        }

        .plan-card .plan-title {
            font-size: 13px;
        }

        .date-box {
            width: 30px;
            height: 30px;
        }

        .date-box .day-number {
            font-size: 11px;
        }

        .date-info .date-label {
            font-size: 12px;
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>

<div class="schedule-container">

    {{-- Header --}}
    <div class="page-header">
        <h1>My Schedule</h1>
        <p>Workout plans assigned by your instructor</p>
    </div>

    {{-- Month Navigator --}}
    <div class="nav-wrapper">
        <div class="nav-center">
            <a href="?month={{ $month == 1 ? 12 : $month - 1 }}&year={{ $month == 1 ? $year - 1 : $year }}"
               class="btn btn-secondary btn-sm">← Prev</a>
            <div class="nav-month">{{ $start->format('F Y') }}</div>
            <a href="?month={{ $month == 12 ? 1 : $month + 1 }}&year={{ $month == 12 ? $year + 1 : $year }}"
               class="btn btn-secondary btn-sm">Next →</a>
        </div>
        <a href="?month={{ now()->month }}&year={{ now()->year }}"
           class="btn btn-secondary btn-sm">Today</a>
    </div>

    @if(empty($plans) || (is_array($plans) && count($plans) === 0) || (!is_array($plans) && $plans->isEmpty()))
        <div class="empty-state">
            <div class="icon">🏋️</div>
            <div class="title">No workouts scheduled</div>
            <div class="sub">
                Your instructor hasn't assigned any workout plans for {{ $start->format('F Y') }} yet.
            </div>
        </div>
    @else

        {{-- Summary Stats --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Total Workouts</div>
                <div class="value accent">{{ is_array($plans) ? count($plans) : $plans->count() }}</div>
                <div class="sub">This month</div>
            </div>
            <div class="stat-card accent-border">
                <div class="label">Completed</div>
                <div class="value success">{{ collect($plans)->where('is_completed', true)->count() }}</div>
                <div class="sub">Finished sessions</div>
            </div>
            <div class="stat-card accent-border" style="border-left-color:var(--info);">
                <div class="label">Upcoming</div>
                <div class="value info">{{ $plans->where('scheduled_date', '>=', now()->toDateString())->count() }}</div>
                <div class="sub">Remaining sessions</div>
            </div>
        </div>

        {{-- Weekly Timeline --}}
        @foreach($grouped->sortKeys() as $dateKey => $dayPlans)
            @php
                $date    = \Carbon\Carbon::parse($dateKey);
                $isToday = $dateKey === now()->format('Y-m-d');
                $isPast  = $date->isPast() && !$isToday;
            @endphp
            <div class="date-header-wrapper" style="margin-bottom:20px;">

                {{-- Date header --}}
                <div class="date-header">
                    <div class="date-box {{ $isToday ? 'today' : '' }}">
                        <div class="day-name">{{ $date->format('D') }}</div>
                        <div class="day-number">{{ $date->format('d') }}</div>
                    </div>
                    <div class="date-info">
                        <div class="date-label {{ $isToday ? 'today' : ($isPast ? 'past' : '') }}">
                            {{ $isToday ? 'Today' : $date->format('l, F d') }}
                        </div>
                        <div class="date-sub">{{ $dayPlans->count() }} workout(s)</div>
                    </div>
                    @if($isToday)
                        <span class="today-badge">TODAY</span>
                    @endif
                </div>

                {{-- Plan cards --}}
                @foreach($dayPlans as $plan)
                    @php
                        $intColors = [
                            'Light'    => ['bg'=>'rgba(74,222,128,0.08)',  'border'=>'rgba(74,222,128,0.25)',  'text'=>'#4ade80'],
                            'Moderate' => ['bg'=>'rgba(96,165,250,0.08)',  'border'=>'rgba(96,165,250,0.25)',  'text'=>'#60a5fa'],
                            'Intense'  => ['bg'=>'rgba(248,113,113,0.08)', 'border'=>'rgba(248,113,113,0.25)', 'text'=>'#f87171'],
                        ];
                        $ic = $intColors[$plan->intensity] ?? $intColors['Moderate'];
                    @endphp
                    <div class="plan-card {{ $isPast ? 'past' : '' }}" style="background:{{ $ic['bg'] }};border:1px solid {{ $ic['border'] }};">
                        <div class="plan-header">
                            <div>
                                <div class="plan-title">{{ $plan->title }}</div>
                                <div class="plan-meta">
                                    @if($plan->category)
                                        <span class="tag tag-category">{{ $plan->category }}</span>
                                    @endif
                                    <span class="tag" style="background:{{ $ic['bg'] }};color:{{ $ic['text'] }};border:1px solid {{ $ic['border'] }};">
                                        {{ $plan->intensity }}
                                    </span>
                                </div>
                            </div>
                            <div class="plan-right">
                                <div>By {{ $plan->instructor->name ?? 'Instructor' }}</div>
                                @if($plan->is_completed)
                                    <div class="completed">✓ Completed</div>
                                @endif
                            </div>
                        </div>

                        @if($plan->description)
                            <p class="plan-description">{{ $plan->description }}</p>
                        @endif

                        @if(!empty($plan->exercises))
                            <div class="exercises-section" style="border-top-color:{{ $ic['border'] }};">
                                <div class="exercises-label">Exercises</div>
                                @foreach($plan->exercises as $i => $ex)
                                    <div class="exercise-item" style="border-bottom-color:{{ $ic['border'] }};">
                                        <span class="num" style="color:{{ $ic['text'] }};">{{ $i + 1 }}</span>
                                        {{ $ex }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach

    @endif

</div>

@endsection