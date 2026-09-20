@extends('layouts.instructor')
@section('title', 'Coach Requests – IRONFORGE')
@section('active', 'requests')

@section('content')

<style>
    :root {
        --bg: #0a0a0a;
        --surface: #111111;
        --surface2: #1a1a1a;
        --border: #2a2a2a;
        --accent: #ff2222;
        --accent-hover: #cc0000;
        --accent-glow: rgba(255,0,0,0.15);
        --text: #f0f0f0;
        --muted: #888888;
        --success: #ff4444;
        --warning: #ff6b35;
        --danger: #ff0000;
        --radius: 10px;
    }

    /* Page Header */
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-header-left h1 {
        font-size: clamp(1.3rem, 3vw, 1.8rem);
        font-weight: 800;
        margin-bottom: 4px;
        color: var(--text);
    }
    .page-header-left h1 span {
        color: var(--accent);
    }
    .page-header-left p {
        color: var(--muted);
        font-size: 13px;
    }
    .pending-badge {
        font-size: 12px;
        padding: 6px 14px;
        background: rgba(255,0,0,0.15);
        color: var(--accent);
        border: 1px solid rgba(255,0,0,0.25);
        border-radius: 100px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Section Title */
    .section-title {
        font-size: 11px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 14px;
    }

    /* Cards */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        transition: border-color 0.3s ease;
    }
    .card:hover {
        border-color: var(--accent);
    }

    /* Empty State */
    .empty-state {
        padding: 60px;
        text-align: center;
    }
    .empty-state svg {
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        display: block;
        opacity: 0.2;
        stroke: currentColor;
        fill: none;
    }
    .empty-state-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text);
    }
    .empty-state-sub {
        font-size: 13px;
        color: var(--muted);
    }

    /* Request Item */
    .request-item {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s ease;
    }
    .request-item:last-child {
        border-bottom: none;
    }
    .request-item:hover {
        background: rgba(255,0,0,0.02);
    }
    .request-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border);
        flex-shrink: 0;
    }
    .request-avatar-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255,0,0,0.08);
        border: 2px solid rgba(255,0,0,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 800;
        color: var(--accent);
        flex-shrink: 0;
        font-family: 'Bebas Neue', sans-serif;
        letter-spacing: 1px;
    }
    .request-info {
        flex: 1;
        min-width: 0;
    }
    .request-name {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 3px;
        color: var(--text);
    }
    .request-contact {
        font-size: 12px;
        color: var(--muted);
    }
    .request-message {
        margin-top: 10px;
        font-size: 12px;
        color: rgba(240, 240, 240, 0.6);
        background: var(--surface2);
        border-left: 2px solid var(--accent);
        padding: 6px 12px;
        border-radius: 0 6px 6px 0;
        font-style: italic;
    }
    .request-meta {
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .request-time {
        font-size: 11px;
        color: var(--muted);
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }
    .badge-plan {
        background: rgba(255,68,68,0.12);
        color: var(--accent);
        border: 1px solid rgba(255,68,68,0.15);
    }
    .badge-monthly {
        background: rgba(255,68,68,0.12);
        color: var(--accent);
        border: 1px solid rgba(255,68,68,0.15);
    }
    .badge-quarterly {
        background: rgba(255,107,53,0.12);
        color: var(--warning);
        border: 1px solid rgba(255,107,53,0.15);
    }
    .badge-annual {
        background: rgba(255,68,68,0.12);
        color: var(--success);
        border: 1px solid rgba(255,68,68,0.15);
    }
    .badge-active {
        background: rgba(255,68,68,0.15);
        color: var(--success);
        border: 1px solid rgba(255,68,68,0.15);
    }
    .badge-expired {
        background: rgba(255,0,0,0.15);
        color: var(--danger);
        border: 1px solid rgba(255,0,0,0.15);
    }
    .badge-pending {
        background: rgba(255,107,53,0.15);
        color: var(--warning);
        border: 1px solid rgba(255,107,53,0.15);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: var(--radius);
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .btn-sm {
        padding: 6px 14px;
        font-size: 12px;
    }
    .btn-primary {
        background: var(--accent);
        color: #000;
        box-shadow: 0 4px 15px rgba(255,0,0,0.3);
    }
    .btn-primary:hover {
        background: var(--accent-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 25px rgba(255,0,0,0.4);
    }
    .btn-secondary {
        background: var(--surface2);
        color: var(--text);
        border: 1px solid var(--border);
    }
    .btn-secondary:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: rgba(255,0,0,0.05);
    }
    .btn-danger {
        color: var(--danger);
        border-color: rgba(255,0,0,0.3);
        background: transparent;
        border: 1px solid rgba(255,0,0,0.3);
    }
    .btn-danger:hover {
        background: rgba(255,0,0,0.1);
        border-color: var(--danger);
    }
    .btn svg {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
    }
    .btn-primary svg {
        stroke: #000;
    }

    .request-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    /* Table */
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }
    thead {
        background: var(--surface2);
        border-bottom: 2px solid var(--accent);
    }
    th {
        padding: 12px 20px;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
        white-space: nowrap;
    }
    td {
        padding: 14px 20px;
        font-size: 13px;
        border-top: 1px solid var(--border);
        vertical-align: middle;
        color: var(--text);
    }
    tr:hover td {
        background: rgba(255,0,0,0.02);
    }

    .table-member {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .table-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--surface2);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: var(--muted);
        flex-shrink: 0;
    }
    .table-member-name {
        font-weight: 600;
        color: var(--text);
    }
    .table-member-email {
        font-size: 12px;
        color: var(--muted);
    }

    /* Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: var(--surface);
    }
    ::-webkit-scrollbar-thumb {
        background: var(--accent);
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: var(--accent-hover);
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .request-item {
        animation: fadeInUp 0.3s ease forwards;
        opacity: 0;
    }
    .request-item:nth-child(1) { animation-delay: 0.05s; }
    .request-item:nth-child(2) { animation-delay: 0.1s; }
    .request-item:nth-child(3) { animation-delay: 0.15s; }
    .request-item:nth-child(4) { animation-delay: 0.2s; }
    .request-item:nth-child(5) { animation-delay: 0.25s; }

    /* ===== RESPONSIVE ===== */

    @media (max-width: 1024px) {
        .request-item {
            padding: 16px 20px;
            gap: 16px;
            flex-wrap: wrap;
        }
        .request-actions {
            width: 100%;
            justify-content: flex-end;
        }
        table {
            min-width: 450px;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        .pending-badge {
            align-self: flex-start;
        }
        .request-item {
            padding: 14px 16px;
            gap: 12px;
        }
        .request-avatar,
        .request-avatar-placeholder {
            width: 40px;
            height: 40px;
            font-size: 13px;
        }
        .request-name {
            font-size: 14px;
        }
        .request-message {
            font-size: 11px;
            padding: 4px 10px;
        }
        .request-actions {
            width: 100%;
            justify-content: flex-start;
            gap: 6px;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 11px;
        }
        .btn-sm svg {
            width: 11px;
            height: 11px;
        }
        .empty-state {
            padding: 40px 20px;
        }
        table {
            min-width: 400px;
        }
        th, td {
            padding: 10px 14px;
            font-size: 12px;
        }
        .table-member-email {
            font-size: 11px;
        }
    }

    @media (max-width: 480px) {
        .page-header-left h1 {
            font-size: 1.3rem;
        }
        .request-item {
            padding: 12px 14px;
            gap: 10px;
        }
        .request-avatar,
        .request-avatar-placeholder {
            width: 36px;
            height: 36px;
            font-size: 11px;
        }
        .request-name {
            font-size: 13px;
        }
        .request-contact {
            font-size: 11px;
        }
        .request-message {
            font-size: 10px;
            padding: 4px 8px;
            margin-top: 6px;
        }
        .request-meta {
            gap: 6px;
        }
        .badge {
            font-size: 9px;
            padding: 2px 8px;
        }
        .request-time {
            font-size: 10px;
        }
        .btn-sm {
            padding: 4px 8px;
            font-size: 10px;
        }
        .btn-sm svg {
            width: 10px;
            height: 10px;
        }
        .empty-state {
            padding: 30px 16px;
        }
        .empty-state svg {
            width: 32px;
            height: 32px;
        }
        .empty-state-title {
            font-size: 14px;
        }
        .empty-state-sub {
            font-size: 12px;
        }
        table {
            min-width: 350px;
        }
        th, td {
            padding: 8px 10px;
            font-size: 11px;
        }
        .table-avatar {
            width: 26px;
            height: 26px;
            font-size: 9px;
        }
        .table-member-name {
            font-size: 12px;
        }
        .table-member-email {
            font-size: 10px;
        }
        .section-title {
            font-size: 10px;
        }
    }

    @media (max-width: 360px) {
        .request-item {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        .request-avatar,
        .request-avatar-placeholder {
            width: 44px;
            height: 44px;
            font-size: 14px;
            align-self: center;
        }
        .request-info {
            text-align: center;
        }
        .request-message {
            text-align: left;
        }
        .request-meta {
            justify-content: center;
        }
        .request-actions {
            justify-content: center;
        }
        table {
            min-width: 300px;
        }
        th, td {
            padding: 6px 8px;
            font-size: 10px;
        }
    }
</style>

<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <h1>Coach <span>Requests</span></h1>
            <p>Review and respond to incoming member requests.</p>
        </div>
        @if($pending->count())
            <span class="pending-badge">
                {{ $pending->count() }} pending
            </span>
        @endif
    </div>

    {{-- PENDING --}}
    <div style="margin-bottom:36px;">
        <div class="section-title">Pending Requests</div>

        @if($pending->isEmpty())
            <div class="card">
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <div class="empty-state-title">All caught up!</div>
                    <div class="empty-state-sub">No pending requests at the moment.</div>
                </div>
            </div>
        @else
            <div class="card">
                @foreach($pending as $req)
                @php $member = $req->member; $user = $member?->user; @endphp
                <div class="request-item">
                    @if($user && $user->photo)
                        <img src="{{ asset('storage/'.$user->photo) }}" class="request-avatar"/>
                    @else
                        <div class="request-avatar-placeholder">
                            {{ strtoupper(substr($member->name ?? 'M', 0, 2)) }}
                        </div>
                    @endif

                    <div class="request-info">
                        <div class="request-name">{{ $member->name ?? 'Unknown Member' }}</div>
                        <div class="request-contact">
                            {{ $user->email ?? '—' }}
                            @if($user?->phone)
                                &nbsp;·&nbsp;{{ $user->phone }}
                            @endif
                        </div>
                        @if($req->message)
                            <div class="request-message">"{{ $req->message }}"</div>
                        @endif
                        <div class="request-meta">
                            @if($member?->fitness_plan)
                                <span class="badge badge-plan">{{ $member->fitness_plan }}</span>
                            @endif
                            @if($member?->membership_type)
                                <span class="badge badge-{{ strtolower($member->membership_type) }}">{{ $member->membership_type }}</span>
                            @endif
                            <span class="request-time">{{ $req->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="request-actions">
                        <form action="{{ route('instructor.requests.approve', $req->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg viewBox="0 0 24 24" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('instructor.requests.reject', $req->id) }}" method="POST" onsubmit="return confirm('Decline this request?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
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
        <div class="section-title">Recent History</div>
        <div class="card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Plan</th>
                            <th>Duration</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $h)
                        <tr>
                            <td>
                                <div class="table-member">
                                    <div class="table-avatar">
                                        {{ strtoupper(substr($h->member->name ?? 'M', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="table-member-name">{{ $h->member->name ?? '—' }}</div>
                                        <div class="table-member-email">{{ $h->member->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--muted);font-size:13px;">
                                {{ $h->member->fitness_plan ?? '—' }}
                            </td>
                            <td>
                                @if($h->member?->membership_type)
                                    <span class="badge badge-{{ strtolower($h->member->membership_type) }}">
                                        {{ $h->member->membership_type }}
                                    </span>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td style="font-size:13px;color:var(--muted);">
                                {{ $h->updated_at->format('M d, Y') }}
                            </td>
                            <td>
                                @if($h->status === 'approved')
                                    <span class="badge badge-active">Approved</span>
                                @else
                                    <span class="badge badge-expired">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>

@endsection