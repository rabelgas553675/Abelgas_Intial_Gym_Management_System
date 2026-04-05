@extends('layouts.app')
@section('title', 'Manage Users – IRONFORGE')
@section('page_title', 'Manage Users')

@section('content')

@if(session('success'))
  <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">✕ {{ session('error') }}</div>
@endif

<div class="card">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);">
    <div style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;">
      <span style="color:var(--danger);">🛡</span> System Users
    </div>
    <button onclick="document.getElementById('add-user-modal').style.display='flex'"
            class="btn btn-primary btn-sm">
      + Add User
    </button>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Members Added</th>
        <th>Last Login</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $i => $user)
      <tr>
        <td style="color:var(--muted)">{{ $i + 1 }}</td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;border-radius:50%;background:rgba(232,255,42,0.15);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:12px;color:var(--accent);flex-shrink:0;">
              {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <span style="font-weight:500;">{{ $user->name }}</span>
            @if($user->id === auth()->id())
              <span style="color:var(--muted);font-size:12px;">(you)</span>
            @endif
          </div>
        </td>
        <td style="color:var(--muted)">{{ $user->email }}</td>
        <td>
          <span class="badge" style="
            {{ $user->role === 'admin'
              ? 'background:rgba(232,255,42,0.15);color:var(--accent);border:1px solid rgba(232,255,42,0.3);'
              : 'background:rgba(96,165,250,0.15);color:var(--info);border:1px solid rgba(96,165,250,0.3);' }}
          ">
            {{ ucfirst($user->role) }}
          </span>
        </td>
        <td style="color:var(--muted)">{{ $memberCount }}</td>
        <td style="color:var(--muted)">
          {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('M d, Y H:i') : '—' }}
        </td>
        <td style="color:var(--muted)">{{ $user->created_at->format('M d, Y') }}</td>
        <td>
          @if($user->id === auth()->id())
            <span style="color:var(--muted);font-size:12px;">Current user</span>
          @else
            <div style="display:flex;gap:6px;">
              @if($user->role !== 'admin')
              <form method="POST" action="{{ route('users.promote', $user) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-secondary btn-sm">
                  ↑ → Admin
                </button>
              </form>
              @endif
              <form method="POST" action="{{ route('users.destroy', $user) }}"
                    onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">🗑</button>
              </form>
            </div>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- Add User Modal --}}
<div id="add-user-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:50;align-items:center;justify-content:center;">
  <div class="card" style="width:100%;max-width:440px;padding:28px;">
    <div style="font-size:16px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
      Add New User
      <button onclick="document.getElementById('add-user-modal').style.display='none'"
              style="background:none;border:none;color:var(--muted);font-size:20px;cursor:pointer;line-height:1;">×</button>
    </div>

    <form method="POST" action="{{ route('users.store') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input type="text" name="name" class="form-control"
               placeholder="e.g. Juan Dela Cruz" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address *</label>
        <input type="email" name="email" class="form-control"
               placeholder="email@example.com" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Password *</label>
        <input type="password" name="password" class="form-control"
               placeholder="Min. 6 characters" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Role *</label>
        <select name="role" class="form-control" required>
          <option value="staff">Staff</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px;">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">
          Add User
        </button>
        <button type="button"
                onclick="document.getElementById('add-user-modal').style.display='none'"
                class="btn btn-secondary" style="flex:1;justify-content:center;">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

@endsection