@props(['title' => null, 'navigation' => null])

@php
    // Server-rendered theme class (avoids a flash before JS runs) — the
    // theme-switcher script keeps this in sync afterwards.
    $theme = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'light' : 'dark';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full {{ $theme === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' - ' : '' }}{{ config('brand.name') }}</title>

    {{-- App chrome before first paint (inline, NOT deferred): theme + sidebar
         state. Without it the theme flickers and a collapsed rail paints
         expanded for a frame, then snaps shut. --}}
    @include('partials.prepaint-script', ['withSidebar' => true])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Markdown renderer - local vendor copy (CDN unreliable) -->
    <script src="{{ asset('js/vendor/marked.min.js') }}"></script>

    {{-- Page-level Alpine registrations MUST be declared before the Alpine core below --}}
    @stack('scripts')

    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>

    @stack('styles')
</head>

<body class="h-full bg-base text-foreground font-sans antialiased">

    {{--
        App chrome state:
          sidebarCollapsed -> desktop icon-only rail (persisted in localStorage)
          sidebarOpen      -> mobile drawer visibility (always false on desktop)
    --}}
    <div class="flex h-full overflow-hidden"
         x-data="appChrome()"
         @keydown.escape.window="sidebarOpen = false">

        <!-- MOBILE DRAWER BACKDROP -->
        <div x-show="sidebarOpen" x-cloak x-transition.opacity
             class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false" aria-hidden="true"></div>

        <!-- GLOBAL SIDEBAR (single instance, permission-filtered).
             Mobile: fixed drawer toggled by the hamburger.
             Desktop: static rail driven by --sidebar-w — collapsed to icons via
             the top-bar button, or dragged wider/narrower with the handle on its
             right edge (width persisted in localStorage).

             NOTE: the width comes from the inherited `--sidebar-w` custom
             property, NOT an inline :style binding. It is written on <html> by
             the pre-paint script (partials/prepaint-script, before first paint)
             and re-written by appChrome().applySidebarVars() on every later
             change — one owner, so the rail never paints at the wrong width. -->
        <aside class="relative h-full w-64 lg:w-[var(--sidebar-w)] flex flex-col border-r border-layer-line bg-layer
                      fixed inset-y-0 left-0 z-50 lg:static lg:z-auto
                      -translate-x-full lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <x-sidebar />

            <!-- Drag handle: resize the sidebar like a table column -->
            <div x-show="!sidebarCollapsed" x-cloak @pointerdown="startResize($event)"
                 class="hidden lg:block absolute inset-y-0 -right-px w-3 z-30 cursor-col-resize group"
                 title="Drag to resize sidebar" role="separator" aria-orientation="vertical"
                 aria-label="Resize sidebar">
                <div class="absolute inset-y-0 left-1/2 -translate-x-1/2 w-0.5 rounded-full bg-transparent
                            group-hover:bg-primary-active group-active:bg-primary-active transition-colors"></div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top bar: mobile menu button + optional sub-navigation (admin tabs) --}}
            <header class="shrink-0 border-b border-layer-line bg-layer">
                <div class="px-4 lg:px-6 h-14 flex items-center justify-between gap-4">

                    <div class="flex items-center gap-2 min-w-0">
                        <!-- Mobile: open drawer -->
                        <button type="button" @click="sidebarOpen = true"
                                class="lg:hidden shrink-0 w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 hover:text-foreground transition inline-flex items-center justify-center"
                                aria-label="Open navigation">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Desktop: collapse/expand the sidebar rail -->
                        <button type="button" @click="toggleCollapsed()"
                                class="hidden lg:inline-flex shrink-0 w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 hover:text-foreground transition items-center justify-center"
                                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                                aria-label="Toggle sidebar width">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M9 4v16M4 4h16v16H4z"/>
                            </svg>
                        </button>

                        @if ($navigation)
                            <nav class="flex items-center gap-x-1 overflow-x-auto [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
                                 aria-label="Section navigation" role="tablist" aria-orientation="horizontal">
                                {{ $navigation }}
                            </nav>
                        @endif
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="flex-1 min-h-0 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')

    {{-- Global floating notifications (toast stack) --}}
    @include('partials.toast')

</body>

</html>
