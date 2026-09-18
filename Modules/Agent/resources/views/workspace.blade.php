@extends('layouts.app')

@section('content')
    <div data-hs-layout-splitter='{"horizontalSplitterClasses": "hs-layout-splitter-control"}'
        class="flex h-full bg-base text-foreground overflow-hidden font-sans" x-data="copilotWorkspace('{{ $activeSession->id ?? '' }}')">

        <div data-hs-layout-splitter-horizontal-group class="flex h-full w-full min-w-0">

            <div data-hs-layout-splitter-item='{"dynamicSize": 22, "minSize": 12, "preLimitSize": 20}'
                class="h-full min-w-0 flex flex-col justify-between border-r border-layer-line bg-layer shrink-0">
                <div class="p-4 border-b border-layer-line">
                    <button @click="createNewSession()"
                        class="w-full py-2.5 px-4 inline-flex items-center justify-center gap-x-2 bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus font-medium rounded-lg text-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Research Session
                    </button>
                </div>

                @include('agent::partials.session-list')

                <div class="p-4 border-t border-layer-line text-xs text-muted-foreground-1 text-center">
                    Credit Shield Active • PostgreSQL JSONB Cache
                </div>
            </div>

            <div data-hs-layout-splitter-item='{"dynamicSize": 48, "minSize": 30}'
                class="h-full min-w-0 flex flex-col bg-base">

                <header class="h-14 border-b border-layer-line px-6 flex items-center justify-between shrink-0">
                    <h2 class="text-base font-semibold text-foreground"
                        x-text="sessionTitle || 'Select or Create a Research Session'"></h2>
                    <div class="flex items-center space-x-2" x-show="activeSessionId">
                        <a :href="`/api/v1/agent/sessions/${activeSessionId}/export/pdf`" target="_blank"
                            class="px-3 py-1.5 bg-layer hover:bg-layer-hover text-xs font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:text-foreground transition">Export
                            PDF</a>
                        <a :href="`/api/v1/agent/sessions/${activeSessionId}/export/md`" target="_blank"
                            class="px-3 py-1.5 bg-layer hover:bg-layer-hover text-xs font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:text-foreground transition">Export
                            MD</a>
                    </div>
                </header>

                <div class="flex-1 overflow-y-auto p-6 space-y-6" id="message-container">

                    <template x-if="demoMode">
                        <div class="space-y-6">
                            <div class="flex justify-end">
                                <div
                                    class="max-w-2xl bg-primary border border-primary-line text-primary-foreground p-4 rounded-2xl rounded-br-sm text-sm leading-relaxed">
                                    Compare BBCA, BBRI and BMRI valuation against their sector
                                </div>
                            </div>
                            <div>
                                <div class="max-w-2xl bg-card border border-card-line text-foreground p-4 rounded-2xl rounded-bl-sm text-sm leading-relaxed shadow-2xs"
                                    x-html="renderMarkdown('**BBCA** trades at a premium: forward P/E of **14.1x** vs the banks subsector median of **10.26x**, backed by the highest ROE in the group (**20.4%**).\n\n- **BBRI** offers the best dividend yield at ~6.1%\n- **BMRI** is the cheapest on P/E at ~6.7x\n\nFull breakdown is available in the valuation matrix on the right panel.')">
                                </div>
                                <p class="mt-3 text-[11px] text-muted-foreground-1">
                                    Disclaimer: analysis is auto-generated for research reference only — not investment
                                    advice.
                                </p>
                            </div>
                        </div>
                    </template>

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="space-y-3">

                            <template x-if="msg.error">
                                <div class="max-w-3xl bg-card border border-card-line border-l-4 border-l-rose-500 rounded-2xl rounded-bl-md p-4 shadow-2xs"
                                    role="alert">
                                    <div class="flex items-start gap-3">
                                        <svg class="size-5 shrink-0 text-rose-500 mt-0.5" fill="none"
                                            stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-foreground"
                                                x-text="msg.title || 'The copilot could not finish this request'"></p>
                                            <div class="mt-1 text-sm leading-relaxed text-muted-foreground-1"
                                                x-text="msg.content"></div>
                                            <p x-show="msg.detail" x-cloak
                                                class="mt-2 text-xs font-mono text-muted-foreground-1 break-words"
                                                x-text="msg.detail"></p>

                                            <button type="button" @click="retryMessage(msg)" :disabled="isResearching"
                                                class="mt-3 inline-flex items-center gap-x-2 px-3 py-1.5 rounded-lg border border-rose-500/50 text-rose-600 dark:text-rose-400 text-xs font-medium hover:bg-rose-500/10 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15" />
                                                </svg>
                                                <span x-text="isResearching ? 'Sending…' : 'Resend prompt'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!msg.error">
                                <div :class="msg.role === 'user' ?
                                    'bg-primary border border-primary-line text-primary-foreground ml-auto rounded-2xl rounded-br-md' :
                                    'bg-card border border-card-line text-foreground rounded-2xl rounded-bl-md'"
                                    class="max-w-3xl p-4 shadow-2xs">
                                    <span class="text-[11px] font-semibold uppercase tracking-wider block mb-1 opacity-70"
                                        x-text="msg.role"></span>
                                    <div class="text-sm leading-relaxed" x-html="renderMarkdown(msg.content)"></div>
                                </div>
                            </template>
                        </div>
                    </template>

                </div>

                <div class="p-4 border-t border-layer-line bg-layer shrink-0">
                    <form @submit.prevent="submitPrompt()" class="max-w-3xl mx-auto flex gap-2">
                        <input type="text" x-model="userPrompt" :disabled="isResearching"
                            placeholder="Ask the copilot (e.g. Compare BBCA and BMRI valuation vs their sector)..."
                            class="flex-1 bg-form-field form-field-border rounded-lg px-4 py-2.5 text-sm text-foreground placeholder:text-muted-foreground-1 focus:outline-none focus:border-primary-focus focus:ring-primary-focus transition">
                        <button type="submit" :disabled="isResearching || !userPrompt.trim()"
                            class="px-5 py-2.5 bg-primary border border-primary-line hover:bg-primary-hover disabled:opacity-40 text-primary-foreground text-sm font-medium rounded-lg transition inline-flex items-center gap-x-2">
                            <span x-show="!isResearching">Send</span>
                            <span x-show="isResearching" class="animate-spin inline-block">↻</span>
                        </button>
                    </form>
                </div>

            </div>

            <div data-hs-layout-splitter-item='{"dynamicSize": 30, "minSize": 18, "preLimitSize": 30}'
                class="h-full min-w-0 border-l border-layer-line bg-layer flex-col overflow-y-auto shrink-0"
                :class="inspectorVisible ? 'flex' : 'hidden'">
                @include('agent::components.inspector-panel')
            </div>

        </div>

        <div x-show="renameTarget" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
            @keydown.escape.window="renameTarget = null">
            <div class="absolute inset-0" @click="renameTarget = null" aria-hidden="true"></div>

            <div class="relative w-full max-w-md bg-layer border border-layer-line rounded-2xl shadow-2xl p-6"
                x-transition.opacity>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-foreground">Rename session</h2>
                    <button type="button" @click="renameTarget = null" aria-label="Close"
                        class="w-8 h-8 rounded-lg hover:bg-layer-hover text-muted-foreground-1 transition inline-flex justify-center items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submitRename()">
                    <div>
                        <label class="block text-sm mb-2 text-foreground">Session title</label>
                        <input type="text" x-model="renameTitle" maxlength="100" required
                            class="py-2.5 px-4 block w-full bg-form-field form-field-border rounded-lg text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus transition"
                            placeholder="Session title">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="renameTarget = null"
                            class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="busy || !renameTitle.trim()"
                            class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover disabled:opacity-50 transition">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="deleteTarget" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/60 backdrop-blur-sm"
            @keydown.escape.window="deleteTarget = null">
            <div class="absolute inset-0" @click="deleteTarget = null" aria-hidden="true"></div>

            <div class="relative w-full max-w-md bg-layer border border-layer-line rounded-2xl shadow-2xl p-6"
                x-transition.opacity>
                <div class="flex items-start gap-x-3">
                    <span
                        class="shrink-0 size-10 rounded-full bg-danger/15 text-danger inline-flex items-center justify-center">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-foreground">Delete session?</h2>
                        <p class="text-sm text-muted-foreground-1 mt-1">
                            “<span class="text-foreground" x-text="deleteTarget?.title"></span>” and all of its
                            messages will be permanently removed. This cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-6">
                    <button type="button" @click="deleteTarget = null"
                        class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line bg-layer text-muted-foreground-1 hover:bg-layer-hover hover:text-foreground transition">
                        Cancel
                    </button>
                    <button type="button" @click="submitDelete()" :disabled="busy"
                        class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-danger text-base hover:opacity-90 disabled:opacity-50 transition">
                        Delete session
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('styles')
        <style>
            .hs-layout-splitter-control {
                position: relative;
                width: 12px;
                margin: 0 -6px;
                z-index: 20;
                cursor: col-resize;
                background-color: transparent;
            }

            .hs-layout-splitter-control::after {
                content: '';
                position: absolute;
                inset: 0 5px;
                border-radius: 9999px;
                background-color: var(--theme-border);
                transition: background-color .15s ease, inset .15s ease;
            }

            .hs-layout-splitter-control:hover::after,
            .hs-layout-splitter-control.dragging::after {
                background-color: var(--theme-accent);
                inset: 0 4px;
            }

            .hs-layout-splitter-control::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 4px;
                height: 24px;
                border-radius: 9999px;
                background-image: radial-gradient(circle, var(--theme-text-muted) 1px, transparent 1.2px);
                background-size: 4px 6px;
                background-repeat: repeat-y;
                opacity: .65;
                pointer-events: none;
                transition: opacity .15s ease;
            }

            .hs-layout-splitter-control:hover::before,
            .hs-layout-splitter-control.dragging::before {
                opacity: 1;
            }
        </style>
    @endpush

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('sessionCard', () => ({
                menuOpen: false,
                menuStyle: '',

                toggleMenu(trigger) {
                    if (this.menuOpen) {
                        this.closeMenu();
                        return;
                    }

                    const r = trigger.getBoundingClientRect();
                    const menuWidth = 176;
                    const menuHeight = 132;
                    const gap = 6;

                    let left = r.right + gap;
                    if (left + menuWidth > window.innerWidth - 8) {
                        left = Math.max(8, r.left - menuWidth - gap);
                    }

                    let top = r.top;
                    if (top + menuHeight > window.innerHeight - 8) {
                        top = Math.max(8, window.innerHeight - menuHeight - 8);
                    }

                    this.menuStyle = `left:${Math.round(left)}px;top:${Math.round(top)}px`;
                    this.menuOpen = true;
                },

                closeMenu() {
                    this.menuOpen = false;
                },
            }));
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('copilotWorkspace', (initialSessionId = '') => ({
                activeSessionId: initialSessionId,
                sessionTitle: '',
                sessionList: @js(
                    $sessions->map(
                            fn($s) => [
                                'id' => $s->id,
                                'title' => $s->title,
                                'is_pinned' => (bool) $s->is_pinned,
                                'updated_at' => optional($s->updated_at)->toISOString(),
                            ],
                        )->values()
                ),
                userPrompt: '',
                messages: [],
                isResearching: false,
                demoMode: false,
                inspectorVisible: true,
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                    '',
                inspectorSteps: [],
                currentStepTime: '',
                latestPayload: null,
                subsectorMedianPe: null,
                renameTarget: null,
                renameTitle: '',
                deleteTarget: null,
                busy: false,

                // TODO(backend): remove when the SSE pipeline is fully accepted.
                demoSteps: [{
                        step: 'planning',
                        tool: null,
                        status: 'success',
                        endpoint: null,
                        message: 'Identified comparison intent for 3 major banks (BBCA, BBRI, BMRI)'
                    },
                    {
                        step: 'tool_execution',
                        tool: 'get_company_overview',
                        status: 'success',
                        endpoint: '/companies/BBCA/overview',
                        message: 'Data successfully retrieved from /companies/BBCA/overview.'
                    },
                    {
                        step: 'tool_execution',
                        tool: 'get_company_overview',
                        status: 'success',
                        endpoint: '/companies/BBRI/overview',
                        message: 'Data successfully retrieved from /companies/BBRI/overview.'
                    },
                    {
                        step: 'tool_execution',
                        tool: 'get_sector_peers',
                        status: 'success',
                        endpoint: '/subsectors/banks/peers',
                        message: 'Data successfully retrieved from /subsectors/banks/peers.'
                    },
                    {
                        step: 'synthesis',
                        tool: null,
                        status: 'success',
                        endpoint: null,
                        message: 'Analysis matrix composed and compliance disclaimer attached'
                    },
                ],

                demoPayload: {
                    get_company_overview: {
                        symbol: 'BBCA.JK',
                        company_name: 'PT Bank Central Asia Tbk.',
                        overview: {
                            sector: 'Financials',
                            sub_sector: 'Banks',
                            market_cap: 817683406650000
                        },
                        valuation: {
                            forward_pe: 14.1,
                            intrinsic_value: 13694,
                            last_close_price: 6700
                        },
                        financials: {
                            historical_financial_ratio: [{
                                    year: '2024',
                                    profitability: {
                                        roe: 0.2017,
                                        roa: 0.0378,
                                        net_profit_margin: 0.5063
                                    }
                                },
                                {
                                    year: '2025',
                                    profitability: {
                                        roe: 0.2043,
                                        roa: 0.0363,
                                        net_profit_margin: 0.5137
                                    }
                                },
                            ],
                        },
                        dividend: {
                            yield_ttm: 0.0569
                        },
                    },
                    get_sector_peers: {
                        sub_sector: 'Banks',
                        valuation: {
                            historical_valuation: {
                                2024: {
                                    pe: 16.19
                                },
                                2026: {
                                    pe: 10.26
                                },
                            },
                        },
                    },
                    widget_type: 'peers_comparison',
                    metrics: {
                        symbol: 'BBCA.JK',
                        company_name: 'PT Bank Central Asia Tbk.',
                        market_cap: 817683406650000
                    },
                },

                init() {
                    if (new URLSearchParams(window.location.search).has('demo')) {
                        this.demoMode = true;
                        this.sessionTitle = 'Demo Research Session';
                        this.inspectorSteps = [...this.demoSteps];
                        this.latestPayload = this.demoPayload;
                        this.currentStepTime = '00:42';

                        return;
                    }

                    if (this.activeSessionId) {
                        this.loadSession(this.activeSessionId);
                    }

                    window.addEventListener('inspector-update', (e) => {
                        this.handleInspectorEvent(e.detail);
                    });
                },

                get sessionListOrdered() {
                    return [...this.sessionList].sort((a, b) => {
                        if (a.is_pinned !== b.is_pinned) return a.is_pinned ? -1 : 1;
                        return String(b.updated_at ?? '').localeCompare(String(a
                            .updated_at ?? ''));
                    });
                },

                get latestRoe() {
                    const overview = this.latestPayload?.get_company_overview;
                    const ratios = overview?.financials?.historical_financial_ratio;
                    const fromHistory = ratios?.length ?
                        ratios[ratios.length - 1]?.profitability?.roe ?? null :
                        null;

                    return fromHistory ?? overview?.financials?.roe ?? overview?.roe_ttm ?? overview
                        ?.roe ?? null;
                },

                get latestDivYield() {
                    const overview = this.latestPayload?.get_company_overview;

                    return overview?.dividend?.yield_ttm ?? overview?.valuation?.dividend_yield ??
                        overview?.dividend_yield ?? null;
                },

                get forwardPeVsMedian() {
                    const pe = parseFloat(this.latestPayload?.get_company_overview?.valuation
                        ?.forward_pe);
                    const median = parseFloat(this.subsectorMedianPe);
                    if (!isNaN(pe) && !isNaN(median) && median > 0) {
                        return pe < median ? 'undervalued' : 'overvalued';
                    }
                    return 'neutral';
                },

                get subsectorMedianPe() {
                    if (this._subsectorMedianPe !== null && this._subsectorMedianPe !== undefined) {
                        return this._subsectorMedianPe;
                    }
                    const history = this.latestPayload?.get_sector_peers?.valuation
                        ?.historical_valuation;
                    const years = history ? Object.keys(history) : [];

                    return years.length ? history[years[years.length - 1]].pe : null;
                },

                renderMarkdown(content) {
                    if (!content) return '';
                    return typeof marked !== 'undefined' ? marked.parse(content) : content.replace(
                        /\n/g, '<br>');
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('message-container');
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                },

                updateTimestamp() {
                    const now = new Date();
                    this.currentStepTime = now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },

                async createNewSession() {
                    try {
                        const res = await fetch("{{ route('agent.sessions.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                title: null
                            }),
                        });
                        const result = await res.json();
                        if (result.success) {
                            const created = {
                                id: result.session.id,
                                title: result.session.title,
                                is_pinned: !!result.session.is_pinned,
                                updated_at: result.session.updated_at ?? new Date()
                                .toISOString(),
                            };
                            this.sessionList.unshift(created);
                            this.activeSessionId = created.id;
                            this.sessionTitle = created.title;
                            this.messages = [];
                            this.inspectorSteps = [];
                            this.livePayload = null;
                            this.demoMode = false;
                            this.userPrompt = '';

                            const url = new URL(window.location.href);
                            url.searchParams.set('session_id', created.id);
                            window.history.replaceState({}, '', url);
                        }
                    } catch (err) {
                        console.error('Failed to create session:', err);
                    }
                },

                async switchSession(sessionId) {
                    if (this.activeSessionId === sessionId) return;
                    this.activeSessionId = sessionId;
                    this.demoMode = false;
                    await this.loadSession(sessionId);
                    window.history.pushState({}, '',
                        `{{ route('agent.workspace') }}?session_id=${sessionId}`);
                },

                async loadSession(sessionId) {
                    try {
                        const res = await fetch(`/agent/sessions/${sessionId}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const result = await res.json();
                        if (result.success) {
                            this.sessionTitle = result.session.title;
                            this.messages = result.session.messages || [];
                            this.demoMode = false;

                            this.restoreHistoryState();
                            this.scrollToBottom();
                        }
                    } catch (err) {
                        console.error('Failed to load session:', err);
                    }
                },

                restoreHistoryState() {
                    this.inspectorSteps = [];
                    this.latestPayload = null;

                    const assistantMsgs = this.messages.filter(m => m.role === 'assistant');
                    if (assistantMsgs.length > 0) {
                        const lastMsg = assistantMsgs[assistantMsgs.length - 1];

                        if (lastMsg.structured_payload) {
                            this.latestPayload = typeof lastMsg.structured_payload === 'string' ?
                                JSON.parse(lastMsg.structured_payload) :
                                lastMsg.structured_payload;
                        }

                        if (lastMsg.step_logs && lastMsg.step_logs.length > 0) {
                            this.inspectorSteps = lastMsg.step_logs.map(log => ({
                                status: log.status ?? 'success',
                                tool: log.tool_name ?? log.tool,
                                message: log.message ?? log.action,
                                endpoint: log.endpoint ?? null
                            }));
                        }
                    }
                },

                async togglePin(session) {
                    const previous = session.is_pinned;
                    session.is_pinned = !previous;

                    try {
                        const res = await fetch(`/agent/sessions/${session.id}/pin`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            }
                        });
                        const result = await res.json();
                        if (!result.success) {
                            session.is_pinned = previous;
                            return;
                        }
                        session.is_pinned = result.is_pinned;
                        if (result.updated_at) session.updated_at = result.updated_at;
                    } catch (err) {
                        session.is_pinned = previous;
                        console.error('Failed to toggle pin:', err);
                    }
                },

                startRename(session) {
                    this.renameTarget = session;
                    this.renameTitle = session.title ?? '';
                },

                async submitRename() {
                    const title = this.renameTitle.trim();
                    if (!title || !this.renameTarget || this.busy) return;

                    this.busy = true;
                    const target = this.renameTarget;
                    try {
                        const res = await fetch(`/agent/sessions/${target.id}/rename`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                title
                            }),
                        });
                        const result = await res.json();
                        if (result.success) {
                            const row = this.sessionList.find((s) => s.id === target.id);
                            if (row) {
                                row.title = result.title;
                                if (result.updated_at) row.updated_at = result.updated_at;
                            }

                            if (this.activeSessionId === target.id) {
                                this.sessionTitle = result.title;
                            }
                            this.renameTarget = null;
                        }
                    } catch (err) {
                        console.error('Failed to rename session:', err);
                    } finally {
                        this.busy = false;
                    }
                },

                askDelete(session) {
                    this.deleteTarget = session;
                },

                async submitDelete() {
                    if (!this.deleteTarget || this.busy) return;

                    this.busy = true;
                    const target = this.deleteTarget;
                    const wasActive = this.activeSessionId === target.id;
                    try {
                        const res = await fetch(`/agent/sessions/${target.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                        });
                        const result = await res.json();
                        if (result.success) {
                            this.sessionList = this.sessionList.filter((s) => s.id !== target.id);
                            this.deleteTarget = null;

                            if (wasActive) {
                                const next = this.sessionListOrdered[0];
                                if (next) {
                                    this.switchSession(next.id);
                                } else {
                                    this.activeSessionId = '';
                                    this.sessionTitle = '';
                                    this.messages = [];
                                    this.inspectorSteps = [];
                                    this.livePayload = null;
                                    window.history.replaceState({}, '',
                                        "{{ route('agent.workspace') }}");
                                }
                            }
                        }
                    } catch (err) {
                        console.error('Failed to delete session:', err);
                    } finally {
                        this.busy = false;
                    }
                },

                quickDrillDown(symbol) {
                    this.userPrompt =
                        `Please run an in-depth fundamental analysis for ${symbol}`;
                    this.submitPrompt();
                },

                async streamPrompt(prompt, botMessageId) {
                    const url =
                        `/api/v1/agent/chat/stream?chat_session_id=${this.activeSessionId}&prompt=${encodeURIComponent(prompt)}`;

                    let res;
                    try {
                        res = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'text/event-stream',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                        });
                    } catch (err) {
                        throw {
                            title: "Couldn't reach the server",
                            message: 'The request did not go through. Check your connection and try again.',
                            detail: err?.message || '',
                        };
                    }

                    if (!res.ok) {
                        let serverMessage = '';
                        let detail = '';
                        try {
                            const raw = await res.text();
                            try {
                                const parsed = JSON.parse(raw);
                                serverMessage = parsed.message || '';
                                detail = parsed.errors ?
                                    Object.values(parsed.errors).flat().join(' ') :
                                    (parsed.exception || '');
                            } catch {
                                detail = raw.slice(0, 300);
                            }
                        } catch {
                        }

                        throw {
                            title: res.status === 401 ? 'Your session expired' : (res.status ===
                                422 ? 'The prompt was rejected' :
                                'The copilot could not start'),
                            message: serverMessage || (res.status === 401 ?
                                'Please sign in again, then resend your prompt.' :
                                'The server returned an error before the answer started. Please resend your prompt.'
                                ),
                            detail: detail || `HTTP ${res.status}`,
                        };
                    }

                    if (!res.body) {
                        throw {
                            title: 'Streaming is not supported',
                            message: 'Your browser could not open the response stream. Please try another browser.',
                            detail: '',
                        };
                    }

                    const reader = res.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';
                    let tempPayload = null;
                    let sawDone = false;
                    let serverError = null;

                    const dispatch = (event, data) => {
                        switch (event) {
                            case 'step_progress':
                                this.handleInspectorEvent(data);
                                if (data.payload) {
                                    tempPayload = {
                                        ...(tempPayload || {}),
                                        ...data.payload
                                    };
                                    this.latestPayload = tempPayload;
                                }
                                break;

                            case 'structured_payload':
                            case 'payload':
                                tempPayload = data;
                                this.latestPayload = {
                                    ...data
                                };
                                break;

                            case 'token':
                                this.appendToMessage(botMessageId, data.text ?? data.token ??
                                    '');
                                break;

                            case 'done':
                                sawDone = true;
                                this.latestPayload = tempPayload ?? this.latestPayload;
                                if (data.content && !this.messageContent(botMessageId)) {
                                    this.appendToMessage(botMessageId, data.content);
                                }
                                break;

                            case 'error':
                                serverError = {
                                    title: 'The AI model failed to respond',
                                    message: data.message ||
                                        'The model did not return an answer. Please send your prompt again.',
                                    detail: data.detail || data.exception || '',
                                };
                                break;
                        }
                    };

                    try {
                        while (true) {
                            const {
                                done,
                                value
                            } = await reader.read();
                            if (done) break;

                            buffer += decoder.decode(value, {
                                stream: true
                            });

                            let sep;
                            while ((sep = buffer.search(/\r?\n\r?\n/)) !== -1) {
                                const frame = buffer.slice(0, sep);
                                buffer = buffer.slice(sep + buffer.match(/\r?\n\r?\n/)[0].length);

                                let eventName = 'message';
                                const dataLines = [];
                                for (const line of frame.split(/\r?\n/)) {
                                    if (line.startsWith('event:')) {
                                        eventName = line.slice(6).trim();
                                    } else if (line.startsWith('data:')) {
                                        dataLines.push(line.slice(5).trimStart());
                                    }
                                }
                                if (!dataLines.length) continue;

                                let parsed = null;
                                try {
                                    parsed = JSON.parse(dataLines.join('\n'));
                                } catch {
                                    continue;
                                }
                                dispatch(eventName, parsed);
                            }
                        }
                    } catch (err) {
                        throw {
                            title: 'The connection dropped',
                            message: 'The answer was interrupted before it finished. Please resend your prompt.',
                            detail: err?.message || '',
                        };
                    }

                    if (serverError) throw serverError;

                    if (!sawDone) {
                        throw {
                            title: 'The model stopped responding',
                            message: 'No answer was received. Please send your prompt again.',
                            detail: '',
                        };
                    }
                },

                messageContent(id) {
                    const msg = this.messages.find(m => m.id === id);
                    return msg ? msg.content : '';
                },

                appendToMessage(id, text) {
                    const msg = this.messages.find(m => m.id === id);
                    if (msg) {
                        msg.content += text;
                        this.scrollToBottom();
                    }
                },

                async submitPrompt() {
                    const prompt = this.userPrompt.trim();
                    if (!prompt || this.isResearching) return;

                    if (!this.activeSessionId) {
                        await this.createNewSession();
                        return;
                    }

                    this.demoMode = false;
                    this.userPrompt = '';
                    this.isResearching = true;
                    this.inspectorSteps = [];
                    this.latestPayload = null;
                    this.updateTimestamp();

                    this.messages.push({
                        id: 'temp-' + Date.now(),
                        role: 'user',
                        content: prompt
                    });

                    const botMessageId = 'bot-' + Date.now();
                    this.messages.push({
                        id: botMessageId,
                        role: 'assistant',
                        content: ''
                    });

                    this.scrollToBottom();

                    try {
                        await this.streamPrompt(prompt, botMessageId);
                    } catch (err) {
                        this.handlePromptFailure(err, botMessageId, prompt);
                    } finally {
                        this.isResearching = false;
                        this.updateTimestamp();
                        this.scrollToBottom();
                    }
                },

                handlePromptFailure(err, botMessageId, prompt) {
                    const title = err?.title || 'The copilot could not finish this request';
                    const message = err?.message ||
                        'Something went wrong while the model was answering. Please send your prompt again.';
                    const detail = err?.detail || '';

                    console.error('Prompt failed:', {
                        title,
                        message,
                        detail,
                        error: err
                    });

                    const idx = this.messages.findIndex(m => m.id === botMessageId);
                    const failureCard = {
                        id: 'err-' + Date.now(),
                        role: 'assistant',
                        error: true,
                        title,
                        content: message,
                        detail,
                        prompt,
                    };

                    if (idx !== -1) {
                        this.messages.splice(idx, 1, failureCard);
                    } else {
                        this.messages.push(failureCard);
                    }

                    if (this.$store?.toast) {
                        this.$store.toast.push({
                            variant: 'error',
                            title,
                            message,
                            duration: 8000,
                            action: {
                                label: 'Resend prompt',
                                handler: () => this.retryMessage(failureCard),
                            },
                        });
                    }

                    this.scrollToBottom();
                },

                retryMessage(msg) {
                    if (this.isResearching) return;

                    const prompt = msg.prompt;
                    if (!prompt) return;

                    const idx = this.messages.findIndex(m => m.id === msg.id);
                    const drop = [msg.id];

                    const prev = idx > 0 ? this.messages[idx - 1] : null;
                    if (prev && prev.role === 'user' && prev.content === prompt) {
                        drop.push(prev.id);
                    }

                    this.messages = this.messages.filter(m => !drop.includes(m.id));
                    this.userPrompt = prompt;
                    this.submitPrompt();
                },

                handleInspectorEvent(data) {
                    this.updateTimestamp();

                    const existingStepIndex = this.inspectorSteps.findIndex(
                        s => (s.tool && s.tool === data.tool) || (s.endpoint && s.endpoint === data
                            .endpoint)
                    );

                    if (existingStepIndex !== -1 && data.status !== 'running') {
                        this.inspectorSteps[existingStepIndex] = {
                            ...this.inspectorSteps[existingStepIndex],
                            ...data
                        };
                    } else {
                        this.inspectorSteps.push({
                            status: data.status || 'running',
                            tool: data.tool || data.step || 'Inspector',
                            message: data.message || '',
                            endpoint: data.endpoint || null
                        });
                    }

                    if (data.payload) {
                        this.latestPayload = {
                            ...(this.latestPayload || {}),
                            ...data.payload
                        };
                    }
                }
            }));
        });
    </script>
@endsection
