@extends('layouts.landing')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

        <section class="text-center space-y-6 pt-8 pb-4">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-accent/15 text-accent border border-accent/30">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>{{ __('badge_title') }}</span>
            </div>

            <h1
                class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-text-primary tracking-tight max-w-4xl mx-auto leading-tight">
                {{ __('hero_title') }}
            </h1>

            <p class="text-sm sm:text-base text-text-muted max-w-2xl mx-auto leading-relaxed">
                {{ __('hero_desc') }}
            </p>

            <div class="pt-2">
                <a href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#' }}"
                    class="px-6 py-3 bg-accent text-white rounded-xl text-sm font-semibold hover:bg-accent-dim transition inline-flex items-center gap-2 shadow-sm">
                    <span>{{ __('cta_button') }}</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </section>

        <section class="space-y-6">
            <div>
                <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider">{{ __('features_badge') }}</h2>
                <p class="text-xl font-bold text-text-primary tracking-tight mt-1">{{ __('features_title') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                <div
                    class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-accent/15 text-accent flex items-center justify-center">
                            <i data-lucide="message-square" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-text-primary">{{ __('feature_1_title') }}</h3>
                        <p class="text-text-muted leading-relaxed">
                            {{ __('feature_1_desc') }}
                        </p>
                    </div>
                    <div class="p-3 bg-background/60 border border-border/80 rounded-xl text-text-muted text-[11px]">
                        {{ __('feature_1_example') }}
                    </div>
                </div>

                <div
                    class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-accent/15 text-accent flex items-center justify-center">
                            <i data-lucide="columns-3" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-text-primary">{{ __('feature_2_title') }}</h3>
                        <p class="text-text-muted leading-relaxed">
                            {{ __('feature_2_desc') }}
                        </p>
                    </div>
                    <div class="p-3 bg-background/60 border border-border/80 rounded-xl text-text-muted text-[11px]">
                        {{ __('feature_2_example') }}
                    </div>
                </div>

                <div
                    class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-accent/15 text-accent flex items-center justify-center">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-text-primary">{{ __('feature_3_title') }}</h3>
                        <p class="text-text-muted leading-relaxed">
                            {{ __('feature_3_desc') }}
                        </p>
                    </div>
                    <div class="p-3 bg-background/60 border border-border/80 rounded-xl text-text-muted text-[11px]">
                        {{ __('feature_3_example') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-surface border border-border rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
            <div>
                <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider">{{ __('workspace_badge') }}</h2>
                <p class="text-xl font-bold text-text-primary tracking-tight mt-1">{{ __('workspace_title') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="bg-background/60 p-4 rounded-xl border border-border/80 flex items-start gap-3">
                    <div
                        class="w-7 h-7 rounded-lg bg-accent/15 text-accent flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="bookmark" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-text-primary text-sm">{{ __('workspace_1_title') }}</h4>
                        <p class="text-text-muted text-[11px] mt-1 leading-relaxed">
                            {{ __('workspace_1_desc') }}
                        </p>
                    </div>
                </div>

                <div class="bg-background/60 p-4 rounded-xl border border-border/80 flex items-start gap-3">
                    <div
                        class="w-7 h-7 rounded-lg bg-accent/15 text-accent flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-text-primary text-sm">{{ __('workspace_2_title') }}</h4>
                        <p class="text-text-muted text-[11px] mt-1 leading-relaxed">
                            {{ __('workspace_2_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection