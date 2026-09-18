<x-app-shell :title="$insight->title">

    <main class="max-w-6xl mx-auto px-6 py-10">
        <div class="grid gap-10 lg:grid-cols-[1fr_292px] lg:items-start">

            <article class="min-w-0">
                <nav class="text-xs text-muted-foreground-1 mb-5" aria-label="Breadcrumb">
                    <a href="{{ route('insights.index') }}" class="hover:text-foreground transition">Market Insights</a>
                    <span class="mx-1.5 opacity-40">/</span>
                    <a href="{{ route('insights.index', ['category' => $insight->category]) }}"
                        class="text-primary-active hover:underline">{{ $insight->category ?? 'General' }}</a>
                    <span class="mx-1.5 opacity-40">/</span>
                    <span class="text-muted-foreground-1/70">{{ \Illuminate\Support\Str::limit($insight->title, 40) }}</span>
                </nav>

                <h1 class="text-3xl md:text-[34px] font-bold text-foreground tracking-tight leading-tight">
                    {{ $insight->title }}
                </h1>

                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 mt-4">
                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5 text-xs text-muted-foreground-1">
                        <span
                            class="size-7 rounded-full bg-primary text-primary-foreground inline-flex items-center justify-center text-[11px] font-bold">
                            {{ mb_substr($insight->author?->name ?? 'S', 0, 1) }}
                        </span>
                        <span>{{ $insight->author?->name ?? 'Sectors desk' }}</span>
                        <span class="opacity-40">·</span>
                        <span>{{ $insight->published_at?->format('M d, Y') }}</span>
                        <span class="opacity-40">·</span>
                        <span>{{ $readTime }} min read</span>
                    </div>

                    <button type="button" x-data="{ copied: false }"
                        @click="navigator.clipboard.writeText(window.location.href).then(() => { copied = true; setTimeout(() => copied = false, 1800) })"
                        class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <span x-text="copied ? 'Link copied' : 'Copy link'">Copy link</span>
                    </button>
                </div>

                <div class="h-px bg-layer-line my-7"></div>

                <p class="text-[15px] leading-relaxed text-foreground border-l-2 border-primary pl-4 mb-7">
                    {{ $excerpt }}
                </p>

                <div class="insight-body max-w-[68ch] text-[15px] leading-[1.75] text-muted-foreground-1">
                    {!! $insight->content !!}
                </div>

                @if (\Illuminate\Support\Facades\Route::has('agent.workspace'))
                    <div
                        class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center bg-primary/5 border border-primary/30 rounded-xl p-5 mt-8">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-primary-active">Ask the copilot about this article</p>
                            <p class="text-xs text-muted-foreground-1 mt-1">
                                Opens the agent workspace with this article's context — no API credits are spent until
                                you send a prompt.
                            </p>
                        </div>

                        <a href="{{ route('agent.workspace', array_filter(['prompt' => 'Analyze the market impact of: ' . $insight->title])) }}"
                            class="shrink-0 py-2.5 px-4 inline-flex items-center justify-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover transition">
                            Research with AI
                        </a>
                    </div>
                @endif
            </article>

            <aside class="lg:sticky lg:top-24 space-y-4">

                @if (! empty($tickers))
                    <div class="bg-layer border border-layer-line rounded-xl p-5">
                        <h3 class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground-1 mb-3">
                            Tickers in this article
                        </h3>

                        <div class="flex flex-wrap gap-2">
                            @foreach ($tickers as $ticker)
                                @if (\Illuminate\Support\Facades\Route::has('agent.workspace'))
                                    <a href="{{ route('agent.workspace', ['ticker' => $ticker, 'prompt' => 'Run an in-depth fundamental analysis for ' . $ticker]) }}"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg font-mono text-xs font-semibold text-primary-active border border-layer-line hover:bg-layer-hover transition">
                                        {{ $ticker }}
                                    </a>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg font-mono text-xs font-semibold text-primary-active border border-layer-line">
                                        {{ $ticker }}
                                    </span>
                                @endif
                            @endforeach
                        </div>

                        <p class="text-[11px] text-muted-foreground-1/70 mt-3">
                            Click a ticker to research it with the copilot.
                        </p>
                    </div>
                @endif

                @if ($related->isNotEmpty())
                    <div class="bg-layer border border-layer-line rounded-xl p-5">
                        <h3 class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground-1 mb-1">
                            Related articles
                        </h3>

                        @foreach ($related as $item)
                            <a href="{{ route('insights.show', $item['slug']) }}"
                                class="block py-3 border-b border-layer-line last:border-b-0 last:pb-0 hover:opacity-80 transition">
                                <p class="text-[13px] font-medium text-foreground leading-snug">{{ $item['title'] }}</p>
                                <p class="text-[11px] text-muted-foreground-1 mt-1">{{ $item['category'] }} ·
                                    {{ $item['date'] }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('insights.index') }}"
                    class="block text-center py-2.5 px-4 text-sm font-medium rounded-xl border border-layer-line text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition">
                    ← All articles
                </a>
            </aside>
        </div>
    </main>

    @push('styles')
        <style>
            .insight-body p { margin-bottom: 1rem; }
            .insight-body h2 {
                font-size: 1.0625rem;
                font-weight: 600;
                color: var(--color-text-primary);
                margin: 1.75rem 0 0.75rem;
                letter-spacing: -0.01em;
            }
            .insight-body h3 {
                font-size: 1rem;
                font-weight: 600;
                color: var(--color-text-primary);
                margin: 1.5rem 0 0.625rem;
            }
            .insight-body strong { color: var(--color-text-primary); font-weight: 600; }
            .insight-body ul { list-style: disc; margin: 0 0 1rem 1.25rem; }
            .insight-body ol { list-style: decimal; margin: 0 0 1rem 1.25rem; }
            .insight-body li { margin-bottom: 0.5rem; }
            .insight-body a { color: var(--color-accent); text-decoration: underline; }
            .insight-body blockquote {
                border-left: 2px solid var(--color-border);
                padding-left: 1rem;
                color: var(--color-text-muted);
                margin: 1.25rem 0;
            }
            .insight-body code {
                font-family: ui-monospace, monospace;
                font-size: 0.8125rem;
                background: color-mix(in srgb, var(--color-accent) 10%, transparent);
                color: var(--color-accent);
                padding: 0.125rem 0.375rem;
                border-radius: 0.3125rem;
            }
        </style>
    @endpush

</x-app-shell>
