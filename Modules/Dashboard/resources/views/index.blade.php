@extends('layouts.app')

@section('content')
<!-- Include Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Main Alpine Container with Multi-language Dictionary State -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" 
     x-data="{ 
        openChat: false,
        lang: 'en',
        t: {
            en: {
                welcome: 'Welcome Back',
                proInvestor: 'Pro Investor',
                subtitle: 'Real-Time Market Monitoring & Portfolio Telemetry System',
                marketSentiment: 'Market Sentiment (AI Index):',
                bullishStatus: 'Strongly Bullish (78/100)',
                askCopilot: 'Ask Copilot',
                ihsgCap: 'IHSG Market Cap & Top Movers',
                realTimeSync: 'Real-Time Sync',
                topGainer: 'TOP GAINER',
                topLoser: 'TOP LOSER',
                mostTraded: 'MOST TRADED',
                creditShield: 'Credit Shield & Cache Telemetry',
                telemetryStatus: 'API Quota Performance & LLM Response',
                cacheHitRate: 'CACHE HIT RATE',
                cacheHitDesc: 'High Query Efficiency',
                cacheAge: 'CACHE AGE',
                cacheAgeDesc: 'Next Refresh: 3m',
                aiQuota: 'REMAINING AI QUOTA',
                sectorHeatmap: 'Sector Heatmap Grid',
                sectorSubtitle: 'Today\'s Industry Sector Performance',
                watchlistTitle: 'User Watchlist & Quick Actions',
                watchlistSubtitle: 'Monitored Banking Sector',
                oneClickCopilot: '⚡ 1-Click Copilot',
                target: 'Target',
                banksMonitored: '2 Banking Stocks Monitored',
                manageWatchlist: 'Manage Watchlist →',
                dailyInsights: 'Daily Market Insights & Editorial',
                insightsSubtitle: 'Curated Articles & Analyst Opinions (Admin CMS)',
                macroTag: 'MACRO INSIGHT',
                articleTitle: 'Banking Sector Outlook Q3 Following BI Interest Rate Announcement',
                articleDesc: 'BBCA and BMRI demonstrate positive performance driven by well-maintained NPL ratios...',
                byAuthor: 'By: Chief Economist Team',
                readFull: 'Read Full Article →',
                aiName: 'Gemini Investment Copilot',
                aiOnline: 'Online',
                aiWelcome: 'Hello! Any banking stocks (BBCA, BMRI) you would like to analyze with Copilot?',
                chatPlaceholder: 'Type a question...',
                chatSend: 'Send'
            },
            id: {
                welcome: 'Selamat Datang',
                proInvestor: 'Pro Investor',
                subtitle: 'Sistem Pemantauan Pasar & Telemetri Portofolio Real-Time',
                marketSentiment: 'Sentimen Pasar (AI Index):',
                bullishStatus: 'Sangat Bullish (78/100)',
                askCopilot: 'Tanya Copilot',
                ihsgCap: 'IHSG Market Cap & Top Movers',
                realTimeSync: 'Sinkronisasi Real-Time',
                topGainer: 'TOP GAINER',
                topLoser: 'TOP LOSER',
                mostTraded: 'PALING AKTIF',
                creditShield: 'Credit Shield & Telemetri Cache',
                telemetryStatus: 'Status Performa Kuota API & Respon LLM',
                cacheHitRate: 'CACHE HIT RATE',
                cacheHitDesc: 'Efisiensi Query Tinggi',
                cacheAge: 'UMUR CACHE',
                cacheAgeDesc: 'Refresh Berikutnya: 3m',
                aiQuota: 'SISA KUOTA AI',
                sectorHeatmap: 'Grid Heatmap Sektor',
                sectorSubtitle: 'Performa Sektor Industri Hari Ini',
                watchlistTitle: 'Watchlist Pengguna & Akses Cepat',
                watchlistSubtitle: 'Sektor Perbankan Dipantau',
                oneClickCopilot: '⚡ 1-Klik Copilot',
                target: 'Target',
                banksMonitored: '2 Saham Bank Dipantau',
                manageWatchlist: 'Kelola Watchlist →',
                dailyInsights: 'Wawasan Pasar Harian & Editorial',
                insightsSubtitle: 'Artikel Kurasi & Opini Analis (CMS Admin)',
                macroTag: 'WAWASAN MAKRO',
                articleTitle: 'Prospek Sektor Perbankan Kuartal III Pasca Pengumuman Suku Bunga BI',
                articleDesc: 'BBCA dan BMRI menunjukkan performa positif berkat rasio NPL yang tetap    aga baik...',
                byAuthor: 'Oleh: Tim Chief Economist',
                readFull: 'Baca Artikel Lengkap →',
                aiName: 'Gemini Investment Copilot',
                aiOnline: 'Online',
                aiWelcome: 'Halo! Ada saham perbankan (BBCA, BMRI) yang ingin kamu analisis dengan Copilot?',
                chatPlaceholder: 'Ketik pertanyaan...',
                chatSend: 'Kirim'
            }
        }
     }">

    <!-- ============================================================ -->
    <!-- HEADER & LANGUAGE SWITCHER                                   -->
    <!-- ============================================================ -->
    <div class="bg-surface border border-border rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-text-primary tracking-tight">
                    <span x-text="t[lang].welcome"></span>{{ auth()->check() ? ', ' . auth()->user()->name : '' }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-accent/15 text-accent border border-accent/30" x-text="t[lang].proInvestor"></span>
            </div>
            <p class="text-xs text-text-muted mt-1" x-text="t[lang].subtitle"></p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Language Switcher Button (EN / ID) -->
            <button @click="lang = (lang === 'en' ? 'id' : 'en')" 
                    class="px-3 py-2 bg-background border border-border hover:border-accent text-text-primary rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-text-primary group-hover:text-accent transition">Chat Workspace</h2>
                <p class="text-sm text-text-muted mt-1">Research equities with the AI copilot.</p>
            </a>

            <!-- Admin Panel -->
            <a href="{{ route('admin.users.index') }}"
               class="group bg-surface border border-border rounded-2xl p-6 hover:border-accent-dim transition">
                <div class="w-11 h-11 rounded-xl bg-accent/15 border border-accent/30 text-accent flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.427 1.756-2.925 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-text-primary group-hover:text-accent transition">Admin Panel</h2>
                <p class="text-sm text-text-muted mt-1">Users, market articles, prompts & cache.</p>
            </a>

            <!-- Profile -->
            <a href="{{ route('workspace.profile') }}"
               class="group bg-surface border border-border rounded-2xl p-6 hover:border-accent-dim transition">
                <div class="w-11 h-11 rounded-xl bg-accent/15 border border-accent/30 text-accent flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-text-primary group-hover:text-accent transition">Profile</h2>
                <p class="text-sm text-text-muted mt-1">Account settings & watchlists.</p>
            </a>
        </div>
    </div>
@endsection