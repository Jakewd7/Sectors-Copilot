@php
    $theme = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'light' : 'dark';
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full {{ $theme === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Sectors Copilot') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Markdown renderer (live preview in the Market Article editor) - local vendor copy -->
    <script src="{{ asset('js/vendor/marked.min.js') }}"></script>

    {{-- Page-level scripts (Alpine component registrations) MUST come before the Alpine core below --}}
    @stack('scripts')

    <!-- Alpine.js - local vendor copy (CDN unreliable on some networks) -->
    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>
</head>

<body class="bg-background text-foreground font-sans antialiased min-h-screen flex flex-col">

    <!-- TOP BAR NAVIGATION (Preline underline-tabs pattern) -->
    <header class="border-b border-line-2 bg-surface shrink-0 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between gap-6">
            <nav class="flex items-center gap-x-1 overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
                 aria-label="Admin sections" role="tablist"
                 aria-orientation="horizontal">
                {{ $navigation ?? '' }}
            </nav>

            <div class="flex items-center gap-3 shrink-0">
                @auth
                    <span class="text-xs text-muted-foreground-1 hidden sm:inline">{{ auth()->user()->email }}</span>
                @endauth

                <!-- Theme toggle (light/dark) -->
                <button type="button" onclick="toggleTheme()" aria-label="Toggle theme"
                        title="Toggle light/dark theme"
                        class="w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 hover:text-foreground transition inline-flex items-center justify-center">
                    <!-- moon (visible in light mode) -->
                    <svg class="w-4.5 h-4.5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <!-- sun (visible in dark mode) -->
                    <svg class="w-4.5 h-4.5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <!-- overflow menu placeholder (matches the "..." in the mockup) -->
                <button type="button" class="w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 transition"
                        aria-label="More options">
                    <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-6 py-8">
        {{ $slot }}
    </main>

</body>

</html>