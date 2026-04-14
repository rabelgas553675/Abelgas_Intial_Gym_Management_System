@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'Members – IRONFORGE')
@section('page_title', 'Members')
@section('active_nav', 'members')

@section('topbar_actions')
<form method="GET" action="{{ route('members.index') }}"
      style="display:flex;align-items:center;gap:8px;">

  {{-- Search --}}
  <div style="position:relative;">
    <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24"
         style="position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none;">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search members..."
           style="padding:8px 14px 8px 32px;background:var(--surface2);border:1px solid var(--border);
                  border-radius:var(--radius);color:var(--text);font-family:'DM Sans',sans-serif;
                  font-size:13px;outline:none;width:210px;transition:border-color 0.15s;"
           onfocus="this.style.borderColor='var(--accent)'"
           onblur="this.style.borderColor='var(--border)'"/>
  </div>

  {{-- Plan --}}
  <select name="plan"
          style="padding:8px 14px;background:var(--surface2);border:1px solid var(--border);
                 border-radius:var(--radius);color:var(--text);font-family:'DM Sans',sans-serif;
                 font-size:13px;outline:none;cursor:pointer;">
    <option value="">All Plans</option>
    <option value="Monthly"     {{ request('plan')=='Monthly'     ?'selected':'' }}>Monthly</option>
    <option value="Quarterly"   {{ request('plan')=='Quarterly'   ?'selected':'' }}>Quarterly</option>
    <option value="Semi-Annual" {{ request('plan')=='Semi-Annual' ?'selected':'' }}>Semi-Annual</option>
    <option value="Annual"      {{ request('plan')=='Annual'      ?'selected':'' }}>Annual</option>
    <option value="Annually"    {{ request('plan')=='Annually'    ?'selected':'' }}>Annually</option>
  </select>

  {{-- Status --}}
  <select name="status"
          style="padding:8px 14px;background:var(--surface2);border:1px solid var(--border);
                 border-radius:var(--radius);color:var(--text);font-family:'DM Sans',sans-serif;
                 font-size:13px;outline:none;cursor:pointer;">
    <option value="">All Status</option>
    <option value="Active"  {{ request('status')=='Active'  ?'selected':'' }}>Active</option>
    <option value="Expired" {{ request('status')=='Expired' ?'selected':'' }}>Expired</option>
    <option value="Pending" {{ request('status')=='Pending' ?'selected':'' }}>Pending</option>
  </select>

  <button type="submit" class="btn btn-secondary">Filter</button>

  @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
    <a href="{{ route('members.create') }}" class="btn btn-primary">+ Add Member</a>
  @endif
</form>
@endsection

@section('content')

<div class="card">
  <table>
    <thead>
      <tr>
        <th style="width:36px;">#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Plan</th>
        <th>Status</th>
        <th>Start Date</th>
        <th>Due Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($members as $i => $member)
      <tr>
        <td style="color:var(--muted);font-size:13px;">{{ $members->firstItem() + $i }}</td>

        {{-- Name + avatar --}}
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            @if($member->photo)
              <img src="{{ asset('storage/'.$member->photo) }}"
                   style="width:32px;height:32px;border-radius:50%;object-fit:cover;
                          flex-shrink:0;border:1px solid var(--border);"/>
            @else
              <div style="width:32px;height:32px;border-radius:50%;
                          background:rgba(200,255,0,0.08);border:1px solid rgba(200,255,0,0.15);
                          display:flex;align-items:center;justify-content:center;
                          font-size:11px;font-weight:700;color:var(--accent);flex-shrink:0;">
                {{ strtoupper(substr($member->name,0,2)) }}
              </div>
            @endif
            <div>
              <div style="font-weight:600;font-size:14px;">{{ $member->name }}</div>
              <div style="font-size:11px;color:var(--muted);">{{ $member->email }}</div>
            </div>
          </div>
        </td>

        <td style="color:var(--muted);font-size:13px;">{{ $member->email }}</td>
        <td style="color:var(--muted);font-size:13px;">{{ $member->phone ?? '—' }}</td>

        {{-- Plan badge --}}
        <td>
          @php
            $pc = match($member->membership_type) {
              'Monthly'     => ['rgba(96,165,250,0.15)',  '#60a5fa'],
              'Quarterly'   => ['rgba(34,211,238,0.15)',  '#22d3ee'],
              'Semi-Annual' => ['rgba(251,191,36,0.15)',  '#fbbf24'],
              'Annual','Annually' => ['rgba(74,222,128,0.15)', '#4ade80'],
              default       => ['rgba(139,92,246,0.15)',  '#a78bfa'],
            };
          @endphp
          <span style="display:inline-flex;align-items:center;padding:4px 12px;border-radius:6px;
                       font-size:11px;font-weight:700;
                       background:{{ $pc[0] }};color:{{ $pc[1] }};">
            {{ $member->membership_type ?? '—' }}
          </span>
        </td>

        {{-- Status badge --}}
        <td>
          <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;
                       border-radius:6px;font-size:11px;font-weight:700;
                       @if($member->status==='Active')  background:rgba(74,222,128,0.15);color:#4ade80;
                       @elseif($member->status==='Expired') background:rgba(248,113,113,0.15);color:#f87171;
                       @else background:rgba(251,191,36,0.15);color:#fbbf24;@endif">
            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;"></span>
            {{ $member->status }}
          </span>
        </td>

        {{-- Start Date --}}
        <td style="color:var(--muted);font-size:13px;">
          {{ $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('Y-m-d') : '—' }}
        </td>

        {{-- Due Date --}}
        @php
          $due = $member->end_date ? \Carbon\Carbon::parse($member->end_date) : null;
          $dueColor = 'var(--muted)'; $dueBold = false; $dueLabel = '—';
          if ($due) {
            $dueLabel = $due->format('Y-m-d');
            if ($due->isPast())                   { $dueColor = 'var(--danger)';  $dueBold = true; }
            elseif ($due->diffInDays(now()) <= 7) { $dueColor = 'var(--warning)'; $dueBold = true; }
            else                                  { $dueColor = 'var(--accent)';  $dueBold = true; }
          }
        @endphp
        <td style="color:{{ $dueColor }};font-weight:{{ $dueBold?'700':'400' }};font-size:13px;">
          {{ $dueLabel }}
        </td>

        {{-- Actions --}}
        <td>
          <div style="display:flex;gap:6px;align-items:center;">
            <a href="{{ route('members.show', $member) }}" class="btn btn-secondary btn-sm">View</a>
            @if(auth()->user()->isAdmin())
              <a href="{{ route('members.edit', $member) }}" class="btn btn-secondary btn-sm">Edit</a>
              <form method="POST" action="{{ route('members.destroy', $member) }}"
                    onsubmit="return confirm('Delete {{ addslashes($member->name) }}?')" style="margin:0;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger-soft">Delete</button>
              </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" style="text-align:center;color:var(--muted);padding:56px;">
          No members found.
          @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('members.create') }}" style="color:var(--accent);margin-left:4px;">Add one →</a>
          @endif
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($members->hasPages())
  <div style="margin-top:16px;display:flex;justify-content:flex-end;">
    {{ $members->withQueryString()->links() }}
  </div>
@endif

@endsection