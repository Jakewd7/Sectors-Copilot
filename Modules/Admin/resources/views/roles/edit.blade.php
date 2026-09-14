<x-admin::layouts.master>
    @php
        $current = 'roles';

        // Permission matrix mirrors the SEEDED Spatie permissions table (28 perms).
        // TODO(backend): build this list from Permission::all() grouped by prefix.
        $permissionGroups = [
            'Admin panel' => [
                ['name' => 'admin.dashboard.view', 'label' => 'Dashboard'],
                ['name' => 'admin.users.view', 'label' => 'Users — view only'],
                ['name' => 'admin.users.manage', 'label' => 'Users — manage (create/edit/status)'],
                ['name' => 'admin.insights.manage', 'label' => 'Market articles — manage'],
                ['name' => 'admin.prompts.manage', 'label' => 'Prompt starters — manage'],
                ['name' => 'admin.system.cache-manage', 'label' => 'System cache — manage'],
                ['name' => 'admin.audit-logs.view', 'label' => 'Audit logs — view'],
            ],
            'Agent' => [
                ['name' => 'agent.chat.view', 'label' => 'Chat sessions — view'],
                ['name' => 'agent.chat.create', 'label' => 'Chat sessions — create'],
                ['name' => 'agent.chat.delete', 'label' => 'Chat sessions — delete'],
                ['name' => 'agent.tools.financials', 'label' => 'Tool: company financials'],
                ['name' => 'agent.tools.screener', 'label' => 'Tool: stock screener'],
                ['name' => 'agent.tools.valuation-matrix', 'label' => 'Tool: valuation matrix'],
                ['name' => 'agent.report.export-markdown', 'label' => 'Export report — Markdown'],
                ['name' => 'agent.report.export-pdf', 'label' => 'Export report — PDF'],
            ],
            'Sectors data' => [
                ['name' => 'sectors.api.metrics.view', 'label' => 'API metrics — view'],
                ['name' => 'sectors.cache.view', 'label' => 'Data cache — view'],
                ['name' => 'sectors.cache.flush', 'label' => 'Data cache — flush'],
            ],
            'General' => [
                ['name' => 'dashboard.view', 'label' => 'User dashboard — view'],
                ['name' => 'market-insights.view', 'label' => 'Market insights — view'],
                ['name' => 'telemetry.view-quota', 'label' => 'Usage quota (telemetry) — view'],
                ['name' => 'profile.view', 'label' => 'Own profile — view'],
                ['name' => 'profile.update', 'label' => 'Own profile — update'],
                ['name' => 'profile.delete', 'label' => 'Own profile — delete'],
                ['name' => 'watchlist.view', 'label' => 'Watchlist — view'],
                ['name' => 'watchlist.create', 'label' => 'Watchlist — create'],
                ['name' => 'watchlist.update', 'label' => 'Watchlist — update'],
                ['name' => 'watchlist.delete', 'label' => 'Watchlist — delete'],
            ],
        ];

        // Flatten all permission names for the "select all" logic.
        $allPermissionNames = collect($permissionGroups)
            ->flatMap(fn ($items) => collect($items)->pluck('name'))
            ->all();

        $checked = $isSuperAdmin ? $allPermissionNames : $rolePermissions;
    @endphp

    <x-slot:navigation>
        <x-admin::partials.tab-link :href="route('admin.users.index')" :active="$current === 'users'">Users</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.insights.index')" :active="$current === 'insights'">Market Article</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.prompts.index')" :active="$current === 'prompts'">Prompt starter</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.caches.index')" :active="$current === 'caches'">Cache</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.roles.index')" :active="$current === 'roles'">Roles & Access</x-admin::partials.tab-link>
    </x-slot:navigation>

    <div class="space-y-6"
         x-data="roleEditor(@js($roleName), @js($checked), @js($allPermissionNames), @js($isSuperAdmin))">

        <!-- Page heading + Back button on one line -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground tracking-tight">
                    {{ $roleId ? 'Edit Role & Access' : 'Create New Role' }}
                </h1>
                <p class="text-sm text-muted-foreground-1 mt-1">
                    @if ($roleId)
                        Update the name and feature permissions for role <span class="font-medium text-foreground">{{ $roleName }}</span>
                    @else
                        Define a new role and its feature permissions.
                    @endif
                </p>
            </div>

            <a href="{{ route('admin.roles.index') }}"
               class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>

        <!-- Card 1: Role information -->
        <div class="bg-layer border border-layer-line rounded-xl p-6">
            <h2 class="text-sm font-semibold text-foreground mb-4">Role information</h2>

            <label class="block text-sm mb-2 text-foreground">
                Role name <span class="text-danger">*</span>
            </label>
            <input type="text" x-model="roleName" required
                   @if ($isSuperAdmin) disabled @endif
                   class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition disabled:opacity-60"
                   placeholder="e.g. compliance-officer">
            @if ($isSuperAdmin)
                <p class="text-xs text-muted-foreground-1 mt-2">The super-admin role name is protected and cannot be renamed.</p>
            @endif
        </div>

        <!-- Card 2: Permission configuration matrix -->
        <div class="bg-layer border border-layer-line rounded-xl overflow-hidden">
            <div class="p-6 pb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Permission configuration</h2>
                    <p class="text-xs text-muted-foreground-1 mt-1">
                        <span x-text="selected.length + ' of ' + allPermissions.length + ' permissions selected'"></span>
                    </p>
                </div>

                <label class="inline-flex items-center gap-x-2 cursor-pointer">
                    <input type="checkbox" x-model="allChecked"
                           @if ($isSuperAdmin) disabled @endif
                           class="shrink-0 size-4 bg-transparent border-line-3 rounded-sm shadow-2xs text-primary focus:ring-0 focus:ring-offset-0 checked:bg-primary-checked checked:border-primary-checked disabled:opacity-60">
                    <span class="text-sm text-primary font-medium">Select all permissions</span>
                </label>
            </div>

            <div class="border-t border-layer-line">
                @foreach ($permissionGroups as $groupName => $items)
                    <div class="px-6 py-2.5 bg-surface-4/60 text-xs font-semibold uppercase tracking-wider text-muted-foreground-1">
                        {{ $groupName }}
                    </div>

                    @foreach ($items as $item)
                        <div class="px-6 py-3.5 flex flex-wrap items-center justify-between gap-3 border-t border-table-line hover:bg-layer-hover/40 transition">
                            <span class="text-sm font-medium text-foreground">{{ $item['label'] }}</span>

                            <label class="inline-flex items-center gap-x-2 cursor-pointer">
                                <input type="checkbox" value="{{ $item['name'] }}" x-model="selected"
                                       @if ($isSuperAdmin) disabled @endif
                                       class="shrink-0 size-4 bg-transparent border-line-3 rounded-sm shadow-2xs text-primary focus:ring-0 focus:ring-offset-0 checked:bg-primary-checked checked:border-primary-checked disabled:opacity-60">
                                <span class="text-xs text-muted-foreground-1 font-mono">{{ $item['name'] }}</span>
                            </label>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        <!-- Save actions -->
        <div class="flex justify-end gap-2 pb-2">
            <a href="{{ route('admin.roles.index') }}"
               class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition">
                Cancel
            </a>
            <!-- TODO: wire form submit to backend (admin.roles.store / admin.roles.update) -->
            <button type="button" @click="alert('TODO: connect save role to backend (Spatie sync)')"
                    class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                {{ $roleId ? 'Save Changes' : 'Create Role' }}
            </button>
        </div>
    </div>
</x-admin::layouts.master>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('roleEditor', (initialName, initialSelected, allPermissions, isSuperAdmin) => ({
            roleName: initialName,
            // super-admin dummy data arrives as ALL permissions pre-checked
            selected: initialSelected,
            allPermissions: allPermissions,
            isSuperAdmin: isSuperAdmin,

            get allChecked() {
                return this.allPermissions.every((p) => this.selected.includes(p));
            },
            set allChecked(value) {
                this.selected = value ? [...this.allPermissions] : [];
            },
        }));
    });
</script>