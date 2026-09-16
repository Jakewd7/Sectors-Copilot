@props(['label'])

{{-- Section label inside the sidebar (e.g. "ADMINISTRATION").
     The collapse hook is required: without it the label would stay visible in the
     icon-only rail. See the sidebar chrome rules in resources/css/preline-bridge.css
     (the sidebar currently renders this markup inline; keep this component in sync). --}}
<p class="px-3 pt-6 pb-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground-1/70 sidebar-hide-collapsed">
    {{ $label }}
</p>
