<x-admin::layouts.master>
    <div class="space-y-6" x-data="insightEditor()">
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

        <div>
            <h1 class="text-2xl font-bold text-foreground tracking-tight">Market Articles</h1>
            <p class="text-sm text-muted-foreground-1 mt-1">Publish market insight articles shown to users across the
                app.</p>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-4">
                @foreach ($stats as $stat)
                    <x-admin::partials.stat-card :label="$stat['label']" :value="$stat['value']" fit />
                @endforeach
            </div>

            <button type="button" @click="openCreate()"
                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add article
            </button>
        </div>

        <div class="space-y-3">
            @forelse ($insights as $insight)
                <div
                    class="bg-layer border border-layer-line rounded-xl px-5 py-4 flex items-center justify-between gap-4 hover:bg-layer-hover/60 transition">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-primary-foreground">
                                {{ $insight['category'] }}
                            </span>
                            <span class="text-xs text-muted-foreground-1">{{ $insight['date'] }}</span>
                            <x-admin::partials.status-badge :status="$insight['status']" />
                        </div>
                        <h3 class="font-semibold text-foreground truncate">{{ $insight['title'] }}</h3>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span @click="openEdit(@js($insight))" class="cursor-pointer">
                            <x-admin::partials.action-button action="edit" />
                        </span>

                        <form method="POST" action="{{ route('admin.insights.destroy', $insight['id']) }}" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this article?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex">
                                <x-admin::partials.action-button action="delete" />
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div
                    class="bg-layer border border-layer-line rounded-xl px-5 py-8 text-center text-sm text-muted-foreground-1">
                    No market articles found.
                </div>
            @endforelse
        </div>

        <div x-show="open" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm overflow-y-auto"
            @keydown.escape.window="open = false">
            <div class="absolute inset-0" @click="open = false" aria-hidden="true"></div>

            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-layer border border-layer-line rounded-2xl shadow-2xl p-6"
                x-show="open" x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-foreground"
                        x-text="editingId ? 'Edit Article' : 'New Article'"></h2>
                    <button type="button"
                        class="w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 transition inline-flex justify-center items-center"
                        @click="open = false" aria-label="Close">
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="formAction" class="space-y-4">
                    @csrf
                    <template x-if="editingId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="action_type" :value="form.action_type">

                    <div>
                        <label class="block text-sm mb-2 text-foreground">Title</label>
                        <input type="text" name="title" x-model="form.title" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                            placeholder="Article title">
                    </div>

                    <div>
                        <label class="block text-sm mb-2 text-foreground">Category</label>
                        <select name="category" x-model="form.category" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border form-select rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus transition">
                            <option value="Weekly review">Weekly review</option>
                            <option value="Stock watch">Stock watch</option>
                            <option value="Macro update">Macro update</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm mb-2 text-foreground">Content</label>
                        <div x-ref="editorHost" class="bg-form-field form-field-border rounded-lg overflow-hidden">
                        </div>
                        <input type="hidden" name="content" x-model="form.content">
                        <p class="mt-2 text-xs text-muted-foreground-1">
                            Rich text is saved as HTML.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                            class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground focus:outline-hidden transition">
                            Cancel
                        </button>
                        <button type="submit" @click="form.action_type = 'draft'; syncContent()"
                            class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-foreground hover:bg-layer-hover focus:outline-hidden transition">
                            Save draft
                        </button>
                        <button type="submit" @click="form.action_type = 'publish'; syncContent()"
                            class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus transition">
                            Publish now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')

        @vite('resources/js/admin-editor.js')

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('insightEditor', () => ({
                    open: false,
                    editingId: null,
                    form: {
                        title: '',
                        category: 'Weekly review',
                        content: '',
                        action_type: 'publish'
                    },

                    get formAction() {
                        return this.editingId
                            ? `{{ url('admin/insights') }}/${this.editingId}`
                            : `{{ route('admin.insights.store') }}`;
                    },
                    editor: null,

                    mountEditor() {
                        if (this.editor || !window.createArticleEditor) return;

                        this.editor = window.createArticleEditor(this.$refs.editorHost, '');

                        this.editor.instance.on('text-change', () => {
                            this.form.content = this.editor.getHtml();
                        });
                    },

                    openCreate() {
                        this.editingId = null;
                        this.form = { title: '', category: 'Weekly review', content: '', action_type: 'publish' };
                        this.open = true;

                        this.mountEditor();
                        this.editor?.setHtml('');
                    },

                    openEdit(insight) {
                        this.editingId = insight.id;
                        this.form = {
                            title: insight.title,
                            category: insight.category,
                            content: insight.content || '',
                            action_type: insight.status === 'published' ? 'publish' : 'draft',
                        };
                        this.open = true;

                        this.mountEditor();
                        this.editor?.setHtml(this.form.content);
                    },

                    syncContent() {
                        if (this.editor) this.form.content = this.editor.getHtml();
                    },
                }));
            });
        </script>
    @endpush
</x-admin::layouts.master>