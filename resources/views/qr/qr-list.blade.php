@extends('layouts.admin')
@section('title', 'QR Codes – APEX')

@section('content')
<style>
    /* ===== THEME =====
       No local color overrides here. Buttons (.btn / .btn-primary / .btn-secondary)
       already come from layouts/admin.blade.php's global styles, which already read
       var(--accent), var(--surface2), var(--border), var(--text). This page just
       adds the QR-card and badge-pill styles on top, using the same tokens. */

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

    /* Same color-mix pill pattern used across the app's status/role badges */
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

    .qr-search-input {
        background: var(--surface2);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 10px;
        border-radius: 8px;
        width: 100%;
        outline: none;
        transition: border-color 0.2s;
    }

    .qr-search-input::placeholder {
        color: var(--muted);
    }

    .qr-search-input:focus {
        border-color: var(--accent);
    }

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
        .badge-role {
            background: #eee !important;
            color: #000 !important;
            border-color: #ccc !important;
        }
    }
</style>

{{-- ─── Page Header ────────────────────────────────────────────────────────── --}}
<div class="no-print" style="margin-bottom:32px; position:relative;">
    <h1 style="font-size:32px; font-weight:700; margin-bottom:8px; color:var(--text);">All QR Codes</h1>
    <p style="color:var(--muted); font-size:14px;">Unified directory for members, instructors, and staff</p>

    <div style="position:absolute; top:0; right:0; display:flex; gap:10px;">
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
            Print All
        </button>
        <a href="{{ route('attendance.generate-tokens') }}" class="btn btn-secondary">Generate Tokens</a>
    </div>
</div>

{{-- ─── Search Form ────────────────────────────────────────────────────────── --}}
<div class="no-print qr-search-panel">
    <form action="{{ route('attendance.qr-list') }}" method="GET" style="display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap;">
        <div style="flex:1; min-width:300px;">
            <label style="font-size:11px; color:var(--muted); text-transform:uppercase; margin-bottom:8px; display:block;">Quick Search</label>
            <input name="q" type="text" value="{{ $search }}" placeholder="Search by name or email..." class="qr-search-input">
        </div>

        <button type="submit" class="btn btn-primary" style="height:42px; padding:0 30px;">
            Search
        </button>

        @if($search)
            <a href="{{ route('attendance.qr-list') }}" class="btn btn-secondary" style="height:42px; line-height:30px;">Clear</a>
        @endif
    </form>
</div>

{{-- ─── Data Merging ──────────────────────────────────────────────────────── --}}
@php
    // Merge both collections into one unified list
    $displayList = $members->concat($staffList);
    $resultCount = $displayList->count();
@endphp

<div class="no-print" style="margin-bottom:16px; font-size:13px; color:var(--muted);">
    Showing <strong style="color:var(--text);">{{ $resultCount }}</strong> total records
</div>

{{-- ─── QR Cards Grid ──────────────────────────────────────────────────────── --}}
<div class="qr-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px;">

    @forelse($displayList->sortBy('name') as $item)
        @php
            $isMember = $item instanceof \App\Models\Member;

            $name  = $isMember ? $item->name : ($item->name ?? $item->user->name ?? 'N/A');
            $email = $isMember ? $item->email : ($item->user->email ?? '—');
            $role  = $isMember ? 'member' : strtolower($item->role ?? 'staff');

            // Branding logic — now maps to shared badge classes instead of raw hex
            $config = [
                'member'     => ['class' => 'badge-member', 'label' => ($item->status ?? 'Active')],
                'instructor' => ['class' => 'badge-instructor', 'label' => 'Instructor'],
                'admin'      => ['class' => 'badge-admin', 'label' => 'Admin'],
                'staff'      => ['class' => 'badge-staff', 'label' => 'Staff'],
            ];
            $ui = $config[$role] ?? ['class' => 'badge-default', 'label' => ucfirst($role)];
        @endphp

        <div class="qr-card">
            <div style="margin-bottom:15px;">
                @if($item->qr_code_path)
                    <img src="{{ asset('storage/' . $item->qr_code_path) }}?v={{ time() }}"
                         alt="QR" style="width:100%; max-width:180px; height:auto; display:block; margin:0 auto; background:#fff; padding:8px; border-radius:8px;">
                @else
                    <div style="width:180px; height:180px; background:var(--surface2); margin:0 auto; display:flex; align-items:center; justify-content:center; color:var(--muted); border-radius:10px; font-size:12px; border:2px dashed var(--border);">
                        No QR Code
                    </div>
                @endif
            </div>

            <div style="font-weight:700; font-size:18px; color:var(--text); margin-bottom:4px;">{{ $name }}</div>
            <div style="font-size:13px; color:var(--muted); margin-bottom:12px;">{{ $email }}</div>

            <span class="badge-role {{ $ui['class'] }}">
                {{ $ui['label'] }}
            </span>

            @if($item->qr_token)
                <div style="font-size:9px; color:var(--muted); margin-top:15px; font-family:monospace; line-height:1.4; word-break:break-all; opacity: 0.7;">
                    {{ $item->qr_token }}
                </div>
            @endif
        </div>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:var(--muted);">
            <p>No results found for "{{ $search }}".</p>
        </div>
    @endforelse
</div>

@endsection
