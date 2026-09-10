@extends('layouts.app')

@section('content')
    <div class="flex h-screen bg-base text-text-primary overflow-hidden font-sans"
        x-data="copilotWorkspace('{{ $activeSession->id ?? '' }}')">

        <!-- SESSION HISTORY SIDEBAR (LEFT) -->
        <aside class="w-72 border-r border-border bg-surface flex flex-col justify-between shrink-0">
            <div class="p-4 border-b border-border">
                <button @click="createNewSession()"
                    class="w-full py-2.5 px-4 bg-accent hover:bg-accent-dim text-base font-semibold rounded-lg text-sm transition">
                    + New Research Session
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-3 space-y-1">
                @foreach($sessions as $session)
                    <div class="flex items-center justify-between p-2.5 rounded-lg cursor-pointer transition hover:bg-border/40"
                        :class="activeSessionId === '{{ $session->id }}' ? 'bg-border/60 border-l-2 border-accent' : ''"
                        @click="switchSession('{{ $session->id }}')">
                        <span class="truncate text-sm text-text-primary">{{ $session->title }}</span>
                        <button @click.stop="togglePin('{{ $session->id }}')" class="text-xs text-text-muted hover:text-accent">
                            {{ $session->is_pinned ? '★' : '☆' }}
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-border text-xs text-text-muted text-center">
                Credit Shield Active • PostgreSQL JSONB Cache
            </div>
        </aside>

        <!-- MAIN AREA (CENTER: CHAT WORKSPACE) -->
        <main class="flex-1 flex flex-col h-full bg-base min-w-0">

            <!-- TOPBAR -->
            <header class="h-14 border-b border-border px-6 flex items-center justify-between shrink-0">
                <h2 class="text-base font-semibold" x-text="sessionTitle || 'Select or Create a Research Session'"></h2>
                <div class="flex items-center space-x-2" x-show="activeSessionId">
                    <a :href="`/api/v1/agent/sessions/${activeSessionId}/export/pdf`" target="_blank"
                        class="px-3 py-1.5 bg-surface hover:bg-border text-xs rounded-md border border-border text-text-muted">Export
                        PDF</a>
                    <a :href="`/api/v1/agent/sessions/${activeSessionId}/export/md`" target="_blank"
                        class="px-3 py-1.5 bg-surface hover:bg-border text-xs rounded-md border border-border text-text-muted">Export
                        MD</a>
                </div>
            </header>

            <!-- CHAT MESSAGE FEED -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6" id="message-container">

                <!-- DEMO MODE: sample conversation so the view can be reviewed without the backend -->
                <template x-if="demoMode">
                    <div class="space-y-6">
                        <div class="flex justify-end">
                            <div class="max-w-2xl bg-accent text-base p-4 rounded-xl text-sm leading-relaxed">
                                Compare BBCA, BBRI and BMRI valuation against their sector
                            </div>
                        </div>
                        <div>
                            <div class="max-w-2xl bg-surface border border-border text-text-primary p-4 rounded-xl text-sm leading-relaxed"
                                x-html="renderMarkdown('**BBCA** trades at a premium: forward P/E of **14.1x** vs the banks subsector median of **10.26x**, backed by the highest ROE in the group (**20.4%**).\n\n- **BBRI** offers the best dividend yield at ~6.1%\n- **BMRI** is the cheapest on P/E at ~6.7x\n\nFull breakdown is available in the valuation matrix on the right panel.')">
                            </div>
                            <p class="mt-3 text-[11px] text-text-muted">
                                Disclaimer: analysis is auto-generated for research reference only — not investment advice.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- PREVIOUS MESSAGES FROM DATABASE -->
                <template x-for="msg in messages" :key="msg.id">
                    <div class="space-y-3">
                        <div :class="msg.role === 'user' ? 'bg-accent text-base ml-auto' : 'bg-surface border border-border text-text-primary'"
                            class="max-w-3xl p-4 rounded-xl">
                            <span class="text-[11px] font-semibold uppercase tracking-wider block mb-1 opacity-70"
                                x-text="msg.role"></span>
                            <div class="text-sm leading-relaxed" x-html="renderMarkdown(msg.content)"></div>
                        </div>
                    </div>
                </template>

            </div>

            <!-- INPUT BOX -->
            <div class="p-4 border-t border-border bg-surface shrink-0">
                <form @submit.prevent="submitPrompt()" class="max-w-3xl mx-auto flex gap-2">
                    <input type="text" x-model="userPrompt" :disabled="isResearching"
                        placeholder="Ask the copilot (e.g. Compare BBCA and BMRI valuation vs their sector)..."
                        class="flex-1 bg-base border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary placeholder-text-muted/60 focus:outline-none focus:border-accent-dim focus:ring-2 focus:ring-accent/20">
                    <button type="submit" :disabled="isResearching || !userPrompt.trim()"
                        class="px-5 py-2.5 bg-accent hover:bg-accent-dim disabled:opacity-40 text-base text-sm font-semibold rounded-lg transition">
                        <span x-show="!isResearching">Send</span>
                        <span x-show="isResearching" class="animate-spin inline-block">↻</span>
                    </button>
                </form>
            </div>

        </main>

        <!-- INSPECTOR SIDEBAR (RIGHT: THOUGHT INSPECTOR + VALUATION MATRIX, split view) -->
        <aside class="w-[400px] border-l border-border bg-surface flex flex-col overflow-y-auto shrink-0"
            x-show="inspectorVisible" x-transition.opacity>
            @include('agent::components.inspector-panel')
        </aside>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

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
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                inspectorSteps: [],
                currentStepTime: '',
                latestPayload: null,
                subsectorMedianPe: null,

                init() {
                    if (this.activeSessionId) {
                        this.loadSession(this.activeSessionId);
                    } else {
                        this.demoMode = true;
                    }

                    window.addEventListener('inspector-update', (e) => {
                        this.handleInspectorEvent(e.detail);
                    });
                },

                get latestRoe() {
                    const overview = this.latestPayload?.get_company_overview;
                    return overview?.financials?.roe ?? overview?.roe_ttm ?? overview?.roe ?? null;
                },

                get latestDivYield() {
                    const overview = this.latestPayload?.get_company_overview;
                    return overview?.valuation?.dividend_yield ?? overview?.dividend_yield ?? null;
                },

                get forwardPeVsMedian() {
                    const pe = parseFloat(this.latestPayload?.get_company_overview?.valuation?.forward_pe);
                    const median = parseFloat(this.subsectorMedianPe);
                    if (!isNaN(pe) && !isNaN(median) && median > 0) {
                        return pe < median ? 'undervalued' : 'overvalued';
                    }
                    return 'neutral';
                },

                renderMarkdown(content) {
                    if (!content) return '';
                    return typeof marked !== 'undefined' ? marked.parse(content) : content.replace(/\n/g, '<br>');
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = document.getElementById('message-container');
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                },

                updateTimestamp() {
                    const now = new Date();
                    this.currentStepTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
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
                            body: JSON.stringify({ title: null }),
                        });
                        const result = await res.json();
                        if (result.success) {
                            window.location.href = `{{ route('agent.workspace') }}?session_id=${result.session.id}`;
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
                    window.history.pushState({}, '', `{{ route('agent.workspace') }}?session_id=${sessionId}`);
                },

                async loadSession(sessionId) {
                    try {
                        const res = await fetch(`/agent/sessions/${sessionId}`, {
                            headers: { 'Accept': 'application/json' }
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
                            this.latestPayload = typeof lastMsg.structured_payload === 'string'
                                ? JSON.parse(lastMsg.structured_payload)
                                : lastMsg.structured_payload;
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
                    this.userPrompt = `Beri analisa mendalam dan perbandingan valuasi untuk saham ${symbol}`;
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
                        const response = await fetch('/v1/agent/chat/stream', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'text/event-stream'
                            },
                            body: JSON.stringify({
                                chat_session_id: this.activeSessionId,
                                prompt: prompt
                            })
                        });

                        if (!response.ok) throw new Error(`HTTP error ${response.status}`);

                        const reader = response.body.getReader();
                        const decoder = new TextDecoder('utf-8');
                        let buffer = '';

                        while (true) {
                            const { done, value } = await reader.read();
                            if (done) break;

                            buffer += decoder.decode(value, { stream: true });
                            const lines = buffer.split('\n\n');
                            buffer = lines.pop();

                            for (const block of lines) {
                                if (!block.trim()) continue;

                                let eventType = 'message';
                                let dataStr = '';

                                block.split('\n').forEach(line => {
                                    if (line.startsWith('event: ')) eventType = line.replace('event: ', '').trim();
                                    if (line.startsWith('data: ')) dataStr = line.replace('data: ', '').trim();
                                });

                                if (dataStr) {
                                    try {
                                        const parsedData = JSON.parse(dataStr);
                                        this.handleStreamEvent(eventType, parsedData, botMessageId);
                                    } catch (e) {
                                        console.error("JSON parse error on SSE chunk:", dataStr);
                                    }
                                }
                            }
                        }
                    } catch (err) {
                        console.error('Streaming error:', err);
                        const botMsg = this.messages.find(m => m.id === botMessageId);
                        if (botMsg) botMsg.content += "\n\n*(Gagal memproses response dari server)*";
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
                                this.subsectorMedianPe = data.subsector_median_pe;
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
                        s => (s.tool && s.tool === data.tool) || (s.endpoint && s.endpoint === data.endpoint)
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