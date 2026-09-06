<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Sectors Copilot') }}</title>

    @fonts
    @vite(['resources/css/app.css'])

    <!-- Markdown renderer (live preview in the Market Article editor) - local vendor copy -->
    <script src="{{ asset('js/vendor/marked.min.js') }}"></script>

    {{-- Page-level scripts (Alpine component registrations) MUST come before the Alpine core below --}}
    @stack('scripts')

    <!-- Alpine.js - local vendor copy (CDN unreliable on some networks) -->
    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>
</head>

<body class="bg-base text-text-primary font-sans antialiased min-h-screen flex flex-col">

    <!-- TOP BAR NAVIGATION -->
    <header class="border-b border-border bg-surface shrink-0">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between gap-6">
            <nav class="flex items-center gap-1 overflow-x-auto" aria-label="Admin sections">
                {{ $navigation ?? '' }}
            </nav>

            <div class="flex items-center gap-3 shrink-0">
                @auth
                    <span class="text-xs text-text-muted hidden sm:inline">{{ auth()->user()->email }}</span>
                @endauth
                <!-- overflow menu placeholder (matches the "..." in the mockup) -->
                <button type="button" class="w-8 h-8 rounded-lg hover:bg-border text-text-muted transition"
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