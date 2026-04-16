@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', $user->name . ' – IRONFORGE')
@section('page_title', 'User Profile')
@section('active_nav', 'members')

@section('content')

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:24px;color:var(--muted);">
  <a href="{{ route('members.index') }}"
     style="color:var(--muted);text-decoration:none;transition:.15s;"
     onmouseover="this.style.color='var(--accent)'"
     onmouseout="this.style.color='var(--muted)'">Members</a>
  <span>/</span>
  <span style="color:var(--text);">{{ $user->name }}</span>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

  {{-- ══ LEFT COLUMN ══════════════════════════════════════════════════════════ --}}
  <div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Profile Card --}}
    <div style="background:var(--surface1);border:1px solid var(--border);border-radius:16px;padding:32px 24px;text-align:center;">

      @if($user->photo)
        <img src="{{ asset('storage/'.$user->photo) }}"
             style="width:96px;height:96px;border-radius:50%;object-fit:cover;
                    border:3px solid rgba(200,255,0,0.35);margin:0 auto 20px;display:block;">
      @else
        <div style="width:96px;height:96px;border-radius:50%;margin:0 auto 20px;
                    background:rgba(200,255,0,0.08);border:3px solid rgba(200,255,0,0.35);
                    display:flex;align-items:center;justify-content:center;
                    font-size:28px;font-weight:800;color:var(--accent);letter-spacing:1px;">
          {{ strtoupper(substr($user->name ?? '?', 0, 2)) }}
        </div>
      @endif

      <div style="font-size:20px;font-weight:700;color:var(--text);margin-bottom:6px;">
        {{ $user->name }}
      </div>
      <div style="font-size:13px;color:var(--muted);margin-bottom:18px;">
        {{ $user->email }}
      </div>

      @php
        $roleKey    = strtolower($user->role ?? '');
        $roleColor  = match($roleKey) {
          'staff'      => '#a78bfa',
          'instructor' => '#fb923c',
          'admin'      => '#f87171',
          default      => 'var(--muted)',
        };
        $roleBg     = match($roleKey) {
          'staff'      => 'rgba(167,139,250,0.12)',
          'instructor' => 'rgba(251,146,60,0.12)',
          'admin'      => 'rgba(248,113,113,0.12)',
          default      => 'rgba(255,255,255,0.05)',
        };
        $roleBorder = match($roleKey) {
          'staff'      => 'rgba(167,139,250,0.25)',
          'instructor' => 'rgba(251,146,60,0.25)',
          'admin'      => 'rgba(248,113,113,0.25)',
          default      => 'var(--border)',
        };
      @endphp

      <span style="display:inline-flex;align-items:center;gap:7px;padding:8px 20px;
                   border-radius:100px;font-size:13px;font-weight:700;
                   background:{{ $roleBg }};color:{{ $roleColor }};
                   border:1px solid {{ $roleBorder }};">
        <span style="width:7px;height:7px;border-radius:50%;background:currentColor;
                     box-shadow:0 0 6px currentColor;"></span>
        {{ ucfirst($user->role) }}
      </span>

      {{-- Instructor specialization badge --}}
      @if($user->isInstructor())
        @if($user->specialization)
          <div style="margin-top:14px;padding:10px 14px;background:rgba(251,146,60,0.06);
                      border:1px solid rgba(251,146,60,0.15);border-radius:10px;
                      font-size:12px;color:#fb923c;font-weight:600;">
            {{ $user->specialization }}
          </div>
        @endif
        @if($user->experience_years)
          <div style="margin-top:8px;font-size:12px;color:var(--muted);">
            {{ $user->experience_years }} yr{{ $user->experience_years != 1 ? 's' : '' }} experience
          </div>
        @endif
      @endif
    </div>

    {{-- Personal Info --}}
    <div style="background:var(--surface1);border:1px solid var(--border);border-radius:16px;padding:24px;">
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:2px;
                  color:var(--muted);margin-bottom:16px;">Personal Info</div>

      {{-- Phone --}}
      <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--surface2);
                  border-radius:10px;margin-bottom:10px;">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(96,165,250,0.12);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg width="16" height="16" fill="none" stroke="#60a5fa" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.68A2 2 0 012 .99h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
        </div>
        <div>
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:3px;">Phone</div>
          <div style="font-size:14px;font-weight:600;color:var(--text);">{{ $user->phone ?? '—' }}</div>
        </div>
      </div>

      {{-- Joined --}}
      <div style="display:flex;align-items:center;gap:14px;padding:14px;background:var(--surface2);border-radius:10px;">
        <div style="width:36px;height:36px;border-radius:10px;background:rgba(74,222,128,0.12);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg width="16" height="16" fill="none" stroke="#4ade80" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <div>
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:3px;">Joined</div>
          <div style="font-size:14px;font-weight:600;color:var(--text);">
            {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
          </div>
        </div>
      </div>
    </div>

    {{-- Instructor Quick Stats --}}
    @if($user->isInstructor())
    <div style="background:var(--surface1);border:1px solid var(--border);border-radius:16px;padding:24px;">
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:2px;
                  color:var(--muted);margin-bottom:16px;">Quick Stats</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;
                    padding:14px;text-align:center;">
          <div style="font-size:24px;font-weight:800;color:var(--accent);">{{ $workoutPlans->count() }}</div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Sessions</div>
        </div>
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;
                    padding:14px;text-align:center;">
          <div style="font-size:24px;font-weight:800;color:#4ade80;">
            {{ $workoutPlans->where('is_completed', true)->count() }}
          </div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Done</div>
        </div>
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;
                    padding:14px;text-align:center;">
          <div style="font-size:24px;font-weight:800;color:#fb923c;">{{ $instructorFees->count() }}</div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Payments</div>
        </div>
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;
                    padding:14px;text-align:center;">
          <div style="font-size:22px;font-weight:800;color:var(--accent);">
            ₱{{ number_format($instructorFees->where('status','Paid')->sum('amount'), 0) }}
          </div>
          <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px;">Earned</div>
        </div>
      </div>
    </div>
    @endif

    {{-- Back --}}
    <a href="{{ route('members.index') }}"
       style="display:flex;align-items:center;justify-content:center;gap:8px;
              padding:12px;background:var(--surface2);color:var(--text);
              border:1px solid var(--border);border-radius:10px;
              font-weight:600;font-size:13px;text-decoration:none;transition:.15s;"
       onmouseover="this.style.borderColor='rgba(255,255,255,.2)'"
       onmouseout="this.style.borderColor='var(--border)'">
      ← Back to Members
    </a>
  </div>

  {{-- ══ RIGHT COLUMN ═════════════════════════════════════════════════════════ --}}
  <div style="display:flex;flex-direction:column;gap:20px;">

    {{-- Account Details Card --}}
    <div style="background:var(--surface1);border:1px solid var(--border);border-radius:16px;padding:28px;">
      <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:2px;
                  color:var(--muted);margin-bottom:20px;">Account Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:18px 20px;">
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:10px;">Full Name</div>
          <div style="font-size:16px;font-weight:700;color:var(--text);">{{ $user->name }}</div>
        </div>

        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:18px 20px;">
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:10px;">Email</div>
          <div style="font-size:15px;font-weight:700;color:var(--text);word-break:break-all;">{{ $user->email }}</div>
        </div>

        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:18px 20px;">
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:10px;">Role</div>
          <span style="padding:4px 12px;border-radius:6px;font-size:13px;font-weight:700;
                       background:{{ $roleBg }};color:{{ $roleColor }};border:1px solid {{ $roleBorder }};">
            {{ ucfirst($user->role) }}
          </span>
        </div>

        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:18px 20px;">
          <div style="font-size:10px;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-bottom:10px;">Account Status</div>
          <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;
                       border-radius:6px;font-size:13px;font-weight:700;
                       background:rgba(74,222,128,0.1);color:#4ade80;">
            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 6px currentColor;"></span>
            Active
          </span>
        </div>

      </div>
    </div>

    {{-- ══ INSTRUCTOR-ONLY SECTIONS ══════════════════════════════════════════ --}}
    @if($user->isInstructor())

    {{-- ── Member Schedule Card ────────────────────────────────────────────── --}}
    <div style="background:var(--surface1);border:1px solid var(--border);border-radius:16px;padding:28px;">

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
          <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:4px;">
            Member Schedule
          </div>
          <div style="font-size:12px;color:var(--muted);">All assigned workout sessions for this instructor</div>
        </div>
        <span style="padding:5px 14px;border-radius:100px;font-size:11px;font-weight:700;
                     background:rgba(251,146,60,0.1);color:#fb923c;border:1px solid rgba(251,146,60,0.2);">
          {{ $workoutPlans->count() }} session(s)
        </span>
      </div>

      <div style="overflow-x:auto;border-radius:10px;border:1px solid var(--border);">
        <table style="width:100%;border-collapse:collapse;min-width:620px;">
          <thead>
            <tr style="background:rgba(255,255,255,0.02);border-bottom:1px solid var(--border);">
              @foreach(['Member','Session','Category','Intensity','Scheduled Date','Status'] as $h)
              <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;
                         text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);white-space:nowrap;">
                {{ $h }}
              </th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @forelse($workoutPlans as $plan)
            @php
              $isPast      = $plan->scheduled_date && $plan->scheduled_date->isPast();
              $isCompleted = $plan->is_completed;
              $isUpcoming  = !$isPast && !$isCompleted;

              $iColor = match(strtolower($plan->intensity ?? '')) {
                'high'   => '#f87171', 'medium' => '#facc15', 'low' => '#4ade80',
                default  => 'var(--muted)'
              };
              $iBg = match(strtolower($plan->intensity ?? '')) {
                'high'   => 'rgba(248,113,113,0.1)',
                'medium' => 'rgba(250,204,21,0.1)',
                'low'    => 'rgba(74,222,128,0.1)',
                default  => 'rgba(255,255,255,0.04)'
              };
            @endphp
            <tr style="border-bottom:1px solid var(--border);transition:.15s;"
                onmouseover="this.style.background='rgba(255,255,255,0.015)'"
                onmouseout="this.style.background='transparent'">

              <td style="padding:14px 16px;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:28px;height:28px;border-radius:50%;flex-shrink:0;
                              background:rgba(200,255,0,0.1);border:1px solid rgba(200,255,0,0.2);
                              display:flex;align-items:center;justify-content:center;
                              font-size:10px;font-weight:700;color:var(--accent);">
                    {{ strtoupper(substr($plan->member->name ?? '?', 0, 2)) }}
                  </div>
                  <span style="font-size:13px;font-weight:600;color:var(--text);">
                    {{ $plan->member->name ?? '—' }}
                  </span>
                </div>
              </td>

              <td style="padding:14px 16px;font-size:13px;color:var(--text);font-weight:500;">
                {{ $plan->title ?? '—' }}
              </td>

              <td style="padding:14px 16px;">
                <span style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;
                             background:rgba(96,165,250,0.1);color:#60a5fa;
                             border:1px solid rgba(96,165,250,0.15);white-space:nowrap;">
                  {{ $plan->category ?? '—' }}
                </span>
              </td>

              <td style="padding:14px 16px;">
                <span style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;
                             background:{{ $iBg }};color:{{ $iColor }};white-space:nowrap;">
                  {{ ucfirst($plan->intensity ?? '—') }}
                </span>
              </td>

              <td style="padding:14px 16px;font-size:13px;color:var(--muted);white-space:nowrap;">
                {{ $plan->scheduled_date ? $plan->scheduled_date->format('M d, Y') : '—' }}
              </td>

              <td style="padding:14px 16px;">
                @if($isCompleted)
                  <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                               border-radius:100px;font-size:11px;font-weight:700;
                               background:rgba(74,222,128,0.1);color:#4ade80;">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;"></span>Completed
                  </span>
                @elseif($isUpcoming)
                  <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                               border-radius:100px;font-size:11px;font-weight:700;
                               background:rgba(96,165,250,0.1);color:#60a5fa;">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;box-shadow:0 0 6px currentColor;"></span>Upcoming
                  </span>
                @else
                  <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                               border-radius:100px;font-size:11px;font-weight:700;
                               background:rgba(248,113,113,0.1);color:#f87171;">
                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;"></span>Missed
                  </span>
                @endif
              </td>

            </tr>
            @empty
            <tr>
              <td colspan="6" style="padding:52px;text-align:center;color:var(--muted);font-size:13px;">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"
                     viewBox="0 0 24 24" style="display:block;margin:0 auto 10px;opacity:.3;">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                No sessions scheduled yet.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- ── Instructor Fees History Card ────────────────────────────────────── --}}
    <div style="background:var(--surface1);border:1px solid var(--border);border-radius:16px;padding:28px;">

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
          <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:4px;">
            Coach Fee History
          </div>
          <div style="font-size:12px;color:var(--muted);">
            Payments received from member subscriptions
          </div>
        </div>
        <span style="padding:5px 14px;border-radius:100px;font-size:11px;font-weight:700;
                     background:rgba(74,222,128,0.1);color:#4ade80;border:1px solid rgba(74,222,128,0.2);">
          Total: ₱{{ number_format($instructorFees->where('status','Paid')->sum('amount'), 0) }}
        </span>
      </div>

      <div style="overflow-x:auto;border-radius:10px;border:1px solid var(--border);">
        <table style="width:100%;border-collapse:collapse;min-width:560px;">
          <thead>
            <tr style="background:rgba(255,255,255,0.02);border-bottom:1px solid var(--border);">
              @foreach(['Receipt','Member','Plan','Duration','Amount','Date','Status'] as $h)
              <th style="padding:12px 16px;text-align:left;font-size:10px;font-weight:700;
                         text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);white-space:nowrap;">
                {{ $h }}
              </th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @forelse($instructorFees as $fee)
            @php
              $fColor = match($fee->status) {
                'Paid'      => '#4ade80',
                'Pending'   => '#facc15',
                'Cancelled' => '#f87171',
                default     => 'var(--muted)'
              };
              $fBg = match($fee->status) {
                'Paid'      => 'rgba(74,222,128,0.1)',
                'Pending'   => 'rgba(250,204,21,0.1)',
                'Cancelled' => 'rgba(248,113,113,0.1)',
                default     => 'rgba(255,255,255,0.04)'
              };
            @endphp
            <tr style="border-bottom:1px solid var(--border);transition:.15s;"
                onmouseover="this.style.background='rgba(255,255,255,0.015)'"
                onmouseout="this.style.background='transparent'">

              <td style="padding:14px 16px;font-family:monospace;font-size:11px;color:var(--muted);">
                {{ Str::limit($fee->receipt_number ?? '—', 18) }}
              </td>

              <td style="padding:14px 16px;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:28px;height:28px;border-radius:50%;flex-shrink:0;
                              background:rgba(200,255,0,0.1);border:1px solid rgba(200,255,0,0.2);
                              display:flex;align-items:center;justify-content:center;
                              font-size:10px;font-weight:700;color:var(--accent);">
                    {{ strtoupper(substr($fee->member->name ?? '?', 0, 2)) }}
                  </div>
                  <span style="font-size:13px;font-weight:600;color:var(--text);">
                    {{ $fee->member->name ?? '—' }}
                  </span>
                </div>
              </td>

              <td style="padding:14px 16px;font-size:13px;color:var(--text);">
                {{ $fee->fitness_plan ?? '—' }}
              </td>

              <td style="padding:14px 16px;">
                <span style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;
                             background:rgba(96,165,250,0.1);color:#60a5fa;white-space:nowrap;">
                  {{ $fee->membership_type ?? '—' }}
                </span>
              </td>

              <td style="padding:14px 16px;">
                <span style="font-size:15px;font-weight:700;color:var(--accent);">
                  ₱{{ number_format($fee->amount, 0) }}
                </span>
              </td>

              <td style="padding:14px 16px;font-size:13px;color:var(--muted);white-space:nowrap;">
                {{ $fee->payment_date ? $fee->payment_date->format('M d, Y') : '—' }}
              </td>

              <td style="padding:14px 16px;">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;
                             border-radius:100px;font-size:11px;font-weight:700;
                             background:{{ $fBg }};color:{{ $fColor }};">
                  <span style="width:5px;height:5px;border-radius:50%;background:currentColor;"></span>
                  {{ $fee->status }}
                </span>
              </td>

            </tr>
            @empty
            <tr>
              <td colspan="7" style="padding:52px;text-align:center;color:var(--muted);font-size:13px;">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"
                     viewBox="0 0 24 24" style="display:block;margin:0 auto 10px;opacity:.3;">
                  <rect x="1" y="4" width="22" height="16" rx="2"/>
                  <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                No coach fee payments received yet.<br>
                <span style="font-size:12px;margin-top:6px;display:inline-block;">
                  Coach fees appear here automatically when a member subscribes with this instructor.
                </span>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @else
    {{-- Non-instructor info notice --}}
    <div style="display:flex;align-items:flex-start;gap:12px;padding:20px;
                background:rgba(96,165,250,0.06);border:1px solid rgba(96,165,250,0.15);
                border-radius:12px;color:#60a5fa;font-size:13px;line-height:1.6;">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <span>
        This is a <strong>{{ ucfirst($user->role) }}</strong> user account — not a gym member.
        Staff accounts do not have membership plans, schedules, or payment records.
        @if(auth()->user()->isAdmin())
          To manage this user's system role, visit
          <a href="{{ route('users.index') }}" style="color:var(--accent);text-decoration:none;font-weight:600;">Manage Users</a>.
        @endif
      </span>
    </div>
    @endif

  </div>
</div>

<style>
@media (max-width:900px) {
  div[style*="grid-template-columns:300px 1fr"] {
    grid-template-columns:1fr !important;
  }
}
</style>

@endsection