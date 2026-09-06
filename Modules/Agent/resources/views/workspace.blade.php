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
                    <button @click.stop="togglePin('{{ $session->id }}')"
                            class="text-xs text-text-muted hover:text-accent">
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
                   class="px-3 py-1.5 bg-surface hover:bg-border text-xs rounded-md border border-border text-text-muted">Export PDF</a>
                <a :href="`/api/v1/agent/sessions/${activeSessionId}/export/md`" target="_blank"
                   class="px-3 py-1.5 bg-surface hover:bg-border text-xs rounded-md border border-border text-text-muted">Export MD</a>
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
@endsection