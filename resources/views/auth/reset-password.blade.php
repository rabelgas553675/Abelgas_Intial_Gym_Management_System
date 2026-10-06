@extends('layouts.auth-modal')
@section('title', 'Reset Password – APEX FITNESS GYM')

@section('content')
  <div class="modal-heading" id="authHeading">NEW PASSWORD</div>
  <div class="modal-sub">Use at least 8 characters, including a letter and a number</div>

  <form method="POST" action="{{ route('password.store') }}" id="reset-form">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="fm-group">
      <label class="fm-label" for="email">Email</label>
      <input type="email" id="email" name="email" class="fm-input @error('email') is-invalid @enderror"
             value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"/>
      @error('email')<div class="fm-field-error">{{ $message }}</div>@enderror
    </div>

    <div class="fm-group">
      <label class="fm-label" for="password">New Password</label>
      <div class="fm-pass">
        <input type="password" id="password" name="password" class="fm-input @error('password') is-invalid @enderror"
               placeholder="••••••••" required minlength="8" autocomplete="new-password"/>
        <button type="button" class="fm-eye" data-target="password" aria-label="Show password">Show</button>
      </div>
      @error('password')<div class="fm-field-error">{{ $message }}</div>@enderror
    </div>

    <div class="fm-group">
      <label class="fm-label" for="password_confirmation">Confirm Password</label>
      <div class="fm-pass">
        <input type="password" id="password_confirmation" name="password_confirmation" class="fm-input"
               placeholder="••••••••" required autocomplete="new-password"/>
        <button type="button" class="fm-eye" data-target="password_confirmation" aria-label="Show password">Show</button>
      </div>
    </div>

    <button type="submit" class="btn-submit" id="reset-btn">Reset Password →</button>
  </form>

  <div class="fm-footer">Remembered it? <a href="{{ url('/') }}?auth=login">Back to sign in</a></div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.fm-eye').forEach(function (b) {
    b.addEventListener('click', function () {
      var i = document.getElementById(b.dataset.target), show = i.type === 'password';
      i.type = show ? 'text' : 'password';
      b.textContent = show ? 'Hide' : 'Show';
      b.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
  (function () {
    var f = document.getElementById('reset-form'), b = document.getElementById('reset-btn');
    f.addEventListener('submit', function () { if (f.checkValidity()) { b.disabled = true; b.textContent = 'Resetting…'; } });
    window.addEventListener('pageshow', function (e) { if (e.persisted) { b.disabled = false; b.textContent = 'Reset Password →'; } });
  })();
</script>
@endpush