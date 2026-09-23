@extends('layouts.instructor')
@section('title', 'Workout Scheduler – APEX')
@section('active', 'workout')

@section('content')

<style>
    :root {
        --bg: #0a0a0a;
        --surface: #111111;
        --surface2: #1a1a1a;
        --border: #2a2a2a;
        --accent: #ff2222;
        --accent-hover: #cc0000;
        --accent-glow: rgba(255,0,0,0.15);
        --text: #f0f0f0;
        --muted: #888888;
        --success: #ff4444;
        --warning: #ff6b35;
        --danger: #ff0000;
        --radius: 10px;
    }

    /* Page Header */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-header-left h1 {
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text);
    }
    .page-header-left h1 span {
        color: var(--accent);
    }
    .page-header-left p {
        color: var(--muted);
        font-size: clamp(0.8rem, 1.2vw, 1rem);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: var(--radius);
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
    }
    .btn-primary {
        background: var(--accent);
        color: #000;
        box-shadow: 0 4px 15px rgba(255,0,0,0.3);
    }
    .btn-primary:hover {
        background: var(--accent-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 25px rgba(255,0,0,0.4);
    }
    .btn-secondary {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
    }
    .btn-secondary:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: rgba(255,0,0,0.05);
    }
    .btn-danger {
        background: rgba(255,0,0,0.1);
        color: var(--danger);
        border: 1px solid rgba(255,0,0,0.2);
    }
    .btn-danger:hover {
        background: rgba(255,0,0,0.2);
        border-color: var(--danger);
    }
    .btn svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
    }
    .btn-primary svg {
        stroke: #000;
    }

    /* Custom Select */
    .custom-select {
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
    .custom-select option {
        background-color: var(--surface2);
        color: var(--text);
    }

    /* Form Controls */
    .form-control {
        width: 100%;
        padding: 10px 14px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 20px rgba(255,0,0,0.1);
    }
    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 6px;
    }
    textarea.form-control {
        resize: vertical;
        min-height: 60px;
    }
    input[type="date"] {
        color-scheme: dark;
        padding-right: 40px;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        width: 40px;
        height: 100%;
        cursor: pointer;
    }

    /* Navigator */
    .navigator {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .navigator-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .navigator-month {
        font-size: clamp(1.1rem, 2vw, 1.5rem);
        font-weight: 700;
        min-width: 160px;
        text-align: center;
        color: var(--accent);
    }

    /* Calendar Grid */
    .calendar-container {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 28px;
        transition: border-color 0.3s ease;
    }
    .calendar-container:hover {
        border-color: var(--accent);
    }
    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: var(--surface2);
        border-bottom: 2px solid var(--accent);
    }
    .calendar-header div {
        padding: 10px;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }
    .calendar-day {
        min-height: 110px;
        border-right: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 8px;
        position: relative;
        transition: background 0.2s ease;
    }
    .calendar-day:hover {
        background: rgba(255,0,0,0.02);
    }
    .calendar-day.empty {
        background: rgba(0, 0, 0, 0.2);
    }
    .calendar-day.today {
        background: rgba(255,0,0,0.05);
    }
    .calendar-day-number {
        font-size: 13px;
        font-weight: 500;
        color: var(--muted);
        margin-bottom: 6px;
    }
    .calendar-day-number.today {
        font-weight: 800;
        color: #000;
        background: var(--accent);
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    /* Workout Plan Item */
    .plan-item {
        background: rgba(255,0,0,0.08);
        border-left: 3px solid var(--accent);
        border-radius: 4px;
        padding: 3px 6px;
        margin-bottom: 3px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .plan-item:hover {
        opacity: 0.8;
        transform: translateX(2px);
    }
    .plan-member {
        font-size: 11px;
        font-weight: 600;
        color: var(--accent);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .plan-title {
        font-size: 10px;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .plan-more {
        font-size: 10px;
        color: var(--muted);
        margin-top: 2px;
    }

    /* Quick Add Button */
    .quick-add {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 20px;
        height: 20px;
        background: rgba(255,0,0,0.12);
        border: 1px solid rgba(255,0,0,0.2);
        border-radius: 4px;
        color: var(--accent);
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.2s ease;
        line-height: 1;
    }
    .calendar-day:hover .quick-add {
        opacity: 1;
    }
    .quick-add:hover {
        background: var(--accent);
        color: #000;
        transform: scale(1.1);
    }

    /* Intensity Badge */
    .intensity-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .intensity-light {
        background: rgba(74, 222, 128, 0.12);
        color: #4ade80;
        border: 1px solid rgba(74, 222, 128, 0.15);
    }
    .intensity-moderate {
        background: rgba(96, 165, 250, 0.12);
        color: #60a5fa;
        border: 1px solid rgba(96, 165, 250, 0.15);
    }
    .intensity-intense {
        background: rgba(248, 113, 113, 0.12);
        color: #f87171;
        border: 1px solid rgba(248, 113, 113, 0.15);
    }

    /* Section */
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .section-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--accent);
    }
    .section-count {
        font-size: 13px;
        color: var(--muted);
    }

    /* Cards */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        transition: border-color 0.3s ease;
    }
    .card:hover {
        border-color: var(--accent);
    }

    /* Table */
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    thead {
        background: var(--surface2);
        border-bottom: 2px solid var(--accent);
    }
    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
        white-space: nowrap;
    }
    td {
        padding: 14px 20px;
        font-size: 13px;
        border-top: 1px solid var(--border);
        vertical-align: middle;
        color: var(--text);
    }
    tr:hover td {
        background: rgba(255,0,0,0.02);
    }
    .table-member {
        font-weight: 600;
        color: var(--text);
    }
    .table-member-email {
        font-size: 12px;
        color: var(--muted);
    }
    .table-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(4px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-overlay.active {
        display: flex;
    }
    .modal-content {
        background: var(--surface);
        border: 1px solid var(--accent);
        border-radius: 16px;
        width: 100%;
        max-width: 560px;
        padding: 32px;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }
    .modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        color: var(--muted);
        font-size: 20px;
        cursor: pointer;
        transition: color 0.2s ease;
    }
    .modal-close:hover {
        color: var(--accent);
    }
    .modal-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--accent);
    }
    .modal-subtitle {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 24px;
    }
    .modal-member {
        font-size: 13px;
        color: var(--accent);
        margin-bottom: 20px;
        font-weight: 600;
    }
    .modal-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    .modal-grid .full-width {
        grid-column: span 2;
    }

    /* Exercise List */
    .exercise-item {
        display: flex;
        gap: 8px;
        margin-bottom: 8px;
    }
    .exercise-item .form-control {
        flex: 1;
    }
    .exercise-remove {
        background: rgba(255,0,0,0.1);
        color: var(--danger);
        border: 1px solid rgba(255,0,0,0.2);
        border-radius: var(--radius);
        padding: 0 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    .exercise-remove:hover {
        background: rgba(255,0,0,0.2);
    }
    .exercise-add {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 7px 14px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 4px;
    }
    .exercise-add:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
    }
    .modal-actions .btn {
        flex: 1;
        padding: 12px;
        font-weight: 700;
        justify-content: center;
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: var(--surface);
    }
    ::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: var(--accent-hover);
    }

    /* ===== RESPONSIVE ===== */

    @media (max-width: 1024px) {
        .modal-grid {
            grid-template-columns: 1fr 1fr;
        }
        table {
            min-width: 500px;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .page-header .btn-primary {
            width: 100%;
            justify-content: center;
        }
        .navigator {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .navigator-left {
            justify-content: center;
        }
        .navigator-month {
            min-width: 120px;
            font-size: 1.1rem;
        }
        .calendar-day {
            min-height: 80px;
            padding: 6px;
        }
        .calendar-day-number {
            font-size: 11px;
        }
        .calendar-day-number.today {
            width: 20px;
            height: 20px;
            font-size: 10px;
        }
        .plan-member {
            font-size: 9px;
        }
        .plan-title {
            font-size: 8px;
        }
        .quick-add {
            width: 16px;
            height: 16px;
            font-size: 11px;
            top: 4px;
            right: 4px;
        }
        .modal-content {
            padding: 24px;
        }
        .modal-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .modal-grid .full-width {
            grid-column: span 1;
        }
        table {
            min-width: 450px;
        }
        th, td {
            padding: 10px 14px;
            font-size: 12px;
        }
        .table-actions {
            flex-direction: column;
            gap: 4px;
        }
        .table-actions .btn {
            width: 100%;
            justify-content: center;
        }
        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .calendar-header div {
            font-size: 9px;
            padding: 6px;
        }
    }

    @media (max-width: 480px) {
        .page-header-left h1 {
            font-size: 1.3rem;
        }
        .navigator-left {
            gap: 8px;
        }
        .navigator-left .btn {
            font-size: 11px;
            padding: 5px 10px;
        }
        .navigator-month {
            font-size: 1rem;
            min-width: 100px;
        }
        .calendar-day {
            min-height: 60px;
            padding: 4px;
        }
        .calendar-day-number {
            font-size: 10px;
            margin-bottom: 3px;
        }
        .calendar-day-number.today {
            width: 18px;
            height: 18px;
            font-size: 9px;
        }
        .plan-member {
            font-size: 8px;
        }
        .plan-title {
            font-size: 7px;
        }
        .plan-more {
            font-size: 8px;
        }
        .quick-add {
            width: 14px;
            height: 14px;
            font-size: 10px;
            top: 2px;
            right: 2px;
        }
        .modal-content {
            padding: 18px;
        }
        .modal-title {
            font-size: 16px;
        }
        .modal-actions {
            flex-direction: column;
        }
        .modal-actions .btn {
            width: 100%;
        }
        table {
            min-width: 380px;
        }
        th, td {
            padding: 8px 10px;
            font-size: 11px;
        }
        .table-member {
            font-size: 12px;
        }
        .table-member-email {
            font-size: 10px;
        }
        .intensity-badge {
            font-size: 9px;
            padding: 2px 8px;
        }
        .section-title {
            font-size: 15px;
        }
        .calendar-header div {
            font-size: 8px;
            padding: 4px;
            letter-spacing: 0.5px;
        }
        .form-control {
            font-size: 13px;
            padding: 8px 12px;
        }
    }

    @media (max-width: 360px) {
        .calendar-day {
            min-height: 50px;
            padding: 3px;
        }
        .calendar-day-number {
            font-size: 9px;
        }
        .calendar-day-number.today {
            width: 16px;
            height: 16px;
            font-size: 8px;
        }
        table {
            min-width: 320px;
        }
        th, td {
            padding: 6px 8px;
            font-size: 10px;
        }
        .navigator-month {
            font-size: 0.9rem;
            min-width: 80px;
        }
    }
</style>

{{-- Header --}}
<div class="page-header">
    <div class="page-header-left">
        <h1>Workout <span>Scheduler</span></h1>
        <p>Create and assign monthly workout plans to your members</p>
    </div>
    <button onclick="openModal('createModal')" class="btn btn-primary">
        <svg viewBox="0 0 24 24" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New Workout Plan
    </button>
</div>

{{-- Month Navigator --}}
<div class="navigator">
    <div class="navigator-left">
        <a href="?month={{ $month == 1 ? 12 : $month - 1 }}&year={{ $month == 1 ? $year - 1 : $year }}"
           class="btn btn-secondary btn-sm">← Prev</a>
        <div class="navigator-month">{{ $start->format('F Y') }}</div>
        <a href="?month={{ $month == 12 ? 1 : $month + 1 }}&year={{ $month == 12 ? $year + 1 : $year }}"
           class="btn btn-secondary btn-sm">Next →</a>
    </div>
    <a href="?month={{ now()->month }}&year={{ now()->year }}"
       class="btn btn-secondary btn-sm">Today</a>
</div>

{{-- Calendar Grid --}}
@php
    $daysInMonth = $start->daysInMonth;
    $firstDow    = $start->dayOfWeek;
    $today       = now()->format('Y-m-d');
@endphp

<div class="calendar-container">
    <div class="calendar-header">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
            <div>{{ $d }}</div>
        @endforeach
    </div>

    <div class="calendar-grid">
        @for($e = 0; $e < $firstDow; $e++)
            <div class="calendar-day empty"></div>
        @endfor

        @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $dateKey  = $start->copy()->setDay($d)->format('Y-m-d');
                $dayPlans = $plans->get($dateKey, collect());
                $isToday  = $dateKey === $today;
            @endphp
            <div class="calendar-day {{ $isToday ? 'today' : '' }}">
                <div class="calendar-day-number {{ $isToday ? 'today' : '' }}">{{ $d }}</div>

                @foreach($dayPlans->take(3) as $plan)
                    @php
                        $colors = ['Light'=>'#4ade80','Moderate'=>'#60a5fa','Intense'=>'#f87171'];
                        $c = $colors[$plan->intensity] ?? '#888';
                        $bg = $plan->intensity === 'Light' ? 'rgba(74,222,128,0.1)' : 
                              ($plan->intensity === 'Intense' ? 'rgba(248,113,113,0.1)' : 'rgba(96,165,250,0.1)');
                    @endphp
                    <div class="plan-item" onclick="editPlan({{ $plan->id }})" style="border-left-color:{{ $c }};background:{{ $bg }};">
                        <div class="plan-member" style="color:{{ $c }};">{{ $plan->member->name ?? '?' }}</div>
                        <div class="plan-title">{{ $plan->title }}</div>
                    </div>
                @endforeach

                @if($dayPlans->count() > 3)
                    <div class="plan-more">+{{ $dayPlans->count() - 3 }} more</div>
                @endif

                <button class="quick-add" onclick="quickAdd('{{ $dateKey }}')" title="Quick add">+</button>
            </div>
        @endfor

        @php $remaining = (7 - ($firstDow + $daysInMonth) % 7) % 7; @endphp
        @for($r = 0; $r < $remaining; $r++)
            <div class="calendar-day empty"></div>
        @endfor
    </div>
</div>

{{-- All Plans List --}}
<div class="section-header">
    <div class="section-title">All Plans This Month</div>
    <span class="section-count">{{ $plans->flatten()->count() }} total</span>
</div>

<div class="card">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Intensity</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans->flatten()->sortBy('scheduled_date') as $plan)
                <tr>
                    <td>
                        <div class="table-member">{{ $plan->member->name ?? '—' }}</div>
                        <div class="table-member-email">{{ $plan->member->email ?? '' }}</div>
                    </td>
                    <td style="font-weight:600;">{{ $plan->title }}</td>
                    <td style="color:var(--muted);">{{ $plan->category ?? '—' }}</td>
                    <td>
                        <span class="intensity-badge intensity-{{ strtolower($plan->intensity) }}">
                            {{ $plan->intensity }}
                        </span>
                    </td>
                    <td style="color:var(--muted);">{{ $plan->scheduled_date->format('M d, Y') }}</td>
                    <td>
                        <div class="table-actions">
                            <button type="button" onclick="editPlan({{ $plan->id }})" class="btn btn-secondary btn-sm">Edit</button>
                            <form method="POST" action="{{ route('workout.destroy', $plan->id) }}" 
                                  onsubmit="return confirm('Delete this plan?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--muted);padding:48px;">
                        No workout plans this month.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── CREATE MODAL ── --}}
<div id="createModal" class="modal-overlay" onclick="if(event.target===this)closeModal('createModal')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('createModal')">✕</button>
        <div class="modal-title">New Workout Plan</div>
        <div class="modal-subtitle">Assign a workout to a member</div>

        <form method="POST" action="{{ route('workout.store') }}" id="createForm">
            @csrf
            <div class="modal-grid">
                <div class="full-width">
                    <label class="form-label">Member *</label>
                    <select name="member_id" class="form-control custom-select" required>
                        <option value="">— Select member —</option>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="full-width">
                    <label class="form-label">Workout Title *</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Upper Body Strength"/>
                </div>

                <div>
                    <label class="form-label">Date *</label>
                    <div style="position:relative;">
                        <input type="date" name="scheduled_date" id="createDate" class="form-control" required/>
                        <svg style="position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--accent);" 
                             width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="form-label">Intensity *</label>
                    <select name="intensity" class="form-control custom-select" required>
                        <option value="Light">Light</option>
                        <option value="Moderate" selected>Moderate</option>
                        <option value="Intense">Intense</option>
                    </select>
                </div>

                <div class="full-width">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control custom-select">
                        <option value="">— Select —</option>
                        @foreach(['Strength','Cardio','Flexibility','HIIT','Calisthenics','Powerlifting','Recovery'] as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="full-width">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief description..."></textarea>
                </div>

                <div class="full-width">
                    <label class="form-label">Exercises</label>
                    <div id="exerciseList">
                        <div class="exercise-item">
                            <input type="text" name="exercises[]" class="form-control" placeholder="e.g. 3 sets x 10 reps Push-ups"/>
                            <button type="button" onclick="addExercise()" 
                                    style="background:var(--accent);color:#000;border:none;border-radius:var(--radius);padding:0 14px;font-weight:700;cursor:pointer;transition:all 0.2s ease;">
                                + Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">✓ Create Plan</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── EDIT MODAL ── --}}
<div id="editModal" class="modal-overlay" onclick="if(event.target===this)closeModal('editModal')">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        <div class="modal-title">Edit Workout Plan</div>
        <div class="modal-member" id="editMemberName"></div>

        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-grid">
                <div class="full-width">
                    <label class="form-label">Workout Title *</label>
                    <input type="text" name="title" id="editTitle" class="form-control" required/>
                </div>

                <div>
                    <label class="form-label">Date *</label>
                    <div style="position:relative;">
                        <input type="date" name="scheduled_date" id="editDate" class="form-control" required/>
                        <svg style="position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--accent);" 
                             width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="form-label">Intensity *</label>
                    <select name="intensity" id="editIntensity" class="form-control custom-select" required>
                        <option value="Light">Light</option>
                        <option value="Moderate">Moderate</option>
                        <option value="Intense">Intense</option>
                    </select>
                </div>

                <div class="full-width">
                    <label class="form-label">Category</label>
                    <select name="category" id="editCategory" class="form-control custom-select">
                        <option value="">— Select —</option>
                        @foreach(['Strength','Cardio','Flexibility','HIIT','Calisthenics','Powerlifting','Recovery'] as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="full-width">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                </div>

                <div class="full-width">
                    <label class="form-label">Exercises</label>
                    <div id="editExerciseList"></div>
                    <button type="button" onclick="addEditExercise()" class="exercise-add">+ Add Exercise</button>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">✓ Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Hidden plan data for JS --}}
<div id="planData" style="display:none;">
    @foreach($plans->flatten() as $plan)
        <div class="plan-json" 
             data-id="{{ $plan->id }}" 
             data-title="{{ $plan->title }}" 
             data-description="{{ $plan->description }}"
             data-date="{{ $plan->scheduled_date->format('Y-m-d') }}" 
             data-category="{{ $plan->category }}" 
             data-intensity="{{ $plan->intensity }}"
             data-member="{{ $plan->member->name ?? '' }}" 
             data-exercises="{{ json_encode($plan->exercises ?? []) }}">
        </div>
    @endforeach
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

function quickAdd(date) {
    document.getElementById('createDate').value = date;
    openModal('createModal');
}

function addExercise() {
    const list = document.getElementById('exerciseList');
    const div = document.createElement('div');
    div.className = 'exercise-item';
    div.innerHTML = `
        <input type="text" name="exercises[]" class="form-control" placeholder="Exercise description"/>
        <button type="button" class="exercise-remove" onclick="this.parentElement.remove()">✕</button>
    `;
    list.appendChild(div);
}

function addEditExercise() {
    const list = document.getElementById('editExerciseList');
    const div = document.createElement('div');
    div.className = 'exercise-item';
    div.innerHTML = `
        <input type="text" name="exercises[]" class="form-control" placeholder="Exercise description"/>
        <button type="button" class="exercise-remove" onclick="this.parentElement.remove()">✕</button>
    `;
    list.appendChild(div);
}

function editPlan(id) {
    const pd = document.querySelector(`.plan-json[data-id="${id}"]`);
    if (!pd) return;
    
    document.getElementById('editTitle').value = pd.dataset.title;
    document.getElementById('editDate').value = pd.dataset.date;
    document.getElementById('editIntensity').value = pd.dataset.intensity;
    document.getElementById('editCategory').value = pd.dataset.category || '';
    document.getElementById('editDescription').value = pd.dataset.description || '';
    document.getElementById('editMemberName').textContent = 'Member: ' + pd.dataset.member;
    document.getElementById('editForm').action = '{{ url("workout") }}/' + id;

    const exList = document.getElementById('editExerciseList');
    exList.innerHTML = '';
    JSON.parse(pd.dataset.exercises || '[]').forEach(ex => {
        const div = document.createElement('div');
        div.className = 'exercise-item';
        div.innerHTML = `
            <input type="text" name="exercises[]" class="form-control" value="${ex}"/>
            <button type="button" class="exercise-remove" onclick="this.parentElement.remove()">✕</button>
        `;
        exList.appendChild(div);
    });
    
    openModal('editModal');
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            closeModal(modal.id);
        });
    }
});
</script>

@endsection