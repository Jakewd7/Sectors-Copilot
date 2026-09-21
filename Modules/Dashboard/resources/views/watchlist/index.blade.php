@extends('layouts.app')

@section('title', 'Watchlist')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        x-data="{
            items: @js($activeList['items'] ?? []),
            view: localStorage.getItem('watchlistView') || 'table',
            tab: 'all',
            q: '',
            sort: 'ticker',
            dir: 'asc',
            noteTarget: null,
            noteText: '',
            renameTarget: null,
            renameText: '',
            sparkline(history) {
                if (!history || history.length < 2) return '';
                const pes = history.map(p => Number(p.pe)).filter(v => isFinite(v));
                if (pes.length < 2) return '';
                const min = Math.min(...pes);
                const max = Math.max(...pes);
                const span = (max - min) || 1;
                const w = 74, h = 22;
                const step = w / (pes.length - 1);
                return pes.map((v, i) => `${(i * step).toFixed(1)},${(h - ((v - min) / span) * h).toFixed(1)}`).join(' ');
            },
            sparkColor(history) {
                if (!history || history.length < 2) return 'var(--theme-text-muted)';
                const first = Number(history[0].pe);
                const last = Number(history[history.length - 1].pe);
                if (!isFinite(first) || !isFinite(last) || first === 0) return 'var(--theme-text-muted)';
                const delta = ((last - first) / first) * 100;
                if (delta > 5) return '#ef4444';
                if (delta < -5) return '#10b981';
                return 'var(--theme-text-muted)';
            },
            formatYield(v) {
                if (v === null || v === undefined || !isFinite(v)) return '—';
                const n = Number(v);
                const pct = Math.abs(n) > 1 ? n : n * 100;
                return pct.toFixed(2) + '%';
            },
            formatPct(v) {
                if (v === null || v === undefined || !isFinite(v)) return '—';
                const n = Number(v);
                const pct = Math.abs(n) > 1.5 ? n : n * 100;
                return (pct >= 0 ? '+' : '') + pct.toFixed(2) + '%';
            },
            setView(v) { this.view = v; localStorage.setItem('watchlistView', v); },
            matches(item) {
                if (this.tab === 'notes' && !item.note) return false;
                if (this.tab === 'nodata' && item.has_data) return false;
                if (this.q && !item.ticker.toLowerCase().includes(this.q.toLowerCase())
                    && !(item.company_name || '').toLowerCase().includes(this.q.toLowerCase())) return false;
                return true;
            },
            sortItems(items) {
                const val = (i) => {
                    switch (this.sort) {
                        case 'day_change': return i.day_change ?? -Infinity;
                        case 'forward_pe': return i.forward_pe ?? Infinity;
                        case 'roe': return i.roe ?? -Infinity;
                        case 'dividend_yield': return i.dividend_yield ?? -Infinity;
                        case 'sector': return (i.sector || 'zzz').toLowerCase();
                        default: return i.ticker;
                    }
                };
                const out = [...items].sort((a, b) => {
                    const x = val(a), y = val(b);
                    if (typeof x === 'string' || typeof y === 'string') {
                        return this.dir === 'asc' ? String(x).localeCompare(String(y)) : String(y).localeCompare(String(x));
                    }
                    return this.dir === 'asc' ? x - y : y - x;
                });
                return out;
            },
            sortBy(field) {
                if (this.sort === field) { this.dir = this.dir === 'asc' ? 'desc' : 'asc'; }
                else { this.sort = field; this.dir = (field === 'ticker' || field === 'sector') ? 'asc' : 'desc'; }
            }
        }">

        @if (session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-accent/10 border border-accent/30 text-accent text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-danger/10 border border-danger/30 text-danger text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- 1. HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-text-primary tracking-tight">Watchlist</h1>
                <p class="text-xs text-text-muted mt-1">
                    @if ($stats && $stats['count'])
                        {{ $stats['count'] }} {{ \Illuminate\Support\Str::plural('stock', $stats['count']) }} tracked
                    @else
                        Track the companies you want to follow
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('watchlist.store') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="stock_ticker" required maxlength="10" placeholder="Add ticker (e.g. BBCA)"
                        class="w-44 bg-form-field form-field-border rounded-lg px-3 py-2 text-xs text-text-primary placeholder:text-text-muted uppercase focus:outline-none focus:border-accent transition">
                    <button type="submit"
                        class="px-3.5 py-2 bg-accent text-accent-foreground rounded-lg text-xs font-semibold hover:bg-accent-dim transition whitespace-nowrap">
                        + Add
                    </button>
                </form>

                @if ($stats && $stats['count'] && \Illuminate\Support\Facades\Route::has('agent.workspace'))
                    <a href="{{ route('agent.workspace', ['context' => 'watchlist', 'tickers' => collect($activeList['items'] ?? [])->pluck('ticker')->implode(',')]) }}"
                        class="px-3.5 py-2 bg-surface border border-border rounded-lg text-xs font-semibold text-text-primary hover:bg-layer-hover transition whitespace-nowrap inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-accent" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16 2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3Z" />
                        </svg>
                        Analyze all
                    </a>
                @endif
            </div>
        </div>

        {{-- 2. STAT STRIP --}}
        @if ($stats && $stats['count'])
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-5">
                <div class="bg-surface border border-border rounded-xl px-4 py-3">
                    <span class="block text-xl font-bold text-text-primary tracking-tight tabular-nums">
                        {{ $stats['count'] }}
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-text-muted mt-0.5 block">
                        Stocks tracked
                    </span>
                </div>

                <div class="bg-surface border border-border rounded-xl px-4 py-3">
                    <span
                        class="block text-xl font-bold tracking-tight tabular-nums {{ $stats['avg_change'] === null ? 'text-text-muted' : ($stats['avg_change'] >= 0 ? 'text-emerald-500' : 'text-danger') }}">
                        {{ $stats['avg_change'] === null ? '—' : ($stats['avg_change'] >= 0 ? '+' : '') . number_format($stats['avg_change'], 2) . '%' }}
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-text-muted mt-0.5 block">
                        Avg. day change
                    </span>
                </div>

                <div class="bg-surface border border-border rounded-xl px-4 py-3">
                    <span class="block text-xl font-bold text-text-primary tracking-tight tabular-nums">
                        {{ $stats['avg_pe'] === null ? '—' : number_format($stats['avg_pe'], 1) . 'x' }}
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-text-muted mt-0.5 block">
                        Avg. forward PE
                    </span>
                </div>

                <div class="bg-surface border border-border rounded-xl px-4 py-3">
                    <span class="block text-xl font-bold text-text-primary tracking-tight tabular-nums">
                        {{ $stats['sectors'] }}
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-text-muted mt-0.5 block">
                        {{ \Illuminate\Support\Str::plural('Sector', $stats['sectors']) }} covered
                    </span>
                </div>
            </div>
        @endif

        {{-- 3. LIST TABS --}}
        @php
            $listCount = count($watchlists);
        @endphp

        <div class="flex items-center gap-2 border-b border-border mt-6">
            <div class="flex items-center gap-1 overflow-x-auto no-scrollbar flex-1 min-w-0">
                @foreach ($watchlists as $list)
                    <a href="{{ route('watchlist.index', ['list' => $list['id']]) }}"
                        class="px-3.5 py-2.5 text-xs font-semibold whitespace-nowrap border-b-2 -mb-px transition {{ ($activeList['id'] ?? null) === $list['id'] ? 'text-accent border-accent' : 'text-text-muted border-transparent hover:text-text-primary' }}">
                        {{ $list['name'] }}
                        <span class="text-[10px] opacity-60 ml-1">{{ $list['count'] }}</span>
                    </a>
                @endforeach

                <button type="button" @click="noteTarget = 'new-list'"
                    class="px-3 py-2.5 text-text-muted hover:text-accent transition text-base leading-none shrink-0"
                    title="Create watchlist">+</button>
            </div>

            @if ($activeList)
                <button type="button"
                    @click="renameTarget = { id: '{{ $activeList['id'] }}', name: @js($activeList['name']) }; renameText = @js($activeList['name'])"
                    class="shrink-0 px-2.5 py-2 text-[11px] text-text-muted hover:text-text-primary transition whitespace-nowrap border-b-2 border-transparent -mb-px"
                    title="Rename this list">Rename</button>
            @endif
        </div>

        {{-- 4. TOOLBAR --}}
        @if ($activeList && $activeList['count'])
            <div class="flex flex-wrap items-center justify-between gap-3 py-4">
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" x-model="q" placeholder="Filter tickers…"
                        class="w-40 bg-form-field form-field-border rounded-lg px-3 py-1.5 text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-accent transition">

                    <div class="inline-flex bg-surface border border-border rounded-lg p-0.5 gap-0.5">
                        <button type="button" @click="setView('table')"
                            :class="view === 'table' ? 'bg-accent text-accent-foreground' : 'text-text-muted hover:text-text-primary'"
                            class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition">Table</button>
                        <button type="button" @click="setView('cards')"
                            :class="view === 'cards' ? 'bg-accent text-accent-foreground' : 'text-text-muted hover:text-text-primary'"
                            class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition">Cards</button>
                    </div>

                    <div class="inline-flex bg-surface border border-border rounded-lg p-0.5 gap-0.5">
                        <button type="button" @click="tab = 'all'"
                            :class="tab === 'all' ? 'bg-accent text-accent-foreground' : 'text-text-muted hover:text-text-primary'"
                            class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition">All</button>
                        <button type="button" @click="tab = 'notes'"
                            :class="tab === 'notes' ? 'bg-accent text-accent-foreground' : 'text-text-muted hover:text-text-primary'"
                            class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition">With notes</button>
                        <button type="button" @click="tab = 'nodata'"
                            :class="tab === 'nodata' ? 'bg-accent text-accent-foreground' : 'text-text-muted hover:text-text-primary'"
                            class="px-2.5 py-1.5 rounded-md text-[11px] font-semibold transition">No data</button>
                    </div>
                </div>

                <p class="text-[11px] text-text-muted">
                    Showing <span x-text="sortItems(items.filter(i => matches(i))).length"></span> of
                    {{ $activeList['count'] }}
                </p>
            </div>

            {{-- 5A. TABLE VIEW --}}
            <div x-show="view === 'table'" class="bg-surface border border-border rounded-2xl overflow-x-auto no-scrollbar">
                <table class="w-full text-xs min-w-[860px]">
                    <thead>
                        <tr class="bg-base/40 text-[10px] uppercase tracking-wider text-text-muted">
                            @foreach ([['ticker', 'Symbol', 'left'], ['close_price', 'Price', 'right'], ['day_change', 'Day', 'right'], ['forward_pe', 'Fwd PE', 'right'], ['roe', 'ROE', 'right'], ['dividend_yield', 'Div yld', 'right']] as [$key, $label, $align])
                                <th class="px-3 py-3 font-bold {{ $align === 'right' ? 'text-right' : 'text-left' }} whitespace-nowrap">
                                    <button type="button" @click="sortBy('{{ $key }}')"
                                        class="hover:text-text-primary transition inline-flex items-center gap-1 {{ $align === 'right' ? 'flex-row-reverse' : '' }}">
                                        {{ $label }}
                                        <span x-show="sort === '{{ $key }}'" x-text="dir === 'asc' ? '▲' : '▼'"
                                            class="text-[8px]"></span>
                                    </button>
                                </th>
                            @endforeach

                            <th class="px-3 py-3 font-bold text-left whitespace-nowrap">
                                <span title="Forward PE over the last 5 fiscal years">PE 5y</span>
                            </th>

                            <th class="px-3 py-3 font-bold text-left whitespace-nowrap">
                                <button type="button" @click="sortBy('sector')" class="hover:text-text-primary transition">
                                    Sector <span x-show="sort === 'sector'" x-text="dir === 'asc' ? '▲' : '▼'"
                                        class="text-[8px]"></span>
                                </button>
                            </th>

                            <th class="px-3 py-3" style="width:110px"></th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="item in sortItems(items.filter(i => matches(i)))" :key="item.ticker">
                            <tr class="border-t border-border hover:bg-layer-hover transition group/row">
                                <td class="px-3 py-2.5">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="font-bold font-mono text-text-primary"
                                            x-text="item.ticker"></span>
                                        <button type="button" x-show="item.note" x-cloak
                                            @click="noteTarget = item.id; noteText = item.note"
                                            class="text-accent hover:text-accent-dim transition"
                                            :title="item.note" aria-label="View note">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10.5h8m-8 3h5m-8.5 6.5 2.4-2.4a1.5 1.5 0 0 1 1.06-.44h8.54a2.5 2.5 0 0 0 2.5-2.5V6.5A2.5 2.5 0 0 0 16.5 4h-9A2.5 2.5 0 0 0 5 6.5v13.5Z"/></svg>
                                        </button>
                                    </div>
                                    <div class="text-[10.5px] text-text-muted truncate max-w-[170px]"
                                        x-text="item.company_name"></div>
                                </td>

                                <td class="px-3 py-2.5 text-right tabular-nums text-text-primary"
                                    x-text="item.close_price ? 'Rp ' + Number(item.close_price).toLocaleString('id-ID') : '—'"></td>

                                <td class="px-3 py-2.5 text-right tabular-nums font-bold"
                                    :class="item.day_change === null ? 'text-text-muted' :
                                        (item.day_change >= 0 ? 'text-emerald-500' : 'text-danger')"
                                    x-text="formatPct(item.day_change)"></td>

                                <td class="px-3 py-2.5 text-right tabular-nums text-text-muted"
                                    x-text="item.forward_pe ? item.forward_pe.toFixed(1) + 'x' : '—'"></td>

                                <td class="px-3 py-2.5 text-right tabular-nums text-text-primary font-semibold"
                                    x-text="item.roe === null ? '—' : item.roe.toFixed(1) + '%'"></td>

                                <td class="px-3 py-2.5 text-right tabular-nums text-text-muted"
                                    x-text="formatYield(item.dividend_yield)"></td>

                                <td class="px-3 py-2.5">
                                    <svg x-show="item.pe_history && item.pe_history.length >= 2" x-cloak
                                        class="block" width="74" height="22" viewBox="0 0 74 22"
                                        preserveAspectRatio="none" role="img"
                                        :aria-label="'PE history for ' + item.ticker">
                                        <polyline fill="none" :stroke="sparkColor(item.pe_history)"
                                            stroke-width="1.6" :points="sparkline(item.pe_history)"></polyline>
                                    </svg>
                                    <span x-show="!item.pe_history || item.pe_history.length < 2"
                                        class="text-text-muted text-[11px]">—</span>
                                </td>

                                <td class="px-3 py-2.5">
                                    <span x-show="item.sector" x-cloak
                                        class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold bg-accent/10 text-accent border border-accent/20 whitespace-nowrap"
                                        x-text="item.sector"></span>
                                    <span x-show="!item.sector" class="text-text-muted">—</span>
                                </td>

                                <td class="px-3 py-2.5">
                                    <div
                                        class="flex items-center justify-end gap-0.5 opacity-0 group-hover/row:opacity-100 focus-within:opacity-100 transition">
                                        <button type="button" @click="noteTarget = item.id; noteText = item.note || ''"
                                            class="size-7 inline-flex items-center justify-center rounded-md text-text-muted hover:text-text-primary hover:bg-base transition"
                                            title="Note" aria-label="Edit note">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </button>

                                        @if (\Illuminate\Support\Facades\Route::has('agent.workspace'))
                                            <a :href="'{{ route('agent.workspace') }}?ticker=' + encodeURIComponent(item.ticker)"
                                                class="size-7 inline-flex items-center justify-center rounded-md text-text-muted hover:text-accent hover:bg-base transition"
                                                title="Ask Copilot" aria-label="Ask Copilot">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16 2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3Z"/></svg>
                                            </a>
                                        @endif

                                        <form method="POST" :action="'{{ url('/watchlist/tickers') }}/' + encodeURIComponent(item.ticker)"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="size-7 inline-flex items-center justify-center rounded-md text-text-muted hover:text-danger hover:bg-base transition"
                                                title="Remove" aria-label="Remove from watchlist">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div x-show="items.filter(i => matches(i)).length === 0" x-cloak
                    class="px-4 py-12 text-center text-text-muted text-xs">
                    No stocks match this filter.
                </div>
            </div>

            {{-- 5B. CARDS VIEW --}}
            <div x-show="view === 'cards'" x-cloak class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="item in sortItems(items.filter(i => matches(i)))" :key="item.ticker">
                    <div class="relative bg-surface border border-border rounded-2xl p-4 hover:border-accent/40 transition group/card">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold font-mono text-sm text-text-primary" x-text="item.ticker"></div>
                                <div class="text-[10.5px] text-text-muted truncate" x-text="item.company_name"></div>
                            </div>
                            <span class="text-xs font-bold tabular-nums shrink-0"
                                :class="item.day_change === null ? 'text-text-muted' :
                                    (item.day_change >= 0 ? 'text-emerald-500' : 'text-danger')"
                                x-text="formatPct(item.day_change)"></span>
                        </div>

                        <div class="text-lg font-bold text-text-primary tracking-tight tabular-nums mt-3"
                            x-text="item.close_price ? 'Rp ' + Number(item.close_price).toLocaleString('id-ID') : '—'"></div>

                        <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-border text-[10.5px] text-text-muted">
                            <div>Fwd PE<b class="block text-xs text-text-primary font-bold tabular-nums mt-0.5"
                                    x-text="item.forward_pe ? item.forward_pe.toFixed(1) + 'x' : '—'"></b></div>
                            <div>ROE<b class="block text-xs text-text-primary font-bold tabular-nums mt-0.5"
                                    x-text="item.roe === null ? '—' : item.roe.toFixed(1) + '%'"></b></div>
                            <div>Div<b class="block text-xs text-text-primary font-bold tabular-nums mt-0.5"
                                    x-text="formatYield(item.dividend_yield)"></b></div>
                        </div>

                        <div x-show="item.note" x-cloak
                            class="mt-3 px-3 py-2 bg-base border border-border rounded-lg text-[11px] text-text-muted leading-relaxed"
                            x-text="item.note"></div>

                        <button type="button" @click="noteTarget = item.id; noteText = item.note || ''"
                            class="mt-3 text-[11px] text-accent hover:underline"
                            x-text="item.note ? 'Edit note' : '+ Add note'"></button>

                        <div
                            class="absolute top-3 right-3 flex gap-0.5 opacity-0 group-hover/card:opacity-100 focus-within:opacity-100 transition">
                            @if (\Illuminate\Support\Facades\Route::has('agent.workspace'))
                                <a :href="'{{ route('agent.workspace') }}?ticker=' + encodeURIComponent(item.ticker)"
                                    class="size-7 inline-flex items-center justify-center rounded-md bg-surface border border-border text-text-muted hover:text-accent transition"
                                    title="Ask Copilot">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16 2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3Z"/></svg>
                                </a>
                            @endif

                            <form method="POST" :action="'{{ url('/watchlist/tickers') }}/' + encodeURIComponent(item.ticker)">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="size-7 inline-flex items-center justify-center rounded-md bg-surface border border-border text-text-muted hover:text-danger transition"
                                    title="Remove">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        @else
            {{-- 6. EMPTY STATE --}}
            <div class="mt-6 bg-surface border border-dashed border-border rounded-2xl px-6 py-14 text-center">
                <div class="size-12 rounded-xl bg-accent/10 text-accent inline-flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                </div>
                <h2 class="text-base font-bold text-text-primary mt-4">This watchlist is empty</h2>
                <p class="text-xs text-text-muted mt-2 max-w-md mx-auto leading-relaxed">
                    Add the companies you want to follow and Sectors Copilot keeps their fundamentals,
                    valuation and daily movement in one place.
                </p>

                <form method="POST" action="{{ route('watchlist.store') }}"
                    class="mt-5 flex flex-wrap justify-center gap-2">
                    @csrf
                    <input type="text" name="stock_ticker" required maxlength="10" placeholder="Ticker (e.g. BBCA)"
                        class="w-40 bg-form-field form-field-border rounded-lg px-3 py-2 text-xs text-text-primary placeholder:text-text-muted uppercase focus:outline-none focus:border-accent transition">
                    <button type="submit"
                        class="px-4 py-2 bg-accent text-accent-foreground rounded-lg text-xs font-semibold hover:bg-accent-dim transition">
                        + Add ticker
                    </button>
                </form>

                @if ($stats && $stats['count'] === 0)
                    <div class="mt-6 pt-5 border-t border-border max-w-md mx-auto">
                        <p class="text-[11px] text-text-muted">Popular right now</p>
                        <div class="flex flex-wrap justify-center gap-1.5 mt-2.5">
                            @foreach (['BBCA', 'BBRI', 'BMRI', 'TLKM', 'ASII'] as $ticker)
                                <form method="POST" action="{{ route('watchlist.store') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="stock_ticker" value="{{ $ticker }}">
                                    <button type="submit"
                                        class="px-2.5 py-1 bg-surface border border-border rounded-md text-[11px] font-mono font-semibold text-text-muted hover:text-accent hover:border-accent/40 transition">
                                        {{ $ticker }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- NOTE MODAL --}}
        <div x-show="noteTarget" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @click.self="noteTarget = null">
            <div class="w-full max-w-md bg-surface border border-border rounded-2xl p-5">
                <template x-if="noteTarget === 'new-list'">
                    <div>
                        <h3 class="text-sm font-bold text-text-primary">New watchlist</h3>
                        <form method="POST" action="{{ route('watchlist.lists.store') }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="text" name="name" required maxlength="60" placeholder="e.g. Banking Picks"
                                class="w-full bg-form-field form-field-border rounded-lg px-3 py-2 text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-accent">
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="noteTarget = null"
                                    class="px-3.5 py-2 bg-surface border border-border rounded-lg text-xs font-semibold text-text-primary hover:bg-layer-hover transition">Cancel</button>
                                <button type="submit"
                                    class="px-3.5 py-2 bg-accent text-accent-foreground rounded-lg text-xs font-semibold hover:bg-accent-dim transition">Create</button>
                            </div>
                        </form>
                    </div>
                </template>

                <template x-if="noteTarget && noteTarget !== 'new-list' && renameTarget === null">
                    <div>
                        <h3 class="text-sm font-bold text-text-primary">Note</h3>
                        <p class="text-[11px] text-text-muted mt-1">Why you are tracking this stock.</p>
                        <form method="POST" action="{{ route('watchlist.store') }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="stock_ticker" :value="items.find(i => i.id === noteTarget)?.ticker">
                            <textarea name="note" rows="3" x-model="noteText" maxlength="255"
                                placeholder="e.g. Cheapest of the big four, watching Q4 loan growth."
                                class="w-full bg-form-field form-field-border rounded-lg px-3 py-2 text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-accent resize-none"></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="noteTarget = null"
                                    class="px-3.5 py-2 bg-surface border border-border rounded-lg text-xs font-semibold text-text-primary hover:bg-layer-hover transition">Cancel</button>
                                <button type="submit"
                                    class="px-3.5 py-2 bg-accent text-accent-foreground rounded-lg text-xs font-semibold hover:bg-accent-dim transition">Save note</button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        {{-- RENAME MODAL --}}
        <div x-show="renameTarget" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @click.self="renameTarget = null">
            <div class="w-full max-w-md bg-surface border border-border rounded-2xl p-5">
                <h3 class="text-sm font-bold text-text-primary">Rename watchlist</h3>
                <form method="POST" :action="'{{ url('/watchlist/lists') }}/' + (renameTarget?.id ?? '')"
                    class="mt-4 space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" required maxlength="60" x-model="renameText"
                        class="w-full bg-form-field form-field-border rounded-lg px-3 py-2 text-xs text-text-primary focus:outline-none focus:border-accent">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="renameTarget = null"
                            class="px-3.5 py-2 bg-surface border border-border rounded-lg text-xs font-semibold text-text-primary hover:bg-layer-hover transition">Cancel</button>
                        <button type="submit"
                            class="px-3.5 py-2 bg-accent text-accent-foreground rounded-lg text-xs font-semibold hover:bg-accent-dim transition">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
