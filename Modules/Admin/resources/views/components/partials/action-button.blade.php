@php
    $action = $action ?? 'edit';
@endphp

@if ($action === 'edit')
    <button type="button" {{ $attributes->merge(['class' => 'w-8 h-8 rounded-lg inline-flex justify-center items-center border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden focus:text-primary-focus transition']) }} aria-label="Edit" title="Edit">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
    </button>
@elseif ($action === 'delete')
    <button type="button" {{ $attributes->merge(['class' => 'w-8 h-8 rounded-lg inline-flex justify-center items-center border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-danger focus:outline-hidden transition']) }} aria-label="Delete" title="Delete">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
    </button>
@elseif ($action === 'flush')
    <button type="button" {{ $attributes->merge(['class' => 'px-3 py-1.5 text-xs font-medium rounded-lg border border-layer-line bg-layer text-foreground hover:bg-layer-hover focus:outline-hidden transition']) }}>
        Flush
    </button>
@endif