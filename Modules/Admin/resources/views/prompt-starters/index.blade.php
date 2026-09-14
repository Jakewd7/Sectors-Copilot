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
            <!-- Preline solid button -->
            <button type="button" @click="editingId = null; form.text = ''; open = true"
                    class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add prompt
            </button>
        </div>

        <!-- Prompt list (Preline card rows) -->
        <div class="space-y-3">
            @foreach ($prompts as $prompt)
                <div class="bg-layer border border-layer-line rounded-xl px-5 py-4 flex items-center justify-between gap-4 hover:bg-layer-hover/60 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-5 h-5 text-muted-foreground-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="font-medium text-foreground truncate">{{ $prompt['text'] }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
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

        <!-- CREATE / EDIT MODAL (Alpine, per D10; text-only per C9) — Preline modal card -->
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="open = false">
            <div class="absolute inset-0" @click="open = false" aria-hidden="true"></div>

            <div class="relative w-full max-w-lg bg-layer border border-layer-line rounded-2xl shadow-2xl p-6"
                 x-show="open" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-foreground" x-text="editingId ? 'Edit Prompt Starter' : 'New Prompt Starter'"></h2>
                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 transition inline-flex justify-center items-center"
                            @click="open = false" aria-label="Close">
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- TODO: wire submit to backend (store/update PromptStarter) -->
                <form class="space-y-4" @submit.prevent="open = false">
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Prompt text</label>
                        <input type="text" x-model="form.text"
                               class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                               placeholder="e.g. Compare the valuation of the 3 biggest banks...">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin::layouts.master>