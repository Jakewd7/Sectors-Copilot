
    <main class="max-w-6xl mx-auto px-6 py-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-foreground tracking-tight">Market Insights</h1>
            <p class="text-sm text-muted-foreground-1 mt-2 max-w-2xl">
                Research notes and weekly reviews on the Indonesian market, written by our analyst desk.
            </p>
        </div>

        @if ($featured)
            <article
                class="bg-layer border border-layer-line rounded-2xl p-7 grid gap-6 md:grid-cols-[1fr_auto] md:items-center bg-gradient-to-br from-primary/5 to-transparent">
                <div class="min-w-0">
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary text-primary-foreground">
                        Featured · {{ $featured['category'] }}
                    </span>

                    <h2 class="text-2xl md:text-[26px] font-bold text-foreground tracking-tight leading-snug mt-3">
                        {{ $featured['title'] }}
                    </h2>

                    <p class="text-sm text-muted-foreground-1 mt-3 max-w-2xl">{{ $featured['excerpt'] }}</p>

                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs text-muted-foreground-1 mt-4">
                        <span>{{ $featured['author'] }}</span>
                        <span class="opacity-40">·</span>
                        <span>{{ $featured['date'] }}</span>
                        <span class="opacity-40">·</span>
                        <span>{{ $featured['read_time'] }} min read</span>
                    </div>
                </div>

                <a href="{{ route('insights.show', $featured['slug']) }}"
                    class="shrink-0 py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover transition">
                    Read article
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </article>
        @endif

        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-8 mb-5">
                <a href="{{ route('insights.index') }}"
                    class="py-1.5 px-3 rounded-full text-xs font-medium border transition {{ request('category') ? 'border-layer-line text-muted-foreground-1 hover:text-foreground' : 'bg-primary border-primary text-primary-foreground' }}">
                    All
                </a>

                @foreach ($categories as $category)
                    <a href="{{ route('insights.index', ['category' => $category]) }}"
                        class="py-1.5 px-3 rounded-full text-xs font-medium border transition {{ request('category') === $category ? 'bg-primary border-primary text-primary-foreground' : 'border-layer-line text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($insights->isNotEmpty())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($insights as $insight)
                    <a href="{{ route('insights.show', $insight['slug']) }}"
                        class="bg-layer border border-layer-line rounded-xl p-5 flex flex-col gap-2.5 hover:bg-layer-hover/60 transition">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-primary-active">
                            {{ $insight['category'] }}
                        </span>

                        <h3 class="text-[15px] font-semibold text-foreground leading-snug">{{ $insight['title'] }}</h3>

                        <p class="text-xs text-muted-foreground-1 flex-1">{{ $insight['excerpt'] }}</p>

                        <div class="text-[11px] text-muted-foreground-1 pt-2.5 border-t border-layer-line">
                            {{ $insight['date'] }} · {{ $insight['read_time'] }} min read
                        </div>
                    </a>
                @endforeach
            </div>
        @elseif (! $featured && request('category'))
            <div class="bg-layer border border-layer-line rounded-xl p-10 text-center">
                <p class="text-sm text-foreground font-medium">
                    No articles in “{{ request('category') }}” yet.
                </p>
                <p class="text-xs text-muted-foreground-1 mt-1.5">Try another category or browse everything.</p>

                <a href="{{ route('insights.index') }}"
                    class="mt-4 inline-flex items-center px-3.5 py-2 text-xs font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition">
                    Show all articles
                </a>
            </div>
        @elseif (! $featured)
            <div class="bg-layer border border-layer-line rounded-xl p-12 text-center">
                <span
                    class="size-11 rounded-full bg-primary/10 text-primary-active inline-flex items-center justify-center">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                </span>

                <p class="text-sm text-foreground font-medium mt-3.5">No articles published yet</p>
                <p class="text-xs text-muted-foreground-1 mt-1.5 max-w-sm mx-auto">
                    Our analyst desk is preparing the first research notes. Check back soon.
                </p>
            </div>
        @endif

        @if ($totalPages > 1)
            <nav class="flex flex-wrap items-center justify-between gap-4 mt-8" aria-label="Pagination">
                <p class="text-xs text-muted-foreground-1">
                    Showing {{ $from }} to {{ $to }} of {{ $total }} articles
                </p>

                <div class="flex items-center gap-1.5">
                    <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                        class="size-8 inline-flex items-center justify-center rounded-lg border border-layer-line text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition {{ $page <= 1 ? 'pointer-events-none opacity-50' : '' }}"
                        aria-label="Previous page">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    @for ($p = 1; $p <= $totalPages; $p++)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $p]) }}"
                            class="size-8 inline-flex items-center justify-center rounded-lg text-xs font-medium transition {{ $p === $page ? 'bg-primary text-primary-foreground border border-primary' : 'border border-layer-line text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover' }}">
                            {{ $p }}
                        </a>
                    @endfor

                    <a href="{{ $page < $totalPages ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                        class="size-8 inline-flex items-center justify-center rounded-lg border border-layer-line text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition {{ $page >= $totalPages ? 'pointer-events-none opacity-50' : '' }}"
                        aria-label="Next page">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </nav>
        @endif

    </main>
