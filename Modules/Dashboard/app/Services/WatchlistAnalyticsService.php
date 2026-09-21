<?php

namespace Modules\Dashboard\Services;

use App\Models\Watchlist;
use Modules\SectorsData\Services\CachedSectorsService;

class WatchlistAnalyticsService
{
    public function __construct(
        protected CachedSectorsService $sectorsService
    ) {}

    /**
     * Reads cache only — never triggers a live API call, so opening the page is free.
     */
    public function listPayload(?string $userId): array
    {
        if (! $userId) {
            return [];
        }

        return Watchlist::with('items')
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get()
            ->map(fn (Watchlist $list) => [
                'id' => $list->id,
                'name' => $list->name,
                'count' => $list->items->count(),
                'items' => $list->items
                    ->sortBy(fn ($item) => $item->stock_ticker)
                    ->map(fn ($item) => $this->decorate($item->stock_ticker, $item->note, $item->added_at))
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    protected function decorate(string $ticker, ?string $note, $addedAt): array
    {
        $response = $this->sectorsService->getCompanyOverview($ticker);
        $detail = $response['data'] ?? [];

        $overview = $detail['overview'] ?? [];
        $valuation = $detail['valuation'] ?? [];
        $financials = $detail['financials'] ?? [];
        $dividend = $detail['dividend'] ?? [];

        $roe = $this->returnOnEquity($financials);

        return [
            'ticker' => strtoupper($ticker),
            'note' => $note,
            'company_name' => $detail['company_name'] ?? strtoupper($ticker),
            'sector' => $overview['sector'] ?? null,
            'sub_sector' => $overview['sub_sector'] ?? null,
            'close_price' => $overview['last_close_price'] ?? null,
            'day_change' => $overview['daily_close_change'] ?? null,
            'forward_pe' => $valuation['forward_pe'] ?? null,
            'roe' => $roe,
            'dividend_yield' => $dividend['yield_ttm'] ?? null,
            'pe_trend' => $this->peTrend($valuation),
            'pe_history' => $this->peHistory($valuation),
            'cached_at' => $response['cached_at'] ?? null,
            'is_stale' => $response['is_stale'] ?? false,
            'has_data' => ! empty($detail),
            'added_at' => $addedAt,
        ];
    }

    /**
     * ROE = latest earnings / latest total equity. Falls back to the
     * pre-computed ratio series when either side is missing.
     */
    protected function returnOnEquity(array $financials): ?float
    {
        $series = $financials['historical_financials'] ?? [];

        if (is_array($series) && $series !== []) {
            $latest = null;

            foreach ($series as $row) {
                if (($row['year'] ?? null) !== null && ($latest === null || $row['year'] > ($latest['year'] ?? 0))) {
                    $latest = $row;
                }
            }

            $latest ??= end($series);

            $equity = $latest['total_equity'] ?? null;
            $earnings = $latest['earnings'] ?? null;

            if (is_numeric($equity) && (float) $equity !== 0.0 && is_numeric($earnings)) {
                return round(((float) $earnings / (float) $equity) * 100, 2);
            }
        }

        $ratios = $financials['historical_financial_ratio'] ?? [];

        if (is_array($ratios) && $ratios !== []) {
            $last = end($ratios);

            if (isset($last['roe']) && is_numeric($last['roe'])) {
                return round((float) $last['roe'], 2);
            }
        }

        return null;
    }

    /**
     * Ordered [year, pe] pairs for the row sparkline.
     */
    protected function peHistory(array $valuation): array
    {
        $history = $valuation['historical_valuation'] ?? [];

        if (! is_array($history)) {
            return [];
        }

        $points = [];

        foreach ($history as $row) {
            if (isset($row['year'], $row['pe']) && is_numeric($row['pe'])) {
                $points[] = ['year' => (int) $row['year'], 'pe' => round((float) $row['pe'], 2)];
            }
        }

        usort($points, fn ($a, $b) => $a['year'] <=> $b['year']);

        return $points;
    }

    protected function peTrend(array $valuation): ?string
    {
        $points = $this->peHistory($valuation);

        if (count($points) < 2) {
            return null;
        }

        $first = $points[0]['pe'];
        $last = end($points)['pe'];

        if ($first == 0.0) {
            return null;
        }

        $delta = (($last - $first) / $first) * 100;

        if ($delta > 5) {
            return 'up';
        }

        if ($delta < -5) {
            return 'down';
        }

        return 'flat';
    }

    public function stats(array $items): array
    {
        $priced = array_filter($items, fn ($i) => $i['has_data']);

        $changes = array_filter(array_column($priced, 'day_change'), fn ($v) => is_numeric($v));
        $pes = array_filter(array_column($priced, 'forward_pe'), fn ($v) => is_numeric($v) && $v > 0);
        $sectors = array_filter(array_column($priced, 'sector'));

        return [
            'count' => count($items),
            'priced_count' => count($priced),
            'avg_change' => $changes !== [] ? round((array_sum($changes) / count($changes)) * 100, 2) : null,
            'avg_pe' => $pes !== [] ? round(array_sum($pes) / count($pes), 1) : null,
            'sectors' => count(array_unique($sectors)),
            'last_cached_at' => collect($priced)->pluck('cached_at')->filter()->max(),
        ];
    }
}
