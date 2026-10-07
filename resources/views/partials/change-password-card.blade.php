{{--
  Change Password card — shared by the Admin, Staff, Instructor and Member profile pages.
  Usage:  @include('partials.change-password-card')

  - Posts to the single authenticated route  PUT /password  (route name: password.update)
  - Only ever changes the logged-in user's own password.
  - Colours come from the theme tokens every layout already defines, so it works in Light + Dark mode.
--}}
@php
    $cpBag     = $errors->getBag('updatePassword');
    $cpSuccess = session('password_success');
    $cpFailure = session('password_error');
    $cpHasErr  = $cpBag->any() || $cpFailure;
@endphp

<style>
  .cp-card{margin-top:24px;background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow-card,none);scroll-margin-top:90px;min-width:0}
  .cp-card, .cp-card *{box-sizing:border-box}
  .cp-head{display:flex;align-items:center;gap:14px;margin-bottom:6px}
  .cp-ico{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;flex-shrink:0;background:rgba(224,169,59,.12);border:1px solid rgba(224,169,59,.30)}
  .cp-ico svg{width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2}
  .cp-title{font-size:18px;font-weight:700;color:var(--text);line-height:1.2}
  .cp-sub{font-size:13px;color:var(--muted);margin:2px 0 0}
  .cp-alert{display:flex;gap:8px;align-items:flex-start;padding:12px 14px;border-radius:10px;font-size:13px;margin:18px 0 0}
  .cp-alert.ok{background:rgba(74,222,128,.10);border:1px solid rgba(74,222,128,.30);color:var(--success)}
  .cp-alert.bad{background:rgba(248,113,113,.10);border:1px solid rgba(248,113,113,.30);color:var(--danger)}
  .cp-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-top:22px}
  @media (max-width:900px){.cp-grid{grid-template-columns:1fr}}
  .cp-field label{display:block;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:7px}
  .cp-input{position:relative}
  .cp-input .form-control{width:100%;padding-right:46px;font-size:15px}
  .cp-eye{position:absolute;top:50%;right:6px;transform:translateY(-50%);width:36px;height:36px;display:grid;place-items:center;border:0;border-radius:8px;background:transparent;color:var(--muted);cursor:pointer;transition:color .15s,background .15s}
  .cp-eye:hover{color:var(--accent);background:rgba(224,169,59,.10)}
  .cp-eye:focus-visible{outline:2px solid var(--accent);outline-offset:1px}
  .cp-eye svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;pointer-events:none}
  .cp-eye .off{display:none}
  .cp-eye.shown .on{display:none}
  .cp-eye.shown .off{display:block}
  .cp-error{color:var(--danger);font-size:12.5px;margin-top:6px}
  .cp-field.has-error .form-control{border-color:var(--danger)}
  .cp-match{font-size:12.5px;margin-top:6px;min-height:18px;color:var(--muted)}
  .cp-match.ok{color:var(--success)} .cp-match.bad{color:var(--danger)}
  .cp-reqs{margin:20px 0 0;padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--surface2)}
  .cp-reqs b{display:block;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
  .cp-reqs ul{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:6px 18px}
  .cp-reqs li{font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px}
  .cp-reqs li::before{content:'○';font-size:12px;width:14px;text-align:center}
  .cp-reqs li.ok{color:var(--success)}
  .cp-reqs li.ok::before{content:'✓';font-weight:700}
  .cp-actions{display:flex;justify-content:flex-end;margin-top:20px}
  .cp-actions .btn{min-height:44px;padding:11px 26px;justify-content:center}
  .cp-actions .btn[disabled]{opacity:.65;cursor:not-allowed;transform:none;filter:none}
  @media (max-width:640px){.cp-card{padding:20px 16px;border-radius:12px}.cp-actions .btn{width:100%}}
</style>

<section class="cp-card" id="change-password" aria-labelledby="cp-title">
  <div class="cp-head">
    <div class="cp-ico">
      <svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0v4"/></svg>
    </div>
    <div>
      <div class="cp-title" id="cp-title">Change Password</div>
      <p class="cp-sub">Use a strong password you don't use anywhere else.</p>
    </div>
  </div>

  @if($cpSuccess)
    <div class="cp-alert ok" role="status">✓ <span>{{ $cpSuccess }}</span></div>
  @endif
  @if($cpFailure)
    <div class="cp-alert bad" role="alert">✕ <span>{{ $cpFailure }}</span></div>
  @endif

  <form method="POST" action="{{ route('password.update') }}" id="cp-form" autocomplete="off" novalidate>
    @csrf
    @method('PUT')

    <div class="cp-grid">
      <div class="cp-field {{ $cpBag->has('current_password') ? 'has-error' : '' }}">
        <label for="cp-current">Current Password</label>
        <div class="cp-input">
          <input type="password" id="cp-current" name="current_password" class="form-control"
                 autocomplete="current-password" required>
          <button type="button" class="cp-eye" aria-label="Show current password" aria-pressed="false">
            <svg class="on" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="off" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 5.1A10.4 10.4 0 0112 5c6.5 0 10 7 10 7a17.6 17.6 0 01-3.2 4.2M6.6 6.6A17.7 17.7 0 002 12s3.5 7 10 7a10.3 10.3 0 004.4-1"/></svg>
          </button>
        </div>
        @if($cpBag->has('current_password'))<div class="cp-error">{{ $cpBag->first('current_password') }}</div>@endif
      </div>

      <div class="cp-field {{ $cpBag->has('password') ? 'has-error' : '' }}">
        <label for="cp-new">New Password</label>
        <div class="cp-input">
          <input type="password" id="cp-new" name="password" class="form-control"
                 autocomplete="new-password" minlength="8" required>
          <button type="button" class="cp-eye" aria-label="Show new password" aria-pressed="false">
            <svg class="on" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="off" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 5.1A10.4 10.4 0 0112 5c6.5 0 10 7 10 7a17.6 17.6 0 01-3.2 4.2M6.6 6.6A17.7 17.7 0 002 12s3.5 7 10 7a10.3 10.3 0 004.4-1"/></svg>
          </button>
        </div>
        @if($cpBag->has('password'))<div class="cp-error">{{ $cpBag->first('password') }}</div>@endif
      </div>

      <div class="cp-field">
        <label for="cp-confirm">Confirm New Password</label>
        <div class="cp-input">
          <input type="password" id="cp-confirm" name="password_confirmation" class="form-control"
                 autocomplete="new-password" required>
          <button type="button" class="cp-eye" aria-label="Show confirm password" aria-pressed="false">
            <svg class="on" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg class="off" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 5.1A10.4 10.4 0 0112 5c6.5 0 10 7 10 7a17.6 17.6 0 01-3.2 4.2M6.6 6.6A17.7 17.7 0 002 12s3.5 7 10 7a10.3 10.3 0 004.4-1"/></svg>
          </button>
        </div>
        <div class="cp-match" id="cp-match" aria-live="polite"></div>
      </div>
    </div>

    {{-- These four rules are exactly what PasswordController enforces on the server. --}}
    <div class="cp-reqs">
      <b>Password requirements</b>
      <ul>
        <li data-rule="len">At least 8 characters</li>
        <li data-rule="letter">At least one letter</li>
        <li data-rule="number">At least one number</li>
        <li data-rule="diff">Different from your current password</li>
      </ul>
    </div>

    <div class="cp-actions">
      <button type="submit" class="btn btn-primary" id="cp-submit">Change Password</button>
    </div>
  </form>
</section>

<script>
  (function () {
    var form    = document.getElementById('cp-form');
    if (!form) return;
    var cur     = document.getElementById('cp-current');
    var neu     = document.getElementById('cp-new');
    var conf    = document.getElementById('cp-confirm');
    var match   = document.getElementById('cp-match');
    var submit  = document.getElementById('cp-submit');
    var rules   = {};
    form.querySelectorAll('[data-rule]').forEach(function (li) { rules[li.dataset.rule] = li; });

    // Show / hide (buttons are type="button" so they never submit the form)
    form.querySelectorAll('.cp-eye').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = btn.parentElement.querySelector('input');
        var show  = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.classList.toggle('shown', show);
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', (show ? 'Hide ' : 'Show ') + btn.getAttribute('aria-label').replace(/^(Show|Hide) /, ''));
      });
    });

    function mark(name, ok) { if (rules[name]) rules[name].classList.toggle('ok', !!ok); }

    function refresh() {
      var v = neu.value;
      mark('len',    v.length >= 8);
      mark('letter', /\p{L}/u.test(v));
      mark('number', /\d/.test(v));
      mark('diff',   v.length > 0 && cur.value.length > 0 && v !== cur.value);

      if (!conf.value) { match.textContent = ''; match.className = 'cp-match'; }
      else if (conf.value === v) { match.textContent = '✓ Passwords match'; match.className = 'cp-match ok'; }
      else { match.textContent = 'Passwords do not match'; match.className = 'cp-match bad'; }
    }
    [cur, neu, conf].forEach(function (el) { el.addEventListener('input', refresh); });
    refresh();

    // Loading / disabled state while submitting (server still validates everything)
    form.addEventListener('submit', function () {
      if (submit.disabled) return;
      submit.disabled = true;
      submit.textContent = 'Changing…';
    });
    // Restore the button if the page is shown again from the back/forward cache
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) { submit.disabled = false; submit.textContent = 'Change Password'; }
    });

    // After saving (or a validation error) bring the card into view
    @if($cpSuccess || $cpHasErr)
      var card = document.getElementById('change-password');
      if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    @endif
  })();
</script>