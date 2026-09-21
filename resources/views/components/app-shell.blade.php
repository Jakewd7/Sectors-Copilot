@props(['title' => null])

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

    @include('partials.prepaint-script', ['withSidebar' => true])

    @include('partials.loading-bar')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="{{ asset('js/vendor/marked.min.js') }}"></script>

    @stack('scripts')

    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>

    @stack('styles')
</head>

<body class="h-full bg-base text-foreground font-sans antialiased">

    <div class="flex h-full overflow-hidden"
         x-data="appChrome()"
         @keydown.escape.window="sidebarOpen = false">

        <div x-show="sidebarOpen" x-cloak x-transition.opacity
             class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false" aria-hidden="true"></div>

        <button type="button" @click="sidebarOpen = true" x-cloak
                class="lg:hidden fixed top-3 left-3 z-30 size-9 rounded-lg border border-layer-line bg-layer
                       text-muted-foreground-1 hover:text-foreground hover:bg-layer-hover transition
                       inline-flex items-center justify-center shadow-sm"
                aria-label="Open navigation">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <aside class="h-full w-64 lg:w-[var(--sidebar-w)] flex flex-col border-r border-layer-line bg-layer
                      fixed inset-y-0 left-0 z-50 lg:static lg:z-auto
                      -translate-x-full lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <x-sidebar />

            <div x-show="!sidebarCollapsed" x-cloak @pointerdown="startResize($event)"
                 class="hidden lg:block absolute inset-y-0 -right-px w-3 z-30 cursor-col-resize group"
                 title="Drag to resize sidebar" role="separator" aria-orientation="vertical"
                 aria-label="Resize sidebar">
                <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-0.5 rounded-full bg-transparent
                            group-hover:bg-primary-active group-active:bg-primary-active transition-colors"></div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <main class="flex-1 min-h-0 overflow-y-auto pt-14 lg:pt-0">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')

    @include('partials.toast')

</body>

</html>
