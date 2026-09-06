<x-admin::layouts.master>
    @php
        $current = 'users';
    @endphp

    <x-slot:navigation>
        <x-admin::partials.tab-link :href="route('admin.users.index')" :active="$current === 'users'">Users</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.insights.index')" :active="$current === 'insights'">Market Article</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.prompts.index')" :active="$current === 'prompts'">Prompt starter</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.caches.index')" :active="$current === 'caches'">Cache</x-admin::partials.tab-link>
    </x-slot:navigation>

    <!-- Dummy data comes from AdminPageController (TODO: connect to backend) -->
    <div class="space-y-6" x-data="{ editUser: null }">
        <!-- Stats row -->
        <div class="flex flex-wrap gap-8">
            @foreach ($stats as $stat)
                <x-admin::partials.stat-card :label="$stat['label']" :value="$stat['value']" />
            @endforeach
        </div>

        <!-- Users table -->
        <div class="bg-surface border border-border rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="text-text-muted text-xs border-b border-border">
                        <th class="px-6 py-4 font-medium">Name</th>
                        <th class="px-6 py-4 font-medium">Email</th>
                        <th class="px-6 py-4 font-medium">Role</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/50">
                    @foreach ($users as $user)
                        <tr class="hover:bg-border/30 transition">
                            <td class="px-6 py-4 font-semibold">{{ $user['name'] }}</td>
                            <td class="px-6 py-4 text-text-muted">{{ $user['email'] }}</td>
                            <td class="px-6 py-4">{{ $user['role'] }}</td>
                            <td class="px-6 py-4">
                                <x-admin::partials.status-badge :status="$user['status']" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Floating edit form opens on the same page (C7).
                                         @click lives on the wrapper span: component attributes
                                         are forwarded as literal strings, so Alpine/Blade
                                         directives must sit outside the component tag. -->
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

        <!-- FLOATING EDIT OVERLAY (same page, per C7) -->
        <div x-show="editUser" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="editUser = null">
            <!-- click-away backdrop -->
            <div class="absolute inset-0" @click="editUser = null" aria-hidden="true"></div>

            <div class="relative w-full max-w-md bg-surface border border-border rounded-2xl shadow-2xl p-6"
                 x-show="editUser" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold">Edit User</h2>
                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-border text-text-muted transition"
                            @click="editUser = null" aria-label="Close">
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- TODO: wire form submit to backend (admin.users.update) -->
                <form class="space-y-4" @submit.prevent="editUser = null">
                    <div>
                        <label class="block text-sm font-medium mb-2">Name</label>
                        <input type="text" x-model="editUser.name"
                               class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" x-model="editUser.email"
                               class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Role</label>
                        <select x-model="editUser.role"
                                class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
                            <option>User</option>
                            <option>Analyst</option>
                            <option>Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Status</label>
                        <select x-model="editUser.status"
                                class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editUser = null"
                                class="px-4 py-2.5 text-sm font-medium text-text-muted hover:text-text-primary rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-semibold bg-accent hover:bg-accent-dim text-base rounded-lg transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts.master>