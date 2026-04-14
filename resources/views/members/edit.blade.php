@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.staff')
@section('title', 'Edit Member')
@section('page_title', 'Edit Member')

@section('topbar_actions')
  <a href="{{ route('members.index') }}" class="btn btn-secondary">← Back to Members</a>
@endsection

@section('content')
<div class="form-page">
  @if($errors->any())
    <div class="alert alert-danger">
      @foreach($errors->all() as $e)<div>✕ {{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('members.update', $member) }}">
    @csrf
    @method('PUT')
    <div class="form-card">
      <div class="form-card-title">Personal Information</div>
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
        </div>
      </div>
    </div>
    <div class="form-card">
      <div class="form-card-title">Membership Details</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Membership Plan *</label>
          <select name="membership_type" class="form-control" required>
            @foreach(['Trial','Monthly','Yearly'] as $plan)
            <option value="{{ $plan }}" {{ old('membership_type',$member->membership_type)==$plan?'selected':'' }}>{{ $plan }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            @foreach(['Active','Expired'] as $s)
            <option value="{{ $s }}" {{ old('status',$member->status)==$s?'selected':'' }}>{{ $s }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Start Date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $member->start_date) }}">
      </div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="{{ route('members.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection
