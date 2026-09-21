@extends('layouts.landing')

@section('content')
    @php
        try {
            $latestInsights = \App\Models\MarketInsight::query()
                ->published()
                ->latest('published_at')
                ->limit(3)
                ->get();
        } catch (\Throwable $e) {
            $latestInsights = collect();
        }
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- 2. HERO + PRODUCT PREVIEW --}}
        <section class="pt-10 sm:pt-24 pb-10 sm:pb-14 text-center">
            <span
                class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-3.5 rounded-full text-[11px] font-bold tracking-wider uppercase text-accent bg-accent/10 border border-accent/30 text-left">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 shrink-0"></i>
                <span>{{ __('badge_title') }}</span>
            </span>

            <h1
                class="text-[2rem] sm:text-5xl lg:text-[3.4rem] font-extrabold text-text-primary tracking-[-0.03em] leading-[1.08] sm:leading-[1.05] max-w-[20ch] mx-auto mt-5 sm:mt-6">
                {{ __('hero_title') }}
            </h1>

            <p class="text-[13.5px] sm:text-base text-text-muted leading-relaxed max-w-2xl mx-auto mt-4 sm:mt-5">
                {{ __('hero_desc') }}
            </p>

            <div class="flex flex-col sm:flex-row flex-wrap justify-center gap-3 mt-7 sm:mt-8">
                <a href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#' }}"
                    class="px-6 py-3.5 bg-accent text-accent-foreground rounded-xl text-sm font-semibold hover:bg-accent-dim transition inline-flex items-center justify-center gap-2 shadow-lg shadow-accent/20">
                    <span>{{ __('cta_button') }}</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="{{ \Illuminate\Support\Facades\Route::has('insights.index') ? route('insights.index') : route('login') }}"
                    class="px-6 py-3.5 bg-surface border border-border text-text-primary rounded-xl text-sm font-semibold hover:bg-layer-hover transition inline-flex items-center justify-center gap-2">
                    {{ __('cta_secondary') }}
                </a>
            </div>

            {{-- Product preview: the product is the proof (Linear pattern) --}}
            <div
                class="mt-10 sm:mt-14 text-left rounded-2xl border border-border bg-surface overflow-hidden shadow-2xl shadow-black/30">
                <div class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 sm:py-3 border-b border-border bg-base/60">
                    <span class="size-2.5 rounded-full bg-border shrink-0"></span>
                    <span class="size-2.5 rounded-full bg-border shrink-0"></span>
                    <span class="size-2.5 rounded-full bg-border shrink-0"></span>
                    <span class="ml-2 sm:ml-3 text-[10px] sm:text-[11px] font-mono text-text-muted truncate">{{ __('preview_url') }}</span>
                </div>

                <div class="grid lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="p-4 sm:p-6 border-b lg:border-b-0 lg:border-r border-border space-y-4">
                        <div class="flex justify-end">
                            <p
                                class="max-w-[92%] px-4 py-2.5 rounded-2xl rounded-br-sm bg-accent text-accent-foreground text-[13px] font-medium leading-snug">
                                {{ __('preview_question') }}
                            </p>
                        </div>

                        <div class="space-y-2.5 max-w-[95%]">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-accent">
                                <i data-lucide="bot" class="w-3.5 h-3.5"></i>
                                <span>Sectors Copilot</span>
                            </div>
                            <p class="text-[13px] text-text-primary leading-relaxed">
                                {{ __('preview_answer') }}
                            </p>
                            <div class="flex flex-wrap gap-2 pt-1">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-mono text-accent bg-accent/10 border border-accent/25">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    {{ __('preview_tool_1') }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-mono text-accent bg-accent/10 border border-accent/25">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    {{ __('preview_tool_2') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <h3 class="text-[11px] font-bold uppercase tracking-[0.08em] text-text-muted">
                            {{ __('preview_matrix_title') }}
                        </h3>

                        <div class="mt-3 text-[11.5px] sm:text-[12px]">
                            @foreach ([['BBCA', '21.4x', '21.8%'], ['BBRI', '11.2x', '19.4%'], ['BMRI', '9.6x', '22.1%'], ['BBNI', '8.9x', '14.2%']] as [$ticker, $per, $roe])
                                <div
                                    class="grid grid-cols-[1fr_auto_auto] gap-2 sm:gap-3 items-center py-2.5 border-b border-border">
                                    <span class="font-bold font-mono text-text-primary">{{ $ticker }}</span>
                                    <span class="text-text-muted tabular-nums">PER {{ $per }}</span>
                                    <span class="font-semibold text-accent tabular-nums">ROE {{ $roe }}</span>
                                </div>
                            @endforeach

                            <div class="grid grid-cols-[1fr_auto_auto] gap-2 sm:gap-3 items-center py-2.5 text-text-muted">
                                <span>{{ __('preview_row_median') }}</span>
                                <span class="tabular-nums">PER 10.4x</span>
                                <span class="tabular-nums">ROE 19.4%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-3 border-t border-border bg-base/40">
                    <p class="text-[11px] text-text-muted leading-relaxed">{{ __('preview_disclaimer') }}</p>
                </div>
            </div>
        </section>

        {{-- 3. PROBLEM -> SOLUTION --}}
        <section class="py-16 border-t border-border">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-text-muted">{{ __('problem_badge') }}</p>
            <h2 class="text-xl sm:text-3xl font-bold text-text-primary tracking-tight mt-2.5 sm:mt-3 max-w-[22ch] leading-tight">
                {{ __('problem_title') }}
            </h2>

            <div class="grid md:grid-cols-3 gap-4 sm:gap-5 mt-8 sm:mt-10">
                @foreach ([1, 2, 3] as $i)
                    <div class="bg-surface border border-border rounded-2xl p-5 sm:p-6 flex flex-col">
                        <div class="flex items-center gap-2 text-[12px] font-semibold text-danger">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            <span>{{ __('problem_' . $i . '_pain') }}</span>
                        </div>

                        <h3 class="text-base font-bold text-text-primary mt-3">{{ __('problem_' . $i . '_title') }}</h3>
                        <p class="text-[13.5px] text-text-muted leading-relaxed mt-2.5">
                            {{ __('problem_' . $i . '_desc') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 4. FEATURES (alternating, each with a UI visual) --}}
        <section id="capabilities" class="py-10 sm:py-16 border-t border-border scroll-mt-20">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-text-muted">{{ __('features_badge') }}</p>
            <h2 class="text-xl sm:text-3xl font-bold text-text-primary tracking-tight mt-2.5 sm:mt-3 max-w-[22ch] leading-tight">
                {{ __('features_title') }}
            </h2>

            <div class="mt-12 space-y-14">
                <div class="grid lg:grid-cols-2 gap-8 lg:gap-14 items-center">
                    <div>
                        <h3 class="text-xl font-bold text-text-primary">{{ __('feature_1_title') }}</h3>
                        <p class="text-[13.5px] text-text-muted leading-relaxed mt-3">{{ __('feature_1_desc') }}</p>

                        <ul class="mt-4 space-y-2">
                            @foreach ([1, 2, 3] as $p)
                                <li class="flex items-start gap-2.5 text-[13px] text-text-muted">
                                    <i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-px"></i>
                                    <span>{{ __('feature_1_point_' . $p) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="mt-5 p-3.5 bg-base/60 border border-border rounded-xl text-[12.5px] text-text-muted leading-relaxed">
                            {{ __('feature_1_example') }}
                        </p>
                    </div>

                    <div class="bg-surface border border-border rounded-2xl p-5 shadow-xl shadow-black/20">
                        <div class="space-y-px text-[11.5px]">
                            <div class="flex justify-between items-center py-2.5 border-b border-border">
                                <span class="font-mono text-text-muted">get_company_overview</span>
                                <span class="font-semibold text-accent">success</span>
                            </div>
                            <div class="flex justify-between items-center py-2.5 border-b border-border">
                                <span class="font-mono text-text-muted">GET /company/report/BMRI</span>
                                <span class="text-text-muted">4 sections</span>
                            </div>
                            <div class="flex justify-between items-center py-2.5 border-b border-border">
                                <span class="font-mono text-text-muted">get_sector_peers</span>
                                <span class="font-semibold text-accent">success</span>
                            </div>
                            <div class="flex justify-between items-center py-2.5 border-b border-border">
                                <span class="text-text-muted">Sector median PE</span>
                                <span class="font-semibold text-text-primary tabular-nums">9.62x</span>
                            </div>
                            <div class="flex justify-between items-center py-2.5">
                                <span class="text-text-muted">Intrinsic value</span>
                                <span class="font-semibold text-text-primary tabular-nums">Rp 5,180</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-8 lg:gap-14 items-center">
                    <div class="lg:order-2">
                        <h3 class="text-xl font-bold text-text-primary">{{ __('feature_2_title') }}</h3>
                        <p class="text-[13.5px] text-text-muted leading-relaxed mt-3">{{ __('feature_2_desc') }}</p>

                        <ul class="mt-4 space-y-2">
                            @foreach ([1, 2, 3] as $p)
                                <li class="flex items-start gap-2.5 text-[13px] text-text-muted">
                                    <i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-px"></i>
                                    <span>{{ __('feature_2_point_' . $p) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="mt-5 p-3.5 bg-base/60 border border-border rounded-xl text-[12.5px] text-text-muted leading-relaxed">
                            {{ __('feature_2_example') }}
                        </p>
                    </div>

                    <div class="lg:order-1 bg-surface border border-border rounded-2xl p-5 shadow-xl shadow-black/20">
                        <div class="flex items-center justify-between text-[11px] pb-2.5 border-b border-border text-text-muted">
                            <span>Ticker</span>
                            <span class="flex gap-6">
                                <span>PER</span>
                                <span>PBV</span>
                                <span>ROE</span>
                            </span>
                        </div>
                        <div class="text-[11.5px]">
                            @foreach ([['BMRI', '9.6x', '1.9x', '22.1%'], ['BBCA', '21.4x', '4.6x', '21.8%'], ['BBRI', '11.2x', '2.4x', '19.4%'], ['BBNI', '8.9x', '1.2x', '14.2%']] as [$t, $per, $pbv, $roe])
                                <div class="flex items-center justify-between py-2.5 border-b border-border">
                                    <span class="font-bold font-mono text-text-primary">{{ $t }}</span>
                                    <span class="flex gap-6 tabular-nums">
                                        <span class="text-text-muted">{{ $per }}</span>
                                        <span class="text-text-muted">{{ $pbv }}</span>
                                        <span class="font-semibold text-accent">{{ $roe }}</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-8 lg:gap-14 items-center">
                    <div>
                        <h3 class="text-xl font-bold text-text-primary">{{ __('feature_3_title') }}</h3>
                        <p class="text-[13.5px] text-text-muted leading-relaxed mt-3">{{ __('feature_3_desc') }}</p>

                        <ul class="mt-4 space-y-2">
                            @foreach ([1, 2, 3] as $p)
                                <li class="flex items-start gap-2.5 text-[13px] text-text-muted">
                                    <i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-px"></i>
                                    <span>{{ __('feature_3_point_' . $p) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="mt-5 p-3.5 bg-base/60 border border-border rounded-xl text-[12.5px] text-text-muted leading-relaxed">
                            {{ __('feature_3_example') }}
                        </p>
                    </div>

                    <div class="bg-surface border border-border rounded-2xl p-5 shadow-xl shadow-black/20">
                        <div class="p-3 rounded-xl bg-base/60 border border-border">
                            <p class="text-[11.5px] text-text-muted italic">{{ __('feature_3_example') }}</p>
                        </div>

                        <div class="mt-4 space-y-2 text-[11.5px]">
                            @foreach ([['ICBP', '3.8%', '0.31x'], ['INDF', '4.2%', '0.45x'], ['MYOR', '2.9%', '0.18x']] as [$t, $div, $der])
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-border">
                                    <span class="font-bold font-mono text-text-primary">{{ $t }}</span>
                                    <span class="text-text-muted tabular-nums">Div {{ $div }} · DER {{ $der }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. WORKSPACE --}}
        <section class="py-16 border-t border-border">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-text-muted">{{ __('workspace_badge') }}</p>
            <h2 class="text-xl sm:text-3xl font-bold text-text-primary tracking-tight mt-2.5 sm:mt-3 max-w-[22ch] leading-tight">
                {{ __('workspace_title') }}
            </h2>

            <div class="grid md:grid-cols-2 gap-4 sm:gap-5 mt-8 sm:mt-10">
                @foreach ([1, 2] as $w)
                    <div class="bg-surface border border-border rounded-2xl p-5 sm:p-6 flex items-start gap-4">
                        <div class="size-9 rounded-xl bg-accent/12 text-accent flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $w === 1 ? 'bookmark' : 'layout-grid' }}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-text-primary">{{ __('workspace_' . $w . '_title') }}</h3>
                            <p class="text-[13.5px] text-text-muted leading-relaxed mt-2">
                                {{ __('workspace_' . $w . '_desc') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 6. MARKET INSIGHTS (real published articles) --}}
        @if ($latestInsights->isNotEmpty())
            <section id="insights" class="py-10 sm:py-16 border-t border-border scroll-mt-20">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-text-muted">
                            {{ __('insights_badge') }}
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-text-primary tracking-tight mt-3 leading-tight">
                            {{ __('insights_title') }}
                        </h2>
                        <p class="text-[13.5px] text-text-muted mt-3 max-w-xl leading-relaxed">
                            {{ __('insights_desc') }}
                        </p>
                    </div>

                    <a href="{{ route('insights.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-surface border border-border rounded-xl text-[12.5px] font-semibold text-text-primary hover:bg-layer-hover transition">
                        {{ __('insights_cta') }}
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid md:grid-cols-3 gap-4 sm:gap-5 mt-7 sm:mt-9">
                    @foreach ($latestInsights as $insight)
                        <a href="{{ route('insights.show', $insight->slug) }}"
                            class="bg-surface border border-border rounded-2xl p-5 sm:p-6 flex flex-col hover:border-accent/40 transition">
                            <span class="text-[11px] font-bold uppercase tracking-[0.08em] text-accent">
                                {{ $insight->category ?? 'General' }}
                            </span>

                            <h3 class="text-base font-bold text-text-primary mt-2.5 leading-snug">
                                {{ $insight->title }}
                            </h3>

                            <p class="text-[13px] text-text-muted leading-relaxed mt-2.5 flex-1">
                                {{ \Illuminate\Support\Str::limit(strip_tags($insight->content ?? ''), 110) }}
                            </p>

                            <div class="flex items-center gap-2 text-[11px] text-text-muted mt-4 pt-3.5 border-t border-border">
                                <span>{{ $insight->published_at?->format('M d, Y') }}</span>
                                @if ($insight->author?->name)
                                    <span class="opacity-50">·</span>
                                    <span>{{ $insight->author->name }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 7. FAQ --}}
        <section id="faq" class="py-10 sm:py-16 border-t border-border scroll-mt-20">
            <p class="text-[11px] font-bold uppercase tracking-[0.1em] text-text-muted">{{ __('faq_badge') }}</p>
            <h2 class="text-xl sm:text-3xl font-bold text-text-primary tracking-tight mt-2.5 sm:mt-3 max-w-[22ch] leading-tight">
                {{ __('faq_title') }}
            </h2>

            <div class="mt-7 sm:mt-9 grid gap-2.5 sm:gap-3 lg:grid-cols-2">
                @foreach ([1, 2, 3, 4] as $f)
                    <details class="group bg-surface border border-border rounded-xl px-5 py-4">
                        <summary
                            class="flex items-center justify-between gap-4 cursor-pointer list-none text-[13.5px] font-semibold text-text-primary">
                            <span>{{ __('faq_' . $f . '_q') }}</span>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-text-muted shrink-0 transition-transform group-open:rotate-180"></i>
                        </summary>
                        <p class="text-[13px] text-text-muted leading-relaxed mt-3 sm:mt-3.5">
                            {{ __('faq_' . $f . '_a') }}
                        </p>
                    </details>
                @endforeach
            </div>
        </section>

        {{-- 8. FINAL CTA --}}
        <section class="py-10 sm:py-16">
            <div
                class="relative overflow-hidden text-center rounded-3xl border border-border bg-surface px-5 sm:px-6 py-12 sm:py-16 shadow-2xl shadow-black/25">
                <div class="absolute inset-0 pointer-events-none"
                    style="background: radial-gradient(120% 120% at 50% 0%, color-mix(in srgb, var(--theme-accent) 10%, transparent), transparent 60%)">
                </div>

                <div class="relative">
                    <h2 class="text-2xl sm:text-3xl font-bold text-text-primary tracking-tight">
                        {{ __('final_title') }}
                    </h2>
                    <p class="text-sm text-text-muted leading-relaxed mt-3.5 max-w-lg mx-auto">
                        {{ __('final_desc') }}
                    </p>

                    <div class="flex flex-wrap justify-center gap-3 mt-8">
                        <a href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#' }}"
                            class="px-6 py-3.5 bg-accent text-accent-foreground rounded-xl text-sm font-semibold hover:bg-accent-dim transition inline-flex items-center gap-2 shadow-lg shadow-accent/20">
                            <span>{{ __('cta_button') }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('insights.index') ? route('insights.index') : route('login') }}"
                            class="px-6 py-3.5 bg-base border border-border text-text-primary rounded-xl text-sm font-semibold hover:bg-layer-hover transition">
                            {{ __('footer_insights') }}
                        </a>
                    </div>

                    <p class="text-[11.5px] text-text-muted mt-6">{{ __('final_fine') }}</p>
                </div>
            </div>
        </section>

    </div>
@endsection
