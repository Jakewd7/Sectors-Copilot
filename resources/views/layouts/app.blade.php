@php
    $theme = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'light' : 'dark';
@endphp
<!DOCTYPE html>
<html lang="id" class="{{ $theme === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sectors Copilot')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Markdown renderer - local vendor copy (CDN unreliable) -->
    <script src="{{ asset('js/vendor/marked.min.js') }}"></script>
    <!-- Chart.js, used by FinancialRadarChart later -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-base text-text-primary font-sans antialiased">

    <!-- Theme toggle: temporarily hidden; will live in the future sidebar.
         (Blade comment wrapper removed - see git history for the markup.) -->
    @php /** toggle markup parked for future sidebar; see git history */ @endphp

    @yield('content')

    <!-- copilotWorkspace Alpine data lives in the Agent workspace view
         (Modules/Agent/resources/views/workspace.blade.php) - single source,
         handles the backend SSE contract (step_progress/token/payload/done). -->

    <!-- Alpine.js core - local vendor copy, MUST come last after the script above -->
    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>

</body>

</html>