<x-admin::layouts.master>
    @php
        $current = 'roles';
        $allPermissionNames = collect($permissionGroups)
            ->flatMap(fn($modules) => collect($modules)->flatMap(fn($actions) => array_values($actions)))
            ->values()
            ->all();

        $checked = $isSuperAdmin ? $allPermissionNames : $rolePermissions;
        $formAction = $roleId ? route('admin.roles.update', $roleId) : route('admin.roles.store');
    @endphp

    <form method="POST" action="{{ $formAction }}" class="space-y-6"
          x-data="roleEditor(@js($roleName), @js($checked), @js($allPermissionNames), @js($isSuperAdmin))">
        @csrf
        @if ($roleId)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="p-4 text-sm text-red-700 bg-red-100 dark:bg-red-950 dark:text-red-300 rounded-lg border border-red-200 dark:border-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

        <div class="bg-layer border border-layer-line rounded-xl p-6">
            <h2 class="text-sm font-semibold text-foreground mb-4">Role information</h2>

            <label class="block text-sm mb-2 text-foreground">
                Role name <span class="text-danger">*</span>
            </label>
            <input type="text" name="name" x-model="roleName" required
                   @if ($isSuperAdmin) readonly @endif
                   class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition disabled:opacity-60"
                   placeholder="e.g. compliance-officer">
            @if ($isSuperAdmin)
                <p class="text-xs text-muted-foreground-1 mt-2">The super-admin role name is protected and cannot be renamed.</p>
            @endif
        </div>

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

            <div class="border-t border-layer-line divide-y divide-layer-line">
                @foreach ($permissionGroups as $groupCategory => $modules)
                    <div class="px-6 py-2.5 bg-surface-4/60 text-xs font-semibold uppercase tracking-wider text-muted-foreground-1">
                        {{ $groupCategory }}
                    </div>

                    @foreach ($modules as $moduleName => $actions)
                        <div class="px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-layer-hover/40 transition">
                            <div class="md:w-1/3">
                                <span class="text-sm font-semibold text-foreground">{{ $moduleName }}</span>
                            </div>

                            <div class="md:w-2/3 flex flex-wrap items-center justify-start md:justify-end gap-x-6 gap-y-3">
                                @foreach ($actions as $actionLabel => $permName)
                                    <label class="inline-flex items-center gap-x-2 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                               x-model="selected"
                                               @if ($isSuperAdmin) disabled @endif
                                               class="shrink-0 size-4 bg-transparent border-line-3 rounded-sm shadow-2xs text-primary focus:ring-0 focus:ring-offset-0 checked:bg-primary-checked checked:border-primary-checked disabled:opacity-60">
                                        <span class="text-sm text-foreground capitalize">{{ $actionLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2 pb-2">
            <a href="{{ route('admin.roles.index') }}"
               class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition">
                Cancel
            </a>
            <button type="submit"
                    class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                {{ $roleId ? 'Save Changes' : 'Create Role' }}
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('roleEditor', (initialName, initialSelected, allPermissions, isSuperAdmin) => ({
                    roleName: initialName,
                    selected: initialSelected,
                    allPermissions: allPermissions,
                    isSuperAdmin: isSuperAdmin,

                    get allChecked() {
                        return this.allPermissions.length > 0 &&
                               this.allPermissions.every((p) => this.selected.includes(p));
                    },
                    set allChecked(value) {
                        this.selected = value ? [...this.allPermissions] : [];
                    },
                }));
            });
        </script>
    @endpush
</x-admin::layouts.master>