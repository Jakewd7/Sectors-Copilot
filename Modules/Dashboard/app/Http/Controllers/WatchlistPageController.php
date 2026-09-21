<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Dashboard\Services\WatchlistAnalyticsService;

class WatchlistPageController extends Controller
{
    public function __construct(
        protected WatchlistAnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $watchlists = $this->analyticsService->listPayload($userId);

        $activeId = $request->query('list') ?: ($watchlists[0]['id'] ?? null);
        $active = collect($watchlists)->firstWhere('id', $activeId) ?? ($watchlists[0] ?? null);

        return view('dashboard::watchlist.index', [
            'watchlists' => $watchlists,
            'activeList' => $active,
            'stats' => $active ? $this->analyticsService->stats($active['items']) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = $request->user()->id;

        $watchlist = Watchlist::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $watchlist->id]);
        }

        return redirect()
            ->route('watchlist.index', ['list' => $watchlist->id])
            ->with('success', "Watchlist \"{$watchlist->name}\" created.");
    }

    public function update(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $watchlist = $this->ownedWatchlist($request, $id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
        ]);

        $watchlist->update(['name' => $validated['name']]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Watchlist renamed.');
    }

    public function destroy(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $watchlist = $this->ownedWatchlist($request, $id);
        $watchlist->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('watchlist.index')->with('success', 'Watchlist deleted.');
    }

    protected function ownedWatchlist(Request $request, string $id): Watchlist
    {
        return Watchlist::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();
    }
}
