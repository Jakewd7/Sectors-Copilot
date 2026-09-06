<x-admin::layouts.master>
    @php
        $current = 'caches';
    @endphp

    <x-slot:navigation>
        <x-admin::partials.tab-link :href="route('admin.users.index')" :active="$current === 'users'">Users</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.insights.index')" :active="$current === 'insights'">Market Article</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.prompts.index')" :active="$current === 'prompts'">Prompt starter</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.caches.index')" :active="$current === 'caches'">Cache</x-admin::partials.tab-link>
    </x-slot:navigation>

    <!-- Dummy data comes from AdminPageController (TODO: connect to backend) -->
    <div class="space-y-8">
        <!-- Stats row (A2: API credits shown here too, dummy + TODO) -->
        <div class="flex flex-wrap gap-8">
            @foreach ($stats as $stat)
                <x-admin::partials.stat-card :label="$stat['label']" :value="$stat['value']" />
            @endforeach
            <x-admin::partials.stat-card label="Cache hit rate" value="68%" />
        </div>

        <!-- Cache table -->
        <div>
            <h2 class="text-sm font-semibold text-text-primary mb-4">Cache per company / sector</h2>

            <div class="bg-surface border border-border rounded-xl overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-text-muted text-xs border-b border-border">
                            <th class="px-6 py-4 font-medium">Cache key</th>
                            <th class="px-6 py-4 font-medium">Expires at</th>
                            <th class="px-6 py-4 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        @foreach ($entries as $entry)
                            <tr class="hover:bg-border/30 transition">
                                <td class="px-6 py-4 font-mono font-semibold text-text-primary">{{ $entry['key'] }}</td>
                                <td class="px-6 py-4 text-text-muted">{{ $entry['expires_at'] }}</td>
                                <td class="px-6 py-4 text-right">
                                    <!-- Flush per-key only (A3); action is a dummy for now -->
                                    <x-admin::partials.action-button action="flush"
                                        @click="alert('TODO: connect per-key flush to backend')" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin::layouts.master>