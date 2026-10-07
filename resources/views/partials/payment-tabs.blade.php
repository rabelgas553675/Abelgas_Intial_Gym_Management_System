{{-- Payments sub-navigation shared by Admin + Staff.
     Usage: @include('partials.payment-tabs', ['active' => 'member'])   or   ['active' => 'walkin'] --}}
@php $ptStaff = auth()->user()->isStaff(); @endphp
<style>
  .pt-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 22px;padding:5px;border:1px solid var(--border);border-radius:12px;background:var(--surface,transparent);width:fit-content;max-width:100%}
  .pt-tab{display:inline-flex;align-items:center;gap:8px;padding:9px 20px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;color:var(--muted);transition:.15s;white-space:nowrap;font-family:'DM Sans',sans-serif}
  .pt-tab svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2}
  .pt-tab:hover{color:var(--accent)}
  .pt-tab.active{background:linear-gradient(135deg,var(--accent-2),var(--accent-dark));color:#1a1a1a;font-weight:700}
  @media (max-width:480px){.pt-tabs{width:100%}.pt-tab{flex:1;justify-content:center;padding:9px 10px}}
</style>
<div class="pt-tabs" role="tablist" aria-label="Payments sections">
  <a href="{{ $ptStaff ? route('staff.payments') : route('payments.index') }}"
     class="pt-tab {{ ($active ?? '') === 'member' ? 'active' : '' }}">
    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    Member Payments
  </a>
  <a href="{{ route('walkin.index') }}"
     class="pt-tab {{ ($active ?? '') === 'walkin' ? 'active' : '' }}">
    <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM9 21l1.5-6.5L8 12l2-4 3 1.5 2.5 3M12.5 14.5L15 21"/></svg>
    Walk-In Payments
  </a>
</div>