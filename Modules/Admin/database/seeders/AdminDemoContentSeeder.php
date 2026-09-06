<?php

namespace Modules\Admin\Database\Seeders;

use App\Models\MarketInsight;
use App\Models\PromptStarter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds sample Market Insights and Prompt Starters so the admin panel
 * pages render with realistic content out of the box.
 */
class AdminDemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::whereIn('email', ['rina@mail.com', 'dimas@mail.com'])
            ->orderByRaw('case when email = ? then 0 else 1 end', ['rina@mail.com'])
            ->first() ?? User::query()->oldest()->first();

        if (! $author) {
            $this->command?->warn('AdminDemoContentSeeder skipped: no users exist yet.');

            return;
        }

        if (MarketInsight::count() === 0) {
            $insights = [
                [
                    'title' => 'Bank Q3 profits beat expectations',
                    'category' => 'Weekly review',
                    'content' => "# Bank Q3 profits beat expectations\n\nThe four largest banks on the IDX closed the quarter **above consensus**, driven by resilient net interest margins and lower provisioning costs.\n\n## Highlights\n- BBCA: NIM steady at 5.6%, cost of credit down 12 bps\n- BBRI: micro-segment lending growth outpaced the sector\n- BMRI: fee income up 18% QoQ on higher capital-market activity",
                    'published_at' => now()->subDays(2),
                ],
                [
                    'title' => 'Energy sector slips as commodity prices decline',
                    'category' => 'Stock watch',
                    'content' => "# Energy sector slips as commodity prices decline\n\nCoal and crude-linked names weakened this week as spot prices cooled. Watch for consolidation among mid-cap producers with elevated leverage.\n\n## What to watch\n- Spot thermal coal benchmarks\n- Refining margins across ASEAN peers",
                    'published_at' => now()->subDay(),
                ],
                [
                    'title' => 'IHSG closes stronger, consumer stocks in demand',
                    'category' => 'Weekly review',
                    'content' => "# IHSG closes stronger, consumer stocks in demand\n\nThe composite index gained on foreign inflows, with consumer names leading the advance as analysts flag defensive rotation into staples.",
                    'published_at' => null, // draft example
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
