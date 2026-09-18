<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sectors Copilot') }} - Asisten Riset Pasar Modal Indonesia</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body
    class="bg-background text-text-primary font-sans antialiased min-h-screen flex flex-col selection:bg-accent selection:text-white">

    <header class="sticky top-0 z-40 w-full border-b border-border bg-surface/80 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-accent flex items-center justify-center text-white shadow-sm">
                    <i data-lucide="line-chart" class="w-4 h-4"></i>
                </div>
                <span class="font-extrabold text-base tracking-tight text-text-primary">Sectors<span
                        class="text-accent">Copilot</span></span>
            </a>

            <div class="inline-flex items-center p-1 bg-surface border border-border rounded-xl text-xs font-semibold">
                <a href="{{ route('lang.switch', 'id') }}"
                    class="px-2.5 py-1 rounded-lg transition {{ app()->getLocale() === 'id' ? 'bg-accent text-white shadow-sm' : 'text-text-muted hover:text-text-primary' }}">
                    ID
                </a>
                <a href="{{ route('lang.switch', 'en') }}"
                    class="px-2.5 py-1 rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-accent text-white shadow-sm' : 'text-text-muted hover:text-text-primary' }}">
                    EN
                </a>
            </div>

            <div>
                <a href="{{ \Illuminate\Support\Facades\Route::has('register') ? route('register') : '#' }}"
                    class="px-4 py-2 bg-accent text-white rounded-xl text-xs font-semibold hover:bg-accent-dim transition shadow-sm inline-flex items-center gap-1.5">
                    <span>Bergabung sekarang</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>

</html>