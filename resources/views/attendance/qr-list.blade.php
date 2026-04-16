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

    /* Dropdown Styling Fix */
    select.group-filter {
        background-color: #1a1a2e !important;
        color: #ffffff !important;
        border: 1px solid #3f3f5f !important;
        padding: 10px 35px 10px 15px; 
        border-radius: 8px;
        appearance: none; 
        width: 100%;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
    }
    
    select.group-filter option {
        background-color: #1a1a2e;
        color: #ffffff;
    }

    .qr-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 24px;
        margin-top: 30px;
    }

    .qr-card {
        background: var(--card-bg);
        border-radius: 16px;
        overflow: hidden;
        text-align: center;
        padding: 24px;
        color: var(--text-dark);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        transition: transform 0.2s;
        border: 1px solid #eee;
    }

    .qr-card:hover { transform: translateY(-5px); }

    .qr-image-wrapper {
        width: 160px;
        height: 160px;
        margin: 0 auto 15px auto;
        background: #f8f8f8;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid #eee;
    }

    .badge-role {
        font-size: 10px;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-top: 8px;
    }

    .scanner-id-tag {
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px dashed #eee;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .id-label {
        font-size: 9px;
        color: #999;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }
    .id-number {
        font-family: 'Courier New', monospace;
        background: #111;
        color: var(--primary-neon);
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 800;
    }

    @media print {
        .no-print { display: none !important; }
        .qr-grid { display: block; }
        .qr-card { break-inside: avoid; margin-bottom: 20px; border: 1px solid #ccc; box-shadow: none; }
    }
</style>

<div class="no-print" style="margin-bottom:32px; display:flex; justify-content:space-between; align-items:flex-end;">
    <div>
        <h1 style="font-size:32px; font-weight:700; margin-bottom:8px;">QR Codes</h1>
        <p style="color:#888; font-size:14px;">View and print QR codes for members and staff</p>
    </div>
    <div style="display:flex; gap:10px;">
        <button onclick="window.print()" class="btn" style="background:var(--primary-neon); color:#000; font-weight:bold; border-radius:8px; border:none; padding:10px 20px;">
            Print All
        </button>
        <a href="{{ route('attendance.scan') }}" class="btn btn-secondary" style="border-radius:8px; padding:10px 20px;">Scanner</a>
    </div>
</div>

{{-- ─── Filter Form ─── --}}
<div class="no-print" style="background:rgba(255,255,255,0.03); padding:24px; border-radius:16px; margin-bottom:32px;">
    <form action="{{ route('attendance.qr-list') }}" method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap: wrap;">
        
        <div style="width:220px;">
            <label style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:8px; display:block;">Group</label>
            <select name="group" class="group-filter" onchange="this.form.submit()">
                <option value="members" {{ request('group') == 'members' ? 'selected' : '' }}>Members</option>
                <option value="staff" {{ request('group') == 'staff' ? 'selected' : '' }}>Admin / Staff / Instructors</option>
            </select>
        </div>

        <button type="submit" class="btn" style="background:#333; color:#fff; height:42px; font-weight:bold; padding:0 20px; border:none; border-radius:8px; display:flex; align-items:center; justify-content:center; gap:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"></path>
            </svg>
            <span>Filter</span>
        </button>

        <div style="width:1px; height:30px; background:rgba(255,255,255,0.1); margin: 0 4px;"></div>

        <div style="flex:1; min-width: 200px;">
            <label style="font-size:11px; color:#888; text-transform:uppercase; margin-bottom:8px; display:block;">Search Name</label>
            <input name="q" type="text" value="{{ request('q') }}" placeholder="Type a name..."
                style="background:#111; border:1px solid #333; color:#fff; padding:10px; border-radius:8px; width:100%; outline: none;">
        </div>

        <button type="submit" class="btn" style="background:var(--primary-neon); color:#000; height:42px; font-weight:bold; padding:0 25px; border:none; border-radius:8px;">Search</button>
        <a href="{{ route('attendance.qr-list') }}" class="btn btn-dark" style="height:42px; padding:10px 20px; border:none; background:#222; color:#fff; border-radius:8px; display:flex; align-items:center; text-decoration: none;">Reset</a>
    </form>
</div>

{{-- ─── Combined Data Logic ─── --}}
@php
    $isStaffGroup = (request('group') == 'staff');
    $items = $isStaffGroup ? $staffList : $members;
@endphp

<div class="qr-grid">
    @forelse($items as $item)
        @php
            $name = $item->name ?? $item->user->name ?? 'N/A';
            $email = $item->email ?? $item->user->email ?? '';
            $memberId = $item->id; 
            
            // FIXED LOGIC: Default styling is Member
            $badgeColor = '#e6fffa'; 
            $textColor = '#38b2ac'; 
            $label = 'Active Member';

            // Only override with Staff/Admin styling IF the group filter is specifically set to 'staff'
            if($isStaffGroup) {
                $rawRole = strtolower($item->role ?? $item->user->role ?? 'staff');
                if($rawRole == 'instructor') { 
                    $badgeColor = '#ebf8ff'; $textColor = '#2b6cb0'; $label = 'Instructor'; 
                } elseif($rawRole == 'admin') { 
                    $badgeColor = '#f0fff4'; $textColor = '#276749'; $label = 'Admin'; 
                } else { 
                    $badgeColor = '#fff5f5'; $textColor = '#e53e3e'; $label = 'Staff'; 
                }
            } else {
                // For members, if a membership type exists, display it as the label
                if(isset($item->membership_type)) {
                    $label = strtoupper($item->membership_type);
                }
            }
        @endphp

        <div class="qr-card">
            <div class="qr-image-wrapper">
                @if($item->qr_code_path)
                    <img src="{{ asset('storage/' . $item->qr_code_path) }}" alt="QR" style="width:90%; height:90%; object-fit:contain;">
                @else
                    <div style="color:#aaa; font-size:12px; text-align:center; padding:10px;">No QR<br>Generated</div>
                @endif
            </div>

            <div style="font-weight:800; font-size:18px; color:#111; margin-bottom:2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $name }}</div>
            <div style="font-size:12px; color:#666; margin-bottom:8px;">{{ $email }}</div>

            <span class="badge-role" style="background:{{ $badgeColor }}; color:{{ $textColor }};">
                {{ $label }}
            </span>

            <div class="scanner-id-tag">
                <span class="id-label">Manual Scanner ID</span>
                <span class="id-number">{{ $memberId }}</span>
            </div>

            @if($item->qr_token)
                <div style="font-size:8px; color:#bbb; margin-top:10px; font-family:monospace; word-break:break-all; opacity: 0.6;">
                    {{ substr($item->qr_token, 0, 24) }}...
                </div>
            @endif
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align:center; padding:100px; color:#888;">
            <div style="font-size:48px; margin-bottom:15px; opacity: 0.2;">🔍</div>
            No records found for "{{ request('group', 'members') }}".
        </div>
    @endforelse
</div>

@endsection