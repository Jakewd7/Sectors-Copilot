<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sectors Copilot')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Markdown renderer - local vendor copy (CDN unreliable) -->
    <script src="{{ asset('js/vendor/marked.min.js') }}"></script>
    <!-- Chart.js, used by FinancialRadarChart later -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-base text-text-primary font-sans antialiased">

    @yield('content')

    <!-- MUST come first of the two Alpine scripts below -->
    <script defer>
        document.addEventListener('alpine:init', () => {
            Alpine.data('copilotWorkspace', (initialSessionId) => ({
                activeSessionId: initialSessionId,
                sessionTitle: '',
                userPrompt: '',
                isResearching: false,
                messages: [],
                inspectorSteps: [],
                livePayload: null,
                inspectorVisible: true,
                // DEMO MODE: set via ?demo=1 - renders the Thought Inspector and
                // Valuation Matrix with DUMMY data so the UI can be reviewed
                // without the backend. TODO(backend): remove when the SSE
                // pipeline is wired up by the backend developer.
                demoMode: new URLSearchParams(window.location.search).has('demo'),
                currentStepTime: '00:00',
                _stepTimer: null,
                _stepStartedAt: null,

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
                    if (this.demoMode) {
                        // DEMO MODE: seed the inspector with dummy content
                        this.sessionTitle = 'Demo Research Session';
                        this.inspectorSteps = [...this.demoSteps];
                        this.livePayload = this.demoPayload;
                        this.currentStepTime = '00:42';

                        return;
                    }

                    if (this.activeSessionId) {
                        this.loadSessionData(this.activeSessionId);
                    }
                },

                async loadSessionData(sessionId) {
                    this.activeSessionId = sessionId;
                    const res = await fetch(`/agent/sessions/${sessionId}`);
                    const data = await res.json();
                    if (data.success) {
                        this.sessionTitle = data.session.title;
                        this.messages = data.session.messages || [];
                        this.hydrateInspectorFromHistory();
                        this.scrollToBottom();
                    }
                },

                hydrateInspectorFromHistory() {
                    // Restore the inspector panel after a page reload: replay the
                    // step logs of the latest assistant message and pick up its
                    // structured payload, so the split view survives refreshes.
                    const assistantMessages = this.messages.filter(
                        (m) => m.role === 'assistant' &&
                            (m.step_logs?.length || m.structured_payload),
                    );
                    const last = assistantMessages[assistantMessages.length - 1];

                    if (!last) {
                        return;
                    }

                    if (!this.inspectorSteps.length && last.step_logs?.length) {
                        this.inspectorSteps = last.step_logs.map((log) => ({
                            step: log.step_type,
                            tool: log.tool_name,
                            status: log.status === 'pending' ? 'success' : log.status,
                            endpoint: log.endpoint,
                            message: log.error_message
                                ? `Failed: ${log.error_message}`
                                : (log.tool_name ? `Data retrieved via [${log.tool_name}].` : log.step_type),
                        }));
                    }

                    this.livePayload = this.livePayload || last.structured_payload || null;
                },

                get latestPayload() {
                    return this.livePayload;
                },

                get subsectorMedianPe() {
                    const history = this.latestPayload?.get_sector_peers?.valuation?.historical_valuation;
                    const years = history ? Object.keys(history) : [];

                    return years.length ? history[years[years.length - 1]].pe : null;
                },

                get latestRoe() {
                    const ratios = this.latestPayload?.get_company_overview?.financials?.historical_financial_ratio;

                    return ratios?.length ? ratios[ratios.length - 1]?.profitability?.roe ?? null : null;
                },

                get latestDivYield() {
                    return this.latestPayload?.get_company_overview?.dividend?.yield_ttm ?? null;
                },

                get forwardPeVsMedian() {
                    const pe = this.latestPayload?.get_company_overview?.valuation?.forward_pe;
                    const median = this.subsectorMedianPe;

                    if (pe == null || median == null) {
                        return 'neutral';
                    }

                    return pe < median ? 'undervalued' : 'overvalued';
                },

                async switchSession(sessionId) {
                    await this.loadSessionData(sessionId);
                    const url = new URL(window.location.href);
                    url.searchParams.set('session_id', sessionId);
                    window.history.replaceState({}, '', url);
                },

                async createNewSession() {
                    const res = await fetch('/agent/sessions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            title: 'New Research ' + new Date().toLocaleTimeString()
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.href = `/agent/workspace?session_id=${data.session.id}`;
                    }
                },

                async togglePin(sessionId) {
                    await fetch(`/agent/sessions/${sessionId}/pin`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    window.location.reload();
                },

                quickDrillDown(symbol) {
                    this.userPrompt =
                        `Please run an in-depth fundamental analysis for ${symbol}`;
                    this.submitPrompt();
                },

                startStepTimer() {
                    this._stepStartedAt = Date.now();
                    this.currentStepTime = '00:00';
                    clearInterval(this._stepTimer);
                    this._stepTimer = setInterval(() => {
                        const elapsed = Math.floor((Date.now() - this._stepStartedAt) / 1000);
                        const mm = String(Math.floor(elapsed / 60)).padStart(2, '0');
                        const ss = String(elapsed % 60).padStart(2, '0');
                        this.currentStepTime = `${mm}:${ss}`;
                    }, 1000);
                },

                stopStepTimer() {
                    clearInterval(this._stepTimer);
                    this._stepTimer = null;
                },

                submitPrompt() {
                    if (!this.userPrompt.trim() || this.isResearching) return;

                    const promptText = this.userPrompt;
                    this.userPrompt = '';
                    this.isResearching = true;
                    this.inspectorSteps = [];
                    this.livePayload = null;
                    this.inspectorVisible = true;
                    this.startStepTimer();

                    this.messages.push({
                        id: 'temp-' + Date.now(),
                        role: 'user',
                        content: promptText,
                        structured_payload: null
                    });
                    this.scrollToBottom();

                    const streamUrl =
                        `/api/v1/agent/chat/stream?chat_session_id=${this.activeSessionId}&prompt=${encodeURIComponent(promptText)}`;
                    const eventSource = new EventSource(streamUrl);

                    let tempPayload = null;

                    eventSource.addEventListener('step_progress', (e) => {
                        const data = JSON.parse(e.data);
                        this.inspectorSteps.push(data);
                        this.scrollToBottom();
                    });

                    eventSource.addEventListener('structured_payload', (e) => {
                        tempPayload = JSON.parse(e.data);
                        this.livePayload = tempPayload;
                    });

                    eventSource.addEventListener('done', (e) => {
                        const data = JSON.parse(e.data);
                        this.messages.push({
                            id: data.message_id,
                            role: 'assistant',
                            content: data.content,
                            structured_payload: tempPayload
                        });
                        this.isResearching = false;
                        this.stopStepTimer();
                        this.hydrateInspectorFromHistory();
                        eventSource.close();
                        this.scrollToBottom();
                    });

                    eventSource.addEventListener('error', (e) => {
                        console.error('SSE Error:', e);
                        this.isResearching = false;
                        this.stopStepTimer();
                        eventSource.close();
                    });
                },

                renderMarkdown(text) {
                    return window.marked ? window.marked.parse(text || '') : text;
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const el = document.getElementById('message-container');
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                }
            }));
        });
    </script>

    <!-- Alpine.js core - local vendor copy, MUST come last after the script above -->
    <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>

</body>

</html>