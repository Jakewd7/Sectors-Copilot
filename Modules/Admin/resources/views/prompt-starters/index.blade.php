<x-admin::layouts.master>
    @php
        $current = 'prompts';
    @endphp

    <x-slot:navigation>
        <x-admin::partials.tab-link :href="route('admin.users.index')" :active="$current === 'users'">Users</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.insights.index')" :active="$current === 'insights'">Market Article</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.prompts.index')" :active="$current === 'prompts'">Prompt starter</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.caches.index')" :active="$current === 'caches'">Cache</x-admin::partials.tab-link>
    </x-slot:navigation>

    <!-- Dummy data comes from AdminPageController (TODO: connect to backend) -->
    <div class="space-y-6" x-data="{ open: false, editingId: null, form: { text: '' } }">
        <div class="flex justify-end">
            <button type="button" @click="editingId = null; form.text = ''; open = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-accent hover:bg-accent-dim text-base text-sm font-semibold rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add prompt
            </button>
        </div>

        <!-- Prompt list -->
        <div class="space-y-3">
            @foreach ($prompts as $prompt)
                <div class="bg-surface border border-border rounded-xl px-5 py-4 flex items-center justify-between gap-4 hover:bg-border/20 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="font-medium text-text-primary truncate">{{ $prompt['text'] }}</span>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- TODO: connect edit to backend -->
                        <span @click="editingId = @js($prompt['id']); form.text = @js($prompt['text']); open = true">
                            <x-admin::partials.action-button action="edit" />
                        </span>
                        <!-- TODO: connect delete to backend -->
                        <span @click="alert('TODO: connect delete to backend')">
                            <x-admin::partials.action-button action="delete" />
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- CREATE / EDIT MODAL (Alpine, per D10; text-only per C9) -->
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="open = false">
            <div class="absolute inset-0" @click="open = false" aria-hidden="true"></div>

            <div class="relative w-full max-w-lg bg-surface border border-border rounded-2xl shadow-2xl p-6"
                 x-show="open" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold" x-text="editingId ? 'Edit Prompt Starter' : 'New Prompt Starter'"></h2>
                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-border text-text-muted transition"
                            @click="open = false" aria-label="Close">
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- TODO: wire submit to backend (store/update PromptStarter) -->
                <form class="space-y-4" @submit.prevent="open = false">
                    <div>
                        <label class="block text-sm font-medium mb-2">Prompt text</label>
                        <input type="text" x-model="form.text"
                               class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition"
                               placeholder="e.g. Compare the valuation of the 3 biggest banks...">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2.5 text-sm font-medium text-text-muted hover:text-text-primary rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-semibold bg-accent hover:bg-accent-dim text-base rounded-lg transition">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts.master>