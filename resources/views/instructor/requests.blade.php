@extends('layouts.instructor')
@section('title', 'Coach Requests – IRONFORGE')
@section('active', 'requests')

@section('content')

{{-- Page Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;">
  <div>
    <h1 style="font-size:24px;font-weight:800;margin-bottom:4px;">Coach Requests</h1>
    <p style="color:var(--muted);font-size:13px;">Review and respond to incoming member requests.</p>
  </div>
  @if($pending->count())
    <span class="badge badge-pending" style="font-size:12px;padding:6px 14px;">
      {{ $pending->count() }} pending
    </span>
  @endif
</div>

{{-- PENDING --}}
<div style="margin-bottom:36px;">
  <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:14px;">
    Pending Requests
  </div>

  @if($pending->isEmpty())
    <div class="card" style="padding:60px;text-align:center;">
      <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"
           style="margin:0 auto 12px;display:block;opacity:0.2;"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <div style="font-size:15px;font-weight:700;margin-bottom:4px;">All caught up!</div>
      <div style="font-size:13px;color:var(--muted);">No pending requests at the moment.</div>
    </div>
  @else
    <div style="display:flex;flex-direction:column;gap:12px;">
      @foreach($pending as $req)
      @php $member = $req->member; $user = $member?->user; @endphp
      <div class="card" style="padding:20px 24px;display:flex;align-items:center;gap:20px;">
        @if($user && $user->photo)
          <img src="{{ asset('storage/'.$user->photo) }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;"/>
        @else
          <div style="width:48px;height:48px;border-radius:50%;background:rgba(200,255,0,0.08);border:1px solid rgba(200,255,0,0.2);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:var(--accent);flex-shrink:0;font-family:'Bebas Neue',sans-serif;letter-spacing:1px;">
            {{ strtoupper(substr($member->name ?? 'M', 0, 2)) }}
          </div>
        @endif
        <div style="flex:1;min-width:0;">
          <div style="font-size:15px;font-weight:700;margin-bottom:3px;">{{ $member->name ?? 'Unknown Member' }}</div>
          <div style="font-size:12px;color:var(--muted);">{{ $user->email ?? '—' }}@if($user?->phone)&nbsp;·&nbsp;{{ $user->phone }}@endif</div>
          @if($req->message)
            <div style="margin-top:10px;font-size:12px;color:rgba(240,240,240,0.6);background:var(--surface2);border-left:2px solid var(--accent);padding:6px 12px;border-radius:0 6px 6px 0;font-style:italic;">"{{ $req->message }}"</div>
          @endif
          <div style="margin-top:8px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            @if($member?->fitness_plan)<span class="badge badge-monthly">{{ $member->fitness_plan }}</span>@endif
            @if($member?->membership_type)<span class="badge badge-{{ strtolower($member->membership_type) }}">{{ $member->membership_type }}</span>@endif
            <span style="font-size:11px;color:var(--muted);">{{ $req->created_at->diffForHumans() }}</span>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
          <form action="{{ route('instructor.requests.approve', $req->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Approve
            </button>
          </form>
          <form action="{{ route('instructor.requests.reject', $req->id) }}" method="POST" onsubmit="return confirm('Decline this request?')">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--danger);border-color:rgba(248,113,113,0.3);">
              <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              Reject
            </button>
          </form>
        </div>
      </div>
      @endforeach
    </div>
  @endif
</div>

{{-- HISTORY --}}
@if($history->isNotEmpty())
<div>
  <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:14px;">Recent History</div>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Member</th><th>Plan</th><th>Duration</th><th>Date</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($history as $h)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:32px;height:32px;border-radius:50%;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--muted);flex-shrink:0;">{{ strtoupper(substr($h->member->name ?? 'M', 0, 2)) }}</div>
              <div>
                <div style="font-weight:600;">{{ $h->member->name ?? '—' }}</div>
                <div style="font-size:12px;color:var(--muted);">{{ $h->member->user->email ?? '' }}</div>
              </div>
            </div>
          </td>
          <td style="color:var(--muted);font-size:13px;">{{ $h->member->fitness_plan ?? '—' }}</td>
          <td>@if($h->member?->membership_type)<span class="badge badge-{{ strtolower($h->member->membership_type) }}">{{ $h->member->membership_type }}</span>@else <span style="color:var(--muted)">—</span>@endif</td>
          <td style="font-size:13px;color:var(--muted);">{{ $h->updated_at->format('M d, Y') }}</td>
          <td>@if($h->status === 'approved')<span class="badge badge-active">Approved</span>@else<span class="badge badge-expired">Rejected</span>@endif</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@endsection