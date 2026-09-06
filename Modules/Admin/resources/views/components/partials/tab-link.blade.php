@php
    /** @var string $href */
    /** @var bool $active */
    $active = $active ?? false;
@endphp

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'px-3.5 py-1.5 rounded-lg text-sm font-medium whitespace-nowrap transition ' . ($active
       ? 'bg-border text-text-primary'
       : 'text-text-muted hover:text-text-primary hover:bg-border/50')]) }}
   :aria-current="$el => $active ? 'page' : null">
    {{ $slot }}
</a>