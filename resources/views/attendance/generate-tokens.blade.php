@extends('layouts.admin')
@section('title', 'Generate QR Tokens – APEX')
@section('active', 'attendance')

@section('content')

@php
  $logItems     = collect($log ?? []);
  $successCount = $logItems->where('type', 'success')->count();
  $skipCount    = $logItems->where('type', 'skip')->count();
  $infoCount    = $logItems->count() - $successCount - $skipCount;
@endphp

{{-- Page Header --}}
<div class="qr-header">
  <h1>QR Token <span>Generator</span></h1>
  <p>
    Tokens have been assigned to all members and users that were missing one.
    Safe to run multiple times — existing tokens are never overwritten.
  </p>
</div>

{{-- Summary Cards --}}
@if($logItems->count() > 0)
<div class="qr-stat-grid">
  <div class="qr-stat-card green">
    <div class="qr-stat-label">Generated</div>
    <div class="qr-stat-value">{{ $successCount }}</div>
    <div class="qr-stat-sub">New tokens assigned</div>
  </div>
  <div class="qr-stat-card blue">
    <div class="qr-stat-label">Skipped</div>
    <div class="qr-stat-value">{{ $skipCount }}</div>
    <div class="qr-stat-sub">Already had a token</div>
  </div>
  <div class="qr-stat-card gold">
    <div class="qr-stat-label">Notices</div>
    <div class="qr-stat-value">{{ $infoCount }}</div>
    <div class="qr-stat-sub">General information</div>
  </div>
</div>
@endif

{{-- Results --}}
<div class="qr-results-card">
  <div class="qr-results-header">
    <div class="qr-results-header-title">Token Generation Results</div>
    <span class="qr-count-pill">{{ $logItems->count() }} {{ \Illuminate\Support\Str::plural('entry', $logItems->count()) }}</span>
  </div>
  <div class="qr-results-body">
    @if($logItems->count() > 0)
      @foreach($logItems as $entry)
        @php
          $type = $entry['type'] ?? 'info';
          $cls  = $type === 'success' ? 'success' : ($type === 'skip' ? 'skip' : 'info');
          $icon = $type === 'success' ? '✅' : ($type === 'skip' ? '⏭️' : 'ℹ️');
        @endphp
        <div class="qr-log-entry {{ $cls }}">
          <span class="icon">{{ $icon }}</span>
          <span class="text">{{ $entry['text'] }}</span>
        </div>
      @endforeach
    @else
      <div class="qr-empty">No token generation activities to display.</div>
    @endif
  </div>
</div>

{{-- Actions --}}
<div class="qr-actions">
  <a href="{{ route('attendance.qr-list') }}" class="btn btn-primary">
    View / Print QR Codes
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M17 8l4 4m0 0l-4 4m4-4H3"/>
    </svg>
  </a>
  <a href="{{ route('attendance.scan') }}" class="btn btn-secondary">
    Open Scanner
  </a>
</div>

<style>
  /* ===== CHARCOAL & GOLD THEME =====
     Same variables as the staff dashboard. Fallbacks are provided
     in case the admin layout doesn't define one of them. */

  /* Page Header */
  .qr-header { margin-bottom: 28px; }

  .qr-header h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 4px;
    color: var(--text, #f5f5f5);
  }

  .qr-header h1 span { color: var(--accent, #e0a93b); }

  .qr-header p {
    color: var(--muted, #8a8a93);
    font-size: 14px;
    max-width: 640px;
    line-height: 1.5;
  }

  /* Summary Cards */
  .qr-stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
  }

  .qr-stat-card {
    background: var(--surface, #1a1a1d);
    border: 1px solid var(--border, #2a2a2e);
    border-radius: 14px;
    padding: 18px 20px;
    border-left: 3px solid var(--accent, #e0a93b);
    transition: border-color 0.3s ease, transform 0.2s ease;
  }

  .qr-stat-card:hover { transform: translateY(-2px); }
  .qr-stat-card.green { border-left-color: var(--success, #4ade80); }
  .qr-stat-card.blue  { border-left-color: var(--info, #60a5fa); }
  .qr-stat-card.gold  { border-left-color: var(--accent, #e0a93b); }

  .qr-stat-label {
    font-size: 10px;
    font-weight: 700;
    color: var(--muted, #8a8a93);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 6px;
  }

  .qr-stat-value {
    font-size: 30px;
    font-weight: 800;
    color: var(--text, #f5f5f5);
    line-height: 1.1;
  }

  .qr-stat-card.green .qr-stat-value { color: var(--success, #4ade80); }
  .qr-stat-card.blue  .qr-stat-value { color: var(--info, #60a5fa); }
  .qr-stat-card.gold  .qr-stat-value { color: var(--accent-2, #f0c05a); }

  .qr-stat-sub {
    font-size: 12px;
    color: var(--muted, #8a8a93);
    margin-top: 4px;
  }

  /* Results Card */
  .qr-results-card {
    background: var(--surface, #1a1a1d);
    border: 1px solid var(--border, #2a2a2e);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 20px;
  }

  .qr-results-header {
    padding: 16px 24px;
    border-bottom: 1px solid var(--border, #2a2a2e);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    background: linear-gradient(135deg, rgba(224,169,59,0.08), transparent);
  }

  .qr-results-header-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--accent, #e0a93b);
  }

  .qr-count-pill {
    font-size: 10px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 40px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    background: var(--accent-soft, rgba(224,169,59,0.15));
    color: var(--accent, #e0a93b);
    border: 1px solid rgba(224,169,59,0.3);
    white-space: nowrap;
  }

  .qr-results-body {
    padding: 16px;
    max-height: 520px;
    overflow-y: auto;
  }

  .qr-results-body::-webkit-scrollbar { width: 6px; }
  .qr-results-body::-webkit-scrollbar-track { background: var(--surface, #1a1a1d); }
  .qr-results-body::-webkit-scrollbar-thumb {
    background: var(--accent-dark, #b8862a);
    border-radius: 3px;
  }

  /* Log Entries */
  .qr-log-entry {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 6px;
    font-family: monospace;
    font-size: 13px;
    word-break: break-word;
    border: 1px solid transparent;
    border-left-width: 3px;
    background: var(--surface2, #222226);
    transition: border-color 0.3s ease;
  }

  .qr-log-entry:last-child { margin-bottom: 0; }

  .qr-log-entry.success {
    background: rgba(74,222,128,0.08);
    border-color: rgba(74,222,128,0.25);
    border-left-color: var(--success, #4ade80);
  }

  .qr-log-entry.skip {
    background: rgba(96,165,250,0.08);
    border-color: rgba(96,165,250,0.25);
    border-left-color: var(--info, #60a5fa);
  }

  .qr-log-entry.info {
    background: rgba(224,169,59,0.08);
    border-color: rgba(224,169,59,0.25);
    border-left-color: var(--accent, #e0a93b);
  }

  .qr-log-entry .icon { flex-shrink: 0; }
  .qr-log-entry .text { color: var(--text-soft, #d4d4d8); }

  .qr-empty {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted, #8a8a93);
    font-size: 14px;
  }

  /* Actions */
  .qr-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn {
    padding: 13px 28px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    min-height: 44px;
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--accent-2, #f0c05a), var(--accent-dark, #b8862a));
    color: #1a1a1a;
  }

  .btn-primary:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(224,169,59,0.25);
  }

  .btn-secondary {
    background: #0e0e10;
    color: var(--text, #f5f5f5);
    border-color: var(--border, #2a2a2e);
    font-weight: 600;
  }

  .btn-secondary:hover {
    color: var(--accent, #e0a93b);
    border-color: rgba(224,169,59,0.45);
  }

  /* ===== RESPONSIVE BREAKPOINTS ===== */

  @media (max-width: 768px) {
    .qr-header h1 { font-size: 24px; }
    .qr-header p { font-size: 13px; }
    .qr-results-header { padding: 14px 16px; }
    .qr-results-header-title { font-size: 14px; }
    .qr-results-body { padding: 12px; }
    .qr-log-entry { padding: 8px 12px; font-size: 12px; gap: 8px; }
    .qr-actions { flex-direction: column; }
    .qr-actions .btn { width: 100%; padding: 12px 20px; }
  }

  @media (max-width: 640px) {
    .qr-stat-grid { grid-template-columns: 1fr; gap: 10px; }
    .qr-stat-card { padding: 14px; }
    .qr-stat-value { font-size: 24px; }
  }

  @media (max-width: 480px) {
    .qr-header h1 { font-size: 20px; }
    .qr-header p { font-size: 12px; }
    .qr-results-card { border-radius: 12px; }
    .qr-results-header { padding: 12px 14px; }
    .qr-results-header-title { font-size: 13px; }
    .qr-results-body { padding: 10px; }
    .qr-log-entry { padding: 8px 10px; font-size: 11px; border-radius: 8px; gap: 6px; line-height: 1.4; }
    .qr-log-entry .icon { font-size: 14px; }
    .btn { font-size: 13px; padding: 10px 16px; min-height: 40px; }
  }

  @media (max-width: 360px) {
    .qr-log-entry { font-size: 10px; padding: 6px 8px; flex-wrap: wrap; }
    .qr-log-entry .icon { font-size: 12px; }
  }
</style>

@endsection