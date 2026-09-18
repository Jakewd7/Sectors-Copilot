@props(['title' => null, 'description' => null])

@php
    $theme = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'light' : 'dark';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full {{ $theme === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}{{ config('brand.name') }}</title>

    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif

    @include('partials.prepaint-script')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('scripts')

    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>

    @stack('styles')
</head>

<body class="min-h-full bg-base text-foreground font-sans antialiased">

    <header class="sticky top-0 z-30 border-b border-layer-line bg-base/85 backdrop-blur-sm">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between gap-6">

            <a href="{{ route('insights.index') }}" class="flex items-center gap-x-2.5 min-w-0">
                @if (config('brand.logo'))
                    <img src="{{ asset(config('brand.logo')) }}" alt="{{ config('brand.name') }}"
                        class="size-8 shrink-0 rounded-lg object-contain">
                @else
                    <span
                        class="size-8 shrink-0 rounded-lg bg-primary text-primary-foreground inline-flex items-center justify-center text-sm font-bold tracking-tight">
                        {{ config('brand.mark') }}
                    </span>
                @endif

                <span class="font-semibold tracking-tight truncate">{{ config('brand.name') }}</span>
            </a>

            <nav class="flex items-center gap-x-1.5">
                <a href="{{ route('insights.index') }}"
                    class="hidden sm:inline-flex px-3 py-2 text-sm font-medium rounded-lg text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition">
                    Market Insights
                </a>

                @auth
                    @if (\Illuminate\Support\Facades\Route::has('dashboard.index'))
                        <a href="{{ route('dashboard.index') }}"
                            class="hidden sm:inline-flex px-3 py-2 text-sm font-medium rounded-lg text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition">
                            Dashboard
                        </a>
                    @endif

                    @if (\Illuminate\Support\Facades\Route::has('agent.workspace'))
                        <a href="{{ route('agent.workspace') }}"
                            class="py-2 px-3.5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover transition">
                            Ask the copilot
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                        class="py-2 px-3.5 inline-flex items-center text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover transition">
                        Sign in
                    </a>
                @endauth

                <button type="button" onclick="toggleTheme()" aria-label="Toggle theme" title="Toggle light/dark theme"
                    class="size-9 inline-flex items-center justify-center rounded-lg text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition">
                    <svg class="size-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg class="size-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 3v2m0 14v2M5.64 5.64l1.42 1.42m9.88 9.88l1.42 1.42M3 12h2m14 0h2M5.64 18.36l1.42-1.42m9.88-9.88l1.42-1.42M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </button>
            </nav>
        </div>
    </header>

    {{ $slot }}

    <footer class="border-t border-layer-line mt-16">
        <div
            class="max-w-6xl mx-auto px-6 py-8 flex flex-wrap items-center justify-between gap-3 text-xs text-muted-foreground-1">
            <span>{{ config('brand.name') }} — market research, not investment advice.</span>
            <span>© {{ now()->year }}</span>
        </div>
    </footer>

    @include('partials.toast')

</body>

</html>
