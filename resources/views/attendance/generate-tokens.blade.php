@extends('layouts.admin')
@section('title', 'Generate QR Tokens – IRONFORGE')
@section('active', 'attendance')

@section('content')

<div style="margin-bottom:28px;">
  <h1 style="font-size:28px;font-weight:700;margin-bottom:4px;">QR Token Generator</h1>
  <p style="color:var(--muted);font-size:14px;">
    Tokens have been assigned to all members and users that were missing one.
    Safe to run multiple times — existing tokens are never overwritten.
  </p>
</div>

<div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px;">
  <div style="padding:18px 20px;border-bottom:1px solid var(--border);">
    <div style="font-size:15px;font-weight:700;">Token Generation Results</div>
  </div>
  <div style="padding:16px;">
    @foreach($log as $entry)
      <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;
                  border-radius:8px;margin-bottom:6px;font-family:monospace;font-size:13px;
                  background:{{ $entry['type']==='success' ? 'rgba(74,222,128,0.08)' : ($entry['type']==='skip' ? 'rgba(96,165,250,0.08)' : 'rgba(200,255,0,0.08)') }};
                  border:1px solid {{ $entry['type']==='success' ? 'rgba(74,222,128,0.2)' : ($entry['type']==='skip' ? 'rgba(96,165,250,0.2)' : 'rgba(200,255,0,0.2)') }};">
        <span>{{ $entry['type']==='success' ? '✅' : ($entry['type']==='skip' ? '⏭️' : 'ℹ️') }}</span>
        <span style="color:var(--text);">{{ $entry['text'] }}</span>
      </div>
    @endforeach
  </div>
</div>

<div style="display:flex;gap:10px;">
  <a href="{{ route('attendance.qr-list') }}" class="btn btn-primary">
    View / Print QR Codes
  </a>
  <a href="{{ route('attendance.scan') }}" class="btn btn-secondary">
    Open Scanner
  </a>
</div>

@endsection