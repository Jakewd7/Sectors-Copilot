<x-admin::layouts.master>
    <!-- Dummy data comes from AdminPageController (TODO: connect to backend) -->
    <div class="space-y-6" x-data>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground tracking-tight">Role & Access Control</h1>
                <p class="text-sm text-muted-foreground-1 mt-1">Manage user roles and their system feature permissions.</p>
            </div>

            <a href="{{ route('admin.roles.create') }}"
               class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Role
            </a>
        </div>

        <div class="bg-layer border border-layer-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-table-line">
                    <thead>
                        <tr>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">No</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Role name</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Guard</th>
                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Total permissions</th>
                            <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-muted-foreground-1 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-table-line">
                        @foreach ($roles as $index => $role)
                            <tr class="hover:bg-layer-hover/60 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground-1">{{ ($page - 1) * 5 + $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ $role['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center py-1 px-2.5 rounded-full text-xs font-mono bg-surface-4 text-foreground border border-layer-line">{{ $role['guard'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span class="inline-flex items-center py-1.5 px-3 rounded-full text-xs font-medium bg-primary/15 text-primary-active">
                                        {{ $role['permissions'] }} permissions
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-end">
                                    <div class="flex items-center justify-end gap-1.5">

                                        <a href="{{ route('admin.roles.edit', ['id' => $role['id']]) }}" aria-label="Edit role">
                                            <x-admin::partials.action-button action="edit" />
                                        </a>
                                        @unless ($role['name'] === 'super-admin')
                                            <!-- TODO: connect delete role to backend (super-admin is not deletable) -->
                                            <span @click="alert('TODO: connect delete role to backend')">
                                                <x-admin::partials.action-button action="delete" />
                                            </span>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-admin::partials.pagination :page="$page" :totalPages="$totalPages" :from="$from" :to="$to" :total="$total" />
        </div>
    </div>
</x-admin::layouts.master>