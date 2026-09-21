<?php

namespace Modules\Dashboard\Database\Seeders;

use App\Models\User;
use App\Models\Watchlist;
use App\Models\WatchlistItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WatchlistDemoSeeder extends Seeder
{
    /**
     * Lists are keyed by owner email so the demo follows whichever analyst
     * account exists in this environment.
     */
    public function run(): void
    {
        $owners = User::whereIn('email', ['analyst_odm@gmail.com', 'qa@sectors.test'])
            ->orWhere('name', 'AnalystWannaBe')
            ->get();

        if ($owners->isEmpty()) {
            return;
        }

        $lists = [
            [
                'name' => 'Main Portfolio',
                'description' => 'Core holdings under active review',
                'items' => [
                    ['BMRI', 'Cheapest of the big four on PER with the best ROE. Watching Q4 loan growth before adding.'],
                    ['BBCA', 'Premium valuation but quality franchise. Hold, not adding at this multiple.'],
                    ['TLKM', 'Dividend play. CapEx-heavy cycle is the risk to monitor.'],
                ],
            ],
            [
                'name' => 'Banking Picks',
                'description' => 'Sector shortlist for the quarterly review',
                'items' => [
                    ['BBRI', 'Micro-lending exposure. Watching NPL trend after the rate move.'],
                    ['BBNI', 'Lowest multiple in the group — verifying the ROE gap is structural.'],
                ],
            ],
            [
                'name' => 'Watch & Wait',
                'description' => 'Ideas parked until the next earnings release',
                'items' => [
                    ['ASII', null],
                    ['ICBP', 'Waiting for input-cost relief before starting a position.'],
                ],
            ],
        ];

        foreach ($owners as $owner) {
            foreach ($lists as $definition) {
                $watchlist = Watchlist::firstOrCreate(
                    ['user_id' => $owner->id, 'name' => $definition['name']],
                    ['description' => $definition['description']]
                );

                foreach ($definition['items'] as $offset => [$ticker, $note]) {
                    WatchlistItem::updateOrCreate(
                        ['watchlist_id' => $watchlist->id, 'stock_ticker' => $ticker],
                        ['note' => $note, 'added_at' => Carbon::now()->subDays(3 * ($offset + 1))]
                    );
                }
            }
        }
    }
}
