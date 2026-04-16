@extends('layouts.admin')
@section('title', 'QR Codes – IRONFORGE')

@section('content')
<style>
    :root {
        --primary-neon: #c8ff00;
        --card-bg: #ffffff;
        --text-dark: #111111;
        --text-muted: #666666;
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
            color: #000 !important;
        }
    }

    .qr-card {
        background: var(--card-bg);
        border-radius: 20px;
        overflow: hidden;
        text-align: center;
        padding: 24px;
        color: var(--text-dark);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        transition: transform 0.2s;
    }

    .qr-card:hover {
        transform: translateY(-5px);
    }

    .badge-role {
        font-size: 10px;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>

{{-- ─── Page Header ────────────────────────────────────────────────────────── --}}
<div class="no-print" style="margin-bottom:32px; position:relative;">
    <h1 style="font-size:32px; font-weight:700; margin-bottom:8px;">All QR Codes</h1>
    <p style="color:var(--muted); font-size:14px;">Unified directory for members, instructors, and staff</p>

    <div style="position:absolute; top:0; right:0; display:flex; gap:10px;">
        <button onclick="window.print()" class="btn btn-primary"
            style="background:var(--primary-neon); color:#000; border:none; display:flex; align-items:center; gap:8px; font-weight:bold;">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
            </svg>
            Print All
        </button>
        <a href="{{ route('attendance.generate-tokens') }}" class="btn btn-secondary">Generate Tokens</a>
    </div>
</div>

{{-- ─── Search Form ────────────────────────────────────────────────────────── --}}
<div class="no-print" style="background:rgba(255,255,255,0.03); padding:24px; border-radius:16px; margin-bottom:32px; border:1px solid var(--border);">
    <form action="{{ route('attendance.qr-list') }}" method="GET" style="display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap;">
        <div style="flex:1; min-width:300px;">
            <label style="font-size:11px; color:var(--muted); text-transform:uppercase; margin-bottom:8px; display:block;">Quick Search</label>
            <input name="q" type="text" value="{{ $search }}" placeholder="Search by name or email..."
                style="background:#111; border:1px solid #333; color:#fff; padding:10px; border-radius:8px; width:100%;">
        </div>

        <button type="submit" class="btn btn-primary"
            style="background:var(--primary-neon); color:#000; height:42px; border:none; padding:0 30px; font-weight:bold;">
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
    Showing <strong style="color:#fff;">{{ $resultCount }}</strong> total records
</div>

{{-- ─── QR Cards Grid ──────────────────────────────────────────────────────── --}}
<div class="qr-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:20px;">

    @forelse($displayList->sortBy('name') as $item)
        @php
            $isMember = $item instanceof \App\Models\Member;
            
            $name  = $isMember ? $item->name : ($item->name ?? $item->user->name ?? 'N/A');
            $email = $isMember ? $item->email : ($item->user->email ?? '—');
            $role  = $isMember ? 'member' : strtolower($item->role ?? 'staff');
            
            // Branding Logic
            $config = [
                'member'     => ['bg' => '#e6fffa', 'text' => '#38b2ac', 'label' => ($item->status ?? 'Active')],
                'instructor' => ['bg' => '#ebf8ff', 'text' => '#2b6cb0', 'label' => 'Instructor'],
                'admin'      => ['bg' => '#f0fff4', 'text' => '#276749', 'label' => 'Admin'],
                'staff'      => ['bg' => '#fff5f5', 'text' => '#e53e3e', 'label' => 'Staff'],
            ];
            $ui = $config[$role] ?? ['bg' => '#f7fafc', 'text' => '#718096', 'label' => ucfirst($role)];
        @endphp

        <div class="qr-card">
            <div style="margin-bottom:15px;">
                @if($item->qr_code_path)
                    <img src="{{ asset('storage/' . $item->qr_code_path) }}?v={{ time() }}"
                         alt="QR" style="width:100%; max-width:180px; height:auto; display:block; margin:0 auto;">
                @else
                    <div style="width:180px; height:180px; background:#f9f9f9; margin:0 auto; display:flex; align-items:center; justify-content:center; color:#aaa; border-radius:10px; font-size:12px; border:2px dashed #ddd;">
                        No QR Code
                    </div>
                @endif
            </div>

            <div style="font-weight:700; font-size:18px; color:#111; margin-bottom:4px;">{{ $name }}</div>
            <div style="font-size:13px; color:#666; margin-bottom:12px;">{{ $email }}</div>

            <span class="badge-role" style="background:{{ $ui['bg'] }}; color:{{ $ui['text'] }};">
                {{ $ui['label'] }}
            </span>

            @if($item->qr_token)
                <div style="font-size:9px; color:#999; margin-top:15px; font-family:monospace; line-height:1.4; word-break:break-all; opacity: 0.7;">
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