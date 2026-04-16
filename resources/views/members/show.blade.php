@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', $member->name . ' – IRONFORGE')
@section('page_title', 'Member Detail')
@section('active_nav', 'members')

@section('content')

{{-- Breadcrumb --}}
<div style="display:flex; align-items:center; gap:8px; font-size:13px; margin-bottom:24px; color:var(--muted);">
    <a href="{{ route('members.index') }}" style="color:var(--muted); text-decoration:none; transition:.15s;"
       onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">Members</a>
    <span>/</span>
    <span style="color:var(--text);">{{ $member->name }}</span>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     TWO-COLUMN LAYOUT
══════════════════════════════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start;">

    {{-- ── LEFT COLUMN ─────────────────────────────────────────── --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Profile Card --}}
        <div style="background:var(--surface1); border:1px solid var(--border); border-radius:16px; padding:32px 24px; text-align:center;">

            {{-- Avatar --}}
            <div style="margin-bottom:20px;">
                @if($member->photo)
                    <img src="{{ asset('storage/' . $member->photo) }}"
                         alt="{{ $member->name }}"
                         style="width:96px; height:96px; border-radius:50%; object-fit:cover;
                                border:3px solid rgba(200,255,0,0.35); margin:0 auto; display:block;">
                @else
                    <div style="width:96px; height:96px; border-radius:50%; margin:0 auto;
                                background:rgba(200,255,0,0.08); border:3px solid rgba(200,255,0,0.35);
                                display:flex; align-items:center; justify-content:center;
                                font-size:28px; font-weight:800; color:var(--accent); letter-spacing:1px;">
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            {{-- Name + Email --}}
            <div style="font-size:20px; font-weight:700; color:var(--text); margin-bottom:6px; letter-spacing:.3px;">
                {{ $member->name }}
            </div>
            <div style="font-size:13px; color:var(--muted); margin-bottom:18px;">
                {{ $member->email }}
            </div>

            {{-- Status Badge --}}
            @php
                $end = $member->end_date ? \Carbon\Carbon::parse($member->end_date) : null;
                $daysLeft = $end ? now()->diffInDays($end, false) : null;

                if ($member->status === 'Active') {
                    if ($daysLeft !== null && $daysLeft <= 30 && $daysLeft > 0) {
                        $badgeLabel = 'Expiring Soon';
                        $badgeColor = '#facc15';
                        $badgeBg    = 'rgba(250,204,21,0.12)';
                        $badgeBorder= 'rgba(250,204,21,0.25)';
                    } else {
                        $badgeLabel = 'Active';
                        $badgeColor = '#4ade80';
                        $badgeBg    = 'rgba(74,222,128,0.12)';
                        $badgeBorder= 'rgba(74,222,128,0.25)';
                    }
                } elseif ($member->status === 'Expired') {
                    $badgeLabel = 'Expired';
                    $badgeColor = '#f87171';
                    $badgeBg    = 'rgba(248,113,113,0.12)';
                    $badgeBorder= 'rgba(248,113,113,0.25)';
                } elseif ($member->status === 'Suspended') {
                    $badgeLabel = 'Suspended';
                    $badgeColor = '#fb923c';
                    $badgeBg    = 'rgba(251,146,60,0.12)';
                    $badgeBorder= 'rgba(251,146,60,0.25)';
                } else {
                    $badgeLabel = $member->status ?? 'Unknown';
                    $badgeColor = 'var(--muted)';
                    $badgeBg    = 'rgba(255,255,255,0.05)';
                    $badgeBorder= 'var(--border)';
                }
            @endphp
            <span style="display:inline-flex; align-items:center; gap:7px; padding:8px 20px;
                         border-radius:100px; font-size:13px; font-weight:700;
                         background:{{ $badgeBg }}; color:{{ $badgeColor }};
                         border:1px solid {{ $badgeBorder }};">
                <span style="width:7px; height:7px; border-radius:50%; background:currentColor;
                             box-shadow:0 0 6px currentColor;"></span>
                {{ $badgeLabel }}
            </span>
        </div>

        {{-- Personal Info Card --}}
        <div style="background:var(--surface1); border:1px solid var(--border); border-radius:16px; padding:24px;">
            <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:2px;
                        color:var(--muted); margin-bottom:16px;">
                Personal Info
            </div>

            {{-- Phone --}}
            <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--surface2);
                        border-radius:10px; margin-bottom:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(96,165,250,0.12);
                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .99h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:3px;">Phone</div>
                    <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $member->phone ?? '—' }}</div>
                </div>
            </div>

            {{-- Gender --}}
            <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--surface2);
                        border-radius:10px; margin-bottom:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(248,113,113,0.12);
                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="#f87171" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:3px;">Gender</div>
                    <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $member->gender ?? '—' }}</div>
                </div>
            </div>

            {{-- Birthdate --}}
            <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--surface2);
                        border-radius:10px; margin-bottom:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(251,191,36,0.12);
                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:3px;">Birthdate</div>
                    <div style="font-size:14px; font-weight:600; color:var(--text);">
                        {{ $member->birthdate ? \Carbon\Carbon::parse($member->birthdate)->format('M d, Y') : '—' }}
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div style="display:flex; align-items:center; gap:14px; padding:14px; background:var(--surface2);
                        border-radius:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(74,222,128,0.12);
                            display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:3px;">Address</div>
                    <div style="font-size:14px; font-weight:600; color:var(--text);">{{ $member->address ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Back / Edit Buttons --}}
        <div style="display:flex; flex-direction:column; gap:8px;">
            @if(auth()->user()->isAdmin())
            <a href="{{ route('members.edit', $member->id) }}" {{-- FIXED: Added ->id --}}
               style="display:flex; align-items:center; justify-content:center; gap:8px;
                      padding:12px; background:var(--accent); color:#000; border-radius:10px;
                      font-weight:700; font-size:13px; text-decoration:none; transition:.15s;"
               onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit Member
            </a>
            @endif
            <a href="{{ route('members.index') }}"
               style="display:flex; align-items:center; justify-content:center; gap:8px;
                      padding:12px; background:var(--surface2); color:var(--text);
                      border:1px solid var(--border); border-radius:10px;
                      font-weight:600; font-size:13px; text-decoration:none; transition:.15s;"
               onmouseover="this.style.borderColor='rgba(255,255,255,.2)'" onmouseout="this.style.borderColor='var(--border)'">
                ← Back to Members
            </a>
        </div>
    </div>

    {{-- ── RIGHT COLUMN ────────────────────────────────────────── --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Membership Details Card --}}
        <div style="background:var(--surface1); border:1px solid var(--border); border-radius:16px; padding:28px;">
            <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:2px;
                        color:var(--muted); margin-bottom:20px;">
                Membership Details
            </div>

            {{-- Top row: Fitness Plan / Duration / Fee --}}
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:14px;">

                {{-- Fitness Plan --}}
                <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:18px 20px;">
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:10px;">
                        Fitness Plan
                    </div>
                    <div style="font-size:18px; font-weight:700; color:var(--accent);">
                        {{ $member->fitness_plan ?? $member->membership_type ?? '—' }}
                    </div>
                </div>

                {{-- Duration --}}
                <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:18px 20px;">
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:10px;">
                        Duration
                    </div>
                    <div style="font-size:18px; font-weight:700; color:var(--text);">
                        {{ $member->membership_type ?? '—' }}
                    </div>
                </div>

                {{-- Fee --}}
                <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:18px 20px;">
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:10px;">
                        Fee
                    </div>
                    <div style="font-size:18px; font-weight:700; color:var(--accent);">
                        ₱{{ number_format($member->fee ?? 0, 2) }}
                    </div>
                </div>
            </div>

            {{-- Bottom row: Start Date / End Date / Status --}}
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:20px;">

                {{-- Start Date --}}
                <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:18px 20px;">
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:10px;">
                        Start Date
                    </div>
                    <div style="font-size:18px; font-weight:700; color:var(--text);">
                        {{ $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('M d, Y') : '—' }}
                    </div>
                </div>

                {{-- End Date --}}
                <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:18px 20px;">
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:10px;">
                        End Date
                    </div>
                    <div style="font-size:18px; font-weight:700;
                        color:{{ $end && $end->isPast() ? '#f87171' : ($end && $daysLeft <= 30 ? '#facc15' : 'var(--text)') }};">
                        {{ $end ? $end->format('M d, Y') : '—' }}
                    </div>
                </div>

                {{-- Status --}}
                <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:18px 20px;">
                    <div style="font-size:10px; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:10px;">
                        Status
                    </div>
                    <div style="display:inline-flex; align-items:center; gap:7px; font-size:16px; font-weight:700; color:{{ $badgeColor }};">
                        <span style="width:7px; height:7px; border-radius:50%; background:currentColor;
                                     box-shadow:0 0 6px currentColor; flex-shrink:0;"></span>
                        {{ $badgeLabel }}
                    </div>
                </div>
            </div>

            {{-- Alert Bar --}}
            @if($end && !$end->isPast() && $daysLeft <= 30 && $daysLeft > 0)
            <div style="display:flex; align-items:center; gap:12px; padding:16px 20px;
                        background:rgba(250,204,21,0.08); border:1px solid rgba(250,204,21,0.2);
                        border-radius:10px; color:#facc15; font-size:14px; font-weight:500;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Membership expiring in {{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }}.
            </div>
            @elseif($end && $end->isPast())
            <div style="display:flex; align-items:center; gap:12px; padding:16px 20px;
                        background:rgba(248,113,113,0.08); border:1px solid rgba(248,113,113,0.2);
                        border-radius:10px; color:#f87171; font-size:14px; font-weight:500;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Membership has expired. Please renew to continue access.
            </div>
            @endif
        </div>

        {{-- Payment History Card --}}
        <div style="background:var(--surface1); border:1px solid var(--border); border-radius:16px; padding:28px;">

            {{-- Header --}}
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                <div style="font-size:18px; font-weight:700; color:var(--text);">Payment History</div>
                <div style="font-size:13px; color:var(--muted);">
                    {{ ($member->payments ?? collect())->count() }} transaction(s)
                </div>
            </div>

            {{-- Table --}}
            <div style="overflow-x:auto; border-radius:10px; border:1px solid var(--border);">
                <table style="width:100%; border-collapse:collapse; min-width:600px;">
                    <thead>
                        <tr style="background:rgba(255,255,255,0.02); border-bottom:1px solid var(--border);">
                            <th style="padding:12px 16px; text-align:left; font-size:10px; font-weight:700;
                                       text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);">Receipt</th>
                            <th style="padding:12px 16px; text-align:left; font-size:10px; font-weight:700;
                                       text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);">Plan</th>
                            <th style="padding:12px 16px; text-align:left; font-size:10px; font-weight:700;
                                       text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);">Amount</th>
                            <th style="padding:12px 16px; text-align:left; font-size:10px; font-weight:700;
                                       text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);">Date</th>
                            <th style="padding:12px 16px; text-align:left; font-size:10px; font-weight:700;
                                       text-transform:uppercase; letter-spacing:1.5px; color:var(--muted);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($member->payments ?? [] as $payment)
                        <tr style="border-bottom:1px solid var(--border); transition:.15s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.015)'"
                            onmouseout="this.style.background='transparent'">

                            {{-- Receipt --}}
                            <td style="padding:14px 16px;">
                                <span style="font-family:monospace; font-size:12px; color:var(--muted);">
                                    {{ $payment->receipt_number ?? ('RCP-' . strtoupper(substr(md5($payment->id ?? uniqid()), 0, 12))) }}
                                </span>
                            </td>

                            {{-- Plan --}}
                            <td style="padding:14px 16px; font-size:13px; color:var(--text);">
                                {{ $payment->plan ?? ($member->fitness_plan ?? $member->membership_type ?? '—') }}
                                @if($payment->plan ?? false)
                                    / {{ $payment->duration ?? $member->membership_type ?? '' }}
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td style="padding:14px 16px;">
                                <span style="font-size:14px; font-weight:700; color:var(--accent);">
                                    ₱{{ number_format($payment->amount, 2) }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td style="padding:14px 16px; font-size:13px; color:var(--muted);">
                                {{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->paid_at ?? $payment->created_at)->format('M d, Y') }}
                            </td>

                            {{-- Status --}}
                            <td style="padding:14px 16px;">
                                @php
                                    $pStatus = $payment->status ?? 'Paid';
                                    $pColor  = $pStatus === 'Paid' ? '#4ade80' : ($pStatus === 'Pending' ? '#facc15' : '#f87171');
                                    $pBg     = $pStatus === 'Paid' ? 'rgba(74,222,128,0.1)' : ($pStatus === 'Pending' ? 'rgba(250,204,21,0.1)' : 'rgba(248,113,113,0.1)');
                                @endphp
                                <span style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px;
                                             border-radius:100px; font-size:11px; font-weight:700;
                                             background:{{ $pBg }}; color:{{ $pColor }};">
                                    <span style="width:5px; height:5px; border-radius:50%; background:currentColor;"></span>
                                    {{ $pStatus }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding:48px; text-align:center; color:var(--muted); font-size:13px;">
                                No payment records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- end right column --}}
</div>{{-- end grid --}}

<style>
@media (max-width: 900px) {
    div[style*="grid-template-columns:300px 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

@endsection