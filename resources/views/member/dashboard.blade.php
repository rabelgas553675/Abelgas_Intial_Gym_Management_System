@extends('layouts.member')
@section('title', 'Dashboard – IRONFORGE')
@section('active', 'dashboard')

@section('content')

{{-- Welcome Header --}}
<div style="margin-bottom:32px;">
  <h1 style="font-size:32px;font-weight:700;margin-bottom:6px;">
    Welcome back, <span style="color:var(--accent);">{{ explode(' ', auth()->user()->name)[0] }}</span>
  </h1>
  <p style="color:var(--muted);font-size:14px;">Track your fitness journey and manage your subscription</p>
</div>

{{-- Near-due warning --}}
@if($nearDue && $member)
<div style="background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.3);
            border-radius:12px;padding:14px 20px;margin-bottom:24px;
            display:flex;align-items:center;gap:12px;">
  <span style="font-size:20px;">⚠️</span>
  <div>
    <strong style="color:var(--warning);">Subscription Expiring Soon</strong>
    <div style="font-size:13px;color:var(--muted);margin-top:2px;">
      Your {{ $member->membership_type }} plan expires on
      <strong>{{ $member->end_date->format('M d, Y') }}</strong>.
      <a href="{{ route('member.select-plan') }}" style="color:var(--accent);margin-left:6px;">Renew now →</a>
    </div>
  </div>
</div>
@endif

{{-- Top Row: Subscription + Profile --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

  {{-- Current Subscription Card --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
      <div style="font-size:16px;font-weight:700;">Current Subscription</div>
      <svg width="20" height="20" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
    </div>

    @if($member)
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <span style="font-size:28px;">
          @php
            $icons = ['Calisthenics'=>'🤸','Bodybuilding'=>'💪','Plyometrics'=>'⚡','Powerlifting'=>'🏋️','Endurance'=>'🏃','Functional Training'=>'🔄','Hybrid Training'=>'🎯'];
          @endphp
          {{ $icons[$member->fitness_plan] ?? '🏋️' }}
        </span>
        <div style="font-size:22px;font-weight:700;">{{ $member->fitness_plan }}</div>
      </div>

      <div style="display:grid;gap:12px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border);">
        <div>
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;">Duration</div>
          <div style="font-weight:600;">{{ $member->membership_type }}</div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;">Instructor</div>
          <div style="font-weight:600;">{{ $member->instructor->name ?? 'Not assigned' }}</div>
        </div>
        <div>
          <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;">Active Period</div>
          <div style="font-weight:600;">
            {{ $member->start_date?->format('M d, Y') }} – {{ $member->end_date?->format('M d, Y') }}
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;">
        <a href="{{ route('member.select-plan') }}" class="btn btn-primary" style="flex:1;justify-content:center;">
          ⚡ {{ $member->isExpired() ? 'Renew Plan' : 'Change Plan' }}
        </a>
        <button type="button" class="btn btn-secondary"
                onclick="document.getElementById('editSubModal').style.display='flex'"
                style="padding:8px 14px;">
          ✏️
        </button>
      </div>

    @else
      <div style="text-align:center;padding:32px 0;">
        <div style="font-size:40px;margin-bottom:12px;">🏋️</div>
        <div style="font-weight:600;margin-bottom:6px;">No Active Plan</div>
        <div style="color:var(--muted);font-size:13px;margin-bottom:16px;">Choose a plan to get started</div>
        <a href="{{ route('member.select-plan') }}" class="btn btn-primary">Choose a Plan</a>
      </div>
    @endif
  </div>

  {{-- Profile Card --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
      <div style="font-size:16px;font-weight:700;">Profile</div>
      <svg width="20" height="20" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
    </div>

    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border);">
      @if(auth()->user()->photo)
        <img src="{{ asset('storage/'.auth()->user()->photo) }}"
             style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--accent);flex-shrink:0;"/>
      @else
        <div style="width:56px;height:56px;border-radius:50%;background:rgba(232,255,42,0.1);
                    border:2px solid var(--accent);display:flex;align-items:center;justify-content:center;
                    font-family:'Bebas Neue',sans-serif;font-size:22px;color:var(--accent);flex-shrink:0;">
          {{ strtoupper(substr(auth()->user()->name,0,2)) }}
        </div>
      @endif
      <div>
        <div style="font-weight:700;font-size:15px;">{{ auth()->user()->name }}</div>
        <div style="font-size:12px;color:var(--muted);">{{ auth()->user()->email }}</div>
      </div>
    </div>

    <div style="display:grid;gap:14px;margin-bottom:20px;">
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;">Phone</div>
        <div style="font-weight:600;">{{ auth()->user()->phone ?? '—' }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;">Member Since</div>
        <div style="font-weight:600;">{{ auth()->user()->created_at->format('M d, Y') }}</div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;">Status</div>
        <div>
          <span style="display:inline-block;padding:3px 10px;border-radius:5px;font-size:12px;font-weight:600;
                       background:rgba(74,222,128,0.15);color:#4ade80;">
            {{ $member ? $member->status : 'No Plan' }}
          </span>
        </div>
      </div>
    </div>

    <a href="{{ route('member.profile') }}" class="btn btn-secondary" style="width:100%;justify-content:center;">
      Edit Profile
    </a>
  </div>

</div>

{{-- Explore Plans Banner --}}
<div style="background:var(--accent);border-radius:16px;padding:28px 32px;margin-bottom:24px;
            display:flex;align-items:center;justify-content:space-between;">
  <div>
    <div style="font-size:18px;font-weight:700;color:#111;margin-bottom:4px;">Explore All Fitness Plans</div>
    <div style="font-size:13px;color:rgba(0,0,0,0.6);">Choose from 7 specialized training programs designed to help you reach your goals</div>
  </div>
  <a href="{{ route('member.select-plan') }}"
     style="background:#111;color:var(--accent);padding:12px 24px;border-radius:10px;
            font-weight:700;text-decoration:none;white-space:nowrap;font-size:14px;">
    View All Plans →
  </a>
</div>

{{-- Recent Payments --}}
<div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div style="font-size:16px;font-weight:700;">Recent Payments</div>
    <svg width="20" height="20" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24">
      <rect x="1" y="4" width="22" height="16" rx="2"/>
      <line x1="1" y1="10" x2="23" y2="10"/>
    </svg>
  </div>

  @forelse($payments->take(5) as $payment)
  <div style="display:flex;align-items:center;justify-content:space-between;
              padding:14px 0;border-bottom:1px solid var(--border);">
    <div style="display:flex;align-items:center;gap:12px;">
      <svg width="16" height="16" fill="none" stroke="var(--muted)" stroke-width="2" viewBox="0 0 24 24">
        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
      </svg>
      <div>
        <div style="font-size:14px;font-weight:600;">{{ $payment->fitness_plan }} - {{ $payment->membership_type }}</div>
        <div style="font-size:12px;color:var(--muted);">{{ $payment->payment_date->format('M d, Y') }}</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:16px;">
      <div style="text-align:right;">
        <div style="font-weight:700;color:var(--accent);">₱{{ number_format($payment->amount, 0) }}</div>
        <div style="font-size:12px;color:#4ade80;">{{ $payment->status }}</div>
      </div>
      <a href="{{ route('member.receipt', $payment) }}"
         style="color:var(--muted);text-decoration:none;" title="View Receipt">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
      </a>
    </div>
  </div>
  @empty
  <div style="text-align:center;color:var(--muted);padding:32px;">No payments yet.</div>
  @endforelse

  @if($payments->count() > 0)
  <div style="text-align:center;margin-top:16px;">
    <a href="{{ route('member.payments') }}" style="color:var(--accent);font-size:14px;text-decoration:none;font-weight:600;">
      View All Payments →
    </a>
  </div>
  @endif
</div>

{{-- Edit Subscription Modal --}}
@if($member)
<div id="editSubModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);
            z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;
              width:100%;max-width:520px;padding:32px;position:relative;max-height:90vh;overflow-y:auto;">
    <button onclick="document.getElementById('editSubModal').style.display='none'"
            style="position:absolute;top:16px;right:16px;background:none;border:none;
                   color:var(--muted);font-size:20px;cursor:pointer;">✕</button>
    <div style="font-size:18px;font-weight:700;margin-bottom:4px;">Edit Subscription</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:24px;">No charge will be made.</div>
    <form method="POST" action="{{ route('member.subscription.update') }}">
      @csrf
      <div style="margin-bottom:20px;">
        <label class="form-label">Fitness Plan</label>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
          @foreach(['Calisthenics','Bodybuilding','Plyometrics','Powerlifting','Endurance','Functional Training','Hybrid Training'] as $plan)
            <label style="cursor:pointer;">
              <input type="radio" name="fitness_plan" value="{{ $plan }}" style="display:none;" class="plan-radio"
                     {{ $member->fitness_plan === $plan ? 'checked' : '' }}/>
              <div class="plan-opt-card" style="border:2px solid var(--border);border-radius:8px;padding:10px;
                          font-size:13px;font-weight:600;text-align:center;transition:all 0.2s;background:var(--surface2);
                          {{ $member->fitness_plan === $plan ? 'border-color:var(--accent);background:rgba(232,255,42,0.08);color:var(--accent);' : '' }}">
                {{ $plan }}
              </div>
            </label>
          @endforeach
        </div>
      </div>
      <div style="margin-bottom:20px;">
        <label class="form-label">Duration</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
          @foreach(['Monthly'=>['₱800','30 days'],'Quarterly'=>['₱2,100','90 days'],'Annually'=>['₱7,500','365 days']] as $type=>$info)
            <label style="cursor:pointer;">
              <input type="radio" name="membership_type" value="{{ $type }}" style="display:none;" class="type-radio"
                     {{ $member->membership_type === $type ? 'checked' : '' }}/>
              <div class="type-opt-card" style="border:2px solid var(--border);border-radius:8px;padding:12px;
                          text-align:center;transition:all 0.2s;background:var(--surface2);
                          {{ $member->membership_type === $type ? 'border-color:var(--accent);background:rgba(232,255,42,0.08);' : '' }}">
                <div style="font-size:15px;font-weight:700;color:var(--accent);">{{ $info[0] }}</div>
                <div style="font-size:12px;font-weight:600;">{{ $type }}</div>
                <div style="font-size:11px;color:var(--muted);">{{ $info[1] }}</div>
              </div>
            </label>
          @endforeach
        </div>
      </div>
      <div style="margin-bottom:24px;">
        <label class="form-label">Instructor (optional)</label>
        <select name="instructor_id" class="form-control">
          <option value="">— No instructor —</option>
          @foreach(\App\Models\User::where('role','instructor')->get() as $inst)
            <option value="{{ $inst->id }}" {{ $member->instructor_id == $inst->id ? 'selected' : '' }}>
              {{ $inst->name }}{{ $inst->specialization ? ' – '.$inst->specialization : '' }}
            </option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;padding:12px;">✓ Save Changes</button>
        <button type="button" class="btn btn-secondary"
                onclick="document.getElementById('editSubModal').style.display='none'"
                style="padding:12px 20px;">Cancel</button>
      </div>
    </form>
  </div>
</div>
<style>
.plan-radio:checked+.plan-opt-card,.plan-opt-card:hover{border-color:var(--accent)!important;background:rgba(232,255,42,0.08)!important;color:var(--accent)!important;}
.type-radio:checked+.type-opt-card,.type-opt-card:hover{border-color:var(--accent)!important;background:rgba(232,255,42,0.08)!important;}
</style>
<script>
document.querySelectorAll('.plan-radio').forEach(r=>{
  r.addEventListener('change',()=>{
    document.querySelectorAll('.plan-opt-card').forEach(c=>{c.style.borderColor='var(--border)';c.style.background='var(--surface2)';c.style.color='var(--text)';});
    if(r.checked){const c=r.closest('label').querySelector('.plan-opt-card');c.style.borderColor='var(--accent)';c.style.background='rgba(232,255,42,0.08)';c.style.color='var(--accent)';}
  });
});
document.querySelectorAll('.type-radio').forEach(r=>{
  r.addEventListener('change',()=>{
    document.querySelectorAll('.type-opt-card').forEach(c=>{c.style.borderColor='var(--border)';c.style.background='var(--surface2)';});
    if(r.checked){const c=r.closest('label').querySelector('.type-opt-card');c.style.borderColor='var(--accent)';c.style.background='rgba(232,255,42,0.08)';}
  });
});
document.getElementById('editSubModal').addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
</script>
@endif

@endsection