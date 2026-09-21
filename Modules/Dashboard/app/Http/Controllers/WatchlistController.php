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
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'stock_ticker' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'note' => ['nullable', 'string', 'max:255'],
            'watchlist_id' => ['nullable', 'string'],
        ]);

        $userId = $request->user()->id;
        $ticker = strtoupper(trim($validated['stock_ticker']));

        $watchlist = null;

        if (! empty($validated['watchlist_id'])) {
            $watchlist = Watchlist::where('user_id', $userId)
                ->where('id', $validated['watchlist_id'])
                ->first();
        }

        $watchlist ??= Watchlist::firstOrCreate(
            ['user_id' => $userId],
            ['name' => 'Main Portfolio', 'description' => 'Personal watchlist']
        );

        $item = WatchlistItem::firstOrNew([
            'watchlist_id' => $watchlist->id,
            'stock_ticker' => $ticker,
        ]);

        if (! $item->exists) {
            $item->added_at = Carbon::now();
        }

        if (array_key_exists('note', $validated)) {
            $item->note = $validated['note'];
        }

        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Emiten {$ticker} berhasil ditambahkan ke watchlist.",
            ]);
        }

        return back()->with('success', "Emiten {$ticker} berhasil ditambahkan ke watchlist.");
    }

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
