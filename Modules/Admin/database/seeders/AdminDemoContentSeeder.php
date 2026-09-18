<?php

namespace Modules\Admin\Database\Seeders;

use App\Models\MarketInsight;
use App\Models\PromptStarter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminDemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'analyst_odm@gmail.com')
            ->orWhere('name', 'AnalystWannaBe')
            ->first() ?? User::whereHas('roles', fn ($q) => $q->where('name', 'analyst'))->first()
            ?? User::query()->oldest()->first();

        if (! $author) {
            $this->command?->warn('AdminDemoContentSeeder skipped: no users exist yet.');

            return;
        }

        if (MarketInsight::count() === 0) {
            $insights = [
                [
                    'title' => 'Bank Q3 profits beat expectations',
                    'category' => 'Weekly review',
                    'content' => '<p>The four largest banks on the IDX closed the quarter <strong>above consensus</strong>, driven by resilient net interest margins and lower provisioning costs.</p><h2>Highlights</h2><ul><li>BBCA: NIM steady at 5.6%, cost of credit down 12 bps</li><li>BBRI: micro-segment lending growth outpaced the sector</li><li>BMRI: fee income up 18% QoQ on higher capital-market activity</li></ul>',
                    'published_at' => now()->subDays(2),
                ],
                [
                    'title' => 'Energy sector slips as commodity prices decline',
                    'category' => 'Stock watch',
                    'content' => '<p>Coal and crude-linked names weakened this week as spot prices cooled. Watch for consolidation among mid-cap producers with elevated leverage.</p><h2>What to watch</h2><ul><li>Spot thermal coal benchmarks</li><li>Refining margins across ASEAN peers</li></ul>',
                    'published_at' => now()->subDay(),
                ],
                [
                    'title' => 'IHSG closes stronger, consumer stocks in demand',
                    'category' => 'Weekly review',
                    'content' => '<p>The composite index gained on foreign inflows, with consumer names leading the advance as analysts flag defensive rotation into staples.</p>',
                    'published_at' => null,
                ],
            ];

            foreach ($insights as $insight) {
                MarketInsight::create($insight + [
                    'author_id' => $author->id,
                    // TODO: move slug generation to the backend model/observer
                    'slug' => Str::slug($insight['title']).'-'.strtolower(Str::random(6)),
                ]);
            }
        }

        if (PromptStarter::count() === 0) {
            $prompts = [
                ['prompt_text' => 'Compare the valuation of the 3 biggest banks against the sector average', 'category' => 'valuation', 'display_order' => 1],
                ['prompt_text' => 'Find high-dividend stocks with cheap valuation', 'category' => 'screener', 'display_order' => 2],
                ['prompt_text' => 'Analyze the fundamental health of a single company in depth', 'category' => 'analysis', 'display_order' => 3],
            ];

            foreach ($prompts as $prompt) {
                PromptStarter::create($prompt + ['user_id' => $author->id, 'is_active' => true]);
            }
        }
    }
}
