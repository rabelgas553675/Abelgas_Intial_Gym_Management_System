@extends('layouts.member')
@section('title', 'My Schedule – IRONFORGE')
@section('active', 'schedule')

@section('content')

<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">My Schedule</h1>
  <p style="color:var(--muted);font-size:14px;">Workout plans assigned by your instructor</p>
</div>

{{-- Month Navigator --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <div style="display:flex;align-items:center;gap:12px;">
    <a href="?month={{ $month == 1 ? 12 : $month - 1 }}&year={{ $month == 1 ? $year - 1 : $year }}"
       class="btn btn-secondary btn-sm">← Prev</a>
    <div style="font-size:20px;font-weight:700;min-width:160px;text-align:center;">
      {{ $start->format('F Y') }}
    </div>
    <a href="?month={{ $month == 12 ? 1 : $month + 1 }}&year={{ $month == 12 ? $year + 1 : $year }}"
       class="btn btn-secondary btn-sm">Next →</a>
  </div>
  <a href="?month={{ now()->month }}&year={{ now()->year }}"
     class="btn btn-secondary btn-sm">Today</a>
</div>

@if(empty($plans) || (is_array($plans) && count($plans) === 0) || (!is_array($plans) && $plans->isEmpty()))
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              padding:64px;text-align:center;">
    <div style="font-size:48px;margin-bottom:16px;">🏋️</div>
    <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No workouts scheduled</div>
    <div style="color:var(--muted);font-size:14px;">
      Your instructor hasn't assigned any workout plans for {{ $start->format('F Y') }} yet.
    </div>
  </div>
@else

  {{-- Summary Stats --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px;">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 24px;">
      <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;">
        Total Workouts
      </div>
      <div style="font-size:32px;font-weight:800;">{{ is_array($plans) ? count($plans) : $plans->count() }}</div>
      <div style="font-size:12px;color:var(--muted);margin-top:4px;">This month</div>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 24px;
                border-left:3px solid var(--accent);">
      <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;">
        Completed
      </div>
      <div style="font-size:32px;font-weight:800;color:var(--success);">
        {{ collect($plans)->where('is_completed', true)->count() }}
      </div>
      <div style="font-size:12px;color:var(--muted);margin-top:4px;">Finished sessions</div>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 24px;
                border-left:3px solid var(--info);">
      <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;">
        Upcoming
      </div>
      <div style="font-size:32px;font-weight:800;color:var(--info);">
        {{ $plans->where('scheduled_date', '>=', now()->toDateString())->count() }}
      </div>
      <div style="font-size:12px;color:var(--muted);margin-top:4px;">Remaining sessions</div>
    </div>
  </div>

  {{-- Weekly Timeline --}}
  @foreach($grouped->sortKeys() as $dateKey => $dayPlans)
    @php
      $date    = \Carbon\Carbon::parse($dateKey);
      $isToday = $dateKey === now()->format('Y-m-d');
      $isPast  = $date->isPast() && !$isToday;
    @endphp
    <div style="margin-bottom:20px;">

      {{-- Date header --}}
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
        <div style="width:44px;height:44px;border-radius:12px;
                    background:{{ $isToday ? 'var(--accent)' : 'var(--surface2)' }};
                    display:flex;flex-direction:column;align-items:center;justify-content:center;
                    flex-shrink:0;">
          <div style="font-size:9px;font-weight:700;color:{{ $isToday ? '#111' : 'var(--muted)' }};
                      text-transform:uppercase;letter-spacing:1px;">
            {{ $date->format('D') }}
          </div>
          <div style="font-size:16px;font-weight:800;color:{{ $isToday ? '#111' : 'var(--text)' }};
                      line-height:1;">
            {{ $date->format('d') }}
          </div>
        </div>
        <div>
          <div style="font-weight:700;font-size:15px;
                      color:{{ $isToday ? 'var(--accent)' : ($isPast ? 'var(--muted)' : 'var(--text)') }};">
            {{ $isToday ? 'Today' : $date->format('l, F d') }}
          </div>
          <div style="font-size:12px;color:var(--muted);">{{ $dayPlans->count() }} workout(s)</div>
        </div>
        @if($isToday)
          <span style="background:rgba(200,255,0,0.15);color:var(--accent);border:1px solid rgba(200,255,0,0.3);
                       padding:3px 10px;border-radius:100px;font-size:11px;font-weight:700;">TODAY</span>
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
        <div style="background:{{ $ic['bg'] }};border:1px solid {{ $ic['border'] }};
                    border-radius:12px;padding:20px 24px;margin-bottom:10px;margin-left:56px;
                    {{ $isPast ? 'opacity:0.6;' : '' }}">
          <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:12px;">
            <div>
              <div style="font-size:16px;font-weight:700;margin-bottom:4px;">{{ $plan->title }}</div>
              <div style="display:flex;align-items:center;gap:8px;">
                @if($plan->category)
                  <span style="background:var(--surface2);color:var(--muted);border:1px solid var(--border);
                               font-size:11px;font-weight:600;padding:2px 8px;border-radius:5px;">
                    {{ $plan->category }}
                  </span>
                @endif
                <span style="background:{{ $ic['bg'] }};color:{{ $ic['text'] }};border:1px solid {{ $ic['border'] }};
                             font-size:11px;font-weight:700;padding:2px 8px;border-radius:5px;">
                  {{ $plan->intensity }}
                </span>
              </div>
            </div>
            <div style="font-size:12px;color:var(--muted);text-align:right;">
              <div>By {{ $plan->instructor->name ?? 'Instructor' }}</div>
              @if($plan->is_completed)
                <span style="color:var(--success);font-weight:600;">✓ Completed</span>
              @endif
            </div>
          </div>

          @if($plan->description)
            <p style="font-size:13px;color:var(--muted);margin-bottom:12px;line-height:1.6;">
              {{ $plan->description }}
            </p>
          @endif

          @if(!empty($plan->exercises))
            <div style="border-top:1px solid {{ $ic['border'] }};padding-top:12px;">
              <div style="font-size:11px;color:var(--muted);text-transform:uppercase;
                          letter-spacing:1px;margin-bottom:8px;font-weight:700;">Exercises</div>
              @foreach($plan->exercises as $i => $ex)
                <div style="display:flex;align-items:center;gap:8px;padding:6px 0;
                            border-bottom:1px solid {{ $ic['border'] }};font-size:13px;">
                  <span style="width:20px;height:20px;border-radius:50%;background:var(--surface2);
                               display:flex;align-items:center;justify-content:center;
                               font-size:10px;font-weight:700;color:{{ $ic['text'] }};flex-shrink:0;">
                    {{ $i + 1 }}
                  </span>
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

@endsection