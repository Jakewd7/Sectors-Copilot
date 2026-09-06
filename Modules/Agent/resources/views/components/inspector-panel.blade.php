<!-- Right panel content: Thought Inspector + Valuation Matrix (split view).
     Expects the Alpine scope of copilotWorkspace: inspectorSteps, currentStepTime,
     isResearching, messages (each assistant msg may carry structured_payload). -->

<!-- ============ THOUGHT INSPECTOR ============ -->
<div class="p-6 border-b border-border">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted">Thought inspector</h3>
        <span class="text-xs text-text-muted" x-text="currentStepTime"></span>
    </div>

    <!-- Empty state before any research has run in this session -->
    <div x-show="inspectorSteps.length === 0 && !isResearching" class="text-xs text-text-muted/70">
        No agent activity yet. Send a research prompt to see the agent's reasoning steps here.
    </div>

    <div class="space-y-2.5">
        <template x-for="(step, index) in inspectorSteps" :key="index">
            <div class="flex items-start gap-2.5 text-xs">
                <div class="mt-0.5 shrink-0">
                    <!-- running: spinner -->
                    <svg x-show="step.status === 'running'" class="w-3.5 h-3.5 text-accent animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <!-- success: check -->
                    <svg x-show="step.status === 'success'" class="w-3.5 h-3.5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <!-- failed: x -->
                    <svg x-show="step.status === 'failed'" class="w-3.5 h-3.5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="font-mono text-text-muted" x-text="step.tool || step.step"></span>
                    <span x-show="step.tool" class="text-text-muted"
                          x-text="' ×' + inspectorSteps.filter(s => s.tool === step.tool && s.status === 'success').length"></span>
                    <span class="block text-text-primary" x-text="step.message"></span>
                    <span x-show="step.endpoint" class="block font-mono text-[10px] text-text-muted/70 break-all" x-text="step.endpoint"></span>
                </div>
            </div>
        </template>

        <!-- Pending synthesis placeholder (matches the reference layout) -->
        <div x-show="isResearching" class="flex items-start gap-2.5 text-xs">
            <div class="mt-0.5 w-3.5 h-3.5 rounded-full border border-border shrink-0"></div>
            <span class="text-text-muted">Synthesis</span>
        </div>
    </div>
</div>

<!-- ============ VALUATION MATRIX ============ -->
<!-- Latest structured payload: live from SSE, otherwise the newest message from DB history -->
<template x-if="latestPayload">
    <div class="p-6 space-y-6">
        <!-- Company overview / valuation comparison table -->
        <template x-if="latestPayload.get_company_overview || latestPayload.get_sector_peers">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4">Valuation matrix</h3>
                @include('agent::components.valuation-matrix-table')
            </div>
        </template>

        <!-- Screener results -->
        <template x-if="latestPayload.screen_stocks">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-text-muted mb-4">Screening results</h3>
                @include('agent::components.screener-result-card')
            </div>
        </template>
    </div>
</template>