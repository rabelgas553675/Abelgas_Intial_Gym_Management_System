@extends('layouts.admin')
@section('title', 'Generate QR Tokens – APEX')
@section('active', 'attendance')

@section('content')

<style>
  .qr-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 16px;
  }

  .qr-header {
    margin-bottom: 28px;
  }

  .qr-header h1 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 4px;
  }

  .qr-header p {
    color: var(--muted);
    font-size: 14px;
  }

  .qr-results-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 20px;
  }

  .qr-results-header {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
  }

  .qr-results-header-title {
    font-size: 15px;
    font-weight: 700;
  }

  .qr-results-body {
    padding: 16px;
  }

  .qr-log-entry {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 8px;
    margin-bottom: 6px;
    font-family: monospace;
    font-size: 13px;
    word-break: break-word;
  }

  .qr-log-entry:last-child {
    margin-bottom: 0;
  }

  .qr-log-entry.success {
    background: rgba(74, 222, 128, 0.08);
    border: 1px solid rgba(74, 222, 128, 0.2);
  }

  .qr-log-entry.skip {
    background: rgba(96, 165, 250, 0.08);
    border: 1px solid rgba(96, 165, 250, 0.2);
  }

  .qr-log-entry.info {
    background: rgba(200, 255, 0, 0.08);
    border: 1px solid rgba(200, 255, 0, 0.2);
  }

  .qr-log-entry .icon {
    flex-shrink: 0;
  }

  .qr-log-entry .text {
    color: var(--text);
  }

  .qr-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn {
    padding: 11px 28px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    min-height: 44px;
  }

  .btn-primary {
    background: var(--accent);
    color: #111;
  }

  .btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }

  .btn-secondary {
    background: var(--surface2);
    color: var(--text);
    border: 1px solid var(--border);
  }

  .btn-secondary:hover {
    background: var(--border);
  }

  /* Empty state for no results */
  .qr-empty {
    text-align: center;
    padding: 20px;
    color: var(--muted);
    font-size: 14px;
  }

  /* ===== RESPONSIVE BREAKPOINTS ===== */

  /* Tablets */
  @media (max-width: 768px) {
    .qr-container {
      padding: 0 12px;
    }

    .qr-header h1 {
      font-size: 24px;
    }

    .qr-header p {
      font-size: 13px;
    }

    .qr-results-header {
      padding: 14px 16px;
    }

    .qr-results-header-title {
      font-size: 14px;
    }

    .qr-results-body {
      padding: 12px;
    }

    .qr-log-entry {
      padding: 8px 12px;
      font-size: 12px;
      gap: 8px;
    }

    .qr-actions {
      flex-direction: column;
    }

    .qr-actions .btn {
      width: 100%;
      justify-content: center;
      padding: 12px 20px;
    }
  }

  /* Mobile */
  @media (max-width: 480px) {
    .qr-container {
      padding: 0 8px;
    }

    .qr-header h1 {
      font-size: 20px;
    }

    .qr-header p {
      font-size: 12px;
      line-height: 1.5;
    }

    .qr-results-card {
      border-radius: 10px;
    }

    .qr-results-header {
      padding: 12px 14px;
    }

    .qr-results-header-title {
      font-size: 13px;
    }

    .qr-results-body {
      padding: 10px;
    }

    .qr-log-entry {
      padding: 8px 10px;
      font-size: 11px;
      border-radius: 6px;
      gap: 6px;
      line-height: 1.4;
    }

    .qr-log-entry .icon {
      font-size: 14px;
    }

    .btn {
      font-size: 13px;
      padding: 10px 16px;
      min-height: 40px;
      border-radius: 6px;
    }
  }

  /* Small phones */
  @media (max-width: 360px) {
    .qr-log-entry {
      font-size: 10px;
      padding: 6px 8px;
      flex-wrap: wrap;
    }

    .qr-log-entry .icon {
      font-size: 12px;
    }
  }
</style>

<div class="qr-container">
  <div class="qr-header">
    <h1>QR Token Generator</h1>
    <p>
      Tokens have been assigned to all members and users that were missing one.
      Safe to run multiple times — existing tokens are never overwritten.
    </p>
  </div>

  <div class="qr-results-card">
    <div class="qr-results-header">
      <div class="qr-results-header-title">Token Generation Results</div>
    </div>
    <div class="qr-results-body">
      @if(!empty($log) && count($log) > 0)
        @foreach($log as $entry)
          <div class="qr-log-entry 
            {{ $entry['type'] === 'success' ? 'success' : ($entry['type'] === 'skip' ? 'skip' : 'info') }}">
            <span class="icon">
              {{ $entry['type'] === 'success' ? '✅' : ($entry['type'] === 'skip' ? '⏭️' : 'ℹ️') }}
            </span>
            <span class="text">{{ $entry['text'] }}</span>
          </div>
        @endforeach
      @else
        <div class="qr-empty">No token generation activities to display.</div>
      @endif
    </div>
  </div>

  <div class="qr-actions">
    <a href="{{ route('attendance.qr-list') }}" class="btn btn-primary">
      View / Print QR Codes
    </a>
    <a href="{{ route('attendance.scan') }}" class="btn btn-secondary">
      Open Scanner
    </a>
  </div>
</div>

@endsection