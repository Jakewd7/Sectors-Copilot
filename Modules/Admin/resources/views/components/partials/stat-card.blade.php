@php
    /** @var string $label */
    /** @var string $value */
    $label = $label ?? '';
    $value = $value ?? '';
@endphp

<div class="flex-1 min-w-[140px]">
    <p class="text-xs text-text-muted">{{ $label }}</p>
    <p class="text-2xl font-bold text-text-primary mt-1 tracking-tight">{{ $value }}</p>
</div>