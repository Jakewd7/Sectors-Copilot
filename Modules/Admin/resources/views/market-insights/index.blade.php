<x-admin::layouts.master>
    @php
        $current = 'insights';
    @endphp

    <x-slot:navigation>
        <x-admin::partials.tab-link :href="route('admin.users.index')" :active="$current === 'users'">Users</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.insights.index')" :active="$current === 'insights'">Market Article</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.prompts.index')" :active="$current === 'prompts'">Prompt starter</x-admin::partials.tab-link>
        <x-admin::partials.tab-link :href="route('admin.caches.index')" :active="$current === 'caches'">Cache</x-admin::partials.tab-link>
    </x-slot:navigation>

    <div class="space-y-6" x-data="insightEditor()">
        <div class="flex justify-end">
            <button type="button" @click="openCreate()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-accent hover:bg-accent-dim text-base text-sm font-semibold rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add article
            </button>
        </div>

        <!-- Article list -->
        <div class="space-y-3">
            @foreach ($insights as $insight)
                <div class="bg-surface border border-border rounded-xl px-5 py-4 flex items-center justify-between gap-4 hover:bg-border/20 transition">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-accent/15 text-accent">
                                {{ $insight['category'] }}
                            </span>
                            <span class="text-xs text-text-muted">{{ $insight['date'] }}</span>
                        </div>
                        <h3 class="font-semibold text-text-primary truncate">{{ $insight['title'] }}</h3>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- TODO: connect edit to backend -->
                        <span @click="openEdit(@js($insight))">
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

        <!-- CREATE / EDIT MODAL (Alpine, per D10) -->
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
             @keydown.escape.window="open = false">
            <div class="absolute inset-0" @click="open = false" aria-hidden="true"></div>

            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-surface border border-border rounded-2xl shadow-2xl p-6"
                 x-show="open" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold" x-text="editingId ? 'Edit Article' : 'New Article'"></h2>
                    <button type="button" class="w-8 h-8 rounded-lg hover:bg-border text-text-muted transition"
                            @click="open = false" aria-label="Close">
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- TODO: wire submit to backend (store/update MarketInsight) -->
                <form class="space-y-4" @submit.prevent="open = false">
                    <div>
                        <label class="block text-sm font-medium mb-2">Title</label>
                        <input type="text" x-model="form.title"
                               class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <select x-model="form.category"
                                class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition">
                            <option>Weekly review</option>
                            <option>Stock watch</option>
                            <option>Macro update</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Content (Markdown)</label>
                        <textarea x-model="form.content" rows="8"
                                  class="w-full bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary font-mono focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20 transition"
                                  placeholder="Write the article body in Markdown..."></textarea>
                    </div>

                    <!-- Live markdown preview (Marked.js, already loaded in master layout) -->
                    <div x-show="form.content.trim()">
                        <label class="block text-sm font-medium mb-2 text-text-muted">Preview</label>
                        <div class="bg-base border border-border rounded-lg px-4 py-3 text-sm text-text-primary prose prose-invert max-w-none break-words"
                             x-html="renderMarkdown(form.content)"></div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2.5 text-sm font-medium text-text-muted hover:text-text-primary rounded-lg transition">
                            Cancel
                        </button>
                        <!-- Save draft: published_at stays null (C8) -->
                        <button type="button" @click="open = false"
                                class="px-5 py-2.5 text-sm font-semibold bg-surface hover:bg-border text-text-primary border border-border rounded-lg transition">
                            Save draft
                        </button>
                        <!-- Publish now: sets published_at to now (C8) -->
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-semibold bg-accent hover:bg-accent-dim text-base rounded-lg transition">
                            Publish now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('insightEditor', () => ({
                    open: false,
                    editingId: null,
                    form: { title: '', category: 'Weekly review', content: '' },

                    openCreate() {
                        this.editingId = null;
                        this.form = { title: '', category: 'Weekly review', content: '' };
                        this.open = true;
                    },

                    openEdit(insight) {
                        this.editingId = insight.id;
                        this.form = {
                            title: insight.title,
                            category: insight.category,
                            content: '', // TODO: load article content from backend
                        };
                        this.open = true;
                    },

                    renderMarkdown(text) {
                        return window.marked ? window.marked.parse(text || '') : text;
                    },
                }));
            });
        </script>
    @endpush
</x-admin::layouts.master>