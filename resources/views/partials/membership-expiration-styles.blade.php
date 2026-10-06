{{-- Shared styles for <x-membership-expiration> and <x-membership-status-badge>.
     Uses each layout's own CSS variables (with fallbacks) so dark/light mode and
     the existing gold/dark design carry over. Rendered once per page. --}}
@once
<style>
    .mx-success { --mx: var(--success, #4ade80); }
    .mx-warning { --mx: var(--warning, #fbbf24); }
    .mx-danger  { --mx: var(--danger,  #f87171); }
    .mx-muted   { --mx: var(--muted,   #94a3b8); }

    .mx-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 11px; border-radius: 999px;
        font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
        color: var(--mx);
        background: color-mix(in srgb, var(--mx) 13%, transparent);
        border: 1px solid color-mix(in srgb, var(--mx) 28%, transparent);
        white-space: nowrap;
    }
    .mx-badge .mx-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; box-shadow: 0 0 6px currentColor; flex-shrink: 0; }

    .mx-card { width: 100%; box-sizing: border-box; }
    .mx-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
    .mx-title { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--muted, #888); }

    .mx-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 16px; margin-bottom: 14px; }
    .mx-label { font-size: 10.5px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--muted, #888); margin-bottom: 3px; }
    .mx-value { font-size: 14.5px; font-weight: 600; color: var(--text, inherit); overflow-wrap: anywhere; }
    .mx-period { margin-bottom: 14px; }

    .mx-days { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 8px; }
    .mx-days-value { font-size: 20px; font-weight: 800; color: var(--mx); }

    .mx-track { height: 10px; border-radius: 999px; overflow: hidden; background: color-mix(in srgb, var(--mx) 14%, var(--surface3, rgba(128,128,128,.18))); }
    .mx-fill  { height: 100%; border-radius: 999px; background: var(--mx); transition: width .6s ease; min-width: 0; }
    .mx-foot  { margin-top: 6px; font-size: 11.5px; color: var(--muted, #888); }

    .mx-alert {
        display: flex; gap: 9px; align-items: flex-start; margin-top: 14px;
        padding: 10px 12px; border-radius: 10px; font-size: 13px; line-height: 1.45;
        color: var(--mx);
        background: color-mix(in srgb, var(--mx) 11%, transparent);
        border: 1px solid color-mix(in srgb, var(--mx) 25%, transparent);
    }
    .mx-alert a { color: inherit; font-weight: 700; text-decoration: underline; }
    .mx-unavailable { color: var(--muted, #888); font-style: italic; }

    @media (max-width: 480px) {
        .mx-grid { grid-template-columns: 1fr; }
        .mx-days-value { font-size: 18px; }
    }
</style>
@endonce