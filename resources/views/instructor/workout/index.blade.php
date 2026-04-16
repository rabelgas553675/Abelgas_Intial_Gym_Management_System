@extends('layouts.instructor')
@section('title', 'Workout Scheduler – IRONFORGE')
@section('active', 'workout')

@section('content')

<style>
    /* Custom Dropdown Styling */
    .custom-select {
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

    /* Style for option dropdown list background */
    .custom-select option {
        background-color: #1a1a1a;
        color: white;
    }

    /* Existing quick-add button styles */
    [id^="add-"] {
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 24px;      
        height: 24px;     
        font-size: 18px;  
        font-weight: bold;
        padding: 0;
        cursor: pointer;
        border-radius: 50%; 
        transition: all 0.2s ease;
    }

    [id^="add-"]:hover {
        transform: scale(1.2);
        background-color: var(--accent);
        color: #111;
    }
</style>

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
  <div>
    <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">Workout Scheduler</h1>
    <p style="color:var(--muted);font-size:14px;">Create and assign monthly workout plans to your members</p>
  </div>
  <button onclick="document.getElementById('createModal').style.display='flex'"
          class="btn btn-primary" style="gap:8px;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    New Workout Plan
  </button>
</div>

{{-- Month Navigator --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
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

{{-- Calendar Grid --}}
@php
  $daysInMonth = $start->daysInMonth;
  $firstDow    = $start->dayOfWeek;
  $today       = now()->format('Y-m-d');
@endphp

<div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:28px;">
  <div style="display:grid;grid-template-columns:repeat(7,1fr);background:var(--surface2);border-bottom:1px solid var(--border);">
    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
      <div style="padding:10px;text-align:center;font-size:11px;font-weight:700;
                  color:var(--muted);text-transform:uppercase;letter-spacing:1px;">
        {{ $d }}
      </div>
    @endforeach
  </div>

  <div style="display:grid;grid-template-columns:repeat(7,1fr);">
    @for($e = 0; $e < $firstDow; $e++)
      <div style="min-height:110px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);
                  background:rgba(0,0,0,0.2);"></div>
    @endfor

    @for($d = 1; $d <= $daysInMonth; $d++)
      @php
        $dateKey  = $start->copy()->setDay($d)->format('Y-m-d');
        $dayPlans = $plans->get($dateKey, collect());
        $isToday  = $dateKey === $today;
      @endphp
      <div style="min-height:110px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);
                  padding:8px;position:relative;
                  {{ $isToday ? 'background:rgba(200,255,0,0.03);' : '' }}">

        <div style="font-size:13px;font-weight:{{ $isToday ? '800' : '500' }};
                    color:{{ $isToday ? 'var(--accent)' : 'var(--muted)' }};
                    {{ $isToday ? 'background:var(--accent);color:#111;width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;' : '' }}
                    margin-bottom:6px;">
          {{ $d }}
        </div>

        @foreach($dayPlans->take(3) as $plan)
          @php
            $colors = ['Light'=>'#4ade80','Moderate'=>'#60a5fa','Intense'=>'#f87171'];
            $c = $colors[$plan->intensity] ?? '#888';
          @endphp
          <div onclick="showPlanDetail({{ $plan->id }})"
               style="background:rgba({{ $plan->intensity==='Light'?'74,222,128':($plan->intensity==='Intense'?'248,113,113':'96,165,250') }},0.15);
                      border-left:3px solid {{ $c }};
                      border-radius:4px;padding:3px 6px;margin-bottom:3px;
                      cursor:pointer;transition:all 0.15s;"
               onmouseover="this.style.opacity='0.8'"
               onmouseout="this.style.opacity='1'">
            <div style="font-size:11px;font-weight:600;color:{{ $c }};
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              {{ $plan->member->name ?? '?' }}
            </div>
            <div style="font-size:10px;color:var(--muted);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              {{ $plan->title }}
            </div>
          </div>
        @endforeach

        @if($dayPlans->count() > 3)
          <div style="font-size:10px;color:var(--muted);margin-top:2px;">
            +{{ $dayPlans->count() - 3 }} more
          </div>
        @endif

        <button onclick="quickAdd('{{ $dateKey }}')"
                style="position:absolute;top:6px;right:6px;width:18px;height:18px;
                       background:rgba(200,255,0,0.15);border:none;border-radius:4px;
                       color:var(--accent);font-size:14px;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;
                       opacity:0;transition:opacity 0.15s;line-height:1;"
                id="add-{{ $dateKey }}">+</button>
      </div>
    @endfor

    @php $remaining = (7 - ($firstDow + $daysInMonth) % 7) % 7; @endphp
    @for($r = 0; $r < $remaining; $r++)
      <div style="min-height:110px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);
                  background:rgba(0,0,0,0.2);"></div>
    @endfor
  </div>
</div>

{{-- All Plans List --}}
<div class="section-header">
  <div class="section-title">All Plans This Month</div>
  <span style="font-size:13px;color:var(--muted);">{{ $plans->flatten()->count() }} total</span>
</div>

<div class="card">
  <table>
    <thead>
      <tr>
        <th>Member</th><th>Title</th><th>Category</th>
        <th>Intensity</th><th>Date</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($plans->flatten()->sortBy('scheduled_date') as $plan)
      <tr>
        <td>
          <div style="font-weight:600;">{{ $plan->member->name ?? '—' }}</div>
          <div style="font-size:12px;color:var(--muted);">{{ $plan->member->email ?? '' }}</div>
        </td>
        <td style="font-weight:600;">{{ $plan->title }}</td>
        <td style="color:var(--muted);">{{ $plan->category ?? '—' }}</td>
        <td>
          @php $ic = ['Light'=>'#4ade80','Moderate'=>'#60a5fa','Intense'=>'#f87171'][$plan->intensity]; @endphp
          <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;
                       background:{{ $ic }}22;color:{{ $ic }};">
            {{ $plan->intensity }}
          </span>
        </td>
        <td style="color:var(--muted);">{{ $plan->scheduled_date->format('M d, Y') }}</td>
        <td>
          <div style="display:flex;gap:6px;">
            <button type="button" onclick="editPlan({{ $plan->id }})" class="btn btn-secondary btn-sm">Edit</button>
            <form method="POST" action="{{ route('workout.destroy', $plan->id) }}" onsubmit="return confirm('Delete this plan?')" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm" style="background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.2);">Delete</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" style="text-align:center;color:var(--muted);padding:48px;">No workout plans this month.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- ── CREATE MODAL ── --}}
<div id="createModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);
            z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              width:100%;max-width:560px;padding:32px;position:relative;max-height:90vh;overflow-y:auto;">

    <button onclick="document.getElementById('createModal').style.display='none'"
            style="position:absolute;top:16px;right:16px;background:none;border:none;
                   color:var(--muted);font-size:20px;cursor:pointer;">✕</button>

    <div style="font-size:18px;font-weight:700;margin-bottom:4px;">New Workout Plan</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:24px;">Assign a workout to a member</div>

    <form method="POST" action="{{ route('workout.store') }}" id="createForm">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div style="grid-column:span 2;">
          <label class="form-label">Member *</label>
          <select name="member_id" class="form-control custom-select" required>
            <option value="">— Select member —</option>
            @foreach($members as $m)
              <option value="{{ $m->id }}">{{ $m->name }}</option>
            @endforeach
          </select>
        </div>

        <div style="grid-column:span 2;">
          <label class="form-label">Workout Title *</label>
          <input type="text" name="title" class="form-control" required placeholder="e.g. Upper Body Strength"/>
        </div>

        <div>
          <label class="form-label">Date *</label>
          <input type="date" name="scheduled_date" id="createDate" class="form-control" required/>
        </div>

        <div>
          <label class="form-label">Intensity *</label>
          <select name="intensity" class="form-control custom-select" required>
            <option value="Light">Light</option>
            <option value="Moderate" selected>Moderate</option>
            <option value="Intense">Intense</option>
          </select>
        </div>

        <div style="grid-column:span 2;">
          <label class="form-label">Category</label>
          <select name="category" class="form-control custom-select">
            <option value="">— Select —</option>
            @foreach(['Strength','Cardio','Flexibility','HIIT','Calisthenics','Powerlifting','Recovery'] as $cat)
              <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
          </select>
        </div>

        <div style="grid-column:span 2;">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Brief description..."></textarea>
        </div>

        <div style="grid-column:span 2;">
          <label class="form-label">Exercises</label>
          <div id="exerciseList">
            <div style="display:flex;gap:8px;margin-bottom:8px;">
              <input type="text" name="exercises[]" class="form-control" placeholder="e.g. 3 sets x 10 reps Push-ups"/>
              <button type="button" onclick="addExercise()"
                      style="background:var(--accent);color:#111;border:none;border-radius:8px;padding:0 14px;font-weight:700;cursor:pointer;">+ Add</button>
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;font-weight:700;">✓ Create Plan</button>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- ── EDIT MODAL ── --}}
<div id="editModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);
            z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              width:100%;max-width:560px;padding:32px;position:relative;max-height:90vh;overflow-y:auto;">

    <button onclick="document.getElementById('editModal').style.display='none'"
            style="position:absolute;top:16px;right:16px;background:none;border:none;
                   color:var(--muted);font-size:20px;cursor:pointer;">✕</button>

    <div style="font-size:18px;font-weight:700;margin-bottom:4px;">Edit Workout Plan</div>
    <div id="editMemberName" style="font-size:13px;color:var(--accent);margin-bottom:20px;font-weight:600;"></div>

    <form method="POST" id="editForm">
      @csrf @method('PUT')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <div style="grid-column:span 2;">
          <label class="form-label">Workout Title *</label>
          <input type="text" name="title" id="editTitle" class="form-control" required/>
        </div>

        <div>
          <label class="form-label">Date *</label>
          <input type="date" name="scheduled_date" id="editDate" class="form-control" required/>
        </div>

        <div>
          <label class="form-label">Intensity *</label>
          <select name="intensity" id="editIntensity" class="form-control custom-select" required>
            <option value="Light">Light</option>
            <option value="Moderate">Moderate</option>
            <option value="Intense">Intense</option>
          </select>
        </div>

        <div style="grid-column:span 2;">
          <label class="form-label">Category</label>
          <select name="category" id="editCategory" class="form-control custom-select">
            <option value="">— Select —</option>
            @foreach(['Strength','Cardio','Flexibility','HIIT','Calisthenics','Powerlifting','Recovery'] as $cat)
              <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
          </select>
        </div>

        <div style="grid-column:span 2;">
          <label class="form-label">Description</label>
          <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
        </div>

        <div style="grid-column:span 2;">
          <label class="form-label">Exercises</label>
          <div id="editExerciseList"></div>
          <button type="button" onclick="addEditExercise()"
                  style="background:var(--surface2);color:var(--text);border:1px solid var(--border);
                         border-radius:8px;padding:7px 14px;font-size:13px;cursor:pointer;margin-top:4px;">
            + Add Exercise
          </button>
        </div>
      </div>

      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;font-weight:700;">✓ Save Changes</button>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- Hidden plan data for JS --}}
<div id="planData" style="display:none;">
  @foreach($plans->flatten() as $plan)
    <div class="plan-json" data-id="{{ $plan->id }}" data-title="{{ $plan->title }}" data-description="{{ $plan->description }}"
         data-date="{{ $plan->scheduled_date->format('Y-m-d') }}" data-category="{{ $plan->category }}" data-intensity="{{ $plan->intensity }}"
         data-member="{{ $plan->member->name ?? '' }}" data-exercises="{{ json_encode($plan->exercises ?? []) }}">
    </div>
  @endforeach
</div>

<script>
['createModal','editModal'].forEach(id => {
  document.getElementById(id).addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
  });
});

function quickAdd(date) {
  document.getElementById('createDate').value = date;
  document.getElementById('createModal').style.display = 'flex';
}

function addExercise() {
  const list = document.getElementById('exerciseList');
  const div = document.createElement('div');
  div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
  div.innerHTML = `<input type="text" name="exercises[]" class="form-control" placeholder="Exercise description"/><button type="button" onclick="this.parentElement.remove()" style="background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.2);border-radius:8px;padding:0 12px;cursor:pointer;">✕</button>`;
  list.appendChild(div);
}

function editPlan(id) {
  const pd = document.querySelector(`.plan-json[data-id="${id}"]`);
  if (!pd) return;
  document.getElementById('editTitle').value       = pd.dataset.title;
  document.getElementById('editDate').value        = pd.dataset.date;
  document.getElementById('editIntensity').value   = pd.dataset.intensity;
  document.getElementById('editCategory').value    = pd.dataset.category;
  document.getElementById('editDescription').value = pd.dataset.description;
  document.getElementById('editMemberName').textContent = pd.dataset.member;
  document.getElementById('editForm').action = '{{ url("workout") }}/' + id;

  const exList = document.getElementById('editExerciseList');
  exList.innerHTML = '';
  JSON.parse(pd.dataset.exercises || '[]').forEach(ex => {
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
    div.innerHTML = `<input type="text" name="exercises[]" class="form-control" value="${ex}"/><button type="button" onclick="this.parentElement.remove()" style="background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.2);border-radius:8px;padding:0 12px;cursor:pointer;">✕</button>`;
    exList.appendChild(div);
  });
  document.getElementById('editModal').style.display = 'flex';
}

function addEditExercise() {
  const list = document.getElementById('editExerciseList');
  const div = document.createElement('div');
  div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
  div.innerHTML = `<input type="text" name="exercises[]" class="form-control" placeholder="Exercise description"/><button type="button" onclick="this.parentElement.remove()" style="background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.2);border-radius:8px;padding:0 12px;cursor:pointer;">✕</button>`;
  list.appendChild(div);
}

document.querySelectorAll('[id^="add-"]').forEach(btn => {
    const cell = btn.closest('div');
    cell.addEventListener('mouseenter', () => btn.style.opacity = '1');
    cell.addEventListener('mouseleave', () => btn.style.opacity = '0');
});
</script>

@endsection