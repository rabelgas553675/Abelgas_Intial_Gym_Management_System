@extends('layouts.app')

@section('page_title', 'Membership Plans')

@section('content')
<div class="plan-grid">

  <div class="plan-card">
    <div class="plan-name">Trial</div>
    <div class="plan-price">₱500</div>
    <div class="plan-period">/ 7 days</div>
    <ul class="plan-features">
      <li>Full gym access</li>
      <li>Locker included</li>
      <li>1 trainer session</li>
    </ul>
  </div>

  <div class="plan-card featured">
    <div class="plan-badge">MOST POPULAR</div>
    <div class="plan-name" style="color:var(--accent);">Monthly</div>
    <div class="plan-price">₱1,100</div>
    <div class="plan-period">/ 30 days</div>
    <ul class="plan-features">
      <li>Full gym access</li>
      <li>Locker included</li>
      <li>4 trainer sessions</li>
      <li>Group classes</li>
    </ul>
  </div>

  <div class="plan-card">
    <div class="plan-name">Yearly</div>
    <div class="plan-price">12,000</div>
    <div class="plan-period">/ 365 days</div>
    <ul class="plan-features">
      <li>Full gym access</li>
      <li>Locker included</li>
      <li>Unlimited trainer sessions</li>
      <li>Group classes</li>
      <li>2 guest passes/month</li>
    </ul>
  </div>

</div>
@endsection