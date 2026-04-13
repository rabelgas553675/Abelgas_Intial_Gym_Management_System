@extends('layouts.admin')
@section('title', 'Manage Users – IRONFORGE')
@section('page_title', 'Manage Users')
@section('active_nav', 'users')

@section('topbar_actions')
  <button onclick="document.getElementById('add-user-modal').style.display='flex'"
          class="btn btn-primary">+ Add User</button>
@endsection

@section('content')

<div style="margin-bottom:28px;">
  <h1 style="font-size:30px;font-weight:700;margin-bottom:4px;">Manage Users</h1>
  <p style="color:var(--muted);font-size:14px;">Manage all system accounts by role.</p>
</div>

@if(session('success'))
  <div class="alert alert-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">✕ {{ session('error') }}</div>
@endif

{{-- Tabs --}}
<div style="display:flex;gap:4px;margin-bottom:24px;background:var(--surface);
            border:1px solid var(--border);border-radius:12px;padding:6px;width:fit-content;">
  @foreach([
    ['id'=>'instructors','label'=>'Instructors','count'=>$instructors->count(),'color'=>'var(--accent2)'],
    ['id'=>'staff',      'label'=>'Staff',       'count'=>$staff->count(),       'color'=>'var(--warning)'],
    ['id'=>'members',    'label'=>'Members',     'count'=>$members->count(),     'color'=>'var(--success)'],
    ['id'=>'admins',     'label'=>'Admins',      'count'=>$admins->count(),      'color'=>'var(--accent)'],
  ] as $tab)
  <button onclick="switchTab('{{ $tab['id'] }}')" id="tab-{{ $tab['id'] }}"
          style="padding:8px 18px;border-radius:8px;border:none;cursor:pointer;font-size:13px;
                 font-weight:600;font-family:'DM Sans',sans-serif;transition:all 0.15s;
                 display:flex;align-items:center;gap:8px;"
          class="tab-btn">
    {{ $tab['label'] }}
    <span style="background:rgba(255,255,255,0.08);padding:2px 8px;border-radius:10px;font-size:11px;">
      {{ $tab['count'] }}
    </span>
  </button>
  @endforeach
</div>

{{-- Instructors Tab --}}
<div id="tab-content-instructors" class="tab-content">
  <div class="section-header">
    <div class="section-title" style="display:flex;align-items:center;gap:8px;">
      <span style="width:10px;height:10px;border-radius:50%;background:var(--accent2);display:inline-block;"></span>
      Instructors
    </div>
  </div>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Joined</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($instructors as $i => $user)
        <tr>
          <td style="color:var(--muted);">{{ $i + 1 }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              @if($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid var(--border);"/>
              @else
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,107,53,0.12);
                            border:1px solid rgba(255,107,53,0.25);display:flex;align-items:center;
                            justify-content:center;font-size:11px;font-weight:700;color:var(--accent2);flex-shrink:0;">
                  {{ strtoupper(substr($user->name,0,2)) }}
                </div>
              @endif
              <span style="font-weight:600;">{{ $user->name }}</span>
            </div>
          </td>
          <td style="color:var(--muted);">{{ $user->email }}</td>
          <td style="color:var(--muted);">{{ $user->created_at->format('M d, Y') }}</td>
          <td>
            <div style="display:flex;gap:6px;">
              <form method="POST" action="{{ route('users.promote', $user) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-secondary btn-sm">↑ Make Admin</button>
              </form>
              <form method="POST" action="{{ route('users.destroy', $user) }}"
                    onsubmit="return confirm('Delete {{ $user->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No instructors found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Staff Tab --}}
<div id="tab-content-staff" class="tab-content" style="display:none;">
  <div class="section-header">
    <div class="section-title" style="display:flex;align-items:center;gap:8px;">
      <span style="width:10px;height:10px;border-radius:50%;background:var(--warning);display:inline-block;"></span>
      Staff
    </div>
  </div>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Joined</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($staff as $i => $user)
        <tr>
          <td style="color:var(--muted);">{{ $i + 1 }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              @if($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid var(--border);"/>
              @else
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(251,191,36,0.12);
                            border:1px solid rgba(251,191,36,0.25);display:flex;align-items:center;
                            justify-content:center;font-size:11px;font-weight:700;color:var(--warning);flex-shrink:0;">
                  {{ strtoupper(substr($user->name,0,2)) }}
                </div>
              @endif
              <span style="font-weight:600;">{{ $user->name }}</span>
            </div>
          </td>
          <td style="color:var(--muted);">{{ $user->email }}</td>
          <td style="color:var(--muted);">{{ $user->created_at->format('M d, Y') }}</td>
          <td>
            <div style="display:flex;gap:6px;">
              <form method="POST" action="{{ route('users.promote', $user) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-secondary btn-sm">↑ Make Admin</button>
              </form>
              <form method="POST" action="{{ route('users.destroy', $user) }}"
                    onsubmit="return confirm('Delete {{ $user->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No staff found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Members Tab --}}
<div id="tab-content-members" class="tab-content" style="display:none;">
  <div class="section-header">
    <div class="section-title" style="display:flex;align-items:center;gap:8px;">
      <span style="width:10px;height:10px;border-radius:50%;background:var(--success);display:inline-block;"></span>
      Members
    </div>
  </div>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Joined</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($members as $i => $user)
        <tr>
          <td style="color:var(--muted);">{{ $i + 1 }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              @if($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid var(--border);"/>
              @else
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(74,222,128,0.12);
                            border:1px solid rgba(74,222,128,0.25);display:flex;align-items:center;
                            justify-content:center;font-size:11px;font-weight:700;color:var(--success);flex-shrink:0;">
                  {{ strtoupper(substr($user->name,0,2)) }}
                </div>
              @endif
              <span style="font-weight:600;">{{ $user->name }}</span>
            </div>
          </td>
          <td style="color:var(--muted);">{{ $user->email }}</td>
          <td style="color:var(--muted);">{{ $user->created_at->format('M d, Y') }}</td>
          <td>
            <div style="display:flex;gap:6px;">
              <form method="POST" action="{{ route('users.destroy', $user) }}"
                    onsubmit="return confirm('Delete {{ $user->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No members found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Admins Tab --}}
<div id="tab-content-admins" class="tab-content" style="display:none;">
  <div class="section-header">
    <div class="section-title" style="display:flex;align-items:center;gap:8px;">
      <span style="width:10px;height:10px;border-radius:50%;background:var(--accent);display:inline-block;"></span>
      Admins
    </div>
  </div>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Joined</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($admins as $i => $user)
        <tr>
          <td style="color:var(--muted);">{{ $i + 1 }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              @if($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:1px solid var(--border);"/>
              @else
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(200,255,0,0.1);
                            border:1px solid rgba(200,255,0,0.2);display:flex;align-items:center;
                            justify-content:center;font-size:11px;font-weight:700;color:var(--accent);flex-shrink:0;">
                  {{ strtoupper(substr($user->name,0,2)) }}
                </div>
              @endif
              <span style="font-weight:600;">{{ $user->name }}</span>
              @if($user->id === auth()->id())
                <span style="color:var(--muted);font-size:11px;">(you)</span>
              @endif
            </div>
          </td>
          <td style="color:var(--muted);">{{ $user->email }}</td>
          <td style="color:var(--muted);">{{ $user->created_at->format('M d, Y') }}</td>
          <td>
            @if($user->id === auth()->id())
              <span style="color:var(--muted);font-size:12px;">Current user</span>
            @else
              <form method="POST" action="{{ route('users.destroy', $user) }}"
                    onsubmit="return confirm('Delete {{ $user->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger-soft btn-sm">🗑</button>
              </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:40px;">No admins found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Add User Modal --}}
<div id="add-user-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);
            z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              width:100%;max-width:460px;padding:32px;position:relative;">
    <button onclick="document.getElementById('add-user-modal').style.display='none'"
            style="position:absolute;top:16px;right:16px;background:none;border:none;
                   color:var(--muted);font-size:22px;cursor:pointer;line-height:1;">✕</button>
    <div style="font-size:18px;font-weight:700;margin-bottom:4px;">Add New User</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:24px;">Create a new system account.</div>
    <form method="POST" action="{{ route('users.store') }}">
      @csrf
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Juan Dela Cruz" required/>
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="email@gym.com" required/>
      </div>
      <div class="form-group" style="margin-bottom:16px;">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required/>
      </div>
      <div class="form-group" style="margin-bottom:24px;">
        <label class="form-label">Role</label>
        <select name="role" class="form-control" required>
          <option value="staff">Staff</option>
          <option value="instructor">Instructor</option>
          <option value="member">Member</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;padding:12px;">
          Add User
        </button>
        <button type="button"
                onclick="document.getElementById('add-user-modal').style.display='none'"
                class="btn btn-secondary" style="padding:12px 20px;">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<style>
.tab-btn { background: transparent; color: var(--muted); }
.tab-btn.active { background: var(--surface2); color: var(--text); }
</style>

<script>
function switchTab(id) {
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
  document.getElementById('tab-content-' + id).style.display = 'block';
  document.getElementById('tab-' + id).classList.add('active');
}
// Default active tab
switchTab('instructors');

// Close modal on backdrop click
document.getElementById('add-user-modal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>

@endsection