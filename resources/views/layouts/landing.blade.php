@php
    $theme = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'light' : 'dark';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full {{ $theme === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('brand.name') }} — {{ __('badge_title') }}</title>
    <meta name="description" content="{{ __('hero_desc') }}">

    @include('partials.prepaint-script')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-base text-text-primary font-sans antialiased min-h-screen flex flex-col selection:bg-accent selection:text-accent-foreground">

    <header class="sticky top-0 z-40 w-full border-b border-border bg-base/85 backdrop-blur-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="/" class="flex items-center gap-2.5 shrink-0">
                <div class="size-8 rounded-lg bg-accent flex items-center justify-center text-accent-foreground">
                    <i data-lucide="line-chart" class="w-4 h-4"></i>
                </div>
                <span class="font-extrabold text-[15px] tracking-tight text-text-primary">Sectors<span
                        class="text-accent">Copilot</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-7 text-[13px] text-text-muted" aria-label="Main">
                <a href="#capabilities" class="hover:text-text-primary transition">{{ __('footer_product') }}</a>
                <a href="#insights" class="hover:text-text-primary transition">{{ __('footer_insights') }}</a>
                <a href="#faq" class="hover:text-text-primary transition">{{ __('footer_faq') }}</a>
            </nav>

            <div class="flex items-center gap-2">
                <div class="inline-flex items-center p-1 bg-surface border border-border rounded-xl text-[11px] font-semibold">
                    <a href="{{ route('lang.switch', 'id') }}"
                        class="px-2.5 py-1 rounded-lg transition {{ app()->getLocale() === 'id' ? 'bg-accent text-accent-foreground' : 'text-text-muted hover:text-text-primary' }}">
                        ID
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="px-2.5 py-1 rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-accent text-accent-foreground' : 'text-text-muted hover:text-text-primary' }}">
                        EN
                    </a>
                </div>

                <button type="button" onclick="toggleTheme()" aria-label="Toggle theme" title="Toggle light/dark theme"
                    class="size-9 inline-flex items-center justify-center rounded-xl border border-border bg-surface text-text-muted hover:text-text-primary hover:bg-base transition">
                    <svg class="size-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg class="size-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 3v2m0 14v2M5.64 5.64l1.42 1.42m9.88 9.88l1.42 1.42M3 12h2m14 0h2M5.64 18.36l1.42-1.42m9.88-9.88l1.42-1.42M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </button>

                <a href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#' }}"
                    class="hidden sm:inline-flex px-4 py-2 bg-accent text-accent-foreground rounded-xl text-xs font-semibold hover:bg-accent-dim transition shadow-sm items-center gap-1.5">
                    <span>{{ __('cta_button') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-border bg-surface/40 mt-4">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14">

            <div class="grid gap-10 lg:gap-8 lg:grid-cols-[1.4fr_repeat(4,1fr)]">

                <div class="lg:pr-8">
                    <a href="/" class="flex items-center gap-2.5">
                        <div class="size-8 rounded-lg bg-accent flex items-center justify-center text-accent-foreground">
                            <i data-lucide="line-chart" class="w-4 h-4"></i>
                        </div>
                        <span class="font-extrabold text-[15px] tracking-tight text-text-primary">
                            Sectors<span class="text-accent">Copilot</span>
                        </span>
                    </a>

                    <p class="text-[12px] text-text-muted leading-relaxed mt-4 max-w-[34ch]">
                        {{ __('insights_desc') }}
                    </p>

                    <a href="{{ \Illuminate\Support\Facades\Route::has('insights.index') ? route('insights.index') : '#' }}"
                        class="inline-flex items-center gap-1.5 mt-5 text-[12px] font-semibold text-accent hover:text-accent-dim transition">
                        {{ __('footer_insights') }}
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-[0.08em] text-text-primary">
                        {{ __('footer_col_research') }}
                    </h3>
                    <ul class="mt-4 space-y-2.5">
                        <li>
                            <a href="#capabilities" class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_link_capabilities') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ \Illuminate\Support\Facades\Route::has('insights.index') ? route('insights.index') : '#' }}"
                                class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_insights') }}
                            </a>
                        </li>
                        <li>
                            <a href="#faq" class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_link_faq') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-[0.08em] text-text-primary">
                        {{ __('footer_col_workspace') }}
                    </h3>
                    <ul class="mt-4 space-y-2.5">
                        <li>
                            <a href="{{ auth()->check() ? route('dashboard.index') : ( \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#') }}"
                                class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_link_dashboard') }}
                            </a>
                        </li>
                        @if (\Illuminate\Support\Facades\Route::has('agent.workspace'))
                            <li>
                                <a href="{{ auth()->check() ? route('agent.workspace') : route('login') }}"
                                    class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                    {{ __('footer_link_agent') }}
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="#capabilities" class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_link_watchlist') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-[0.08em] text-text-primary">
                        {{ __('footer_col_company') }}
                    </h3>
                    <ul class="mt-4 space-y-2.5">
                        <li>
                            <a href="#capabilities" class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_link_about') }}
                            </a>
                        </li>
                        <li>
                            <a href="#faq" class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_link_faq') }}
                            </a>
                        </li>
                        <li>
                            <a href="mailto:hello@sectorscopilot.app"
                                class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_link_contact') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-[11px] font-bold uppercase tracking-[0.08em] text-text-primary">
                        {{ __('footer_col_account') }}
                    </h3>
                    <ul class="mt-4 space-y-2.5">
                        @auth
                            <li>
                                <a href="{{ route('workspace.profile') }}"
                                    class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                    {{ __('footer_link_profile') }}
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{ \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#' }}"
                                    class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                    {{ __('footer_link_signin') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#' }}"
                                    class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                    {{ __('footer_link_register') }}
                                </a>
                            </li>
                        @endauth
                        <li>
                            <a href="#faq" class="text-[12.5px] text-text-muted hover:text-text-primary transition">
                                {{ __('footer_legal_disclaimer') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <p class="text-[11px] text-text-muted leading-relaxed mt-12 pt-7 border-t border-border max-w-4xl">
                {{ __('footer_disclaimer') }}
            </p>

            <div class="flex flex-wrap items-center justify-between gap-4 mt-6">
                <p class="text-[11px] text-text-muted">
                    &copy; {{ date('Y') }} {{ config('brand.name') }}. {{ __('footer_rights') }}
                </p>

                <nav class="flex items-center gap-5 text-[11px] text-text-muted" aria-label="Legal">
                    <a href="#faq" class="hover:text-text-primary transition">
                        {{ __('footer_legal_privacy') }}
                    </a>
                    <a href="#faq" class="hover:text-text-primary transition">
                        {{ __('footer_legal_terms') }}
                    </a>
                    <a href="#faq" class="hover:text-text-primary transition">
                        {{ __('footer_legal_disclaimer') }}
                    </a>
                </nav>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>
