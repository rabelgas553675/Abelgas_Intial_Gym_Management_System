@extends('layouts.instructor')
@section('title', 'Coach Requests – APEX')
@section('active', 'requests')

@section('content')

<style>
    /* Colors come from layouts/instructor.blade.php (charcoal & gold) */
    .page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
    .page-header-left h1 { font-size:28px; font-weight:700; margin-bottom:4px; color:var(--text); }
    .page-header-left h1 span { color:var(--accent); }
    .page-header-left p { color:var(--muted); font-size:14px; }
    .pending-badge {
        font-size:12px; padding:6px 14px; font-weight:700; white-space:nowrap;
        background:var(--accent-soft); color:var(--accent);
        border:1px solid rgba(224,169,59,0.3); border-radius:100px;
    }

    .section-title { font-size:10px; font-weight:700; color:var(--accent); text-transform:uppercase; letter-spacing:2px; margin-bottom:12px; }

    .card { background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; transition:border-color .3s ease; }
    .card:hover { border-color:rgba(224,169,59,0.5); }

    .empty-state { padding:60px 20px; text-align:center; }
    .empty-state svg { width:44px; height:44px; margin:0 auto 12px; display:block; opacity:.3; stroke:var(--accent); fill:none; }
    .empty-state-title { font-size:15px; font-weight:700; margin-bottom:4px; color:var(--text); }
    .empty-state-sub { font-size:13px; color:var(--muted); }

    .request-item { padding:20px 24px; display:flex; align-items:center; gap:20px; border-bottom:1px solid var(--border); border-left:3px solid transparent; transition:all .2s ease; }
    .request-item:last-child { border-bottom:none; }
    .request-item:hover { background:var(--surface2); border-left-color:var(--accent); }
    .request-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; border:1px solid var(--border); flex-shrink:0; }
    .request-avatar-placeholder {
        width:48px; height:48px; border-radius:50%; flex-shrink:0;
        background:var(--accent-soft); border:1px solid rgba(224,169,59,0.3);
        display:flex; align-items:center; justify-content:center;
        font-size:15px; font-weight:700; color:var(--accent);
    }
    .request-info { flex:1; min-width:0; }
    .request-name { font-size:15px; font-weight:700; margin-bottom:3px; color:var(--text); }
    .request-contact { font-size:12px; color:var(--muted); }
    .request-message {
        margin-top:10px; font-size:12px; color:var(--muted); font-style:italic;
        background:var(--surface2); border-left:2px solid var(--accent);
        padding:6px 12px; border-radius:0 6px 6px 0;
    }
    .request-meta { margin-top:8px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .request-time { font-size:11px; color:var(--muted); }

    /* Badges */
    .badge { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:40px; font-size:11px; font-weight:600; white-space:nowrap; }
    .badge-plan, .badge-monthly { background:var(--accent-soft); color:var(--accent); border:1px solid rgba(224,169,59,0.25); }
    .badge-quarterly, .badge-pending { background:color-mix(in srgb, var(--warning) 15%, transparent); color:var(--warning); border:1px solid color-mix(in srgb, var(--warning) 27%, transparent); }
    .badge-annual, .badge-active { background:color-mix(in srgb, var(--success) 15%, transparent); color:var(--success); border:1px solid color-mix(in srgb, var(--success) 27%, transparent); }
    .badge-expired { background:color-mix(in srgb, var(--danger) 15%, transparent); color:var(--danger); border:1px solid color-mix(in srgb, var(--danger) 27%, transparent); }

    /* Buttons */
    .btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; border:none; text-decoration:none; transition:all .25s ease; white-space:nowrap; }
    .btn-sm { padding:6px 14px; font-size:12px; }
    .btn-primary { background:linear-gradient(135deg, var(--accent-2), var(--accent-dark)); color:#1a1a1a; }
    .btn-primary:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(224,169,59,0.3); }
    .btn-secondary { background:var(--surface2); color:var(--text); border:1px solid var(--border); }
    .btn-secondary:hover { border-color:var(--accent); color:var(--accent); }
    .btn-danger { background:transparent; color:var(--danger); border:1px solid color-mix(in srgb, var(--danger) 35%, transparent); }
    .btn-danger:hover { background:color-mix(in srgb, var(--danger) 12%, transparent); border-color:var(--danger); }
    .btn svg { width:13px; height:13px; flex-shrink:0; stroke:currentColor; fill:none; }
    .request-actions { display:flex; gap:8px; flex-shrink:0; }

    /* Table */
    .table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    table { width:100%; border-collapse:collapse; min-width:500px; }
    thead { background:var(--surface2); border-bottom:2px solid var(--accent); }
    th { padding:12px 20px; text-align:left; font-size:10px; font-weight:700; color:var(--accent); text-transform:uppercase; letter-spacing:2px; white-space:nowrap; }
    td { padding:14px 20px; font-size:13px; border-top:1px solid var(--border); vertical-align:middle; color:var(--text); }
    tr:hover td { background:var(--surface2); }
    .table-member { display:flex; align-items:center; gap:10px; }
    .table-avatar {
        width:32px; height:32px; border-radius:50%; flex-shrink:0;
        background:var(--accent-soft); border:1px solid rgba(224,169,59,0.3);
        display:flex; align-items:center; justify-content:center;
        font-size:11px; font-weight:700; color:var(--accent);
    }
    .table-member-name { font-weight:600; color:var(--text); }
    .table-member-email { font-size:12px; color:var(--muted); }

    /* Scrollbar */
    ::-webkit-scrollbar { width:6px; height:6px; }
    ::-webkit-scrollbar-track { background:var(--surface); }
    ::-webkit-scrollbar-thumb { background:var(--accent-dark); border-radius:3px; }

    /* Animation */
    @keyframes fadeInUp { from { opacity:0; transform:translateY(15px); } to { opacity:1; transform:translateY(0); } }
    .request-item { animation:fadeInUp .3s ease forwards; opacity:0; }
    .request-item:nth-child(1) { animation-delay:.05s; }
    .request-item:nth-child(2) { animation-delay:.1s; }
    .request-item:nth-child(3) { animation-delay:.15s; }
    .request-item:nth-child(4) { animation-delay:.2s; }
    .request-item:nth-child(5) { animation-delay:.25s; }

    /* Held-payment box, rejection reason, reject modal */
    .hold-box { margin-top:10px; padding:10px 14px; border-radius:10px; font-size:12px; color:var(--muted);
        background:var(--surface2); border:1px dashed rgba(224,169,59,0.35); display:flex; flex-wrap:wrap; gap:6px 16px; }
    .hold-box strong { color:var(--text); }
    .reason-text { font-size:11px; color:var(--muted); margin-top:4px; max-width:280px; white-space:normal; }
    .modal-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.6); display:none; align-items:center; justify-content:center; z-index:1000; padding:16px; }
    .modal-backdrop.open { display:flex; }
    .modal-box { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:24px; width:100%; max-width:440px; }
    .modal-box h3 { font-size:16px; font-weight:700; margin-bottom:6px; color:var(--text); }
    .modal-box p { font-size:12px; color:var(--muted); margin-bottom:14px; }
    .modal-box textarea { width:100%; min-height:96px; resize:vertical; padding:10px 12px; border-radius:10px;
        background:var(--surface2); color:var(--text); border:1px solid var(--border); font:inherit; font-size:13px; }
    .modal-box textarea:focus { outline:none; border-color:var(--accent); }
    .modal-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:14px; }

    /* Responsive */
    @media (max-width:1024px) {
        .request-item { padding:16px 20px; gap:16px; flex-wrap:wrap; }
        .request-actions { width:100%; justify-content:flex-end; }
    }
    @media (max-width:768px) {
        .page-header { flex-direction:column; align-items:stretch; gap:8px; }
        .pending-badge { align-self:flex-start; }
        .request-item { padding:14px 16px; gap:12px; }
        .request-avatar, .request-avatar-placeholder { width:40px; height:40px; font-size:13px; }
        .request-name { font-size:14px; }
        .request-actions { justify-content:flex-start; gap:6px; }
        table { min-width:400px; }
        th, td { padding:10px 14px; font-size:12px; }
    }
    @media (max-width:480px) {
        .page-header-left h1 { font-size:1.3rem; }
        .request-avatar, .request-avatar-placeholder { width:36px; height:36px; font-size:11px; }
        .request-message { font-size:11px; padding:4px 8px; }
        .badge { font-size:9px; padding:2px 8px; }
        .btn-sm { padding:4px 8px; font-size:10px; }
        table { min-width:350px; }
        th, td { padding:8px 10px; font-size:11px; }
    }
    @media (max-width:360px) {
        .request-item { flex-direction:column; align-items:stretch; gap:8px; }
        .request-avatar, .request-avatar-placeholder { align-self:center; }
        .request-info { text-align:center; }
        .request-message { text-align:left; }
        .request-meta, .request-actions { justify-content:center; }
    }
</style>

<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <h1>Coach <span>Requests</span></h1>
            <p>Review and respond to incoming member requests.</p>
        </div>
        @if($pending->count() + $planPending->count())
            <span class="pending-badge">
                {{ $pending->count() + $planPending->count() }} pending
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

                        @if($req->isManual())
                            <div class="hold-box">
                                <span>Coaching package: <strong>{{ $req->coach_membership_type ?? '—' }}</strong></span>
                                @if($req->payment)
                                    <span>Payment on hold: <strong>₱{{ number_format((float) $req->payment->amount, 2) }}</strong></span>
                                @endif
                                <span>Start:
                                    <strong>
                                        @if($req->starts_on && $req->starts_on->isFuture())
                                            {{ $req->starts_on->format('M d, Y') }}
                                        @else
                                            Upon your approval
                                        @endif
                                    </strong>
                                </span>
                                @if($req->requester)
                                    <span>Recorded by: <strong>{{ $req->requester->name }}</strong></span>
                                @endif
                            </div>
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
                        <button type="button" class="btn btn-danger btn-sm js-reject"
                                data-action="{{ route('instructor.requests.reject', $req->id) }}"
                                data-member="{{ $member->name ?? 'this member' }}">
                            <svg viewBox="0 0 24 24" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                            Reject
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- PLAN CHANGE REQUESTS (members assigned to this coach) --}}
    <div style="margin-bottom:36px;" id="plan-change-requests">
        <div class="section-title">Plan Change Requests</div>

        @if($planPending->isEmpty())
            <div class="card">
                <div class="empty-state">
                    <div class="empty-state-title">No plan change requests</div>
                    <div class="empty-state-sub">When one of your members asks to switch fitness plans, it will appear here.</div>
                </div>
            </div>
        @else
            <div class="card">
                @foreach($planPending as $pc)
                @php $pcMember = $pc->member; $pcUser = $pcMember?->user; @endphp
                <div class="request-item">
                    @if($pcUser && $pcUser->photo)
                        <img src="{{ asset('storage/'.$pcUser->photo) }}" class="request-avatar" alt=""/>
                    @else
                        <div class="request-avatar-placeholder">{{ strtoupper(substr($pcMember->name ?? 'M', 0, 2)) }}</div>
                    @endif

                    <div class="request-info">
                        <div class="request-name">{{ $pcMember->full_name ?? 'Unknown Member' }}</div>
                        <div class="request-contact">{{ $pcUser->email ?? '—' }}</div>

                        <div class="hold-box">
                            <span>Current plan: <strong>{{ $pc->current_plan ?: '—' }}</strong></span>
                            <span>Requested plan: <strong>{{ $pc->requested_plan }}</strong></span>
                            <span>Requested: <strong>{{ ($pc->requested_at ?? $pc->created_at)->format('M d, Y g:i A') }}</strong></span>
                        </div>

                        @if($pc->reason)
                            <div class="request-message">"{{ $pc->reason }}"</div>
                        @endif

                        <div class="request-meta">
                            <span class="badge badge-pending">Pending</span>
                            <span class="request-time">{{ ($pc->requested_at ?? $pc->created_at)->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="request-actions">
                        <form action="{{ route('instructor.plan-changes.approve', $pc->id) }}" method="POST"
                              onsubmit="return confirm('Approve the change to {{ $pc->requested_plan }}?');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg viewBox="0 0 24 24" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Approve Request
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm js-reject-plan"
                                data-action="{{ route('instructor.plan-changes.reject', $pc->id) }}"
                                data-member="{{ $pcMember->full_name ?? 'this member' }}"
                                data-plan="{{ $pc->requested_plan }}">
                            <svg viewBox="0 0 24 24" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Reject Request
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- HISTORY --}}
    @if(count($history) > 0)
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
                                @php $duration = $h->coach_membership_type ?? $h->member?->membership_type; @endphp
                                @if($duration)
                                    <span class="badge badge-{{ strtolower($duration) }}">{{ $duration }}</span>
                                @else
                                    <span style="color:var(--muted)">—</span>
                                @endif
                            </td>
                            <td style="font-size:13px;color:var(--muted);">
                                {{ ($h->responded_at ?? $h->updated_at)->format('M d, Y') }}
                            </td>
                            <td>
                                @if($h->status === 'approved')
                                    @if($h->isScheduled())
                                        <span class="badge badge-pending">Scheduled · {{ $h->starts_on?->format('M d, Y') }}</span>
                                    @else
                                        <span class="badge badge-active">Approved</span>
                                    @endif
                                @else
                                    <span class="badge badge-expired">Rejected</span>
                                    @if($h->rejection_reason)
                                        <div class="reason-text">{{ $h->rejection_reason }}</div>
                                    @endif
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

    {{-- PLAN CHANGE HISTORY --}}
    @if($planHistory->isNotEmpty())
    <div style="margin-top:36px;">
        <div class="section-title">Plan Change History</div>
        <div class="card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr><th>Member</th><th>Change</th><th>Reviewed</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($planHistory as $ph)
                        <tr>
                            <td>
                                <div class="table-member">
                                    <div class="table-avatar">{{ strtoupper(substr($ph->member->name ?? 'M', 0, 2)) }}</div>
                                    <div>
                                        <div class="table-member-name">{{ $ph->member->full_name ?? '—' }}</div>
                                        <div class="table-member-email">{{ $ph->member->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--muted);font-size:13px;">{{ $ph->current_plan ?: '—' }} → {{ $ph->requested_plan }}</td>
                            <td style="font-size:13px;color:var(--muted);">{{ $ph->reviewed_at?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                @if($ph->isApproved())
                                    <span class="badge badge-active">Approved</span>
                                @else
                                    <span class="badge badge-expired">Rejected</span>
                                    @if($ph->coach_feedback)
                                        <div class="reason-text">{{ $ph->coach_feedback }}</div>
                                    @endif
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

    {{-- REJECT MODAL (reason required) --}}
    <div class="modal-backdrop" id="rejectModal">
        <form class="modal-box" id="rejectForm" method="POST">
            @csrf
            <h3>Reject request</h3>
            <p id="rejectMember">Tell the admin/staff why you are declining. The payment will not be recorded and no coaching will be assigned.</p>
            <textarea name="rejection_reason" id="rejectReason" required minlength="5" maxlength="500"
                      placeholder="e.g. My schedule is full for that period."></textarea>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary btn-sm" id="rejectCancel">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Reject request</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const modal  = document.getElementById('rejectModal');
            const form   = document.getElementById('rejectForm');
            const reason = document.getElementById('rejectReason');
            const label  = document.getElementById('rejectMember');

            document.querySelectorAll('.js-reject').forEach(btn => {
                btn.addEventListener('click', () => {
                    form.action = btn.dataset.action;
                    label.textContent = 'Why are you declining ' + btn.dataset.member + '? The payment will not be recorded and no coaching will be assigned.';
                    reason.value = '';
                    modal.classList.add('open');
                    reason.focus();
                });
            });

            const close = () => modal.classList.remove('open');
            document.getElementById('rejectCancel').addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
        })();
    </script>

    {{-- REJECT PLAN CHANGE MODAL (reason required) --}}
    <div class="modal-backdrop" id="rejectPlanModal">
        <form class="modal-box" id="rejectPlanForm" method="POST">
            @csrf
            <h3>Reject plan change</h3>
            <p id="rejectPlanLabel">Tell the member why you are declining. Their current plan stays unchanged.</p>
            <textarea name="rejection_reason" id="rejectPlanReason" required minlength="5" maxlength="500"
                      placeholder="e.g. Let's finish this training block before switching."></textarea>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary btn-sm" id="rejectPlanCancel">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Reject request</button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const modal  = document.getElementById('rejectPlanModal');
            const form   = document.getElementById('rejectPlanForm');
            const reason = document.getElementById('rejectPlanReason');
            const label  = document.getElementById('rejectPlanLabel');

            document.querySelectorAll('.js-reject-plan').forEach(btn => {
                btn.addEventListener('click', () => {
                    form.action = btn.dataset.action;
                    label.textContent = 'Why are you declining ' + btn.dataset.member + '\'s change to ' + btn.dataset.plan + '? Their current plan stays unchanged.';
                    reason.value = '';
                    modal.classList.add('open');
                    reason.focus();
                });
            });

            const close = () => modal.classList.remove('open');
            document.getElementById('rejectPlanCancel').addEventListener('click', close);
            modal.addEventListener('click', e => { if (e.target === modal) close(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
        })();
    </script>

</div>

@endsection