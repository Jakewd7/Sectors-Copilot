<x-admin::layouts.master>
    @php
        $current = 'users';
    @endphp

    <x-slot:navigation>
        <x-admin::partials.tab-link :href="route('admin.users.index')"
            :active="$current === 'users'">Users</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.insights.index')" :active="$current === 'insights'">Market
            Article</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.prompts.index')" :active="$current === 'prompts'">Prompt
            starter</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.caches.index')"
            :active="$current === 'caches'">Cache</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.roles.index')" :active="$current === 'roles'">Roles &
            Access</x-admin::partials.tab-link>
    </x-slot:navigation>

    <div class="space-y-6" x-data="{ editUser: null, showCreate: false }">
        <!-- Flash messages & validation alert -->
        @if (session('success'))
            <div
                class="p-4 text-sm text-green-700 bg-green-100 dark:bg-green-950 dark:text-green-300 rounded-lg border border-green-200 dark:border-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="p-4 text-sm text-red-700 bg-red-100 dark:bg-red-950 dark:text-red-300 rounded-lg border border-red-200 dark:border-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page heading -->
        <div>
            <h1 class="text-2xl font-bold text-foreground tracking-tight">Users</h1>
            <p class="text-sm text-muted-foreground-1 mt-1">Manage registered accounts, their roles and account status.
            </p>
        </div>

        <!-- Stats row + Create User button -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-4">
                @foreach ($stats as $stat)
                    <x-admin::partials.stat-card :label="$stat['label']" :value="$stat['value']" fit />
                @endforeach
            </div>

            <button type="button" @click="showCreate = true"
                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create User
            </button>
        </div>

        <!-- Users table -->
        <div class="bg-layer border border-layer-line rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-table-line">
                    <thead>
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Name
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Email
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Role
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">
                                Account created</th>
                            <th scope="col"
                                class="px-6 py-3 text-end text-xs font-medium text-muted-foreground-1 uppercase">Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-table-line">
                        @forelse ($users as $user)
                            <tr class="hover:bg-layer-hover/60 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
                                    {{ $user['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground-1">{{ $user['email'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $user['role'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-admin::partials.status-badge :status="$user['status']" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground-1">
                                    {{ $user['created_at'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit button -->
                                        <span @click="editUser = @js($user)" class="cursor-pointer">
                                            <x-admin::partials.action-button action="edit" />
                                        </span>

                                        <!-- Toggle Suspend Form -->
                                        <form method="POST" action="{{ route('admin.users.suspend', $user['id']) }}"
                                            class="inline"
                                            onsubmit="return confirm('Change status for {{ addslashes($user['name']) }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex">
                                                <x-admin::partials.action-button action="delete" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-muted-foreground-1">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <x-admin::partials.pagination :page="$page" :totalPages="$totalPages" :from="$from" :to="$to"
                :total="$total" />
        </div>

        <!-- FLOATING CREATE OVERLAY -->
        <div x-show="showCreate" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm overflow-y-auto"
            @keydown.escape.window="showCreate = false">
            <div class="absolute inset-0" @click="showCreate = false" aria-hidden="true"></div>

            <div class="relative w-full max-w-md bg-layer border border-layer-line rounded-2xl shadow-2xl p-6"
                x-show="showCreate" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-foreground">Create User</h2>
                    <button type="button"
                        class="w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 transition inline-flex justify-center items-center"
                        @click="showCreate = false" aria-label="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Name</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                            placeholder="username">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Email</label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                            placeholder="name@company.com">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Role</label>
                        <select name="role" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            @foreach ($availableRoles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Status</label>
                        <select name="status" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Password</label>
                        <input type="password" name="password" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                            placeholder="Temporary password">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showCreate = false"
                            class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- FLOATING EDIT OVERLAY -->
        <div x-show="editUser" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm overflow-y-auto"
            @keydown.escape.window="editUser = null">
            <div class="absolute inset-0" @click="editUser = null" aria-hidden="true"></div>

            <div class="relative w-full max-w-md bg-layer border border-layer-line rounded-2xl shadow-2xl p-6"
                x-show="editUser" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-foreground">Edit User</h2>
                    <button type="button"
                        class="w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 transition inline-flex justify-center items-center"
                        @click="editUser = null" aria-label="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="`{{ url('admin/users') }}/${editUser?.id}`" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Name</label>
                        <input type="text" name="name" x-model="editUser.name" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Email</label>
                        <input type="email" name="email" x-model="editUser.email" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Role</label>
                        <select name="role" x-model="editUser.role" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            @foreach ($availableRoles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Status</label>
                        <select name="status" x-model="editUser.status" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editUser = null"
                            class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts.master>