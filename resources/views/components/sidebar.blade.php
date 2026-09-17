@php
    $user = auth()->user();

    $sections = [
        [
            'label' => null,
            'items' => [
                ['route' => 'dashboard.index', 'label' => 'Dashboard', 'icon' => 'grid', 'can' => 'dashboard.view'],
            ],
        ],
        [
            'label' => 'Research',
            'items' => [
                ['route' => 'agent.workspace', 'label' => 'Agent Workspace', 'icon' => 'sparkles', 'can' => 'agent.chat.view'],
            ],
        ],
        [
            'label' => 'Administration',
            'items' => [
                ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => 'users', 'can' => 'admin.users.view'],
                ['route' => 'admin.insights.index', 'label' => 'Market Articles', 'icon' => 'news', 'can' => 'admin.insights.manage'],
                ['route' => 'admin.prompts.index', 'label' => 'Prompt Starters', 'icon' => 'chat', 'can' => 'admin.prompts.manage'],
                ['route' => 'admin.caches.index', 'label' => 'Cache & Usage', 'icon' => 'database', 'can' => 'admin.system.cache-manage'],
                // TODO(backend): permission does not exist yet in RolePermissionSeeder
                ['route' => 'admin.roles.index', 'label' => 'Roles & Access', 'icon' => 'shield', 'can' => 'admin.roles.view'],
            ],
        ],
        [
            'label' => 'Account',
            'items' => [
                ['route' => 'workspace.profile', 'label' => 'Profile', 'icon' => 'user', 'can' => 'profile.view'],
            ],
        ],
    ];

    $currentRoute = request()->route()?->getName() ?? '';
    $activeRoute = null;
    foreach ($sections as $section) {
        foreach ($section['items'] as $item) {
            if ($currentRoute === $item['route'] || str_starts_with($currentRoute, $item['route'] . '.')) {
                if ($activeRoute === null || strlen($item['route']) > strlen($activeRoute)) {
                    $activeRoute = $item['route'];
                }
            }
        }
    }

    $iconPaths = [
        'grid' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
        'sparkles' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        'news' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
        'bookmark' => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
        'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'chat' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'database' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    ];
@endphp

<div class="h-full w-full flex flex-col">

    <div class="h-16 shrink-0 px-3 flex items-center gap-2 border-b border-layer-line">
        <a href="{{ \Illuminate\Support\Facades\Route::has('dashboard.index') ? route('dashboard.index') : url('/') }}"
           class="flex items-center gap-x-2.5 min-w-0 flex-1 sidebar-center-collapsed">
            @if (config('brand.logo'))
                <img src="{{ asset(config('brand.logo')) }}" alt="{{ config('brand.name') }}"
                     class="size-8 shrink-0 rounded-lg object-contain">
            @else

                <span class="size-8 shrink-0 rounded-lg bg-primary text-primary-foreground
                             inline-flex items-center justify-center text-sm font-bold tracking-tight">
                    {{ config('brand.mark') }}
                </span>
            @endif
            <span class="truncate text-sm font-semibold text-foreground sidebar-hide-collapsed">{{ config('brand.name') }}</span>
        </a>

        <button type="button" @click="sidebarOpen = false"
                class="lg:hidden shrink-0 size-8 rounded-lg text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground transition inline-flex items-center justify-center"
                aria-label="Close navigation">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="hidden lg:block shrink-0 px-3 pt-3">
        <button type="button" @click="toggleCollapsed()"
                class="w-full flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground transition sidebar-center-collapsed"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                aria-label="Toggle sidebar width">
            <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 4v16M4 4h16v16H4z"/>
            </svg>
            <span class="truncate sidebar-hide-collapsed">Collapse sidebar</span>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-3 space-y-0.5 sidebar-scroll"
         aria-label="Main navigation">
        @foreach ($sections as $section)
            @php
                $visible = collect($section['items'])
                    ->filter(fn ($item) =>
                        \Illuminate\Support\Facades\Route::has($item['route'])
                        && ($item['can'] === null || ($user && $user->can($item['can'])))
                    )
                    ->values();
            @endphp

            @if ($visible->isNotEmpty())
                @if ($section['label'])
                    <p class="px-3 pt-6 pb-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground-1/70 sidebar-hide-collapsed">
                        {{ $section['label'] }}
                    </p>

                    <div class="sidebar-hide-expanded pt-4 pb-2">
                        <div class="mx-3 border-t border-layer-line"></div>
                    </div>
                @else
                    <div class="pt-1"></div>
                @endif

                @foreach ($visible as $item)
                    <x-sidebar-item :href="route($item['route'])" :active="$activeRoute === $item['route']"
                                    :title="$item['label']">
                        <x-slot:icon>
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="{{ $iconPaths[$item['icon']] ?? $iconPaths['grid'] }}"/>
                            </svg>
                        </x-slot:icon>
                        <span class="sidebar-hide-collapsed">{{ $item['label'] }}</span>
                    </x-sidebar-item>
                @endforeach
            @endif
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-layer-line p-3 space-y-1">
        <button type="button" onclick="toggleTheme()" aria-label="Toggle theme" title="Toggle light/dark theme"
                class="w-full flex items-center gap-x-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground transition sidebar-center-collapsed">

            <svg class="size-5 shrink-0 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>

            <svg class="size-5 shrink-0 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="truncate sidebar-hide-collapsed">
                <span class="hidden dark:inline">Light mode</span>
                <span class="inline dark:hidden">Dark mode</span>
            </span>
        </button>

        @auth
            <div class="flex items-center gap-x-2.5 rounded-lg px-3 py-2.5 sidebar-center-collapsed sidebar-flush-collapsed">
                <span class="size-7 shrink-0 rounded-full bg-surface-4 text-foreground inline-flex items-center
                             justify-center text-xs font-semibold uppercase">
                    {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                </span>
                <span class="min-w-0 flex-1 sidebar-hide-collapsed">
                    <span class="block truncate text-xs font-medium text-foreground">{{ auth()->user()->name ?? 'User' }}</span>
                    <span class="block truncate text-[11px] text-muted-foreground-1">{{ auth()->user()->getRoleNames()->first() ?? 'no role' }}</span>
                </span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Log out"
                        class="w-full flex items-center gap-x-3 rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground transition sidebar-center-collapsed">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="sidebar-hide-collapsed">Log out</span>
                </button>
            </form>
        @endauth
    </div>
</div>
