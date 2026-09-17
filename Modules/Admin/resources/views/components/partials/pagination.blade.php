@php
    $page = (int) ($page ?? 1);
    $totalPages = max(1, (int) ($totalPages ?? 1));
    $from = (int) ($from ?? 0);
    $to = (int) ($to ?? 0);
    $total = (int) ($total ?? 0);

    $pageUrl = fn (int $p) => request()->fullUrlWithQuery(['page' => $p]);
@endphp

<nav class="flex items-center justify-between gap-4 px-6 py-4 border-t border-table-line" aria-label="Table pagination">
    <p class="text-sm text-muted-foreground-1">
        Showing <span class="font-medium text-foreground">{{ $from }}</span>
        to <span class="font-medium text-foreground">{{ $to }}</span>
        of <span class="font-medium text-foreground">{{ $total }}</span> data
    </p>

    <div class="flex items-center gap-x-1">

        <a href="{{ $pageUrl(max(1, $page - 1)) }}"
           class="size-8 inline-flex justify-center items-center rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition {{ $page <= 1 ? 'pointer-events-none opacity-50' : '' }}"
           aria-label="Previous page">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
        </a>

        @foreach (range(1, $totalPages) as $p)
            <a href="{{ $pageUrl($p) }}"
               class="min-w-8 size-8 inline-flex justify-center items-center rounded-lg text-sm {{ $p === $page
                   ? 'bg-primary border border-primary-line text-primary-foreground font-semibold'
                   : 'border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground transition' }}"
               @if ($p === $page) aria-current="page" @endif>
                {{ $p }}
            </a>
        @endforeach

        <a href="{{ $pageUrl(min($totalPages, $page + 1)) }}"
           class="size-8 inline-flex justify-center items-center rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition {{ $page >= $totalPages ? 'pointer-events-none opacity-50' : '' }}"
           aria-label="Next page">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
    </div>
</nav>