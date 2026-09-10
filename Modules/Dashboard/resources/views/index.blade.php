@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6 space-y-6">

        {{-- Feedback Flash Message --}}
        @if(session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- 1. Widget Telemetry Credit Shield --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 text-lg mb-2">Credit Shield Telemetry</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Kredit Tersisa</p>
                    <p class="text-xl font-extrabold text-blue-600">{{ $telemetry['credits_remaining'] }} /
                        {{ $telemetry['quota_limit'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Cache Hit Rate</p>
                    <p class="text-xl font-extrabold text-emerald-600">{{ $telemetry['hit_rate_percentage'] }}%</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Permintaan</p>
                    <p class="text-xl font-semibold">{{ $telemetry['total_requests'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Kredit Terpakai</p>
                    <p class="text-xl font-semibold text-rose-500">{{ $telemetry['credits_used'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- 2. User Watchlist Table --}}
            <div class="lg:col-span-2 bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-800 text-lg">Watchlist: {{ $watchlist['name'] }}</h3>

                    {{-- Form Tambah Watchlist --}}
                    <form action="{{ route('dashboard.watchlist.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="stock_ticker" placeholder="Kode Saham (e.g. BBCA)"
                            class="border px-3 py-1 text-sm rounded-lg uppercase" required>
                        <button type="submit"
                            class="bg-blue-600 text-white px-3 py-1 text-sm rounded-lg hover:bg-blue-700">+ Tambah</button>
                    </form>
                </div>

                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b text-gray-500">
                            <th class="pb-2">Ticker</th>
                            <th class="pb-2">Perusahaan</th>
                            <th class="pb-2">Harga</th>
                            <th class="pb-2">Forward P/E</th>
                            <th class="pb-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($watchlist['items'] as $item)
                            <tr>
                                <td class="py-3 font-semibold text-blue-600">{{ $item['ticker'] }}</td>
                                <td class="py-3">{{ $item['company_name'] }}</td>
                                <td class="py-3">Rp {{ number_format($item['close_price'], 0, ',', '.') }}</td>
                                <td class="py-3">{{ $item['forward_pe'] ?? '-' }}</td>
                                <td class="py-3 text-right">
                                    <form action="{{ route('dashboard.watchlist.destroy', $item['ticker']) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline text-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-400">Belum ada emiten di watchlist Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 3. Curated Market Insights (CMS) --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 text-lg mb-4">Market Insights</h3>
                <div class="space-y-4">
                    @forelse($insights as $post)
                        <div class="border-b pb-3">
                            <span
                                class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $post['category'] ?? 'Umum' }}</span>
                            <h4 class="font-semibold text-sm mt-1 hover:text-blue-600 cursor-pointer">{{ $post['title'] }}</h4>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ \Carbon\Carbon::parse($post['published_at'])->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">Belum ada artikel insight terbaru.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection