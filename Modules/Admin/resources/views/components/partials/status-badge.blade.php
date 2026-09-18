@php
    /** @var string $status */
    $status = $status ?? 'active';
@endphp

@if ($status === 'active')
    <span
        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-primary text-primary-foreground">Active</span>
@elseif ($status === 'inactive' || $status === 'suspended')
    <span
        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-rose-600 text-white">Inactive</span>
@else
    <span
        class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-surface-4 text-foreground-inverse">{{ ucfirst($status) }}</span>
@endif