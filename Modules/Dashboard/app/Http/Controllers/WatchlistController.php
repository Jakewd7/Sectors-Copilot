<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use App\Models\WatchlistItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    /**
     * Tambah emiten ke watchlist user
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'stock_ticker' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = $request->user()->id;
        $ticker = strtoupper(trim($validated['stock_ticker']));

        $watchlist = Watchlist::firstOrCreate(
            ['user_id' => $userId],
            ['name' => 'Main Portfolio', 'description' => 'Personal watchlist']
        );

        WatchlistItem::updateOrCreate(
            [
                'watchlist_id' => $watchlist->id,
                'stock_ticker' => $ticker,
            ],
            [
                'note' => $validated['note'] ?? null,
                'added_at' => Carbon::now(),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Emiten {$ticker} berhasil ditambahkan ke watchlist.",
            ]);
        }

        return back()->with('success', "Emiten {$ticker} berhasil ditambahkan ke watchlist.");
    }

    /**
     * Hapus emiten dari watchlist user
     */
    public function destroy(Request $request, string $ticker): RedirectResponse|JsonResponse
    {
        $watchlist = Watchlist::where('user_id', $request->user()->id)->first();

        if ($watchlist) {
            WatchlistItem::where('watchlist_id', $watchlist->id)
                ->where('stock_ticker', strtoupper($ticker))
                ->delete();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Emiten {$ticker} dihapus dari watchlist.",
            ]);
        }

        return back()->with('success', "Emiten {$ticker} dihapus dari watchlist.");
    }
}
