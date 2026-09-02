Berikut adalah **Dokumentasi Frontend Integration Guide** yang dirancang khusus untuk tim frontend (ramah *vibe coding* / *prompt-friendly*). Dokumentasi ini menjelaskan alur kerja data, kontrak SSE, contoh implementasi komponen Blade + Alpine.js, hingga integrasi Chart.js.

---

# 📖 Frontend Integration Guide: Modul Agent (AI Research Copilot)

Panduan integrasi antarmuka untuk menghubungkan Blade, Alpine.js, dan Chart.js ke Modul Agent Laravel 13.

---

## 1. Daftar Endpoint API & Web Route

| Method | URI | Deskripsi | Tipe Response |
| --- | --- | --- | --- |
| `GET` | `/agent/workspace` | Halaman utama split-view workspace | HTML (Blade View) |
| `POST` | `/agent/sessions` | Membuat sesi chat/riset baru | JSON `{ success, session }` |
| `GET` | `/agent/sessions/{id}` | Mengambil detail & riwayat pesan sesi | JSON `{ success, session }` |
| `PATCH` | `/agent/sessions/{id}/pin` | Pin/Unpin sesi riset di sidebar | JSON `{ success, is_pinned }` |
| `GET` / `POST` | `/api/v1/agent/chat/stream` | **SSE Stream Core:** Eksekusi AI & Live Step | `text/event-stream` |
| `GET` | `/api/v1/agent/sessions/{id}/export/pdf` | Download laporan PDF | File Binary PDF |
| `GET` | `/api/v1/agent/sessions/{id}/export/md` | Download laporan Markdown | File Plain Text MD |

---

## 2. Kontrak Data Server-Sent Events (SSE)

Ketika pengguna mengirim prompt, frontend membuka koneksi SSE ke `/api/v1/agent/chat/stream`. Server akan memancarkan 4 tipe event:

### A. Event `step_progress` (Live Thought Inspector)

Dikirim setiap kali agen berpindah tahap penalaran atau mengeksekusi API.

```json
// event: step_progress
{
  "step": "planning" | "tool_execution" | "synthesis",
  "status": "running" | "success" | "failed",
  "tool": "get_company_overview", // opsional, saat step = tool_execution
  "endpoint": "/companies/BBCA/overview", // opsional
  "message": "Memanggil Sectors API via tool [get_company_overview]..."
}

```

### B. Event `structured_payload` (Data Widget / Visual)

Dikirim saat tool selesai menarik data angka agar frontend bisa me-render tabel/grafik.

```json
// event: structured_payload
{
  "widget_type": "comparison_matrix", // atau "screener_card"
  "get_company_overview": {
    "symbol": "BBCA.JK",
    "company_name": "PT Bank Central Asia Tbk.",
    "valuation": { "forward_pe": 12.8, "intrinsic_value": 13694 },
    "overview": { "sector": "Financials", "market_cap": 753611199412500 }
  }
}

```

### C. Event `done` (Sintesis Selesai)

Menandakan jawaban analisis final sudah siap beserta *compliance disclaimer*.

```json
// event: done
{
  "message_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "content": "Berdasarkan data kuartal terakhir, BBCA mencatatkan pertumbuhan...\n\n---\n**Disclaimer Regulasi...**"
}

```

### D. Event `error`

```json
// event: error
{
  "message": "Gagal memproses riset: Kuota API habis."
}

```

---

## 3. Template Utama: Workspace Split-View (`workspace.blade.php`)

Gunakan template ini sebagai layout utama yang membagi antarmuka menjadi **Sidebar Sesi (Kiri)** dan **Chat & Widget Area (Kanan)**:

```html
@extends('layouts.app')

@section('content')
<div class="flex h-screen bg-slate-950 text-slate-100 overflow-hidden" x-data="copilotWorkspace('{{ $activeSession->id ?? '' }}')">
    
    <!-- SIDEBAR RIWAYAT SESI (KIRI) -->
    <aside class="w-72 border-r border-slate-800 bg-slate-900 flex flex-col justify-between">
        <div class="p-4 border-b border-slate-800">
            <button @click="createNewSession()" class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-500 font-semibold rounded-lg text-sm transition">
                + Sesi Riset Baru
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-3 space-y-1">
            @foreach($sessions as $session)
                <div class="flex items-center justify-between p-2 rounded-lg cursor-pointer transition hover:bg-slate-800"
                     :class="activeSessionId === '{{ $session->id }}' ? 'bg-slate-800 border-l-4 border-emerald-500' : ''"
                     @click="switchSession('{{ $session->id }}')">
                    <span class="truncate text-sm">{{ $session->title }}</span>
                    <button @click.stop="togglePin('{{ $session->id }}')" class="text-xs text-slate-400 hover:text-amber-400">
                        {{ $session->is_pinned ? '★' : '☆' }}
                    </button>
                </div>
            @endforeach
        </div>

        <div class="p-4 border-t border-slate-800 text-xs text-slate-500 text-center">
            Credit Shield Active • PostgreSQL JSONB Cache
        </div>
    </aside>

    <!-- MAIN AREA (KANAN: CHAT & WIDGET) -->
    <main class="flex-1 flex flex-col h-full bg-slate-950">
        
        <!-- TOPBAR -->
        <header class="h-14 border-b border-slate-800 px-6 flex items-center justify-between">
            <h2 class="text-base font-semibold" x-text="sessionTitle || 'Pilih atau Buat Sesi Riset'"></h2>
            <div class="flex items-center space-x-2" x-show="activeSessionId">
                <a :href="`/api/v1/agent/sessions/${activeSessionId}/export/pdf`" target="_blank" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-xs rounded border border-slate-700">Export PDF</a>
                <a :href="`/api/v1/agent/sessions/${activeSessionId}/export/md`" target="_blank" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-xs rounded border border-slate-700">Export MD</a>
            </div>
        </header>

        <!-- STREAM / MESSAGE FEED -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6" id="message-container">
            
            <!-- PESAN SEBELUMNYA DARI DATABASE -->
            <template x-for="msg in messages" :key="msg.id">
                <div class="space-y-3">
                    <div :class="msg.role === 'user' ? 'bg-slate-800 ml-auto' : 'bg-slate-900 border border-slate-800'" class="max-w-3xl p-4 rounded-xl">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1" x-text="msg.role"></span>
                        <div class="prose prose-invert max-w-none text-sm leading-relaxed" x-html="renderMarkdown(msg.content)"></div>
                    </div>

                    <!-- WIDGET CONDITIONAL RENDERING -->
                    <template x-if="msg.structured_payload">
                        <div class="max-w-3xl">
                            <!-- 1. Table Komparasi -->
                            <template x-if="msg.structured_payload.get_company_overview || msg.structured_payload.get_sector_peers">
                                @include('agent::components.valuation-matrix-table')
                            </template>
                            <!-- 2. Screener Card -->
                            <template x-if="msg.structured_payload.screen_stocks">
                                @include('agent::components.screener-result-card')
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <!-- LIVE THOUGHT INSPECTOR (STEP ANIMATION) -->
            <div x-show="isResearching" class="max-w-3xl">
                @include('agent::components.thought-inspector')
            </div>

        </div>

        <!-- INPUT BOX -->
        <div class="p-4 border-t border-slate-800 bg-slate-900">
            <form @submit.prevent="submitPrompt()" class="max-w-3xl mx-auto flex gap-2">
                <input type="text" x-model="userPrompt" :disabled="isResearching"
                       placeholder="Tanya copilot (contoh: Bandingkan valuasi BBCA dan BMRI vs sektornya)..." 
                       class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500">
                <button type="submit" :disabled="isResearching || !userPrompt.trim()"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition">
                    <span x-show="!isResearching">Kirim</span>
                    <span x-show="isResearching" class="animate-spin inline-block">↻</span>
                </button>
            </form>
        </div>

    </main>
</div>
@endsection

```

---

## 4. Komponen Parsial Blade

### A. Stepper Animasi (`components/thought-inspector.blade.php`)

Menampilkan status real-time langkah logika AI (*explainability*):

```html
<div class="p-4 bg-slate-900 border border-emerald-500/30 rounded-xl space-y-3 shadow-lg shadow-emerald-500/5">
    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            Agent Thought & Execution Inspector
        </span>
        <span class="text-xs text-slate-500" x-text="currentStepTime"></span>
    </div>

    <div class="space-y-2">
        <template x-for="(step, index) in inspectorSteps" :key="index">
            <div class="flex items-start gap-3 text-xs">
                <div class="mt-0.5">
                    <span x-show="step.status === 'running'" class="w-2.5 h-2.5 rounded-full bg-amber-400 block animate-pulse"></span>
                    <span x-show="step.status === 'success'" class="w-2.5 h-2.5 rounded-full bg-emerald-400 block"></span>
                    <span x-show="step.status === 'failed'" class="w-2.5 h-2.5 rounded-full bg-rose-500 block"></span>
                </div>
                <div class="flex-1">
                    <span class="font-mono text-slate-400" x-text="'[' + step.step.toUpperCase() + ']'"></span>
                    <span class="text-slate-200 ml-1" x-text="step.message"></span>
                    <span x-show="step.endpoint" class="block font-mono text-[10px] text-slate-500" x-text="step.endpoint"></span>
                </div>
            </div>
        </template>
    </div>
</div>

```

### B. Tabel Matriks Valuasi (`components/valuation-matrix-table.blade.php`)

Menampilkan komparasi rasio keuangan side-by-side:

```html
<div class="overflow-x-auto my-3 border border-slate-800 rounded-lg bg-slate-900/60 p-3">
    <h4 class="text-xs font-bold text-slate-300 uppercase mb-2">Fundamental & Valuation Matrix</h4>
    <table class="w-full text-left text-xs border-collapse">
        <thead>
            <tr class="border-b border-slate-800 text-slate-400">
                <th class="py-2">Metrik</th>
                <th class="py-2">Nilai Emiten</th>
                <th class="py-2">Median Subsektor</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800 font-mono">
            <tr>
                <td class="py-2 text-slate-400">P/E Ratio (TTM)</td>
                <td class="py-2 text-emerald-400" x-text="msg.structured_payload.get_company_overview?.valuation?.forward_pe ?? '-'"></td>
                <td class="py-2 text-slate-300">14.2x</td>
            </tr>
            <tr>
                <td class="py-2 text-slate-400">Intrinsic Value</td>
                <td class="py-2 text-slate-200" x-text="'Rp ' + (msg.structured_payload.get_company_overview?.valuation?.intrinsic_value ?? '-')"></td>
                <td class="py-2 text-slate-300">-</td>
            </tr>
            <tr>
                <td class="py-2 text-slate-400">Market Capitalization</td>
                <td class="py-2 text-slate-200" x-text="(msg.structured_payload.get_company_overview?.overview?.market_cap / 1e12).toFixed(2) + ' T'"></td>
                <td class="py-2 text-slate-300">-</td>
            </tr>
        </tbody>
    </table>
</div>

```

### C. Screener Result Cards (`components/screener-result-card.blade.php`)

Grid emiten yang lolos filter dengan tombol *Quick Drill-Down*:

```html
<div class="my-3 space-y-2">
    <h4 class="text-xs font-bold text-slate-300 uppercase">Hasil Screening Saham</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <template x-for="stock in (msg.structured_payload.screen_stocks || [])" :key="stock.symbol">
            <div class="p-3 bg-slate-900 border border-slate-800 rounded-lg flex items-center justify-between">
                <div>
                    <span class="text-sm font-bold text-emerald-400 font-mono" x-text="stock.symbol"></span>
                    <span class="block text-xs text-slate-400" x-text="stock.company_name"></span>
                    <span class="text-[10px] text-slate-500" x-text="'ROE: ' + (stock.roe * 100).toFixed(1) + '% | PER: ' + stock.per + 'x'"></span>
                </div>
                <button @click="quickDrillDown(stock.symbol)" class="px-2.5 py-1 text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 rounded border border-slate-700 transition">
                    Drill Down →
                </button>
            </div>
        </template>
    </div>
</div>

```

---

## 5. State Management & SSE Script (`Alpine.js`)

Simpan skrip berikut di view atau di asset JS bundler Anda:

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.data('copilotWorkspace', (initialSessionId) => ({
        activeSessionId: initialSessionId,
        sessionTitle: '',
        userPrompt: '',
        isResearching: false,
        messages: [],
        inspectorSteps: [],

        init() {
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
                this.scrollToBottom();
            }
        },

        async createNewSession() {
            const res = await fetch('/agent/sessions', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ title: 'Riset Baru ' + new Date().toLocaleTimeString() })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = `/agent/workspace?session_id=${data.session.id}`;
            }
        },

        async togglePin(sessionId) {
            await fetch(`/agent/sessions/${sessionId}/pin`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            window.location.reload();
        },

        quickDrillDown(symbol) {
            this.userPrompt = `Tolong lakukan analisis fundamental mendalam untuk emiten ${symbol}`;
            this.submitPrompt();
        },

        submitPrompt() {
            if (!this.userPrompt.trim() || this.isResearching) return;

            const promptText = this.userPrompt;
            this.userPrompt = '';
            this.isResearching = true;
            this.inspectorSteps = [];

            // Tambahkan bubble chat pengguna secara optimis ke antarmuka
            this.messages.push({
                id: 'temp-' + Date.now(),
                role: 'user',
                content: promptText,
                structured_payload: null
            });
            this.scrollToBottom();

            // Buka SSE stream
            const streamUrl = `/api/v1/agent/chat/stream?chat_session_id=${this.activeSessionId}&prompt=${encodeURIComponent(promptText)}`;
            const eventSource = new EventSource(streamUrl);

            let tempPayload = null;

            eventSource.addEventListener('step_progress', (e) => {
                const data = JSON.parse(e.data);
                this.inspectorSteps.push(data);
                this.scrollToBottom();
            });

            eventSource.addEventListener('structured_payload', (e) => {
                tempPayload = JSON.parse(e.data);
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
                eventSource.close();
                this.scrollToBottom();
            });

            eventSource.addEventListener('error', (e) => {
                console.error('SSE Error:', e);
                this.isResearching = false;
                eventSource.close();
            });
        },

        renderMarkdown(text) {
            // Bisa pakai library Marked.js di CDN (window.marked.parse(text))
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

```

---

## 6. Dependensi CDN Tambahan

Untuk mendukung *markdown rendering* dan grafik tanpa bundler rumit, tambahkan script berikut di header layout Blade:

```html
<!-- Markdown Parser -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<!-- Chart.js (Untuk FinancialRadarChart) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Tailwind Typography (opsional, untuk formatting paragraf output markdown) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/typography@0.5.x/dist/typography.min.css" />

```