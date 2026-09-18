@props(['href', 'active' => false, 'icon' => null])

@php
    $base = 'group flex items-center gap-x-3 rounded-lg px-3 py-2.5 text-sm transition sidebar-center-collapsed';
    $state = $active
        ? 'bg-primary/15 text-primary-active font-semibold'
        : 'text-muted-foreground-1 font-medium hover:bg-layer-hover hover:text-foreground';
@endphp

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
   {{ $attributes->merge(['class' => $base . ' ' . $state]) }}>
    @if ($icon)
        <span class="shrink-0 inline-flex items-center justify-center size-5">{{ $icon }}</span>
    @endif

    <span class="truncate sidebar-hide-collapsed">{{ $slot }}</span>
</a>
