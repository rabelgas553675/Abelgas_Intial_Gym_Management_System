{{--
    Subscription card: plan, duration, active period, days remaining + progress bar.
    Every value comes from Member::expiration() (App\Services\MembershipExpiration),
    so admin, staff and member screens always agree.

    <x-membership-expiration :member="$member" />                  full card (plan + duration)
    <x-membership-expiration :member="$member" :plan="false" />    when the page already shows plan/duration
    <x-membership-expiration :member="$member" :alert="true" />    adds the member-facing warning sentence
--}}
@props(['member' => null, 'exp' => null, 'plan' => true, 'alert' => false])
@php
    $exp ??= $member?->expiration() ?? \App\Services\MembershipExpiration::calculate(null, null);

    $tone     = $exp->tone();                       // success | warning | danger | muted
    $hasDates = $exp->hasDates();
    $pct      = max(0, min(100, (int) $exp->progress));

    $foot = match (true) {
        $exp->startsInFuture              => 'Starts ' . $exp->startDate->format('M d, Y'),
        $hasDates && $exp->dateExpired    => 'Ended ' . $exp->endDate->format('M d, Y'),
        default                           => null,
    };
@endphp

@include('partials.membership-expiration-styles')

<div {{ $attributes->class(['mx-card', "mx-{$tone}"]) }}>

    <div class="mx-head">
        <div class="mx-title">Subscription</div>
        <span class="mx-badge"><span class="mx-dot"></span>{{ $exp->label() }}</span>
    </div>

    @if($plan)
        <div class="mx-grid">
            <div>
                <div class="mx-label">Plan</div>
                <div class="mx-value">{{ $member?->fitness_plan ?: '—' }}</div>
            </div>
            <div>
                <div class="mx-label">Duration</div>
                <div class="mx-value">{{ $member?->membership_type ?: '—' }}</div>
            </div>
        </div>
    @endif

    <div class="mx-period">
        <div class="mx-label">Active period</div>
        <div class="mx-value">
            @if($hasDates)
                {{ $exp->periodText() }}
            @else
                <span class="mx-unavailable">Expiration date unavailable</span>
            @endif
        </div>
    </div>

    <div class="mx-days">
        <div class="mx-label" style="margin:0;">Days remaining</div>
        <div class="mx-days-value">{{ $exp->displayText() }}</div>
    </div>

    <div class="mx-track" role="progressbar" aria-label="Membership time remaining"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}">
        <div class="mx-fill" style="width: {{ $pct }}%;"></div>
    </div>

    @if($foot)
        <div class="mx-foot">{{ $foot }}</div>
    @endif

    @if($alert && ($message = $exp->message()))
        <div class="mx-alert">
            <span>{{ $tone === 'danger' ? '⛔' : '⚠️' }}</span>
            <span>
                {{ $message }}
                @if(Route::has('member.select-plan') && auth()->user()?->isMember())
                    <a href="{{ route('member.select-plan') }}">Renew now →</a>
                @endif
            </span>
        </div>
    @endif
</div>