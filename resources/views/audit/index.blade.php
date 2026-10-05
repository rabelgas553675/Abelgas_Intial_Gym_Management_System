@extends('layouts.admin')

@section('title', 'Audit Trail')
@section('page_title', 'Audit Trail')
@section('active_nav', 'audit')

@section('content')
<style>
  /* All colours come from the layout's global theme tokens, so this page
     follows the Light / Dark toggle automatically. */
  .au-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
  .au-head h1{font-size:26px;font-weight:700;letter-spacing:-.3px;line-height:1.2}
  .au-head p{color:var(--text-secondary);font-size:14px;margin-top:4px}
  .au-count{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:999px;background:var(--accent-soft);color:var(--accent);font-size:12px;font-weight:700;border:1px solid rgba(224,169,59,.25)}
  .au-count svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2}

  /* Filters */
  .au-filters{padding:18px 20px;margin-bottom:18px;overflow:visible}
  .au-grid{display:grid;grid-template-columns:2fr repeat(3,1fr) 1fr 1fr auto;gap:12px;align-items:end}
  .au-grid .form-control{font-size:14px;padding:9px 12px}
  .au-actions{display:flex;gap:8px}
  @media (max-width:1100px){.au-grid{grid-template-columns:repeat(3,1fr)}.au-grid .au-search{grid-column:1/-1}}
  @media (max-width:640px){.au-grid{grid-template-columns:1fr 1fr}}

  /* Table */
  .au-table{min-width:980px}
  .au-table td{font-size:13.5px}
  .au-time{white-space:nowrap;font-weight:600}
  .au-time small{display:block;font-weight:400;color:var(--text-secondary);font-size:12px;margin-top:2px}
  .au-user{display:flex;align-items:center;gap:10px;min-width:0}
  .au-avatar{width:32px;height:32px;border-radius:50%;flex-shrink:0;display:grid;place-items:center;font-size:12px;font-weight:700;color:var(--accent);background:var(--accent-soft);border:1px solid rgba(224,169,59,.3)}
  .au-user b{display:block;font-weight:600;line-height:1.25}
  .au-user small{color:var(--text-secondary);font-size:11.5px;text-transform:uppercase;letter-spacing:.6px}
  .au-desc{max-width:340px;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
  .au-muted{color:var(--text-secondary)}
  .au-module{display:inline-block;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:600;background:var(--bg-card-secondary);border:1px solid var(--border);white-space:nowrap}
  .au-rec{font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:12px;color:var(--text-secondary);white-space:nowrap}

  /* Action badges (same dot-badge look as the rest of the app) */
  .au-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 11px;border-radius:6px;font-size:11px;font-weight:700;white-space:nowrap;background:rgba(150,150,160,.15);color:var(--text-secondary)}
  .au-badge::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
  .au-badge.ok{background:rgba(74,222,128,.15);color:var(--success)}
  .au-badge.warn{background:rgba(251,191,36,.16);color:var(--warning)}
  .au-badge.danger{background:rgba(248,113,113,.15);color:var(--danger)}
  .au-badge.info{background:rgba(96,165,250,.15);color:var(--info)}

  .au-empty{text-align:center;padding:56px 20px;color:var(--text-secondary)}
  .au-empty svg{width:42px;height:42px;stroke:var(--text-secondary);fill:none;stroke-width:1.5;opacity:.6;margin-bottom:10px}
  .au-empty b{display:block;color:var(--text-primary);font-size:15px;margin-bottom:4px}

  /* Pagination */
  .au-pager{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:14px 20px;border-top:1px solid var(--border)}
  .au-pager span{font-size:13px;color:var(--text-secondary)}
  .au-pager .btn[aria-disabled="true"]{opacity:.4;pointer-events:none}

  /* Modal */
  .au-modal{position:fixed;inset:0;z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(0,0,0,.65);backdrop-filter:blur(3px)}
  .au-modal.open{display:flex}
  .au-box{width:100%;max-width:780px;max-height:90vh;display:flex;flex-direction:column;background:var(--bg-card);border:1px solid var(--border);border-radius:16px;box-shadow:0 24px 60px rgba(0,0,0,.45);overflow:hidden}
  .au-box-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 22px;border-bottom:1px solid var(--border)}
  .au-box-head h3{font-size:16px;font-weight:700}
  .au-x{width:34px;height:34px;display:grid;place-items:center;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary);cursor:pointer;font-size:18px;line-height:1}
  .au-x:hover{border-color:var(--accent);color:var(--accent)}
  .au-box-body{padding:20px 22px;overflow:auto}
  .au-meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px}
  .au-meta div{background:var(--bg-card-secondary);border:1px solid var(--border);border-radius:10px;padding:10px 12px;min-width:0}
  .au-meta label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1.4px;color:var(--text-secondary);margin-bottom:4px}
  .au-meta span{font-size:13.5px;font-weight:600;word-break:break-word}
  .au-sec{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1.6px;color:var(--text-secondary);margin:4px 0 8px}
  .au-diff{width:100%;min-width:0;border:1px solid var(--border);border-radius:10px;border-collapse:separate;border-spacing:0;overflow:hidden;margin-bottom:20px}
  .au-diff th{background:var(--table-header);padding:9px 12px;font-size:10px}
  .au-diff td{padding:9px 12px;font-size:13px;border-top:1px solid var(--border);vertical-align:top;word-break:break-word}
  .au-diff td:first-child{font-weight:600;white-space:nowrap;width:1%}
  .au-diff td.o{background:rgba(248,113,113,.10)}
  .au-diff td.n{background:rgba(74,222,128,.10)}
  .au-diff tr:hover td.o{background:rgba(248,113,113,.10)}
  .au-diff tr:hover td.n{background:rgba(74,222,128,.10)}
  .au-diff .nil{color:var(--text-secondary);font-style:italic}
  .au-agent{font-size:12px;color:var(--text-secondary);background:var(--bg-card-secondary);border:1px solid var(--border);border-radius:10px;padding:10px 12px;word-break:break-word}
</style>

<div class="au-head">
  <div>
    <h1>Audit <span class="gold-text">Trail</span></h1>
    <p>Read-only record of who did what, and when.</p>
  </div>
  <span class="au-count">
    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ number_format($logs->total()) }} {{ \Illuminate\Support\Str::plural('record', $logs->total()) }}
  </span>
</div>

{{-- ── Filters ── --}}
<div class="card au-filters">
  <form method="GET" action="{{ route('admin.audit.index') }}" class="au-grid">
    <div class="au-search">
      <label class="form-label">Search</label>
      <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="User, action, description, IP…">
    </div>
    <div>
      <label class="form-label">User</label>
      <select class="form-control" name="user_id">
        <option value="">All users</option>
        @foreach($users as $u)
          <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="form-label">Action</label>
      <select class="form-control" name="action">
        <option value="">All actions</option>
        @foreach($actions as $a)
          <option value="{{ $a }}" @selected(request('action') === $a)>{{ ucfirst(str_replace('_',' ',$a)) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="form-label">Module</label>
      <select class="form-control" name="module">
        <option value="">All modules</option>
        @foreach($modules as $m)
          <option value="{{ $m }}" @selected(request('module') === $m)>{{ $m }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="form-label">From</label>
      <input class="form-control" type="date" name="date_from" value="{{ request('date_from') }}">
    </div>
    <div>
      <label class="form-label">To</label>
      <input class="form-control" type="date" name="date_to" value="{{ request('date_to') }}">
    </div>
    <div class="au-actions">
      <button class="btn btn-primary" type="submit">Filter</button>
      <a class="btn btn-secondary" href="{{ route('admin.audit.index') }}">Reset</a>
    </div>
  </form>
</div>

{{-- ── Table ── --}}
<div class="card">
  <div class="table-scroll">
    <table class="au-table">
      <thead>
        <tr>
          <th>Date &amp; Time</th>
          <th>User</th>
          <th>Action</th>
          <th>Module</th>
          <th>Description</th>
          <th>Record</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @forelse($logs as $log)
        @php
          $a = $log->action;
          $badge = match (true) {
              str_contains($a, 'fail'), str_contains($a, 'lockout'), str_contains($a, 'delet') => 'danger',
              in_array($a, ['role_changed', 'password_changed'])                              => 'warn',
              in_array($a, ['created', 'recorded', 'login', 'check_in', 'check_out'])         => 'ok',
              in_array($a, ['updated', 'logout'])                                             => 'info',
              default                                                                         => '',
          };
          $who      = $log->user?->name ?? 'System / Guest';
          $actionTx = ucfirst(str_replace('_', ' ', $a));
          $record   = $log->record_type ? class_basename($log->record_type) . ' #' . $log->record_id : null;
          $detail = [
              'user'        => $who,
              'role'        => $log->user ? ucfirst($log->user->role) : null,
              'action'      => $actionTx,
              'module'      => $log->module,
              'description' => $log->description,
              'record'      => $record,
              'time'        => $log->created_at->format('M d, Y h:i:s A'),
              'old'         => $log->old_values,
              'new'         => $log->new_values,
              'ip'          => $log->ip_address,
              'agent'       => $log->user_agent,
          ];
        @endphp
        <tr>
          <td class="au-time">
            {{ $log->created_at->format('M d, Y') }}
            <small>{{ $log->created_at->format('h:i:s A') }}</small>
          </td>
          <td>
            <div class="au-user">
              <div class="au-avatar">{{ strtoupper(mb_substr($who, 0, 1)) }}</div>
              <div>
                <b>{{ $who }}</b>
                <small>{{ $log->user ? $log->user->role : 'guest' }}</small>
              </div>
            </div>
          </td>
          <td><span class="au-badge {{ $badge }}">{{ $actionTx }}</span></td>
          <td>@if($log->module)<span class="au-module">{{ $log->module }}</span>@else<span class="au-muted">—</span>@endif</td>
          <td><div class="au-desc" title="{{ $log->description }}">{{ $log->description ?: '—' }}</div></td>
          <td class="au-rec">{{ $record ?? '—' }}</td>
          <td style="text-align:right">
            <button type="button" class="btn btn-secondary btn-sm"
                    data-detail="{{ json_encode($detail) }}" onclick="openAudit(this)">View</button>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" style="border-top:0">
            <div class="au-empty">
              <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
              <b>No audit records found</b>
              Try changing or resetting the filters.
            </div>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  @if($logs->total() > 0)
  <div class="au-pager">
    <span>Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }}</span>
    <div style="display:flex;align-items:center;gap:8px">
      <a class="btn btn-secondary btn-sm" href="{{ $logs->previousPageUrl() ?? '#' }}" @if($logs->onFirstPage()) aria-disabled="true" @endif>← Previous</a>
      <span>Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
      <a class="btn btn-secondary btn-sm" href="{{ $logs->nextPageUrl() ?? '#' }}" @if(! $logs->hasMorePages()) aria-disabled="true" @endif>Next →</a>
    </div>
  </div>
  @endif
</div>

{{-- ── Details modal ── --}}
<div class="au-modal" id="auModal" onclick="if(event.target===this)closeAudit()">
  <div class="au-box" role="dialog" aria-modal="true" aria-labelledby="auTitle">
    <div class="au-box-head">
      <h3 id="auTitle">Audit Details</h3>
      <button type="button" class="au-x" onclick="closeAudit()" aria-label="Close">&times;</button>
    </div>
    <div class="au-box-body">
      <div class="au-meta" id="auMeta"></div>
      <div id="auChanges"></div>
      <div class="au-sec">User agent</div>
      <div class="au-agent" id="auAgent"></div>
    </div>
  </div>
</div>

<script>
  const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const val = v => (v === null || v === undefined)
      ? '<span class="nil">empty</span>'
      : esc(typeof v === 'object' ? JSON.stringify(v) : v);

  function openAudit(btn){
    const d = JSON.parse(btn.dataset.detail);

    const meta = [
      ['User', d.user + (d.role ? ' · ' + d.role : '')],
      ['Action', d.action],
      ['Module', d.module || '—'],
      ['When', d.time],
      ['Record', d.record || '—'],
      ['IP address', d.ip || '—'],
    ];
    document.getElementById('auMeta').innerHTML =
      meta.map(([k, v]) => `<div><label>${k}</label><span>${esc(v)}</span></div>`).join('');

    const old = d.old || {}, nw = d.new || {};
    const keys = [...new Set([...Object.keys(old), ...Object.keys(nw)])];
    let html = '';
    if (d.description) html += `<div class="au-sec">Description</div><p style="margin:0 0 18px;font-size:14px">${esc(d.description)}</p>`;
    if (keys.length) {
      const hasOld = Object.keys(old).length, hasNew = Object.keys(nw).length;
      html += '<div class="au-sec">Changes</div><table class="au-diff"><thead><tr><th>Field</th>'
           + (hasOld ? '<th>Old value</th>' : '') + (hasNew ? '<th>New value</th>' : '') + '</tr></thead><tbody>'
           + keys.map(k => `<tr><td>${esc(k)}</td>`
               + (hasOld ? `<td class="o">${val(old[k])}</td>` : '')
               + (hasNew ? `<td class="n">${val(nw[k])}</td>` : '') + '</tr>').join('')
           + '</tbody></table>';
    } else {
      html += '<p class="au-muted" style="margin:0 0 20px;font-size:13px">No field-level changes were recorded for this event.</p>';
    }
    document.getElementById('auChanges').innerHTML = html;
    document.getElementById('auAgent').textContent = d.agent || '—';

    document.getElementById('auModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeAudit(){
    document.getElementById('auModal').classList.remove('open');
    document.body.style.overflow = '';
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAudit(); });
</script>
@endsection