@extends('layouts.auth-modal')
@section('title', 'Forgot Password – APEX FITNESS GYM')

@section('content')
  <div class="modal-heading" id="authHeading">FORGOT PASSWORD?</div>
  <div class="modal-sub">Enter your email and we'll send you a link to reset it</div>

  @if(session('status'))
    <div class="fm-success" role="status">✓ {{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="fm-error" role="alert">{{ session('error') }}</div>
  @endif

  <form method="POST" action="{{ route('password.email') }}" id="forgot-form">
    @csrf

    <div class="fm-group">
      <label class="fm-label" for="email">Email</label>
      <input type="email" id="email" name="email" class="fm-input @error('email') is-invalid @enderror"
             value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="email"/>
      @error('email')<div class="fm-field-error">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn-submit" id="forgot-btn">Send Reset Link →</button>
  </form>

  <div class="fm-footer">Remembered it? <a href="{{ url('/') }}?auth=login">Back to sign in</a></div>
@endsection

@push('scripts')
<script>
  (function () {
    var f = document.getElementById('forgot-form'), b = document.getElementById('forgot-btn');
    f.addEventListener('submit', function () { b.disabled = true; b.textContent = 'Sending…'; });
    window.addEventListener('pageshow', function (e) { if (e.persisted) { b.disabled = false; b.textContent = 'Send Reset Link →'; } });
  })();
</script>
@endpush