@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')

@section('title', 'Members – IRONFORGE')
@section('page_title', 'Members')
@section('active_nav', 'members')

@section('content')

{{-- ══ Inline toolbar ══════════════════════════════════════ --}}
<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:24px; flex-wrap:wrap;">

    {{-- Search + Filters --}}
    <form method="GET" action="{{ route('members.index') }}"
        style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">

        <div style="position:relative;">
            <svg width="14" height="14" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24"
                style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none; opacity:0.5;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search members..."
                style="padding:10px 14px 10px 36px; background:var(--surface2);
                        border:1px solid var(--border); border-radius:8px;
                        color:var(--text); font-size:13px; width:230px; outline:none;
                        transition:border-color .15s;"
                onfocus="this.style.borderColor='var(--accent)'"
                onblur="this.style.borderColor='var(--border)'">
        </div>

        {{-- Plan Filter --}}
        <select name="plan" class="custom-select-iron"
                style="padding:10px 14px; background:var(--surface2); border:1px solid var(--border);
                    border-radius:8px; color:{{ request('plan') ? 'var(--text)' : 'var(--muted)' }};
                    font-size:13px; outline:none; cursor:pointer;">
            <option value="">All Plans</option>
            @foreach(['Monthly','Quarterly','Semi-Annual','Annual'] as $p)
                <option value="{{ $p }}" {{ request('plan')==$p?'selected':'' }}>{{ $p }}</option>
            @endforeach
        </select>

        {{-- Status Filter --}}
        <select name="status" class="custom-select-iron"
                style="padding:10px 14px; background:var(--surface2); border:1px solid var(--border);
                    border-radius:8px; color:{{ request('status') ? 'var(--text)' : 'var(--muted)' }};
                    font-size:13px; outline:none; cursor:pointer;">
            <option value="">All Status</option>
            <option value="Active"  {{ request('status')=='Active' ?'selected':'' }}>Active</option>
            <option value="Expired" {{ request('status')=='Expired'?'selected':'' }}>Expired</option>
        </select>

        {{-- Role Filter --}}
        <select name="role" class="custom-select-iron"
                style="padding:10px 14px; background:var(--surface2); border:1px solid var(--border);
                    border-radius:8px; color:{{ request('role') ? 'var(--text)' : 'var(--muted)' }};
                    font-size:13px; outline:none; cursor:pointer;">
            <option value="">All Roles</option>
            <option value="Staff"      {{ request('role')=='Staff'      ? 'selected' : '' }}>Staff</option>
            <option value="Instructor" {{ request('role')=='Instructor' ? 'selected' : '' }}>Instructor</option>
        </select>

        <button type="submit"
                style="padding:10px 18px; background:var(--surface2); border:1px solid var(--border);
                    border-radius:8px; color:var(--text); font-size:13px; font-weight:600;
                    cursor:pointer; transition:.15s;"
                onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
            Filter
        </button>

        @if(request('search') || request('plan') || request('status') || request('role'))
            <a href="{{ route('members.index') }}"
            style="padding:10px 14px; color:var(--muted); font-size:12px; text-decoration:none;
                    border-radius:8px; border:1px solid transparent; transition:.15s;"
            onmouseover="this.style.color='var(--text)'"
            onmouseout="this.style.color='var(--muted)'">
                ✕ Clear
            </a>
        @endif
    </form>

    @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
        <a href="{{ route('members.create') }}"
        style="padding:10px 22px; background:var(--accent); color:#000; border-radius:8px;
                font-weight:700; font-size:13px; text-decoration:none; display:inline-flex;
                align-items:center; gap:8px; white-space:nowrap; transition:.15s; border:none;"
        onmouseover="this.style.opacity='.88'"
        onmouseout="this.style.opacity='1'">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Member
        </a>
    @endif
</div>

@if(request('search') || request('plan') || request('status') || request('role'))
<div style="margin-bottom:14px; font-size:13px; color:var(--muted);">
    Showing <strong style="color:var(--text);">{{ $members->total() }}</strong> result(s)
    @if(request('search')) for "<strong style="color:var(--accent);">{{ request('search') }}</strong>"@endif
    @if(request('plan')) · Plan: <strong style="color:var(--accent);">{{ request('plan') }}</strong>@endif
    @if(request('status')) · Status: <strong style="color:var(--accent);">{{ request('status') }}</strong>@endif
    @if(request('role')) · Role: <strong style="color:var(--accent);">{{ request('role') }}</strong>@endif
</div>
@endif

{{-- Table --}}
<div style="overflow-x:auto; border:1px solid var(--border); border-radius:12px; background:var(--surface1);">
    <table style="width:100%; border-collapse:collapse; text-align:left; min-width:900px;">
        <thead>
            <tr style="background:rgba(255,255,255,0.02); border-bottom:1px solid var(--border);">
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; width:44px;">#</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; min-width:200px;">Name</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Phone</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; width:110px;">Plan</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; width:100px;">Role</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; width:100px;">Status</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; width:110px; white-space:nowrap;">Start Date</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; width:110px; white-space:nowrap;">Due Date</th>
                <th style="padding:14px 16px; color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; text-align:right; width:180px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
            @php
                $memberId  = is_object($member) && isset($member->id) ? $member->id : (is_array($member) ? $member['id'] : null);
                $isUserRow = in_array(strtolower($member->role ?? ''), ['staff', 'instructor']);
            @endphp
            <tr style="border-bottom:1px solid var(--border); transition:.15s;"
                onmouseover="this.style.background='rgba(255,255,255,0.015)'"
                onmouseout="this.style.background='transparent'">

                <td style="padding:14px 16px; color:var(--muted); font-size:13px;">
                    {{-- Correct paginated row number calculation --}}
                   {{ $members->firstItem() + $loop->index }}
                </td>

                <td style="padding:14px 16px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}"
                                style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:1px solid var(--border); flex-shrink:0;">
                        @else
                            <div style="width:36px; height:36px; border-radius:50%; flex-shrink:0;
                                background:rgba(200,255,0,0.1); border:1px solid rgba(200,255,0,0.2);
                                display:flex; align-items:center; justify-content:center;
                                font-size:12px; font-weight:700; color:var(--accent);">
                                {{ strtoupper(substr($member->name ?? ($member->first_name ?? '?'), 0, 2)) }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <div style="font-weight:600; color:var(--text); font-size:14px; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $member->name ?? trim(($member->first_name ?? '').' '.($member->last_name ?? '')) }}
                            </div>
                            <div style="font-size:11px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $member->email }}
                            </div>
                        </div>
                    </div>
                </td>

                <td style="padding:14px 16px; color:var(--muted); font-size:13px; white-space:nowrap;">
                    {{ $member->phone ?? '—' }}
                </td>

                <td style="padding:14px 16px;">
                    <span style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;
                        background:rgba(96,165,250,0.1); color:#60a5fa; border:1px solid rgba(96,165,250,0.15);
                        white-space:nowrap;">
                        {{ $member->membership_type ?? '—' }}
                    </span>
                </td>

                {{-- Role Badge --}}
                <td style="padding:14px 16px;">
                    @php
                        $role = $member->role ?? null;
                        $roleColor  = match($role) {
                            'Staff'      => '#a78bfa',
                            'Instructor' => '#fb923c',
                            'Member'     => '#4ade80',
                            default      => 'var(--muted)',
                        };
                        $roleBg     = match($role) {
                            'Staff'      => 'rgba(167,139,250,0.1)',
                            'Instructor' => 'rgba(251,146,60,0.1)',
                            'Member'     => 'rgba(74,222,128,0.1)',
                            default      => 'rgba(255,255,255,0.04)',
                        };
                        $roleBorder = match($role) {
                            'Staff'      => 'rgba(167,139,250,0.2)',
                            'Instructor' => 'rgba(251,146,60,0.2)',
                            'Member'     => 'rgba(74,222,128,0.2)',
                            default      => 'var(--border)',
                        };
                    @endphp
                    <span style="padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;
                        background:{{ $roleBg }}; color:{{ $roleColor }}; border:1px solid {{ $roleBorder }};
                        white-space:nowrap;">
                        {{ $role ?? '—' }}
                    </span>
                </td>

                <td style="padding:14px 16px;">
                    @php
                        $isActive  = ($member->status ?? '') === 'Active';
                        $isExpired = ($member->status ?? '') === 'Expired';
                        $clr = $isActive ? '#4ade80' : ($isExpired ? '#f87171' : '#facc15');
                        $bg  = $isActive ? 'rgba(74,222,128,0.1)' : ($isExpired ? 'rgba(248,113,113,0.1)' : 'rgba(250,204,21,0.1)');
                    @endphp
                    <span style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px;
                        border-radius:6px; font-size:11px; font-weight:700; white-space:nowrap;
                        background:{{ $bg }}; color:{{ $clr }};">
                        <span style="width:5px; height:5px; border-radius:50%; background:currentColor; flex-shrink:0;
                            {{ $isActive ? 'box-shadow:0 0 6px currentColor;' : '' }}"></span>
                        {{ $member->status ?? '—' }}
                    </span>
                </td>

                <td style="padding:14px 16px; color:var(--muted); font-size:13px; white-space:nowrap;">
                    {{ isset($member->start_date) && $member->start_date ? \Carbon\Carbon::parse($member->start_date)->format('Y-m-d') : '—' }}
                </td>

                <td style="padding:14px 16px; white-space:nowrap;">
                    @if(isset($member->end_date) && $member->end_date)
                        @php $due = \Carbon\Carbon::parse($member->end_date); @endphp
                        <span style="font-weight:700; font-size:13px;
                            color:{{ $due->isPast() ? '#f87171' : ($due->diffInDays(now()) <= 7 ? '#facc15' : 'var(--accent)') }};">
                            {{ $due->format('Y-m-d') }}
                        </span>
                    @else
                        <span style="color:var(--muted);">—</span>
                    @endif
                </td>

                <td style="padding:14px 16px; text-align:right; white-space:nowrap;">
                    <div style="display:inline-flex; gap:6px; align-items:center;">

                        @if(!$isUserRow)
                            <a href="{{ route('members.show', $memberId) }}" class="btn-pill">View</a>
                            @if(auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <a href="{{ route('members.edit', $memberId) }}" class="btn-pill">Edit</a>
                                <form method="POST" action="{{ route('members.destroy', $memberId) }}"
                                    onsubmit="return confirm('Delete this member?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-pill btn-pill-danger">Delete</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('users.show', $memberId) }}" class="btn-pill">View</a>
                        @endif

                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="9" style="padding:80px; text-align:center; color:var(--muted);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24" style="display:block; margin:0 auto 12px; opacity:.3;">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    No members found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination UI --}}
    @if($members->hasPages())
    <div style="padding:16px 20px; border-top:1px solid var(--border);
                display:flex; align-items:center; justify-content:space-between;">
        <div style="font-size:12px; color:var(--muted);">
            Showing {{ $members->firstItem() }}–{{ $members->lastItem() }} of {{ $members->total() }}
        </div>
        {{ $members->withQueryString()->links() }}
    </div>
    @endif
</div>

<style>
.custom-select-iron {
    appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23aaaaaa' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
    background-size: 11px !important;
    padding-right: 36px !important;
}
.btn-pill {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: .15s;
    display: inline-block;
}
.btn-pill:hover { background: var(--surface2); border-color: rgba(255,255,255,.15); }
.btn-pill-danger:hover { color: #f87171; border-color: #f87171; background: rgba(248,113,113,.06); }
</style>

@endsection