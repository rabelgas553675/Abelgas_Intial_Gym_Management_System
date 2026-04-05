@extends('layouts.app')
@section('title', 'Members – IRONFORGE')
@section('page_title', 'Members')

@section('topbar_actions')
  <form method="GET" action="{{ route('members.index') }}" style="display:flex;gap:8px;align-items:center;">
    <input type="text" name="search" class="search-bar" placeholder="Search members..."
           value="{{ request('search') }}">
    <select name="plan" class="form-control" style="width:140px;padding:9px 12px;">
      <option value="">All Plans</option>
      <option value="Monthly"     {{ request('plan')=='Monthly'     ?'selected':'' }}>Monthly</option>
      <option value="Quarterly"   {{ request('plan')=='Quarterly'   ?'selected':'' }}>Quarterly</option>
      <option value="Semi-Annual" {{ request('plan')=='Semi-Annual' ?'selected':'' }}>Semi-Annual</option>
      <option value="Annual"      {{ request('plan')=='Annual'      ?'selected':'' }}>Annual</option>
    </select>
    <select name="status" class="form-control" style="width:140px;padding:9px 12px;">
      <option value="">All Status</option>
      <option value="Active"  {{ request('status')=='Active'  ?'selected':'' }}>Active</option>
      <option value="Expired" {{ request('status')=='Expired' ?'selected':'' }}>Expired</option>
    </select>
    <button type="submit" class="btn btn-secondary">Filter</button>

    {{-- + Add Member: admin AND staff --}}
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
        <th>#</th>
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
        <td style="color:var(--muted)">{{ $members->firstItem() + $i }}</td>

        {{-- Name + email stacked --}}
        <td>
          <div style="font-weight:600;">{{ $member->name }}</div>
          <div style="font-size:12px;color:var(--muted);">{{ $member->email }}</div>
        </td>

        <td style="color:var(--muted)">{{ $member->email }}</td>
        <td style="color:var(--muted)">{{ $member->phone ?? '—' }}</td>

        {{-- Plan badge --}}
        <td>
          <span style="
            display:inline-block;padding:3px 10px;border-radius:5px;font-size:11px;font-weight:600;
            @if($member->membership_type=='Monthly')       background:rgba(96,165,250,0.2);color:#60a5fa;
            @elseif($member->membership_type=='Quarterly') background:rgba(34,211,238,0.2);color:#22d3ee;
            @elseif($member->membership_type=='Semi-Annual') background:rgba(251,191,36,0.2);color:#fbbf24;
            @elseif($member->membership_type=='Annual')    background:rgba(74,222,128,0.2);color:#4ade80;
            @else background:rgba(139,92,246,0.2);color:#a78bfa;
            @endif
          ">{{ $member->membership_type }}</span>
        </td>

        {{-- Status badge --}}
        <td>
          <span class="badge badge-{{ strtolower($member->status) }}">
            {{ $member->status }}
          </span>
        </td>

        {{-- Start Date --}}
        <td style="color:var(--muted)">
          {{ $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('Y-m-d') : '—' }}
        </td>

        {{-- Due Date --}}
        @php
          $due      = $member->end_date ? \Carbon\Carbon::parse($member->end_date) : null;
          $dueStyle = 'color:var(--muted);';
          $dueLabel = '—';
          if ($due) {
            $dueLabel = $due->format('Y-m-d');
            if ($due->isPast())                   $dueStyle = 'color:var(--danger);font-weight:600;';
            elseif ($due->diffInDays(now()) <= 7) $dueStyle = 'color:var(--warning);font-weight:600;';
            else                                  $dueStyle = 'color:var(--accent);font-weight:600;';
          }
        @endphp
        <td style="{{ $dueStyle }}">{{ $dueLabel }}</td>

        {{-- Actions --}}
        <td>
          <div style="display:flex;gap:6px;">

            {{-- View — all roles --}}
            <a href="{{ route('members.show', $member) }}" class="btn btn-secondary btn-sm">View</a>

            {{-- Edit & Delete — admin only --}}
            @if(auth()->user()->isAdmin())
            <a href="{{ route('members.edit', $member) }}" class="btn btn-secondary btn-sm">Edit</a>
            <form method="POST" action="{{ route('members.destroy', $member) }}"
                  onsubmit="return confirm('Delete {{ $member->name }}?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            @endif

          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="9" style="text-align:center;color:var(--muted);padding:40px;">
          No members found.
          @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
            <a href="{{ route('members.create') }}" style="color:var(--accent)">Add one</a>.
          @endif
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:16px;">{{ $members->withQueryString()->links() }}</div>
@endsection