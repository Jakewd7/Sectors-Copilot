@php
    /** @var string $label */
    /** @var string $value */
    /** @var bool $fit */
    $label = $label ?? '';
    $value = $value ?? '';
    $fit = $fit ?? false; // fit=true: hug content instead of stretching
@endphp

{{-- Preline card-stat: bordered card with muted label + strong value --}}
<div class="{{ $fit ? '' : 'flex-1 ' }}min-w-[160px] w-fit bg-layer border border-layer-line rounded-xl px-5 py-4">
    <p class="text-xs text-muted-foreground-1">{{ $label }}</p>
    <p class="text-2xl font-bold text-foreground mt-1 tracking-tight">{{ $value }}</p>
</div>