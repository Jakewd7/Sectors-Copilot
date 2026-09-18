<x-admin::layouts.master>
    <div class="space-y-8">
        @if (session('success'))
            <div
                class="p-4 text-sm text-green-700 bg-green-100 dark:bg-green-950 dark:text-green-300 rounded-lg border border-green-200 dark:border-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-bold text-foreground tracking-tight">Cache & Usage</h1>
            <p class="text-sm text-muted-foreground-1 mt-1">Monitor the sectors data cache and the agent's daily LLM
                token usage.</p>
        </div>

        <div class="flex flex-wrap gap-4">
            @foreach ($stats as $stat)
                <x-admin::partials.stat-card :label="$stat['label']" :value="$stat['value']" />
            @endforeach
        </div>

        <x-admin::partials.token-usage-card title="LLM token usage — daily"
            subtitle="Tokens consumed by the research agent across all sessions today."
            :label="$llmUsage['daily']['label']" :percent="$llmUsage['daily']['percent']"
            :percent-label="$llmUsage['daily']['percentLabel']" :meta="$llmUsage['daily']['meta']"
            :models="$llmUsage['daily']['models']" />

        <div>
            <h2 class="text-sm font-semibold text-foreground mb-4">Cache per company / sector</h2>

            <div class="bg-layer border border-layer-line rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-table-line">
                        <thead>
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">
                                    Cache key</th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">
                                    Expires at</th>
                                <th scope="col"
                                    class="px-6 py-3 text-end text-xs font-medium text-muted-foreground-1 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-table-line">
                            @forelse ($entries as $entry)
                                <tr class="hover:bg-layer-hover/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground font-mono">
                                        {{ $entry['key'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground-1">
                                        <span class="{{ $entry['is_expired'] ? 'text-red-500 font-medium' : '' }}">
                                            {{ $entry['expires_at'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end">
                                        <form method="POST" action="{{ route('admin.caches.flush-key', $entry['id']) }}"
                                            class="inline" onsubmit="return confirm('Flush cache key ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex">
                                                <x-admin::partials.action-button action="flush" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-muted-foreground-1">
                                        No cache items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($cacheEntries->hasPages())
                    <div class="px-6 py-4 border-t border-table-line">
                        {{ $cacheEntries->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin::layouts.master>