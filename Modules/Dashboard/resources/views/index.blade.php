@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @php
        $gainers = collect($topMovers)->where('change', '>', 0)->sortByDesc('change')->values();
        $losers = collect($topMovers)->where('change', '<', 0)->sortBy('change')->values();

        $topGainer = $gainers->first();
        $topLoser = $losers->first();

        $topTraded = $mostTraded[0] ?? null;

        $marketCapVal = $summary['market_cap'] ?? 0;
        $marketCapTrillion = $marketCapVal > 0 ? ($marketCapVal / 1000000000000) : 0;

        $watchlistTickers = collect($watchlist['items'] ?? [])->pluck('ticker')->implode(',');
        $agentRouteExists = \Illuminate\Support\Facades\Route::has('agent.workspace');
    @endphp

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{
            lang: 'id',
            t: {
                en: {
                    welcome: 'Welcome Back',
                    proInvestor: 'Pro Investor',
                    subtitle: 'Real-Time Market Monitoring & Portfolio Telemetry System',
                    marketSentiment: 'Market Sentiment:',
                    bullishStatus: 'Strongly Bullish (78/100)',
                    askCopilot: 'Open Copilot Workspace',
                    ihsgCap: 'IHSG Market Cap & Top Movers',
                    realTimeSync: 'Real-Time Sync',
                    topGainer: 'TOP GAINER',
                    topLoser: 'TOP LOSER',
                    mostTraded: 'MOST TRADED',
                    creditShield: 'Credit Shield & Cache Telemetry',
                    telemetryStatus: 'API Quota Performance & System Efficiency',
                    cacheHitRate: 'CACHE HIT RATE',
                    cacheHitDesc: 'Query Efficiency',
                    aiQuota: 'REMAINING API QUOTA',
                    totalRequests: 'TOTAL REQUESTS',
                    sectorHeatmap: 'Sector Benchmark Grid',
                    sectorSubtitle: 'Tracked Industry Sector Fundamentals',
                    watchlistTitle: 'User Watchlist',
                    watchlistSubtitle: '{{ $watchlist['name'] ?? 'Main Portfolio' }}',
                    oneClickCopilot: '⚡ 1-Click Copilot',
                    target: 'Forward PE',
                    monitoredCount: '{{ count($watchlist['items'] ?? []) }} Stocks Monitored',
                    dailyInsights: 'Daily Market Insights & Editorial',
                    insightsSubtitle: 'Curated Articles & Sector Updates',
                    readFull: 'Read Full Article →',
                    emptyWatchlist: 'No stocks in watchlist yet.',
                    emptyInsights: 'No published insights available.'
                },
                id: {
                    welcome: 'Selamat Datang',
                    proInvestor: 'Pro Investor',
                    subtitle: 'Sistem Pemantauan Pasar & Telemetri Portofolio Real-Time',
                    marketSentiment: 'Sentimen Pasar:',
                    bullishStatus: 'Sangat Bullish (78/100)',
                    askCopilot: 'Buka Workspace Copilot',
                    ihsgCap: 'IHSG Market Cap & Top Movers',
                    realTimeSync: 'Sinkronisasi Real-Time',
                    topGainer: 'TOP GAINER',
                    topLoser: 'TOP LOSER',
                    mostTraded: 'PALING AKTIF',
                    creditShield: 'Credit Shield & Telemetri Cache',
                    telemetryStatus: 'Status Kuota API & Efisiensi Sistem',
                    cacheHitRate: 'CACHE HIT RATE',
                    cacheHitDesc: 'Efisiensi Query Tinggi',
                    aiQuota: 'SISA KUOTA API',
                    totalRequests: 'TOTAL REQUEST',
                    sectorHeatmap: 'Grid Benchmark Sektor',
                    sectorSubtitle: 'Fundamental Sektor Industri Terpantau',
                    watchlistTitle: 'Watchlist Pengguna',
                    watchlistSubtitle: '{{ $watchlist['name'] ?? 'Portofolio Utama' }}',
                    oneClickCopilot: '⚡ 1-Klik Copilot',
                    target: 'Forward PE',
                    monitoredCount: '{{ count($watchlist['items'] ?? []) }} Saham Dipantau',
                    dailyInsights: 'Wawasan Pasar Harian & Editorial',
                    insightsSubtitle: 'Artikel Kurasi & Pembaruan Emiten',
                    readFull: 'Baca Artikel Lengkap →',
                    emptyWatchlist: 'Belum ada saham di watchlist.',
                    emptyInsights: 'Belum ada wawasan yang dipublikasikan.'
                }
            }
        }">

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-text-primary tracking-tight">
                            <span x-text="t[lang].welcome"></span>{{ auth()->check() ? ', ' . auth()->user()->name : '' }}
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-accent/15 text-accent border border-accent/30"
                            x-text="t[lang].proInvestor"></span>
                    </div>
                    <p class="text-xs text-text-muted mt-1" x-text="t[lang].subtitle"></p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button @click="lang = (lang === 'en' ? 'id' : 'en')"
                        class="px-3 py-2 bg-background border border-border hover:border-accent text-text-primary rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        <span x-text="lang === 'en' ? 'EN ➔ ID' : 'ID ➔ EN'"></span>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 bg-background/80 px-3 py-2 rounded-xl border border-border text-xs">
                        <span class="text-text-muted" x-text="t[lang].marketSentiment"></span>
                        <div class="flex items-center gap-1.5 font-semibold text-emerald-500">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span x-text="t[lang].bullishStatus"></span>
                        </div>
                    </div>

                    <a href="{{ $agentRouteExists ? route('agent.workspace') : '#' }}"
                        class="px-4 py-2 bg-accent text-white rounded-xl text-xs font-semibold hover:bg-accent-dim transition flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-text="t[lang].askCopilot"></span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider" x-text="t[lang].ihsgCap"></h2>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-xl font-extrabold text-text-primary">
                                        Rp {{ number_format($marketCapTrillion, 2, ',', '.') }} T
                                    </span>
                                    <span class="text-xs font-semibold text-emerald-500">
                                        ↑ +0.85%
                                    </span>
                                </div>
                            </div>
                            <span class="text-[10px] bg-background border border-border px-2 py-1 rounded-md text-text-muted"
                                x-text="t[lang].realTimeSync"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">

                            <div class="bg-background/60 p-3 rounded-xl border border-border/80 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[10px] text-text-muted mb-1">
                                        <span x-text="t[lang].topGainer"></span>
                                        <span class="text-emerald-500 font-bold">▲</span>
                                    </div>
                                    <span class="font-bold text-text-primary block">
                                        {{ str_replace('.JK', '', $topGainer['symbol'] ?? 'N/A') }}
                                    </span>
                                    @php
                                        $gainerPct = (float) ($topGainer['change'] ?? 0);
                                        $gainerPrice = $topGainer['price'] ?? 0;
                                    @endphp
                                    <span class="text-[11px] text-emerald-500 font-semibold mt-0.5 block">
                                        Rp {{ number_format($gainerPrice, 0, ',', '.') }}
                                        (+{{ number_format($gainerPct, 2) }}%)
                                    </span>
                                </div>
                                <div class="h-10 mt-2"><canvas id="chartGainer"></canvas></div>
                            </div>

                            <div class="bg-background/60 p-3 rounded-xl border border-border/80 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[10px] text-text-muted mb-1">
                                        <span x-text="t[lang].topLoser"></span>
                                        <span class="text-red-500 font-bold">▼</span>
                                    </div>
                                    <span class="font-bold text-text-primary block">
    {{ str_replace('.JK', '', $topLoser['symbol'] ?? 'N/A') }}
</span>
@php
    $loserPct = (float) ($topLoser['change'] ?? 0);
    $loserPrice = $topLoser['price'] ?? 0;
@endphp
<span class="text-[11px] text-red-500 font-semibold mt-0.5 block">
    Rp {{ number_format($loserPrice, 0, ',', '.') }}
    ({{ number_format($loserPct, 2) }}%)
</span>
                                </div>
                                <div class="h-10 mt-2"><canvas id="chartLoser"></canvas></div>
                            </div>

                            <div class="bg-background/60 p-3 rounded-xl border border-border/80 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[10px] text-text-muted mb-1">
                                        <span x-text="t[lang].mostTraded"></span>
                                        <span class="text-accent font-bold">★</span>
                                    </div>
                                    <span class="font-bold text-text-primary block">
                                        {{ str_replace('.JK', '', $topTraded['symbol'] ?? ($topTraded['ticker'] ?? 'N/A')) }}
                                    </span>
                                    <span class="text-[11px] text-text-primary font-semibold mt-0.5 block">
                                        Rp {{ number_format($topTraded['price'] ?? ($topTraded['last_close_price'] ?? 0), 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="h-10 mt-2"><canvas id="chartMostTraded"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider" x-text="t[lang].creditShield"></h2>
                                <p class="text-xs text-text-primary font-semibold mt-1" x-text="t[lang].telemetryStatus"></p>
                            </div>
                            <span class="flex h-2 w-2 rounded-full {{ ($telemetry['credits_remaining'] ?? 0) > 50 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-xs">
                            <div class="bg-background/60 p-3 rounded-xl border border-border/80">
                                <span class="text-[10px] text-text-muted block" x-text="t[lang].cacheHitRate"></span>
                                <span class="text-lg font-bold text-accent mt-0.5 block">{{ $telemetry['hit_rate_percentage'] ?? 0 }}%</span>
                                <p class="text-[9px] text-emerald-500 mt-1" x-text="t[lang].cacheHitDesc"></p>
                            </div>

                            <div class="bg-background/60 p-3 rounded-xl border border-border/80">
                                <span class="text-[10px] text-text-muted block" x-text="t[lang].totalRequests"></span>
                                <span class="text-lg font-bold text-text-primary mt-0.5 block">{{ number_format($telemetry['total_requests'] ?? 0) }}</span>
                                <p class="text-[9px] text-text-muted mt-1">{{ $telemetry['credits_used'] ?? 0 }} misses</p>
                            </div>

                            <div class="bg-background/60 p-3 rounded-xl border border-border/80">
                                <span class="text-[10px] text-text-muted block" x-text="t[lang].aiQuota"></span>
                                <span class="text-lg font-bold text-emerald-500 mt-0.5 block">
                                    {{ $telemetry['credits_remaining'] ?? 0 }}
                                    <span class="text-xs font-normal text-text-muted">/{{ $telemetry['quota_limit'] ?? 1000 }}</span>
                                </span>
                                @php
                                    $quotaLimit = $telemetry['quota_limit'] ?? 1000;
                                    $remaining = $telemetry['credits_remaining'] ?? 0;
                                    $pct = $quotaLimit > 0 ? ($remaining / $quotaLimit) * 100 : 0;
                                @endphp
                                <div class="w-full bg-border h-1.5 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider" x-text="t[lang].sectorHeatmap"></h2>
                        <p class="text-xs text-text-primary font-semibold mt-0.5" x-text="t[lang].sectorSubtitle"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
                    @forelse ($sectors as $sector)
                        @php
                            $pe = $sector['benchmark']['mean_pe']
                                ?? ($sector['benchmark']['pe_ratio']
                                    ?? ($sector['benchmark']['median_pe'] ?? null));
                        @endphp
                        <div class="bg-background border border-border/80 hover:border-accent/40 p-3.5 rounded-xl transition">
                            <span class="font-bold text-text-primary block truncate">{{ $sector['name'] }}</span>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-text-muted text-[10px]">Peers: {{ $sector['peers_count'] }}</span>
                                <span class="font-semibold text-accent text-xs">
                                    {{ $pe ? 'PE ' . number_format((float) $pe, 1) . 'x' : 'Active' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center text-xs text-text-muted py-4">Data benchmark sektor tidak tersedia.</div>
                    @endforelse
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider" x-text="t[lang].watchlistTitle"></h2>
                                <p class="text-xs text-text-primary font-semibold mt-0.5" x-text="t[lang].watchlistSubtitle"></p>
                            </div>
                            @if(!empty($watchlistTickers) && $agentRouteExists)
                                <a href="{{ route('agent.workspace', ['context' => 'watchlist', 'tickers' => $watchlistTickers]) }}"
                                    class="px-2.5 py-1 bg-accent/15 text-accent border border-accent/30 rounded-lg text-xs font-bold hover:bg-accent/25 transition"
                                    x-text="t[lang].oneClickCopilot">
                                </a>
                            @endif
                        </div>

                        <div class="space-y-3 text-xs">
                            @forelse ($watchlist['items'] ?? [] as $index => $item)
                                <div class="p-3.5 bg-background/60 border border-border/80 rounded-xl flex items-center justify-between gap-4">
                                    <div class="w-1/3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-extrabold text-text-primary">{{ $item['ticker'] }}</span>
                                            @if($agentRouteExists)
                                                <a href="{{ route('agent.workspace', ['ticker' => $item['ticker']]) }}"
                                                    class="text-[10px] text-accent font-semibold hover:underline bg-accent/10 px-1 rounded">
                                                    AI
                                                </a>
                                            @endif
                                            @if(\Illuminate\Support\Facades\Route::has('dashboard.watchlist.destroy'))
                                                <form method="POST" action="{{ route('dashboard.watchlist.destroy', $item['ticker']) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[10px] text-red-400 hover:text-red-500 font-semibold ml-1" title="Remove">✕</button>
                                                </form>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-text-muted mt-0.5 truncate">{{ $item['company_name'] }}</p>
                                        <span class="font-bold text-emerald-500 text-xs mt-1 block">
                                            Rp {{ number_format($item['close_price'], 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="w-1/3 h-10">
                                        <canvas id="chartWatchlist-{{ $index }}"></canvas>
                                    </div>

                                    <div class="text-right w-1/3">
                                        <span class="text-text-muted text-[10px] block truncate">{{ $item['sector'] }}</span>
                                        <span class="text-[10px] text-text-primary block mt-1">
                                            <span x-text="t[lang].target"></span>:
                                            {{ $item['forward_pe'] ? number_format((float) $item['forward_pe'], 1) . 'x' : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-text-muted bg-background/30 rounded-xl border border-border/60">
                                    <p x-text="t[lang].emptyWatchlist"></p>
                                    @if(\Illuminate\Support\Facades\Route::has('dashboard.watchlist.store'))
                                        <form action="{{ route('dashboard.watchlist.store') }}" method="POST" class="mt-3 flex justify-center gap-2">
                                            @csrf
                                            <input type="text" name="stock_ticker" placeholder="Kode Saham (e.g. BBCA)" class="bg-background border border-border px-3 py-1.5 text-xs rounded-lg uppercase" required>
                                            <button type="submit" class="bg-accent text-white px-3 py-1.5 text-xs rounded-lg hover:bg-accent-dim transition">+ Tambah</button>
                                        </form>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-border flex justify-between items-center text-xs">
                        <span class="text-text-muted text-[11px]" x-text="t[lang].monitoredCount"></span>
                    </div>
                </div>

                <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-xs font-bold text-text-muted uppercase tracking-wider" x-text="t[lang].dailyInsights"></h2>
                                <p class="text-xs text-text-primary font-semibold mt-0.5" x-text="t[lang].insightsSubtitle"></p>
                            </div>
                        </div>

                        <div class="space-y-3 text-xs">
                            @forelse ($insights as $article)
                                @php
                                    $canRead = auth()->check()
                                        && \Illuminate\Support\Facades\Route::has('insights.show')
                                        && auth()->user()->can('market-insights.view');
                                @endphp

                                @if ($canRead)
                                    <a href="{{ route('insights.show', $article['slug']) }}"
                                        class="block p-3.5 bg-background/60 border border-border/80 rounded-xl hover:bg-background transition">
                                @else
                                    <div class="p-3.5 bg-background/60 border border-border/80 rounded-xl">
                                @endif
                                        <div class="flex justify-between items-center">
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-accent text-white rounded uppercase">
                                                {{ $article['category'] ?? 'MACRO' }}
                                            </span>
                                            <span class="text-[10px] text-text-muted">
                                                {{ !empty($article['published_at']) ? \Carbon\Carbon::parse($article['published_at'])->diffForHumans() : '' }}
                                            </span>
                                        </div>
                                        <h3 class="font-bold text-text-primary text-sm mt-1">
                                            {{ $article['title'] }}
                                        </h3>
                                @if ($canRead)
                                    </a>
                                @else
                                    </div>
                                @endif
                            @empty
                                <div class="p-8 text-center text-text-muted bg-background/30 rounded-xl border border-border/60">
                                    <p x-text="t[lang].emptyInsights"></p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if (\Illuminate\Support\Facades\Route::has('insights.index'))
                        <div class="mt-4 pt-3 border-t border-border flex justify-end items-center text-xs">
                            <a href="{{ route('insights.index') }}"
                                class="text-accent hover:underline text-[11px] font-semibold">
                                <span x-text="t[lang].readFull"></span>
                            </a>
                        </div>
                    @else
                        <div class="mt-4 pt-3 border-t border-border flex justify-end items-center text-xs">
                            <span class="text-text-muted text-[11px]">Editorial CMS &bull; Published</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="fixed bottom-6 right-6 z-50">
                <a href="{{ $agentRouteExists ? route('agent.workspace') : '#' }}"
                    class="w-14 h-14 bg-accent text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-105 transition relative border border-accent/40"
                    title="Buka Copilot Workspace">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span class="absolute top-1 right-1 w-3 h-3 bg-emerald-400 border-2 border-surface rounded-full"></span>
                </a>
            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sparklineOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: { x: { display: false }, y: { display: false } },
                    elements: {
                        point: { radius: 0 },
                        line: { borderWidth: 2, tension: 0.3 }
                    }
                };

                const renderChart = (id, data, color, fill = true) => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    new Chart(el, {
                        type: 'line',
                        data: {
                            labels: ['1', '2', '3', '4', '5'],
                            datasets: [{
                                data: data,
                                borderColor: color,
                                backgroundColor: fill ? color.replace(')', ', 0.1)').replace('rgb', 'rgba') : 'transparent',
                                fill: fill
                            }]
                        },
                        options: sparklineOptions
                    });
                };

                renderChart('chartGainer', [100, 102, 101, 104, 108], '#10b981');
                renderChart('chartLoser', [100, 98, 97, 95, 92], '#ef4444');
                renderChart('chartMostTraded', [50, 60, 55, 75, 70], '#6366f1');

                @foreach ($watchlist['items'] ?? [] as $index => $item)
                    renderChart('chartWatchlist-{{ $index }}', [95, 96, 94, 98, 100], '#10b981', false);
                @endforeach
            });
        </script>
@endsection