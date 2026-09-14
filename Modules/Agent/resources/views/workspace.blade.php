@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-base text-foreground overflow-hidden font-sans" x-data="copilotWorkspace('{{ $activeSession->id ?? '' }}')">

        <!-- SESSION HISTORY SIDEBAR (LEFT) — Preline panel pattern -->
        <aside class="w-72 border-r border-layer-line bg-layer flex flex-col justify-between shrink-0">
            <div class="p-4 border-b border-layer-line">
                <button @click="createNewSession()"
                    class="w-full py-2.5 px-4 inline-flex items-center justify-center gap-x-2 bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus font-medium rounded-lg text-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Research Session
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-3 space-y-1">
                @foreach ($sessions as $session)
                    <div class="flex items-center justify-between p-2.5 rounded-lg cursor-pointer transition hover:bg-layer-hover border border-transparent"
                        :class="activeSessionId === '{{ $session->id }}' ? 'bg-layer-hover border-layer-line' : ''"
                        @click="switchSession('{{ $session->id }}')">
                        <span class="truncate text-sm text-foreground">{{ $session->title }}</span>
                        <button @click.stop="togglePin('{{ $session->id }}')"
                            class="text-xs text-muted-foreground-1 hover:text-primary transition">
                            {{ $session->is_pinned ? '★' : '☆' }}
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-layer-line text-xs text-muted-foreground-1 text-center">
                Credit Shield Active • PostgreSQL JSONB Cache
            </div>
        </aside>

        <!-- MAIN AREA (CENTER: CHAT WORKSPACE) -->
        <main class="flex-1 flex flex-col h-full bg-base min-w-0">

            <!-- TOPBAR -->
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

            <!-- CHAT MESSAGE FEED -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6" id="message-container">

                <!-- DEMO MODE: sample conversation so the view can be reviewed without the backend -->
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
                                Disclaimer: analysis is auto-generated for research reference only — not investment advice.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- PREVIOUS MESSAGES FROM DATABASE -->
                <template x-for="msg in messages" :key="msg.id">
                    <div class="space-y-3">
                        <div :class="msg.role === 'user' ?
                            'bg-primary border border-primary-line text-primary-foreground ml-auto rounded-2xl rounded-br-md' :
                            'bg-card border border-card-line text-foreground rounded-2xl rounded-bl-md'"
                            class="max-w-3xl p-4 shadow-2xs">
                            <span class="text-[11px] font-semibold uppercase tracking-wider block mb-1 opacity-70"
                                x-text="msg.role"></span>
                            <div class="text-sm leading-relaxed" x-html="renderMarkdown(msg.content)"></div>
                        </div>
                    </div>
                </template>

            </div>

            <!-- INPUT BOX — Preline form-field pattern -->
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

        </main>

        <!-- INSPECTOR SIDEBAR (RIGHT: THOUGHT INSPECTOR + VALUATION MATRIX, split view) -->
        <aside class="w-[400px] border-l border-layer-line bg-layer flex flex-col overflow-y-auto shrink-0"
            x-show="inspectorVisible" x-transition.opacity>
            @include('agent::components.inspector-panel')
        </aside>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('copilotWorkspace', (initialSessionId = '') => ({
                activeSessionId: initialSessionId,
                sessionTitle: '',
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

                // DEMO MODE (?demo=1): seeds the Thought Inspector + Valuation Matrix
                // with dummy data so the UI can be reviewed without the backend.
                // TODO(backend): remove when the SSE pipeline is fully accepted.
                demoSteps: [
                    { step: 'planning', tool: null, status: 'success', endpoint: null,
                        message: 'Identified comparison intent for 3 major banks (BBCA, BBRI, BMRI)' },
                    { step: 'tool_execution', tool: 'get_company_overview', status: 'success',
                        endpoint: '/companies/BBCA/overview',
                        message: 'Data successfully retrieved from /companies/BBCA/overview.' },
                    { step: 'tool_execution', tool: 'get_company_overview', status: 'success',
                        endpoint: '/companies/BBRI/overview',
                        message: 'Data successfully retrieved from /companies/BBRI/overview.' },
                    { step: 'tool_execution', tool: 'get_sector_peers', status: 'success',
                        endpoint: '/subsectors/banks/peers',
                        message: 'Data successfully retrieved from /subsectors/banks/peers.' },
                    { step: 'synthesis', tool: null, status: 'success', endpoint: null,
                        message: 'Analysis matrix composed and compliance disclaimer attached' },
                ],

                demoPayload: {
                    get_company_overview: {
                        symbol: 'BBCA.JK',
                        company_name: 'PT Bank Central Asia Tbk.',
                        overview: { sector: 'Financials', sub_sector: 'Banks', market_cap: 817683406650000 },
                        valuation: { forward_pe: 14.1, intrinsic_value: 13694, last_close_price: 6700 },
                        financials: {
                            historical_financial_ratio: [
                                { year: '2024', profitability: { roe: 0.2017, roa: 0.0378, net_profit_margin: 0.5063 } },
                                { year: '2025', profitability: { roe: 0.2043, roa: 0.0363, net_profit_margin: 0.5137 } },
                            ],
                        },
                        dividend: { yield_ttm: 0.0569 },
                    },
                    get_sector_peers: {
                        sub_sector: 'Banks',
                        valuation: {
                            historical_valuation: {
                                2024: { pe: 16.19 },
                                2026: { pe: 10.26 },
                            },
                        },
                    },
                    widget_type: 'peers_comparison',
                    metrics: { symbol: 'BBCA.JK', company_name: 'PT Bank Central Asia Tbk.', market_cap: 817683406650000 },
                },

                init() {
                    // ?demo=1 (or no active session) => seed dummy content
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

                get latestRoe() {
                    const overview = this.latestPayload?.get_company_overview;
                    const ratios = overview?.financials?.historical_financial_ratio;
                    const fromHistory = ratios?.length
                        ? ratios[ratios.length - 1]?.profitability?.roe ?? null
                        : null;

                    return fromHistory ?? overview?.financials?.roe ?? overview?.roe_ttm ?? overview?.roe ?? null;
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
                    // Prefer the SSE-provided median, otherwise read it from the
                    // peers payload history (demo mode + DB-stored payloads).
                    if (this._subsectorMedianPe !== null && this._subsectorMedianPe !== undefined) {
                        return this._subsectorMedianPe;
                    }
                    const history = this.latestPayload?.get_sector_peers?.valuation?.historical_valuation;
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
                            window.location.href =
                                `{{ route('agent.workspace') }}?session_id=${result.session.id}`;
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

                async togglePin(sessionId) {
                    try {
                        const res = await fetch(`/agent/sessions/${sessionId}/pin`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            }
                        });
                        const result = await res.json();
                        if (result.success) {
                            window.location.reload();
                        }
                    } catch (err) {
                        console.error('Failed to toggle pin:', err);
                    }
                },


                quickDrillDown(symbol) {
                    this.userPrompt =
                        `Please run an in-depth fundamental analysis for ${symbol}`;
                    this.submitPrompt();
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
                        // SSE stream core (dibaca.md): GET/POST /api/v1/agent/chat/stream
                        const streamUrl =
                            `/api/v1/agent/chat/stream?chat_session_id=${this.activeSessionId}&prompt=${encodeURIComponent(prompt)}`;
                        const eventSource = new EventSource(streamUrl);

                        let tempPayload = null;

                        // A. Live Thought Inspector steps
                        eventSource.addEventListener('step_progress', (e) => {
                            const data = JSON.parse(e.data);
                            this.handleInspectorEvent(data);
                            // step_progress may carry a partial tool payload
                            if (data.payload) {
                                tempPayload = { ...(tempPayload || {}), ...data.payload };
                                this.latestPayload = tempPayload;
                            }
                        });

                        // B. Final payload (structured widget data)
                        eventSource.addEventListener('structured_payload', (e) => {
                            tempPayload = JSON.parse(e.data);
                            this.latestPayload = tempPayload;
                        });

                        // B2. Alternate payload event name used by the orchestrator
                        eventSource.addEventListener('payload', (e) => {
                            tempPayload = JSON.parse(e.data);
                            this.latestPayload = {
                                ...(tempPayload || {}),
                            };
                        });

                        // C. Final assistant content (full text, single event)
                        eventSource.addEventListener('token', (e) => {
                            const data = JSON.parse(e.data);
                            const botMsg = this.messages.find(m => m.id === botMessageId);
                            if (botMsg) {
                                botMsg.content += (data.text ?? data.token ?? '');
                                this.scrollToBottom();
                            }
                        });

                        // D. Stream finished — persist the assistant message locally
                        eventSource.addEventListener('done', (e) => {
                            const data = JSON.parse(e.data);
                            const botMsg = this.messages.find(m => m.id === botMessageId);
                            if (botMsg && !botMsg.content) {
                                botMsg.content = data.content ?? '';
                            }
                            this.latestPayload = tempPayload ?? this.latestPayload;
                            this.isResearching = false;
                            eventSource.close();
                            this.scrollToBottom();
                        });

                        eventSource.addEventListener('error', (e) => {
                            // EventSource also fires generic 'error' on connection
                            // issues — only treat it as a server error event when
                            // it carries a data payload.
                            if (e.data) {
                                const data = JSON.parse(e.data);
                                const botMsg = this.messages.find(m => m.id === botMessageId);
                                if (botMsg) botMsg.content += `\n\n**Error:** ${data.message}`;
                            }
                            this.isResearching = false;
                            eventSource.close();
                            this.scrollToBottom();
                        });

                    } catch (err) {
                        console.error('Streaming error:', err);
                        const botMsg = this.messages.find(m => m.id === botMessageId);
                        if (botMsg) botMsg.content +=
                        "\n\n*(Failed to process the server response)*";
                        this.isResearching = false;
                    } finally {
                        this.isResearching = false;
                        this.updateTimestamp();
                        this.scrollToBottom();
                    }
                },

                handleStreamEvent(event, data, botMessageId) {
                    const botMsg = this.messages.find(m => m.id === botMessageId);

                    switch (event) {
                        case 'token':
                        case 'chunk':
                            if (botMsg) {
                                botMsg.content += (data.token ?? data.text ?? '');
                                this.scrollToBottom();
                            }
                            break;

                        case 'step':
                        case 'thought':
                            this.handleInspectorEvent(data);
                            break;

                        case 'payload':
                        case 'matrix':
                            this.latestPayload = {
                                ...(this.latestPayload || {}),
                                ...data
                            };
                            if (data.subsector_median_pe) {
                                this._subsectorMedianPe = data.subsector_median_pe;
                            }
                            break;

                        case 'error':
                            if (botMsg) botMsg.content += `\n\n**Error:** ${data.message}`;
                            break;
                    }
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