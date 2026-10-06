{{--
    Status pill for a membership. Accepts any of:
      <x-membership-status-badge :exp="$exp" />
      <x-membership-status-badge :member="$member" />
      <x-membership-status-badge :status="$member->status" />
    Add :days="true" to show "N days left" under the pill.
    Colors are inline so it doesn't depend on any stylesheet.
--}}
@props(['exp' => null, 'member' => null, 'status' => null, 'days' => false])
@php
    if (!$exp && $member && method_exists($member, 'expiration')) {
        try { $exp = $member->expiration(); } catch (\Throwable $e) { $exp = null; }
    }

    $rawStatus = $exp->status ?? $status ?? ($member->status ?? null);
    $state = $exp->state ?? null;

    if (!$state) {
        $s = strtolower((string) $rawStatus);
        $state = match (true) {
            str_contains($s, 'expiring') => 'expiring',
            str_contains($s, 'expired')  => 'expired',
            str_contains($s, 'suspend')  => 'suspended',
            str_contains($s, 'inactive') => 'inactive',
            str_contains($s, 'active')   => 'active',
            default                      => 'pending',
        };
    }

    $colors = [
        'active'    => ['#4ade80', 'rgba(74,222,128,.12)'],
        'expiring'  => ['#fbbf24', 'rgba(251,191,36,.14)'],
        'expired'   => ['#f87171', 'rgba(248,113,113,.12)'],
        'suspended' => ['#fb923c', 'rgba(251,146,60,.14)'],
        'inactive'  => ['#94a3b8', 'rgba(148,163,184,.14)'],
        'pending'   => ['#facc15', 'rgba(250,204,21,.12)'],
    ];
    [$fg, $bg] = $colors[$state] ?? $colors['pending'];

    $label = $rawStatus ?: ucfirst($state);

    $daysText = null;
    if ($days && $exp && method_exists($exp, 'shortText')) {
        try { $daysText = $exp->shortText(); } catch (\Throwable $e) { $daysText = null; }
    }
@endphp

<span style="display:inline-flex;flex-direction:column;align-items:center;gap:6px;">
    <span {{ $attributes->merge(['style' => "display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;white-space:nowrap;color:{$fg};background:{$bg};"]) }}>
        <span style="width:6px;height:6px;border-radius:50%;flex-shrink:0;background:{{ $fg }};"></span>
        {{ $label }}
    </span>
    @if($daysText)
        <span style="font-size:12px;font-weight:600;color:{{ $fg }};">{{ $daysText }}</span>
    @endif
</span>