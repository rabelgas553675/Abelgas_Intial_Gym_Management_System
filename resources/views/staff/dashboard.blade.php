@extends('layouts.staff')
@section('title', 'Staff Dashboard – APEX')

@section('content')

{{-- Page Header --}}
<div style="margin-bottom:32px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    Welcome, <span style="color:var(--accent);">{{ explode(' ', auth()->user()->name)[0] }}</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">Staff overview of gym members and transactions</p>
</div>

{{-- Stat Cards --}}
<div class="stat-grid">

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">Total Members</div>
      <div class="stat-value">{{ $stats['total'] }}</div>
      <div class="stat-sub stat-up">All time registrations</div>
    </div>
    <div class="stat-icon icon-red">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">Active Members</div>
      <div class="stat-value">{{ $stats['active'] }}</div>
      <div class="stat-sub">Currently enrolled</div>
    </div>
    <div class="stat-icon icon-red-dark">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <circle cx="12" cy="12" r="8" stroke="var(--success)" fill="none"/>
        <circle cx="12" cy="12" r="3" fill="var(--success)" stroke="none"/>
      </svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:28px;color:var(--accent);">
        ₱{{ number_format($thisMonth ?? 0, 0) }}
      </div>
      <div class="stat-sub">Monthly revenue</div>
    </div>
    <div class="stat-icon icon-red-bright">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0
                 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1
                 M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">Total Collected</div>
      <div class="stat-value" style="font-size:28px;color:var(--accent);">
        ₱{{ number_format($totalCollected ?? 0, 0) }}
      </div>
      <div class="stat-sub">All time revenue</div>
    </div>
    <div class="stat-icon" style="background:rgba(255,0,0,0.15);color:var(--accent);">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0
                 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946
                 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138
                 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806
                 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438
                 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
      </svg>
    </div>
  </div>

</div>

{{-- Split Panel: Members List + Member Detail --}}
<div class="split-panel" style="margin-bottom:24px;">

  {{-- LEFT: Members List --}}
  <div class="members-panel">
    <div class="members-panel-header">
      <div class="members-panel-title">Members List</div>
      <div class="members-search">
        <svg viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" id="memberSearch" placeholder="Search members..."
               oninput="filterMembers(this.value)"/>
      </div>
    </div>

    <div class="members-list" id="membersList">
      @forelse($members as $member)
        @php
          $end        = $member->end_date;
          $isExpired  = $end && $end->isPast();
          $isExpiring = $end && !$isExpired && now()->diffInDays($end) <= 7;
          $pillClass  = $isExpired  ? 'pill-expired'
                      : ($isExpiring ? 'pill-expiring'
                      : 'pill-active');
          $pillLabel  = $isExpired  ? 'Expired'
                      : ($isExpiring ? 'Expiring'
                      : 'Active');
          $memberPhoto = $member->user?->photo ?? $member->photo ?? null;
        @endphp
        <div class="member-item"
             data-name="{{ strtolower($member->name) }}"
             data-email="{{ strtolower($member->email) }}"
             onclick="showMemberDetail({{ $member->id }}, this)"
             id="item-{{ $member->id }}">
          <div class="member-item-left">
            @if($memberPhoto)
              <img src="{{ asset('storage/'.$memberPhoto) }}"
                   class="member-avatar"/>
            @else
              <div class="member-avatar-placeholder">
                {{ strtoupper(substr($member->name, 0, 2)) }}
              </div>
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
          No members found.
        </div>
      @endforelse
    </div>
  </div>

  {{-- RIGHT: Member Detail Panel --}}
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

      {{-- Hero row --}}
      <div class="details-hero">
        <div id="detailsAvatar" class="details-avatar"></div>
        <div>
          <div id="detailsName" class="details-name"></div>
          <div id="detailsBadge"></div>
        </div>
      </div>

      {{-- Body --}}
      <div class="details-body">

        <div class="section-label">Contact Information</div>

        <div class="contact-grid">
          <div class="contact-item">
            <div class="contact-icon email-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                   stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8
                         M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <div class="contact-label">Email</div>
              <div id="detailsEmail" class="contact-value"></div>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-icon phone-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                   stroke="#ff4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493
                         a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516
                         l1.13-2.257a1 1 0 011.21-.502l4.493 1.498
                         a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
            </div>
            <div>
              <div class="contact-label">Phone</div>
              <div id="detailsPhone" class="contact-value"></div>
            </div>
          </div>
        </div>

        <div class="section-label">Subscription</div>

        <div class="subscription-grid">
          <div class="subscription-item">
            <div class="subscription-label">Plan</div>
            <div id="detailsPlan" class="subscription-value accent"></div>
          </div>
          <div class="subscription-item">
            <div class="subscription-label">Duration</div>
            <div id="detailsDuration" class="subscription-value"></div>
          </div>
          <div class="subscription-item full-width">
            <div class="subscription-label">Active Period</div>
            <div id="detailsPeriod" class="subscription-value"></div>
          </div>
        </div>

        {{-- Days Remaining Bar --}}
        <div class="days-remaining-container">
          <div class="days-remaining-header">
            <div class="days-remaining-label">Days Remaining</div>
            <div id="daysRemainingLabel" class="days-remaining-value"></div>
          </div>
          <div class="progress-bar">
            <div id="daysRemainingBar" class="progress-fill"></div>
          </div>
        </div>

        <a id="detailsViewBtn" href="#" class="view-profile-btn"
           onmouseover="this.style.background='#cc0000'"
           onmouseout="this.style.background='var(--accent)'">
          View Full Profile
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="#111" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
      $end         = $member->end_date;
      $isExpired   = $end && $end->isPast();
      $isExpiring  = $end && !$isExpired && now()->diffInDays($end) <= 7;

      $statusLabel = $isExpired  ? 'Expired'
                   : ($isExpiring ? 'Expiring Soon'
                   : 'Active');
      $statusColor = $isExpired  ? '#ff3333'
                   : ($isExpiring ? '#ff6b35'
                   : '#ff4444');
      $statusBg    = $isExpired  ? 'rgba(255,51,51,0.15)'
                   : ($isExpiring ? 'rgba(255,107,53,0.15)'
                   : 'rgba(255,68,68,0.15)');
      $barColor    = $statusColor;

      $daysRemaining = $isExpired ? 0 : (int) now()->diffInDays($end);
      $totalDays     = ($member->start_date && $end)
                         ? (int) $member->start_date->diffInDays($end)
                         : 30;
      $progressPct   = $totalDays > 0
                         ? min(100, round(($daysRemaining / $totalDays) * 100))
                         : 0;

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
         data-end="{{ $end?->format('M d, Y') ?? '—' }}"
         data-status="{{ $statusLabel }}"
         data-status-color="{{ $statusColor }}"
         data-status-bg="{{ $statusBg }}"
         data-days-remaining="{{ $daysRemaining }}"
         data-progress-pct="{{ $progressPct }}"
         data-bar-color="{{ $barColor }}"
         data-photo="{{ $memberPhotoUrl }}"
         data-url="{{ route('members.show', $member) }}">
    </div>
  @endforeach
</div>

{{-- Recent Payments --}}
<div class="payments-container">
  <div class="payments-header">
    <div class="payments-title">Recent Payments</div>
    <a href="{{ route('staff.payments') }}" class="view-all-btn">
      View All →
    </a>
  </div>
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>Transaction ID</th>
          <th>Member</th>
          <th>Plan</th>
          <th>Amount</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentPayments as $p)
        <tr>
          <td class="transaction-id">
            {{ $p->receipt_number }}
          </td>
          <td>
            @php
              $pPhoto = $p->member?->user?->photo ?? $p->member?->photo ?? null;
            @endphp
            <div class="member-cell">
              @if($pPhoto)
                <img src="{{ asset('storage/'.$pPhoto) }}" class="payment-avatar"/>
              @else
                <div class="payment-avatar-placeholder">
                  {{ strtoupper(substr($p->member?->name ?? '?', 0, 2)) }}
                </div>
              @endif
              <div>
                <div class="payment-member-name">{{ $p->member?->name ?? '—' }}</div>
                <div class="payment-member-email">{{ $p->member?->email ?? '' }}</div>
              </div>
            </div>
          </td>
          <td class="payment-plan">
            {{ $p->fitness_plan }} / {{ $p->membership_type }}
          </td>
          <td class="payment-amount">
            ₱{{ number_format($p->amount, 2) }}
          </td>
          <td class="payment-date">
            {{ $p->payment_date->format('M d, Y') }}
          </td>
          <td>
            <span class="payment-status">
              <span class="status-dot"></span>
              {{ $p->status }}
            </span>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="padding:40px;text-align:center;color:var(--muted);">
            No payment records yet.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<style>
  /* ===== BLACK & RED THEME ===== */
  
  /* Base styles */
  :root {
    --accent: #ff2222;
    --accent-hover: #cc0000;
    --accent-glow: rgba(255,0,0,0.3);
    --muted: #888888;
    --border: #2a2a2a;
    --surface: #0a0a0a;
    --surface2: #141414;
    --success: #ff4444;
    --warning: #ff6b35;
    --danger: #ff0000;
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
  }

  body {
    background: #000000;
    color: var(--text-primary);
  }

  /* Stat Grid */
  .stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
  }

  .stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    border-color: var(--accent);
    box-shadow: 0 0 20px rgba(255,0,0,0.1);
  }

  .stat-card-left {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .stat-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
  }

  .stat-value {
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: var(--text-primary);
  }

  .stat-sub {
    font-size: 11px;
    color: var(--muted);
    margin-top: 2px;
  }

  .stat-up { color: var(--accent); }

  .stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .stat-icon svg {
    width: 24px;
    height: 24px;
    stroke: currentColor;
    fill: none;
  }

  .icon-red { background: rgba(255,0,0,0.12); color: var(--accent); }
  .icon-red-dark { background: rgba(200,0,0,0.15); color: #ff3333; }
  .icon-red-bright { background: rgba(255,50,50,0.15); color: #ff4444; }

  /* Split Panel */
  .split-panel {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 24px;
    margin-bottom: 28px;
  }

  /* Members Panel */
  .members-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 600px;
  }

  .members-panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
  }

  .members-panel-title {
    font-weight: 700;
    font-size: 16px;
    color: var(--accent);
  }

  .members-search {
    display: flex;
    align-items: center;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 40px;
    padding: 4px 14px 4px 10px;
    gap: 6px;
    flex: 1 1 180px;
    min-width: 120px;
  }

  .members-search svg {
    width: 16px;
    height: 16px;
    stroke: var(--muted);
    flex-shrink: 0;
  }

  .members-search input {
    background: transparent;
    border: none;
    padding: 8px 0;
    font-size: 13px;
    color: var(--text-primary);
    width: 100%;
    outline: none;
  }

  .members-search input::placeholder { color: var(--muted); }
  .members-search input:focus { color: var(--accent); }

  .members-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
  }

  .members-list::-webkit-scrollbar {
    width: 6px;
  }

  .members-list::-webkit-scrollbar-track {
    background: var(--surface);
  }

  .members-list::-webkit-scrollbar-thumb {
    background: var(--accent);
    border-radius: 3px;
  }

  .member-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    gap: 10px;
  }

  .member-item:hover {
    background: var(--surface2);
    border-left-color: var(--accent);
  }

  .member-item.active-item {
    background: rgba(255,0,0,0.08);
    border-left-color: var(--accent);
  }

  .member-item-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex: 1;
  }

  .member-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid var(--border);
  }

  .member-avatar-placeholder {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,0,0,0.15);
    border: 1px solid rgba(255,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: var(--accent);
    flex-shrink: 0;
  }

  .member-item-info {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
    min-width: 0;
  }

  .member-item-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .member-item-email {
    font-size: 11px;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .status-pill {
    font-size: 10px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 40px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .pill-active { background: rgba(255,68,68,0.2); color: #ff4444; border: 1px solid rgba(255,68,68,0.3); }
  .pill-expiring { background: rgba(255,107,53,0.2); color: #ff6b35; border: 1px solid rgba(255,107,53,0.3); }
  .pill-expired { background: rgba(255,0,0,0.2); color: #ff0000; border: 1px solid rgba(255,0,0,0.3); }

  /* Details Panel */
  .details-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    min-height: 400px;
  }

  .details-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    min-height: 320px;
    color: var(--muted);
    padding: 20px;
    text-align: center;
  }

  .details-empty svg {
    width: 48px;
    height: 48px;
    stroke: var(--border);
    margin-bottom: 8px;
  }

  .details-content {
    display: none;
    flex-direction: column;
  }

  .details-content.visible {
    display: flex;
  }

  .details-hero {
    padding: 28px 28px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, rgba(255,0,0,0.05), transparent);
  }

  .details-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    flex-shrink: 0;
    background: rgba(255,0,0,0.12);
    border: 2px solid var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 26px;
    color: var(--accent);
    overflow: hidden;
  }

  .details-name {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 8px;
    color: var(--text-primary);
  }

  .details-body {
    padding: 22px 28px;
  }

  .section-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 12px;
  }

  .contact-grid {
    display: grid;
    gap: 10px;
    margin-bottom: 20px;
  }

  .contact-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--surface2);
    border-radius: 10px;
    border: 1px solid var(--border);
    flex-wrap: wrap;
    transition: border-color 0.3s ease;
  }

  .contact-item:hover {
    border-color: var(--accent);
  }

  .contact-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .email-icon { background: rgba(255,68,68,0.15); }
  .phone-icon { background: rgba(255,68,68,0.15); }

  .contact-label {
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 2px;
  }

  .contact-value {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    word-break: break-word;
  }

  .subscription-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 20px;
  }

  .subscription-item {
    padding: 14px;
    background: var(--surface2);
    border-radius: 10px;
    border: 1px solid var(--border);
    transition: border-color 0.3s ease;
  }

  .subscription-item:hover {
    border-color: var(--accent);
  }

  .subscription-item.full-width {
    grid-column: span 2;
  }

  .subscription-label {
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
  }

  .subscription-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
  }

  .subscription-value.accent {
    color: var(--accent);
  }

  .days-remaining-container {
    padding: 14px;
    background: var(--surface2);
    border-radius: 10px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
  }

  .days-remaining-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 4px;
  }

  .days-remaining-label {
    font-size: 10px;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .days-remaining-value {
    font-size: 13px;
    font-weight: 700;
    color: var(--accent);
  }

  .progress-bar {
    background: var(--border);
    border-radius: 999px;
    height: 6px;
    overflow: hidden;
  }

  .progress-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.4s ease;
    width: 0%;
    background: var(--accent);
  }

  .view-profile-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px;
    background: var(--accent);
    color: #000000;
    font-size: 14px;
    font-weight: 800;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid transparent;
  }

  .view-profile-btn:hover {
    background: var(--accent-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,0,0,0.3);
  }

  /* Payments */
  .payments-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
  }

  .payments-header {
    padding: 18px 24px 14px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
  }

  .payments-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--accent);
  }

  .view-all-btn {
    font-size: 12px;
    color: var(--accent);
    text-decoration: none;
    font-weight: 600;
    padding: 5px 13px;
    border: 1px solid rgba(255,0,0,0.3);
    border-radius: 6px;
    transition: all 0.3s ease;
  }

  .view-all-btn:hover {
    background: var(--accent);
    color: #000000;
    border-color: var(--accent);
  }

  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    min-width: 700px;
  }

  th {
    text-align: left;
    padding: 14px 16px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--accent);
    border-bottom: 2px solid var(--accent);
    font-weight: 700;
  }

  td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    color: var(--text-secondary);
  }

  tr:hover td {
    background: rgba(255,0,0,0.03);
  }

  tr:last-child td { border-bottom: none; }

  .transaction-id {
    font-family: monospace;
    font-size: 11px;
    color: var(--muted);
  }

  .member-cell {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .payment-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid rgba(255,0,0,0.3);
  }

  .payment-avatar-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,0,0,0.15);
    border: 1px solid rgba(255,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: var(--accent);
    flex-shrink: 0;
  }

  .payment-member-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
  }

  .payment-member-email {
    font-size: 11px;
    color: var(--muted);
  }

  .payment-plan {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary);
  }

  .payment-amount {
    font-size: 14px;
    font-weight: 700;
    color: var(--accent);
  }

  .payment-date {
    font-size: 13px;
    color: var(--muted);
  }

  .payment-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    background: rgba(255,68,68,0.15);
    color: var(--accent);
    border: 1px solid rgba(255,68,68,0.2);
  }

  .status-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--accent);
    display: inline-block;
    animation: pulse-dot 1.5s infinite;
  }

  @keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
  }

  /* ===== RESPONSIVE BREAKPOINTS ===== */

  /* Tablets & small desktops */
  @media (max-width: 1024px) {
    .stat-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .split-panel {
      grid-template-columns: 1fr;
    }

    .members-panel {
      max-height: 420px;
    }

    .details-panel {
      min-height: 320px;
    }
  }

  /* Mobile phones */
  @media (max-width: 640px) {
    .stat-grid {
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .stat-card {
      padding: 14px;
    }

    .stat-value {
      font-size: 24px;
    }

    .stat-icon {
      width: 36px;
      height: 36px;
    }

    .stat-icon svg {
      width: 20px;
      height: 20px;
    }

    .split-panel {
      gap: 16px;
    }

    .members-panel-header {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }

    .members-search {
      flex: 1;
    }

    .member-item {
      padding: 10px 14px;
      flex-wrap: wrap;
      gap: 6px;
    }

    .member-item-left {
      flex: 1;
      min-width: 120px;
    }

    .status-pill {
      font-size: 9px;
      padding: 3px 10px;
    }

    .details-hero {
      padding: 16px !important;
      gap: 12px;
    }

    .details-avatar {
      width: 56px;
      height: 56px;
      font-size: 20px;
    }

    .details-name {
      font-size: 18px;
    }

    .details-body {
      padding: 16px !important;
    }

    .subscription-grid {
      grid-template-columns: 1fr !important;
    }

    .subscription-item.full-width {
      grid-column: span 1 !important;
    }

    .contact-item {
      flex-wrap: wrap;
    }

    .payments-header {
      padding: 14px 16px;
    }

    table {
      min-width: 600px;
      font-size: 12px;
    }

    th, td {
      padding: 10px 12px;
      white-space: nowrap;
    }

    .payment-amount {
      font-size: 13px;
    }
  }

  /* Small phones */
  @media (max-width: 400px) {
    .stat-grid {
      grid-template-columns: 1fr;
    }

    .stat-card {
      padding: 12px;
    }

    .stat-value {
      font-size: 22px;
    }

    .member-item-name {
      font-size: 13px;
    }

    .member-item-email {
      font-size: 10px;
    }
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
                  border:1px solid ${md.dataset.statusColor}44;">
       <span style="width:6px;height:6px;border-radius:50%;
                    background:${md.dataset.statusColor};display:inline-block;"></span>
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
    const match = (item.dataset.name || '').includes(q)
               || (item.dataset.email || '').includes(q);
    item.style.display = match ? '' : 'none';
  });
}

// Auto-select first member on load
document.addEventListener('DOMContentLoaded', function() {
  const first = document.querySelector('.member-item');
  if (first) {
    first.click();
  }
});
</script>

@endsection