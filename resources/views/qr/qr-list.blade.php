@extends('layouts.admin')
@section('title', 'QR Codes – APEX')

@section('content')
<style>
    /* ===== THEME =====
       Uses the global tokens from layouts/admin.blade.php
       (--accent, --surface, --surface2, --border, --text, --muted, --success, --info, --warning)
       and the global .btn / .btn-primary / .btn-secondary styles. */

    .qr-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        text-align: center;
        padding: 24px;
        color: var(--text);
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .qr-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.35);
        border-color: color-mix(in srgb, var(--accent) 35%, var(--border));
    }

    .badge-role {
        font-size: 10px;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        border: 1px solid transparent;
    }

    .badge-member {
        background: color-mix(in srgb, var(--success) 15%, transparent);
        color: var(--success);
        border-color: color-mix(in srgb, var(--success) 30%, transparent);
    }
    .badge-instructor {
        background: color-mix(in srgb, var(--info) 15%, transparent);
        color: var(--info);
        border-color: color-mix(in srgb, var(--info) 30%, transparent);
    }
    .badge-admin {
        background: color-mix(in srgb, var(--accent) 15%, transparent);
        color: var(--accent);
        border-color: color-mix(in srgb, var(--accent) 30%, transparent);
    }
    .badge-staff {
        background: color-mix(in srgb, var(--warning) 15%, transparent);
        color: var(--warning);
        border-color: color-mix(in srgb, var(--warning) 30%, transparent);
    }
    .badge-default {
        background: color-mix(in srgb, var(--muted) 15%, transparent);
        color: var(--muted);
        border-color: color-mix(in srgb, var(--muted) 30%, transparent);
    }

    .qr-search-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        padding: 24px;
        border-radius: 16px;
        margin-bottom: 32px;
    }

    .qr-label {
        font-size: 11px;
        color: var(--muted);
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }

    .qr-search-input,
    .qr-select {
        background: var(--surface2);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 10px;
        border-radius: 8px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
        height: 42px;
    }
    .qr-search-input::placeholder { color: var(--muted); }
    .qr-search-input:focus,
    .qr-select:focus { border-color: var(--accent); }

    .scanner-id {
        margin-top: 14px;
        padding: 8px 10px;
        border: 1px dashed var(--border);
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
    }
    .scanner-id .lbl { color: var(--muted); text-transform: uppercase; letter-spacing: .5px; }
    .scanner-id .num { color: var(--accent); font-weight: 800; font-size: 14px; }

    @media print {
        .no-print, .btn, nav, .sidebar, header {
            display: none !important;
        }
        .container-fluid, .content-wrapper, main {
            padding: 0 !important;
            margin: 0 !important;
        }
        .qr-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 16px !important;
        }
        .qr-card {
            break-inside: avoid;
            page-break-inside: avoid;
            margin-bottom: 20px;
            border: 1px solid #eee !important;
            box-shadow: none !important;
            background: #fff !important;
            color: #000 !important;
        }
        .qr-card * { color: #000 !important; }
        .badge-role {
            background: #eee !important;
            color: #000 !important;
            border-color: #ccc !important;
        }
    }
</style>

@php
    // $group, $search, $members, $staffList come from AttendanceController@qrList
    $isStaffGroup = ($group ?? 'members') === 'staff';
    $displayList  = $isStaffGroup ? $staffList : $members;
    $resultCount  = $displayList->count();
@endphp

{{-- ─── Page Header ────────────────────────────────────────────────────────── --}}
<div class="no-print" style="margin-bottom:32px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
    <div>
        <h1 style="font-size:32px; font-weight:700; margin-bottom:8px; color:var(--text);">All QR Codes</h1>
        <p style="color:var(--muted); font-size:14px;">View and print QR codes for members, instructors, and staff</p>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button type="button" onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
            Print All
        </button>
        <a href="{{ route('attendance.scan') }}" class="btn btn-secondary">Scanner</a>
        <a href="{{ route('attendance.generate-tokens') }}" class="btn btn-secondary">Generate Tokens</a>
    </div>
</div>

{{-- ─── Filter / Search ───────────────────────────────────────────────────── --}}
<div class="no-print qr-search-panel">
    <form action="{{ route('attendance.qr-list') }}" method="GET" style="display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap;">
        <div style="min-width:220px;">
            <label class="qr-label" for="group">Group</label>
            <select id="group" name="group" class="qr-select" onchange="this.form.submit()">
                <option value="members" {{ !$isStaffGroup ? 'selected' : '' }}>Members</option>
                <option value="staff"   {{ $isStaffGroup ? 'selected' : '' }}>Admin / Staff / Instructors</option>
            </select>
        </div>

        <div style="flex:1; min-width:260px;">
            <label class="qr-label" for="q">Quick Search</label>
            <input id="q" name="q" type="text" value="{{ $search }}" placeholder="Search by name..." class="qr-search-input">
        </div>

        <button type="submit" class="btn btn-primary" style="height:42px; padding:0 30px;">Search</button>

        @if($search || $isStaffGroup)
            <a href="{{ route('attendance.qr-list') }}" class="btn btn-secondary" style="height:42px; display:inline-flex; align-items:center;">Reset</a>
        @endif
    </form>
</div>

<div class="no-print" style="margin-bottom:16px; font-size:13px; color:var(--muted);">
    Showing <strong style="color:var(--text);">{{ $resultCount }}</strong>
    {{ $isStaffGroup ? 'staff' : 'member' }} {{ \Illuminate\Support\Str::plural('record', $resultCount) }}
</div>

{{-- ─── QR Cards Grid ──────────────────────────────────────────────────────── --}}
<div class="qr-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px;">

    @forelse($displayList as $item)
        @php
            $name  = $item->name ?? $item->user->name ?? 'N/A';
            $email = $item->email ?? $item->user->email ?? '—';

            if ($isStaffGroup) {
                $role = strtolower($item->role ?? $item->user->role ?? 'staff');
                $roleMap = [
                    'instructor' => ['badge-instructor', 'Instructor'],
                    'admin'      => ['badge-admin',      'Admin'],
                    'staff'      => ['badge-staff',      'Staff'],
                ];
                [$badgeClass, $label] = $roleMap[$role] ?? ['badge-default', ucfirst($role)];

                // Staff cards belong to a user; the print route needs the USER id,
                // not the id of the user_qr_tokens row.
                $printRoute = route('users.qr.print', $item->user_id);
            } else {
                $badgeClass = 'badge-member';
                $label      = isset($item->membership_type) ? strtoupper($item->membership_type) : 'Active Member';
                $printRoute = route('members.qr.print', $item);
            }
        @endphp

        <div class="qr-card">
            <div style="margin-bottom:15px;">
                @if($item->qr_code_path)
                    <img src="{{ asset('storage/' . $item->qr_code_path) }}"
                         alt="QR Code for {{ $name }}" loading="lazy"
                         style="width:100%; max-width:180px; height:auto; display:block; margin:0 auto; background:#fff; padding:8px; border-radius:8px;">
                @else
                    <div style="width:180px; height:180px; background:var(--surface2); margin:0 auto; display:flex; align-items:center; justify-content:center; color:var(--muted); border-radius:10px; font-size:12px; border:2px dashed var(--border);">
                        No QR Code
                    </div>
                @endif
            </div>

            <div style="font-weight:700; font-size:18px; color:var(--text); margin-bottom:4px;" title="{{ $name }}">{{ $name }}</div>
            <div style="font-size:13px; color:var(--muted); margin-bottom:12px; word-break:break-all;">{{ $email }}</div>

            <span class="badge-role {{ $badgeClass }}">{{ $label }}</span>

            <div class="scanner-id">
                <span class="lbl">Manual Scanner ID</span>
                <span class="num">{{ $item->id }}</span>
            </div>

            @if($item->qr_token)
                <div style="font-size:9px; color:var(--muted); margin-top:12px; font-family:monospace; line-height:1.4; word-break:break-all; opacity:.7;">
                    {{ \Illuminate\Support\Str::limit($item->qr_token, 24) }}
                </div>
            @endif

            <a href="{{ $printRoute }}" class="btn btn-secondary no-print"
               style="margin-top:14px; width:100%; justify-content:center;">
                Print QR
            </a>
        </div>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:var(--muted);">
            <p>No records found{{ $search ? ' for "' . e($search) . '"' : '' }}.</p>
            @if($search)
                <p style="margin-top:8px; font-size:13px;">Try adjusting your search terms.</p>
            @endif
        </div>
    @endforelse
</div>

@endsection