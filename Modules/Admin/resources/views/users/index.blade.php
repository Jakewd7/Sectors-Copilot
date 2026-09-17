<x-admin::layouts.master>
    <!-- Dummy data comes from AdminPageController (TODO: connect to backend) -->
    <div class="space-y-6" x-data="{ editUser: null, showCreate: false }">

        <div>
            <h1 class="text-2xl font-bold text-foreground tracking-tight">Users</h1>
            <p class="text-sm text-muted-foreground-1 mt-1">Manage registered accounts, their roles and account status.</p>
        </div>

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
                        @foreach ($users as $user)
                            <tr class="hover:bg-layer-hover/60 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
                                    {{ $user['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground-1">
                                    {{ $user['email'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $user['role'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-admin::partials.status-badge :status="$user['status']" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground-1">
                                    {{ $user['created_at'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end">
                                    <div class="flex items-center justify-end gap-1.5">

                                        <span @click="editUser = @js($user)">
                                            <x-admin::partials.action-button action="edit" />
                                        </span>
                                        <!-- TODO: connect suspend/unsuspend action to backend -->
                                        <span @click="alert('TODO: connect suspend toggle to backend')">
                                            <x-admin::partials.action-button action="delete" />
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-admin::partials.pagination :page="$page" :totalPages="$totalPages" :from="$from" :to="$to" :total="$total" />
        </div>

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
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- TODO: wire form submit to backend (admin.users.store) -->
                <form class="space-y-4" @submit.prevent="showCreate = false">
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Name</label>
                        <input type="text" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                            placeholder="username">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Email</label>
                        <input type="email" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                            placeholder="name@company.com">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Role</label>
                        <select
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            <option>User</option>
                            <option>Analyst</option>
                            <option>Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Status</label>
                        <select
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Password</label>
                        <input type="password" required
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
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- TODO: wire form submit to backend (admin.users.update) -->
                <form class="space-y-4" @submit.prevent="editUser = null">
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Name</label>
                        <input type="text" x-model="editUser.name"
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Email</label>
                        <input type="email" x-model="editUser.email"
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition">
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Role</label>
                        <select x-model="editUser.role"
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            <option>User</option>
                            <option>Analyst</option>
                            <option>Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Status</label>
                        <select x-model="editUser.status"
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
