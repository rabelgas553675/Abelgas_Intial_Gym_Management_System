@extends('layouts.instructor')
@section('title', 'Instructor Dashboard – APEX')
@section('active', 'dashboard')

@section('content')

{{-- Page Header --}}
<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    Welcome, <span style="color:var(--accent);">{{ explode(' ', auth()->user()->name)[0] }}</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">Manage and monitor your assigned members</p>
</div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-card-left">
                <div class="stat-label">Total Members</div>
                <div class="stat-value">{{ count($members) }}</div>
            </div>
            <div class="stat-icon icon-green">
                <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-left">
                <div class="stat-label">Active</div>
                <div class="stat-value">{{ $active }}</div>
            </div>
            <div class="stat-icon icon-orange">
                <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <circle cx="12" cy="12" r="8" stroke="var(--success)"/>
                    <circle cx="12" cy="12" r="3" fill="var(--success)" stroke="none"/>
                </svg>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-left">
                <div class="stat-label">Expiring Soon</div>
                <div class="stat-value" style="color:var(--warning);">{{ $nearDue }}</div>
            </div>
            <div class="stat-icon icon-yellow">
                <svg viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
        </div>
    </div>

{{-- Split Panel --}}
<div class="split-panel">

  {{-- LEFT: Members List --}}
  <div class="members-panel">
    <div class="members-panel-header">
      <div class="members-panel-title">Members List</div>
      <div class="members-search">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" id="memberSearch" placeholder="Search members..." oninput="filterMembers(this.value)"/>
      </div>
    </div>

    <div class="members-list" id="membersList">
      @forelse($members as $member)
        @php
          $isExpired   = $member->isExpired();
          $isExpiring  = $member->isDueWithinDays(7) && !$isExpired;
          $pillClass   = $isExpired ? 'pill-expired' : ($isExpiring ? 'pill-expiring' : 'pill-active');
          $pillLabel   = $isExpired ? 'Expired'      : ($isExpiring ? 'Expiring'      : 'Active');
          $memberPhoto = $member->user?->photo ?? $member->photo ?? null;
        @endphp
        <div class="member-item"
             data-name="{{ strtolower($member->name) }}"
             data-email="{{ strtolower($member->email) }}"
             onclick="showMemberDetail({{ $member->id }}, this)"
             id="item-{{ $member->id }}">
          <div class="member-item-left">
            @if($memberPhoto)
              <img src="{{ asset('storage/'.$memberPhoto) }}" class="member-avatar"/>
            @else
              <div class="member-avatar-placeholder">{{ strtoupper(substr($member->name, 0, 2)) }}</div>
            @endif
            <div class="member-item-info">
              <span class="member-item-name">{{ $member->name }}</span>
              <span class="member-item-email">{{ $member->email }}</span>
            </div>
          </div>
          <span class="status-pill {{ $pillClass }}">{{ $pillLabel }}</span>
        </div>
      @empty
        <div style="padding:40px;text-align:center;color:var(--muted);font-size:14px;">
          No members assigned yet.
        </div>
      @endforelse
    </div>
  </div>

  {{-- RIGHT: Member Details Panel --}}
  <div class="details-panel" id="detailsPanel">

    <div class="details-empty" id="detailsEmpty">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                 M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                 m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <div style="font-size:14px;margin-top:4px;">Select a member to view details</div>
    </div>

    <div class="details-content" id="detailsContent" style="padding:0;">

      <div class="details-hero">
        <div class="details-avatar" id="detailsAvatar"></div>
        <div>
          <div class="details-name" id="detailsName"></div>
          <div id="detailsBadge"></div>
        </div>
      </div>

      <div class="details-body">

        <div class="section-label">Contact Information</div>

        <div class="contact-grid">
          <div class="contact-item">
            <div class="contact-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <div class="contact-label">Email</div>
              <div class="contact-value" id="detailsEmail"></div>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
            </div>
            <div>
              <div class="contact-label">Phone</div>
              <div class="contact-value" id="detailsPhone"></div>
            </div>
          </div>
        </div>

        <div class="section-label">Subscription</div>

        <div class="sub-grid">
          <div class="sub-item">
            <div class="sub-label">Plan</div>
            <div class="sub-value accent" id="detailsPlan"></div>
          </div>
          <div class="sub-item">
            <div class="sub-label">Duration</div>
            <div class="sub-value" id="detailsDuration"></div>
          </div>
          <div class="sub-item full-width">
            <div class="sub-label">Active Period</div>
            <div class="sub-value" id="detailsPeriod"></div>
          </div>
        </div>

        <div class="days-container">
          <div class="days-header">
            <div class="days-label">Days Remaining</div>
            <div class="days-value" id="daysRemainingLabel"></div>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" id="daysRemainingBar"></div>
          </div>
        </div>

        <a id="detailsViewBtn" href="#" class="view-profile-btn">
          View Full Profile
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 8l4 4m0 0l-4 4m4-4H3"/>
          </svg>
        </a>

      </div>
    </div>
  </div>
</div>

{{-- Hidden member data for JS --}}
<div id="memberData" style="display:none;">
  @foreach($members as $member)
    @php
      $isExpired      = $member->isExpired();
      $isExpiring     = $member->isDueWithinDays(7) && !$isExpired;
      $statusLabel    = $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active');
      $statusColor    = $isExpired ? 'var(--danger)' : ($isExpiring ? 'var(--warning)' : 'var(--success)');
      $statusBg       = 'color-mix(in srgb, ' . $statusColor . ' 15%, transparent)';
      $daysRemaining  = $isExpired ? 0 : (int) now()->diffInDays($member->end_date);
      $totalDays      = ($member->start_date && $member->end_date)
                          ? (int) $member->start_date->diffInDays($member->end_date) : 30;
      $progressPct    = $totalDays > 0 ? min(100, round(($daysRemaining / $totalDays) * 100)) : 0;
      $memberPhotoUrl = ($member->user?->photo ?? $member->photo ?? null)
                          ? asset('storage/' . ($member->user?->photo ?? $member->photo))
                          : '';
    @endphp
    <div class="md"
         data-id="{{ $member->id }}"
         data-name="{{ $member->name }}"
         data-email="{{ $member->email }}"
         data-phone="{{ $member->phone ?? '—' }}"
         data-plan="{{ $member->fitness_plan ?? '—' }}"
         data-duration="{{ $member->membership_type ?? '—' }}"
         data-start="{{ $member->start_date?->format('M d, Y') ?? '—' }}"
         data-end="{{ $member->end_date?->format('M d, Y') ?? '—' }}"
         data-status="{{ $statusLabel }}"
         data-status-color="{{ $statusColor }}"
         data-status-bg="{{ $statusBg }}"
         data-days-remaining="{{ $daysRemaining }}"
         data-progress-pct="{{ $progressPct }}"
         data-bar-color="{{ $statusColor }}"
         data-photo="{{ $memberPhotoUrl }}"
         data-url="{{ route('instructor.member.show', $member) }}">
    </div>
  @endforeach
</div>

<style>
  /* Charcoal & gold — colours come from the tokens in layouts/instructor.blade.php */

  .stat-grid { grid-template-columns: repeat(3, 1fr); }

  .split-panel { grid-template-columns: 1fr 1.2fr; gap: 24px; margin-bottom: 28px; align-items: stretch; }

  .members-panel { display: flex; flex-direction: column; max-height: 600px; border-radius: 16px; }
  .members-panel-header {
    padding: 16px 20px; display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 10px;
  }
  .members-panel-title { font-weight: 700; font-size: 16px; color: var(--accent); margin-bottom: 0; }

  .members-search {
    display: flex; align-items: center; background: var(--surface2);
    border: 1px solid var(--border); border-radius: 40px;
    padding: 4px 14px 4px 10px; gap: 6px; flex: 1 1 180px; min-width: 120px;
  }
  .members-search svg { position: static; transform: none; width: 16px; height: 16px; stroke: var(--muted); flex-shrink: 0; }
  .members-search input { background: transparent; border: none; padding: 8px 0; font-size: 13px; color: var(--text); width: 100%; outline: none; }
  .members-search input::placeholder { color: var(--muted); }
  .members-search:focus-within { border-color: var(--accent); }

  .members-list { flex: 1; overflow-y: auto; padding: 8px 0; max-height: none; }
  .members-list::-webkit-scrollbar { width: 6px; }
  .members-list::-webkit-scrollbar-thumb { background: var(--accent-dark); border-radius: 3px; }

  .member-item { padding: 12px 20px; margin-bottom: 0; border-radius: 0; border: none; border-left: 3px solid transparent; }
  .member-item:hover { background: var(--surface2); border-left-color: var(--accent); }
  .member-item.active-item { background: var(--accent-soft); border-left-color: var(--accent); }
  .member-item-left { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }

  .member-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); }
  .member-avatar-placeholder {
    width: 36px; height: 36px; border-radius: 50%; background: var(--accent-soft);
    border: 1px solid rgba(224,169,59,0.3); display: flex; align-items: center;
    justify-content: center; font-size: 12px; font-weight: 700; color: var(--accent); flex-shrink: 0;
  }
  .member-item-info { line-height: 1.3; }

  .status-pill { font-size: 10px; padding: 4px 12px; border-radius: 40px; text-transform: uppercase; letter-spacing: .3px; }

  .details-panel { border-radius: 16px; overflow: hidden; min-height: 400px; }
  .details-empty { height: 100%; min-height: 320px; padding: 20px; text-align: center; }
  .details-content { display: none; flex-direction: column; padding: 0; }
  .details-content.visible { display: flex; }

  .details-hero {
    padding: 28px 28px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(224,169,59,0.08), transparent);
  }
  .details-avatar {
    width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
    background: var(--accent-soft); border: 2px solid var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 24px; color: var(--accent); overflow: hidden;
  }
  .details-name { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
  .details-body { padding: 22px 28px; }

  .section-label { font-size: 10px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; }

  .contact-grid { display: grid; gap: 10px; margin-bottom: 20px; }
  .contact-item, .sub-item, .days-container {
    background: var(--surface2); border: 1px solid var(--border); border-radius: 10px;
    transition: border-color .3s ease;
  }
  .contact-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; flex-wrap: wrap; }
  .contact-item:hover, .sub-item:hover, .days-container:hover { border-color: rgba(224,169,59,0.5); }
  .contact-icon {
    width: 32px; height: 32px; border-radius: 8px; background: var(--accent-soft);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--accent);
  }
  .contact-label, .sub-label, .days-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; }
  .contact-label { margin-bottom: 2px; }
  .contact-value { font-size: 13px; font-weight: 600; color: var(--text); word-break: break-word; }

  .sub-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
  .sub-item { padding: 14px; }
  .sub-item.full-width { grid-column: span 2; }
  .sub-label { margin-bottom: 5px; }
  .sub-value { font-size: 14px; font-weight: 700; color: var(--text); }
  .sub-value.accent { color: var(--accent); }

  .days-container { padding: 14px; margin-bottom: 20px; }
  .days-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 4px; }
  .days-value { font-size: 13px; font-weight: 700; color: var(--accent); }
  .progress-bar { background: var(--surface3); border-radius: 999px; height: 6px; overflow: hidden; }
  .progress-fill { height: 100%; border-radius: 999px; transition: width .4s ease; width: 0%; background: var(--accent); }

  .view-profile-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
    padding: 13px; margin-top: 0; background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
    color: #1a1a1a; font-size: 14px; font-weight: 800; border-radius: 10px; text-decoration: none;
  }
  html:root[data-theme="light"] .view-profile-btn { color: #111; }
  .view-profile-btn svg { stroke: currentColor; }

  @media (max-width: 1024px) {
    .split-panel { grid-template-columns: 1fr; }
    .members-panel { max-height: 420px; }
    .details-panel { min-height: 320px; }
  }
  @media (max-width: 768px) {
    .stat-grid { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 640px) {
    .stat-grid { grid-template-columns: 1fr; }
    .members-panel-header { flex-direction: column; align-items: stretch; gap: 8px; }
    .member-item { padding: 10px 14px; flex-wrap: wrap; gap: 6px; }
    .member-item-left { min-width: 120px; }
    .details-hero { padding: 16px; gap: 12px; }
    .details-avatar { width: 56px; height: 56px; font-size: 20px; }
    .details-name { font-size: 18px; }
    .details-body { padding: 16px; }
    .sub-grid { grid-template-columns: 1fr; }
    .sub-item.full-width { grid-column: span 1; }
  }
</style>

<script>
function showMemberDetail(id, el) {
  document.querySelectorAll('.member-item').forEach(i => i.classList.remove('active-item'));
  el.classList.add('active-item');

  const md = document.querySelector(`.md[data-id="${id}"]`);
  if (!md) return;

  const avatar = document.getElementById('detailsAvatar');
  const initials = md.dataset.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
  avatar.innerHTML = md.dataset.photo
    ? `<img src="${md.dataset.photo}" style="width:100%;height:100%;object-fit:cover;"/>`
    : initials;

  document.getElementById('detailsName').textContent = md.dataset.name;

  document.getElementById('detailsBadge').innerHTML =
    `<span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;
                  border-radius:100px;font-size:12px;font-weight:700;
                  background:${md.dataset.statusBg};color:${md.dataset.statusColor};
                  border:1px solid color-mix(in srgb, ${md.dataset.statusColor} 27%, transparent);">
       <span style="width:6px;height:6px;border-radius:50%;background:${md.dataset.statusColor};display:inline-block;"></span>
       ${md.dataset.status}
     </span>`;

  document.getElementById('detailsEmail').textContent = md.dataset.email;
  document.getElementById('detailsPhone').textContent = md.dataset.phone;
  document.getElementById('detailsPlan').textContent = md.dataset.plan;
  document.getElementById('detailsDuration').textContent = md.dataset.duration;
  document.getElementById('detailsPeriod').textContent = `${md.dataset.start} – ${md.dataset.end}`;

  const days = parseInt(md.dataset.daysRemaining) || 0;
  const pct = parseInt(md.dataset.progressPct) || 0;
  const barColor = md.dataset.barColor;

  document.getElementById('daysRemainingLabel').textContent =
    days === 0 ? 'Expired' : `${days} day${days !== 1 ? 's' : ''} left`;
  document.getElementById('daysRemainingLabel').style.color = barColor;
  document.getElementById('daysRemainingBar').style.width = pct + '%';
  document.getElementById('daysRemainingBar').style.background = barColor;

  document.getElementById('detailsViewBtn').href = md.dataset.url;

  document.getElementById('detailsEmpty').style.display = 'none';
  document.getElementById('detailsContent').classList.add('visible');
}

function filterMembers(query) {
  const q = query.toLowerCase();
  document.querySelectorAll('.member-item').forEach(item => {
    const match = (item.dataset.name || '').includes(q) || (item.dataset.email || '').includes(q);
    item.style.display = match ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', function() {
  const first = document.querySelector('.member-item');
  if (first) first.click();
});
</script>

@endsection