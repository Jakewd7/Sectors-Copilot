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
        <!-- Stats row (A2: API credits shown here too, dummy + TODO) — Preline card-stats -->
        <div class="flex flex-wrap gap-4">
            @foreach ($stats as $stat)
                <x-admin::partials.stat-card :label="$stat['label']" :value="$stat['value']" />
            @endforeach
            <x-admin::partials.stat-card label="Cache hit rate" value="68%" />
        </div>

        <!-- LLM token usage (Ollama-cloud-style usage bar, dummy data + TODO) -->
        <x-admin::partials.token-usage-card
            title="LLM token usage — daily"
            subtitle="Tokens consumed by the research agent across all sessions today."
            :label="$llmUsage['daily']['label']"
            :percent="$llmUsage['daily']['percent']"
            :meta="$llmUsage['daily']['meta']"
            :models="$llmUsage['daily']['models']" />

        <!-- Cache table (Preline tables pattern) -->
        <div>
            <h2 class="text-sm font-semibold text-foreground mb-4">Cache per company / sector</h2>

            <div class="bg-layer border border-layer-line rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-table-line">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Cache key</th>
                                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Expires at</th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-muted-foreground-1 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-table-line">
                            @foreach ($entries as $entry)
                                <tr class="hover:bg-layer-hover/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground font-mono">{{ $entry['key'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground-1">{{ $entry['expires_at'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end">
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
    </div>
</x-admin::layouts.master>