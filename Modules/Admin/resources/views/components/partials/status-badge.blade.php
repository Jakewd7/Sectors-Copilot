@php
    /** @var string $status */
    $status = $status ?? 'active';
@endphp

@if ($status === 'active')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-accent/15 text-accent">Active</span>
@elseif ($status === 'suspended')
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-danger/15 text-danger">Suspended</span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-border text-text-muted">{{ ucfirst($status) }}</span>
@endif