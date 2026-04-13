@extends('layouts.staff')
@section('title', 'Staff Dashboard – IRONFORGE')

@section('content')

{{-- Page Header --}}
<div style="margin-bottom:32px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">
    Welcome, <span style="color:var(--accent);">{{ explode(' ', auth()->user()->name)[0] }}</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">Staff overview of gym members and transactions</p>
</div>

{{-- Stat Cards --}}
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">

  <div class="stat-card">
    <div class="stat-card-left">
      <div class="stat-label">Total Members</div>
      <div class="stat-value">{{ $stats['total'] }}</div>
      <div class="stat-sub stat-up">All time registrations</div>
    </div>
    <div class="stat-icon icon-green">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
    </div>
  </div>

  <div class="stat-card orange">
    <div class="stat-card-left">
      <div class="stat-label">Active Members</div>
      <div class="stat-value">{{ $stats['active'] }}</div>
      <div class="stat-sub">Currently enrolled</div>
    </div>
    <div class="stat-icon icon-orange">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <circle cx="12" cy="12" r="8" stroke="var(--success)" fill="none"/>
        <circle cx="12" cy="12" r="3" fill="var(--success)" stroke="none"/>
      </svg>
    </div>
  </div>

  <div class="stat-card yellow">
    <div class="stat-card-left">
      <div class="stat-label">This Month</div>
      <div class="stat-value" style="font-size:28px;color:var(--warning);">
        ₱{{ number_format($thisMonth ?? 0, 0) }}
      </div>
      <div class="stat-sub">Monthly revenue</div>
    </div>
    <div class="stat-icon icon-yellow">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
  </div>

  <div class="stat-card green">
    <div class="stat-card-left">
      <div class="stat-label">Total Collected</div>
      <div class="stat-value" style="font-size:28px;color:var(--success);">
        ₱{{ number_format($totalCollected ?? 0, 0) }}
      </div>
      <div class="stat-sub">All time revenue</div>
    </div>
    <div class="stat-icon" style="background:rgba(74,222,128,0.1);color:var(--success);">
      <svg viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
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
      @forelse($recent as $member)
        @php
          $end        = $member->end_date ? \Carbon\Carbon::parse($member->end_date) : null;
          $isExpired  = $end && $end->isPast();
          $isExpiring = $end && !$isExpired && $end->diffInDays(now()) <= 7;
          $pillClass  = $isExpired  ? 'pill-expired'  :
                       ($isExpiring ? 'pill-expiring' : 'pill-active');
          $pillLabel  = $isExpired  ? 'Expired'       :
                       ($isExpiring ? 'Expiring'      : 'Active');
        @endphp
        <div class="member-item"
             data-name="{{ strtolower($member->name) }}"
             data-email="{{ strtolower($member->email) }}"
             onclick="showMemberDetail({{ $member->id }}, this)"
             id="item-{{ $member->id }}">
          <div style="display:flex;align-items:center;gap:10px;">
            @if($member->photo)
              <img src="{{ asset('storage/'.$member->photo) }}"
                   style="width:36px;height:36px;border-radius:50%;object-fit:cover;
                          flex-shrink:0;border:1px solid var(--border);"/>
            @else
              <div style="width:36px;height:36px;border-radius:50%;
                          background:rgba(200,255,0,0.08);border:1px solid rgba(200,255,0,0.15);
                          display:flex;align-items:center;justify-content:center;
                          font-size:12px;font-weight:700;color:var(--accent);flex-shrink:0;">
                {{ strtoupper(substr($member->name,0,2)) }}
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
      <div style="padding:28px 28px 20px;border-bottom:1px solid var(--border);
                  display:flex;align-items:center;gap:20px;">
        <div id="detailsAvatar"
             style="width:72px;height:72px;border-radius:50%;flex-shrink:0;
                    background:rgba(200,255,0,0.08);border:2px solid rgba(200,255,0,0.2);
                    display:flex;align-items:center;justify-content:center;
                    font-family:'Bebas Neue',sans-serif;font-size:26px;color:var(--accent);
                    overflow:hidden;"></div>
        <div>
          <div id="detailsName" style="font-size:22px;font-weight:800;margin-bottom:8px;"></div>
          <div id="detailsBadge"></div>
        </div>
      </div>

      {{-- Body --}}
      <div style="padding:22px 28px;">

        <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;
                    letter-spacing:2px;margin-bottom:12px;">Contact Information</div>

        <div style="display:grid;gap:10px;margin-bottom:20px;">
          {{-- Email --}}
          <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;
                      background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(96,165,250,0.1);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                   stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Email</div>
              <div id="detailsEmail" style="font-size:13px;font-weight:600;"></div>
            </div>
          </div>

          {{-- Phone --}}
          <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;
                      background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(74,222,128,0.1);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                   stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21
                         l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502
                         l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
            </div>
            <div>
              <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:2px;">Phone</div>
              <div id="detailsPhone" style="font-size:13px;font-weight:600;"></div>
            </div>
          </div>
        </div>

        <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;
                    letter-spacing:2px;margin-bottom:12px;">Subscription</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
          <div style="padding:14px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Plan</div>
            <div id="detailsPlan" style="font-size:14px;font-weight:700;color:var(--accent);"></div>
          </div>
          <div style="padding:14px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Duration</div>
            <div id="detailsDuration" style="font-size:14px;font-weight:700;"></div>
          </div>
          <div style="padding:14px;background:var(--surface2);border-radius:10px;
                      border:1px solid var(--border);grid-column:span 2;">
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Active Period</div>
            <div id="detailsPeriod" style="font-size:14px;font-weight:700;"></div>
          </div>
        </div>

        <a id="detailsViewBtn" href="#"
           style="display:flex;align-items:center;justify-content:center;gap:8px;
                  width:100%;padding:13px;background:var(--accent);color:#111;
                  font-size:14px;font-weight:800;border-radius:10px;text-decoration:none;
                  transition:all 0.15s;" onmouseover="this.style.background='#b8ef00'"
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
  @foreach($recent as $member)
    @php
      $end         = $member->end_date ? \Carbon\Carbon::parse($member->end_date) : null;
      $isExpired   = $end && $end->isPast();
      $isExpiring  = $end && !$isExpired && $end->diffInDays(now()) <= 7;
      $statusLabel = $isExpired ? 'Expired' : ($isExpiring ? 'Expiring Soon' : 'Active');
      $statusColor = $isExpired ? '#f87171' : ($isExpiring ? '#fbbf24' : '#4ade80');
      $statusBg    = $isExpired ? 'rgba(248,113,113,0.15)' : ($isExpiring ? 'rgba(251,191,36,0.15)' : 'rgba(74,222,128,0.15)');
    @endphp
    <div class="md"
         data-id="{{ $member->id }}"
         data-name="{{ $member->name }}"
         data-email="{{ $member->email }}"
         data-phone="{{ $member->phone ?? '—' }}"
         data-plan="{{ $member->fitness_plan ?? '—' }}"
         data-duration="{{ $member->membership_type ?? '—' }}"
         data-start="{{ $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('M d, Y') : '—' }}"
         data-end="{{ $end ? $end->format('M d, Y') : '—' }}"
         data-status="{{ $statusLabel }}"
         data-status-color="{{ $statusColor }}"
         data-status-bg="{{ $statusBg }}"
         data-photo="{{ $member->photo ? asset('storage/'.$member->photo) : '' }}"
         data-url="{{ route('members.show', $member) }}">
    </div>
  @endforeach
</div>

{{-- Recent Payments --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;">
  <div style="padding:18px 24px 14px;border-bottom:1px solid var(--border);
              display:flex;align-items:center;justify-content:space-between;">
    <div style="font-size:16px;font-weight:700;">Recent Payments</div>
    <a href="{{ route('staff.payments') }}"
       style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600;
              padding:5px 13px;border:1px solid rgba(200,255,0,0.2);border-radius:6px;">
      View All →
    </a>
  </div>
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
        <td style="font-family:monospace;font-size:11px;color:var(--muted);">
          {{ $p->receipt_number }}
        </td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            @if($p->member?->photo)
              <img src="{{ asset('storage/'.$p->member->photo) }}"
                   style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;"/>
            @else
              <div style="width:32px;height:32px;border-radius:50%;background:rgba(200,255,0,0.08);
                          border:1px solid rgba(200,255,0,0.15);display:flex;align-items:center;
                          justify-content:center;font-size:11px;font-weight:700;
                          color:var(--accent);flex-shrink:0;">
                {{ strtoupper(substr($p->member?->name ?? '?', 0, 2)) }}
              </div>
            @endif
            <div>
              <div style="font-size:13px;font-weight:600;">{{ $p->member?->name ?? '—' }}</div>
              <div style="font-size:11px;color:var(--muted);">{{ $p->member?->email ?? '' }}</div>
            </div>
          </div>
        </td>
        <td style="font-size:13px;font-weight:600;">
          {{ $p->fitness_plan }} / {{ $p->membership_type }}
        </td>
        <td style="font-size:14px;font-weight:700;color:var(--accent);">
          ₱{{ number_format($p->amount, 2) }}
        </td>
        <td style="font-size:13px;color:var(--muted);">
          {{ $p->payment_date->format('M d, Y') }}
        </td>
        <td>
          <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 12px;
                       border-radius:6px;font-size:11px;font-weight:700;
                       background:rgba(74,222,128,0.15);color:#4ade80;">
            <span style="width:5px;height:5px;border-radius:50%;background:#4ade80;display:inline-block;"></span>
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

<script>
function showMemberDetail(id, el) {
  document.querySelectorAll('.member-item').forEach(i => i.classList.remove('active-item'));
  el.classList.add('active-item');

  const md = document.querySelector(`.md[data-id="${id}"]`);
  if (!md) return;

  const avatar   = document.getElementById('detailsAvatar');
  const initials = md.dataset.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
  avatar.innerHTML = md.dataset.photo
    ? `<img src="${md.dataset.photo}" style="width:100%;height:100%;object-fit:cover;"/>`
    : initials;

  document.getElementById('detailsName').textContent = md.dataset.name;
  document.getElementById('detailsBadge').innerHTML  =
    `<span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;
      border-radius:100px;font-size:12px;font-weight:700;
      background:${md.dataset.statusBg};color:${md.dataset.statusColor};
      border:1px solid ${md.dataset.statusColor}33;">
      <span style="width:6px;height:6px;border-radius:50%;
                   background:${md.dataset.statusColor};display:inline-block;"></span>
      ${md.dataset.status}
    </span>`;

  document.getElementById('detailsEmail').textContent    = md.dataset.email;
  document.getElementById('detailsPhone').textContent    = md.dataset.phone;
  document.getElementById('detailsPlan').textContent     = md.dataset.plan;
  document.getElementById('detailsDuration').textContent = md.dataset.duration;
  document.getElementById('detailsPeriod').textContent   = `${md.dataset.start} – ${md.dataset.end}`;
  document.getElementById('detailsViewBtn').href         = md.dataset.url;

  document.getElementById('detailsEmpty').style.display = 'none';
  document.getElementById('detailsContent').classList.add('visible');
}

function filterMembers(query) {
  const q = query.toLowerCase();
  document.querySelectorAll('.member-item').forEach(item => {
    const match = (item.dataset.name  || '').includes(q)
               || (item.dataset.email || '').includes(q);
    item.style.display = match ? '' : 'none';
  });
}
</script>

@endsection