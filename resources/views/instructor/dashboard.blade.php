@extends('layouts.instructor')
@section('title', 'Instructor Dashboard – APEX')
@section('active', 'dashboard')

@section('content')

{{-- Page Header --}}
<div class="dash-head">
  <h1>
    Welcome, <span style="color:var(--accent);">{{ explode(' ', auth()->user()->name)[0] }}</span>
  </h1>
  <p>Manage and monitor your assigned members</p>
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

{{-- Coaching rates --}}
<div class="rate-card">
    <div class="rate-card-head">
        <h2>My Coaching Rate</h2>
        <span>Current package pricing</span>
    </div>
    <div class="rate-grid">
        @foreach($currentCoachRates ?? [] as $period => $rate)
            <div class="rate-item">
                <div class="rate-item-label">{{ $period }}</div>
                <div class="rate-item-value">₱{{ number_format((int) $rate, 0) }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- Split Panel --}}
<div class="split-panel">

  {{-- LEFT: Members List --}}
  <div class="members-panel">
    <div class="members-panel-header">
      <div class="members-panel-title">Members List</div>
      <div class="members-search">
        <svg viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="search" id="memberSearch" placeholder="Search members..." aria-label="Search members" autocomplete="off"/>
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
             role="button" tabindex="0"
             data-id="{{ $member->id }}"
             data-name="{{ strtolower($member->name) }}"
             data-email="{{ strtolower($member->email) }}"
             id="item-{{ $member->id }}">
          <div class="member-item-left">
            @if($memberPhoto)
              <img src="{{ asset('storage/'.$memberPhoto) }}" class="member-avatar" alt="" loading="lazy"/>
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
        <div class="list-message">No members assigned yet.</div>
      @endforelse
      <div class="list-message" id="noResults" style="display:none;">No members match your search.</div>
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

    <div class="details-content" id="detailsContent">

      <div class="details-hero">
        <div class="details-avatar" id="detailsAvatar"></div>
        <div class="details-hero-text">
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
            <div class="contact-text">
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
            <div class="contact-text">
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
      $exp            = $member->expiration();   // single source of truth
      $daysRemaining  = $exp->daysRemaining ?? 0;
      $progressPct    = $exp->progress;
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

  /* ── Safety net: no grid/flex child may push the page wider than the screen ── */
  .stat-grid > *, .split-panel > *, .rate-grid > *,
  .contact-grid > *, .sub-grid > * { min-width: 0; }

  /* ── Header ── */
  .dash-head { margin-bottom: 28px; }
  .dash-head h1 { font-size: clamp(20px, 4vw, 28px); font-weight: 700; margin-bottom: 4px; overflow-wrap: anywhere; }
  .dash-head p { color: var(--muted); font-size: 14px; }

  /* ── Stat cards ── */
  .stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
  .stat-card .stat-value { font-size: clamp(22px, 3vw, 32px); }

  /* ── Rate card ── */
  .rate-card {
    margin: 20px 0 28px; background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 16px; padding: 20px 22px;
  }
  .rate-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
  .rate-card-head h2 { margin: 0; font-size: 18px; font-weight: 700; color: var(--accent); }
  .rate-card-head span { font-size: 12px; color: var(--muted); }
  .rate-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; }
  .rate-item { background: var(--surface2); border: 1px solid var(--border); border-radius: 12px; padding: 12px 14px; }
  .rate-item-label { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; }
  .rate-item-value { font-size: 18px; font-weight: 700; color: var(--text); overflow-wrap: anywhere; }

  /* ── Split panel ── */
  .split-panel {
    display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
    gap: 24px; margin-bottom: 28px; align-items: stretch;
  }

  .members-panel { display: flex; flex-direction: column; max-height: 600px; border-radius: 16px; overflow: hidden; }
  .members-panel-header {
    padding: 16px 20px; display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 10px;
  }
  .members-panel-title { font-weight: 700; font-size: 16px; color: var(--accent); margin-bottom: 0; }

  .members-search {
    display: flex; align-items: center; background: var(--surface2);
    border: 1px solid var(--border); border-radius: 40px;
    padding: 2px 14px 2px 12px; gap: 8px;
    flex: 1 1 180px; min-width: 120px; height: 44px; align-self: auto;
  }
  .members-search svg { position: static; transform: none; width: 16px; height: 16px; stroke: var(--muted); fill: none; flex-shrink: 0; }
  .members-search input {
    background: transparent; border: none; padding: 8px 0; font-size: 13px; color: var(--text);
    width: 100%; min-width: 0; outline: none; box-shadow: none; -webkit-appearance: none; appearance: none;
  }
  .members-search input::-webkit-search-cancel-button { -webkit-appearance: none; }
  .members-search input::placeholder { color: var(--muted); }
  .members-search:focus-within { border-color: var(--accent); }

  .members-list { flex: 1; overflow-y: auto; padding: 8px 0; max-height: none; -webkit-overflow-scrolling: touch; overscroll-behavior: contain; }
  .members-list::-webkit-scrollbar { width: 6px; }
  .members-list::-webkit-scrollbar-thumb { background: var(--accent-dark); border-radius: 3px; }
  .list-message { padding: 40px 20px; text-align: center; color: var(--muted); font-size: 14px; }

  .member-item {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 20px; margin-bottom: 0; border-radius: 0; border: none;
    border-left: 3px solid transparent; cursor: pointer; min-height: 56px;
    -webkit-tap-highlight-color: transparent;
  }
  .member-item:hover { background: var(--surface2); border-left-color: var(--accent); }
  .member-item:focus-visible { outline: 2px solid var(--accent); outline-offset: -2px; }
  .member-item.active-item { background: var(--accent-soft); border-left-color: var(--accent); }
  .member-item-left { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }

  .member-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); }
  .member-avatar-placeholder {
    width: 36px; height: 36px; border-radius: 50%; background: var(--accent-soft);
    border: 1px solid rgba(224,169,59,0.3); display: flex; align-items: center;
    justify-content: center; font-size: 12px; font-weight: 700; color: var(--accent); flex-shrink: 0;
  }
  .member-item-info { display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
  .member-item-name, .member-item-email { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }

  .status-pill {
    font-size: 10px; padding: 4px 12px; border-radius: 40px; text-transform: uppercase;
    letter-spacing: .3px; flex-shrink: 0; white-space: nowrap;
  }

  /* ── Details panel ── */
  .details-panel { border-radius: 16px; overflow: hidden; min-height: 400px; scroll-margin-top: 80px; }
  .details-empty { height: 100%; min-height: 320px; padding: 20px; text-align: center; }
  .details-content { display: none; flex-direction: column; padding: 0; }
  .details-content.visible { display: flex; }

  .details-hero {
    padding: 28px 28px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(224,169,59,0.08), transparent);
  }
  .details-hero-text { min-width: 0; flex: 1 1 160px; }
  .details-avatar {
    width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
    background: var(--accent-soft); border: 2px solid var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 24px; color: var(--accent); overflow: hidden;
  }
  .details-avatar img { width: 100%; height: 100%; object-fit: cover; }
  .details-name { font-size: 22px; font-weight: 800; margin-bottom: 8px; overflow-wrap: anywhere; }
  .details-body { padding: 22px 28px; }

  .section-label { font-size: 10px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; }

  .contact-grid { display: grid; gap: 10px; margin-bottom: 20px; }
  .contact-item, .sub-item, .days-container {
    background: var(--surface2); border: 1px solid var(--border); border-radius: 10px;
    transition: border-color .3s ease;
  }
  .contact-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; }
  .contact-text { min-width: 0; flex: 1; }
  .contact-item:hover, .sub-item:hover, .days-container:hover { border-color: rgba(224,169,59,0.5); }
  .contact-icon {
    width: 32px; height: 32px; border-radius: 8px; background: var(--accent-soft);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: var(--accent);
  }
  .contact-label, .sub-label, .days-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; }
  .contact-label { margin-bottom: 2px; }
  .contact-value { font-size: 13px; font-weight: 600; color: var(--text); overflow-wrap: anywhere; }

  .sub-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
  .sub-item { padding: 14px; }
  .sub-item.full-width { grid-column: span 2; }
  .sub-label { margin-bottom: 5px; }
  .sub-value { font-size: 14px; font-weight: 700; color: var(--text); overflow-wrap: anywhere; }
  .sub-value.accent { color: var(--accent); }

  .days-container { padding: 14px; margin-bottom: 20px; }
  .days-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 4px; }
  .days-value { font-size: 13px; font-weight: 700; color: var(--accent); }
  .progress-bar { background: var(--surface3); border-radius: 999px; height: 6px; overflow: hidden; }
  .progress-fill { height: 100%; border-radius: 999px; transition: width .4s ease; width: 0%; background: var(--accent); }

  .view-profile-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
    min-height: 46px; padding: 13px; margin-top: 0;
    background: linear-gradient(135deg, var(--accent-2), var(--accent-dark));
    color: #1a1a1a; font-size: 14px; font-weight: 800; border-radius: 10px; text-decoration: none;
  }
  html:root[data-theme="light"] .view-profile-btn { color: #111; }
  .view-profile-btn svg { stroke: currentColor; }

  /* ═══════════ RESPONSIVE ═══════════ */

  @media (max-width: 1024px) {
    .split-panel { grid-template-columns: minmax(0, 1fr); gap: 20px; }
    .members-panel { max-height: 440px; }
    .details-panel { min-height: 320px; }
  }

  @media (max-width: 768px) {
    .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .stat-grid > :last-child:nth-child(odd) { grid-column: 1 / -1; }
    .dash-head { margin-bottom: 20px; }
    .rate-card { padding: 16px; margin: 16px 0 22px; }
  }

  @media (max-width: 640px) {
    /* Header stacks: title on top, full-width search below.
       IMPORTANT: reset flex so the search box does not stretch vertically
       (flex-basis acts as HEIGHT in a column layout — that caused the tall search box). */
    .members-panel-header { flex-direction: column; align-items: stretch; gap: 10px; padding: 14px; }
    .members-search { flex: 0 0 auto; width: 100%; height: 44px; min-width: 0; }
    .members-search input { font-size: 16px; } /* prevents iOS zoom on focus */

    .member-item { padding: 10px 14px; }
    .member-item-email { font-size: 12px; }
    .details-hero { padding: 16px; gap: 12px; }
    .details-avatar { width: 56px; height: 56px; font-size: 20px; }
    .details-name { font-size: 18px; }
    .details-body { padding: 16px; }
    .sub-grid { grid-template-columns: 1fr; }
    .sub-item.full-width { grid-column: span 1; }
    .rate-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
  }

  @media (max-width: 420px) {
    .stat-grid { grid-template-columns: minmax(0, 1fr); }
    .status-pill { padding: 3px 9px; font-size: 9px; }
    .member-avatar, .member-avatar-placeholder { width: 32px; height: 32px; font-size: 11px; }
    .members-panel { max-height: 380px; }
  }

  @media (prefers-reduced-motion: reduce) {
    * { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
  }
</style>

<script>
(function () {
  'use strict';

  var detailsPanel   = document.getElementById('detailsPanel');
  var detailsEmpty   = document.getElementById('detailsEmpty');
  var detailsContent = document.getElementById('detailsContent');
  var memberList     = document.getElementById('membersList');
  var searchInput    = document.getElementById('memberSearch');
  var noResults      = document.getElementById('noResults');
  var isStacked      = window.matchMedia('(max-width: 1024px)');

  function setText(id, value) { document.getElementById(id).textContent = value; }

  function showMemberDetail(id, el, userInitiated) {
    document.querySelectorAll('.member-item').forEach(function (i) { i.classList.remove('active-item'); });
    el.classList.add('active-item');

    var md = document.querySelector('.md[data-id="' + id + '"]');
    if (!md) return;
    var d = md.dataset;

    // Avatar (built with DOM methods rather than innerHTML)
    var avatar = document.getElementById('detailsAvatar');
    avatar.textContent = '';
    if (d.photo) {
      var img = document.createElement('img');
      img.src = d.photo;
      img.alt = '';
      avatar.appendChild(img);
    } else {
      avatar.textContent = d.name.split(' ').filter(Boolean).map(function (w) { return w[0]; })
        .join('').substring(0, 2).toUpperCase();
    }

    setText('detailsName', d.name);

    // Status badge
    var badge = document.getElementById('detailsBadge');
    badge.textContent = '';
    var pill = document.createElement('span');
    pill.style.cssText = 'display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:100px;' +
      'font-size:12px;font-weight:700;background:' + d.statusBg + ';color:' + d.statusColor + ';' +
      'border:1px solid color-mix(in srgb, ' + d.statusColor + ' 27%, transparent);';
    var dot = document.createElement('span');
    dot.style.cssText = 'width:6px;height:6px;border-radius:50%;display:inline-block;background:' + d.statusColor + ';';
    pill.appendChild(dot);
    pill.appendChild(document.createTextNode(d.status));
    badge.appendChild(pill);

    setText('detailsEmail', d.email);
    setText('detailsPhone', d.phone);
    setText('detailsPlan', d.plan);
    setText('detailsDuration', d.duration);
    setText('detailsPeriod', d.start + ' – ' + d.end);

    var days = parseInt(d.daysRemaining, 10) || 0;
    var pct = Math.max(0, Math.min(100, parseInt(d.progressPct, 10) || 0));
    var label = document.getElementById('daysRemainingLabel');
    var bar = document.getElementById('daysRemainingBar');
    label.textContent = days === 0 ? 'Expired' : days + ' day' + (days !== 1 ? 's' : '') + ' left';
    label.style.color = d.barColor;
    bar.style.width = pct + '%';
    bar.style.background = d.barColor;

    document.getElementById('detailsViewBtn').href = d.url;

    detailsEmpty.style.display = 'none';
    detailsContent.classList.add('visible');

    // On phones/tablets the details panel sits below the list, so bring it into view after a tap
    if (userInitiated && isStacked.matches && detailsPanel) {
      detailsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function filterMembers(query) {
    var q = query.trim().toLowerCase();
    var visible = 0;
    document.querySelectorAll('.member-item').forEach(function (item) {
      var match = (item.dataset.name || '').indexOf(q) !== -1 || (item.dataset.email || '').indexOf(q) !== -1;
      item.style.display = match ? '' : 'none';
      if (match) visible++;
    });
    if (noResults) {
      noResults.style.display = (visible === 0 && document.querySelector('.member-item')) ? 'block' : 'none';
    }
  }

  // Click + keyboard (Enter / Space) on member rows via delegation
  if (memberList) {
    memberList.addEventListener('click', function (e) {
      var item = e.target.closest('.member-item');
      if (item) showMemberDetail(item.dataset.id, item, true);
    });
    memberList.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      var item = e.target.closest('.member-item');
      if (item) {
        e.preventDefault();
        showMemberDetail(item.dataset.id, item, true);
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () { filterMembers(this.value); });
  }

  // Preselect first member (without scrolling the page)
  var first = document.querySelector('.member-item');
  if (first) showMemberDetail(first.dataset.id, first, false);
})();
</script>

@endsection