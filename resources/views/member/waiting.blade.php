@extends('layouts.member')
@section('title', 'Waiting for Approval – IRONFORGE')

@section('content')
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;">
  <div style="text-align:center;max-width:480px;width:100%;padding:40px;">

    {{-- Animated Icon --}}
    <div id="status-icon" style="margin-bottom:28px;">
      @if($member->coach_status === 'rejected')
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(248,113,113,0.1);border:2px solid rgba(248,113,113,0.4);display:flex;align-items:center;justify-content:center;margin:0 auto;">
          <svg width="36" height="36" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </div>
      @else
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(200,255,0,0.08);border:2px solid rgba(200,255,0,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto;animation:pulse 2s infinite;">
          <svg width="36" height="36" fill="none" stroke="var(--accent)" stroke-width="1.5" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
        </div>
      @endif
    </div>

    {{-- Title --}}
    <h1 id="status-title" style="font-size:24px;font-weight:800;margin-bottom:12px;">
      @if($member->coach_status === 'rejected')
        Request Rejected
      @else
        Waiting for Coach Approval
      @endif
    </h1>

    {{-- Message --}}
    <p id="status-message" style="color:var(--muted);font-size:14px;line-height:1.7;margin-bottom:32px;">
      @if($member->coach_status === 'rejected')
        Your coach request was rejected. You can go back and choose a different coach or train independently.
      @else
        Your subscription request has been sent to your coach for approval. This page will automatically update once they respond.
      @endif
    </p>

    {{-- Status Badge --}}
    <div style="margin-bottom:32px;">
      @if($member->coach_status === 'rejected')
        <span style="background:rgba(248,113,113,0.1);color:#f87171;border:1px solid rgba(248,113,113,0.3);padding:8px 20px;border-radius:20px;font-size:13px;font-weight:700;letter-spacing:1px;">
          ✕ REJECTED
        </span>
      @else
        <span style="background:rgba(200,255,0,0.08);color:var(--accent);border:1px solid rgba(200,255,0,0.3);padding:8px 20px;border-radius:20px;font-size:13px;font-weight:700;letter-spacing:1px;">
          ⏳ PENDING APPROVAL
        </span>
      @endif
    </div>

    {{-- Member Info Card --}}
    <div class="card" style="padding:20px;text-align:left;margin-bottom:24px;">
      <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:14px;">
        Your Subscription Details
      </div>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;justify-content:space-between;font-size:13px;">
          <span style="color:var(--muted);">Plan</span>
          <span style="font-weight:600;">{{ $member->fitness_plan ?? '—' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;">
          <span style="color:var(--muted);">Duration</span>
          <span style="font-weight:600;">{{ $member->membership_type ?? '—' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;">
          <span style="color:var(--muted);">Coach Fee</span>
          <span style="font-weight:600;">{{ $member->coach_membership_type ?? '—' }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;">
          <span style="color:var(--muted);">Total Fee</span>
          <span style="font-weight:600;color:var(--accent);">₱{{ number_format($member->fee, 2) }}</span>
        </div>
      </div>
    </div>

    {{-- Action Buttons --}}
    @if($member->coach_status === 'rejected')
      <a href="{{ route('member.select-plan') }}" class="btn btn-primary" style="width:100%;justify-content:center;">
        Choose Again
      </a>
    @else
      <div style="font-size:12px;color:var(--muted);margin-top:8px;" id="polling-status">
        Checking for updates...
      </div>
    @endif

  </div>
</div>

{{-- Pulse animation --}}
<style>
@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(200,255,0,0.2); }
  50%       { box-shadow: 0 0 0 12px rgba(200,255,0,0); }
}
</style>

{{-- AJAX Polling — auto-redirect when approved --}}
@if($member->coach_status === 'pending')
<script>
  const statusUrl  = "{{ route('member.coach.status') }}";
  const dashUrl    = "{{ route('member.dashboard') }}";
  let   checkCount = 0;

  function checkStatus() {
    fetch(statusUrl, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      checkCount++;
      document.getElementById('polling-status').textContent =
        `Last checked: just now (check #${checkCount})`;

      if (data.coach_status === 'approved') {
        document.getElementById('status-title').textContent   = '🎉 Approved!';
        document.getElementById('status-message').textContent = 'Your coach has approved your request. Redirecting to dashboard...';
        setTimeout(() => window.location.href = dashUrl, 2000);
      }

      if (data.coach_status === 'rejected') {
        window.location.reload();
      }
    })
    .catch(() => {
      document.getElementById('polling-status').textContent = 'Checking...';
    });
  }

  // Poll every 5 seconds
  setInterval(checkStatus, 5000);
</script>
@endif

@endsection