<?php

namespace Tests\Feature;

use App\Models\MarketInsight;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketInsightPageTest extends TestCase
{
    use RefreshDatabase;

    protected function author(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('analyst');

        return $user;
    }

    protected function insight(array $attributes = []): MarketInsight
    {
        return MarketInsight::create(array_merge([
            'author_id' => $this->author()->id,
            'title' => 'Bank Q3 profits beat expectations',
            'slug' => Str::slug('Bank Q3 profits beat expectations').'-'.Str::lower(Str::random(6)),
            'category' => 'Weekly review',
            'content' => '<p>Banks closed <strong>above consensus</strong>.</p><ul><li>BBCA: NIM steady</li></ul>',
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    public function test_index_is_public(): void
    {
        $this->insight();

        $this->get('/insights')
            ->assertOk()
            ->assertSee('Market Insights')
            ->assertSee('Bank Q3 profits beat expectations');
    }

    public function test_index_does_not_render_the_global_sidebar_for_guests(): void
    {
        $this->insight();

        $html = $this->get('/insights')->getContent();

        $this->assertStringNotContainsString('Main navigation', $html);
        $this->assertStringNotContainsString('sidebar-width', $html);
    }

    public function test_index_wraps_in_the_app_shell_once_authenticated(): void
    {
        $this->insight();

        $html = $this->actingAs($this->author())->get('/insights')->getContent();

        $this->assertStringContainsString('Main navigation', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    public function test_detail_highlights_the_sidebar_entry(): void
    {
        $insight = $this->insight();

        $html = $this->actingAs($this->author())
            ->get('/insights/'.$insight->slug)
            ->getContent();

        $this->assertStringContainsString('Main navigation', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    public function test_index_shows_a_reset_action_when_a_category_is_empty(): void
    {
        $this->insight();

        $this->get('/insights?category=Nonexistent')
            ->assertOk()
            ->assertSee('No articles in')
            ->assertSee('Show all articles');
    }

    public function test_index_hides_drafts(): void
    {
        $draft = $this->insight([
            'title' => 'Unfinished draft article',
            'published_at' => null,
        ]);

        $this->get('/insights')
            ->assertOk()
            ->assertDontSee($draft->title);
    }

    public function test_index_can_filter_by_category(): void
    {
        $this->insight(['title' => 'Bank Q3 profits beat expectations', 'category' => 'Weekly review']);
        $this->insight([
            'title' => 'Energy sector slips',
            'slug' => 'energy-sector-slips-'.Str::lower(Str::random(6)),
            'category' => 'Stock watch',
        ]);

        $this->get('/insights?category=Stock+watch')
            ->assertOk()
            ->assertSee('Energy sector slips')
            ->assertDontSee('Bank Q3 profits beat expectations');
    }

    public function test_show_requires_authentication(): void
    {
        $insight = $this->insight();

        $this->get('/insights/'.$insight->slug)->assertRedirect('/login');
    }

    public function test_show_requires_the_view_permission(): void
    {
        $insight = $this->insight();

        $this->seed(RolePermissionSeeder::class);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get('/insights/'.$insight->slug)
            ->assertForbidden();
    }

    public function test_show_renders_the_article_body_and_related_sidebar(): void
    {
        $insight = $this->insight();

        $this->insight([
            'title' => 'IHSG closes stronger',
            'slug' => 'ihsg-closes-stronger-'.Str::lower(Str::random(6)),
        ]);

        $this->actingAs($this->author())
            ->get('/insights/'.$insight->slug)
            ->assertOk()
            ->assertSee('above consensus', escape: false)
            ->assertSee('Related articles')
            ->assertSee('Ask the copilot');
    }

    public function test_show_detects_tickers_from_the_body(): void
    {
        $insight = $this->insight([
            'content' => '<p>Banks closed above consensus.</p><ul><li>BBCA: NIM steady</li><li>BBRI: growth outpaced</li></ul>',
        ]);

        $html = $this->actingAs($this->author())
            ->get('/insights/'.$insight->slug)
            ->getContent();

        $this->assertStringContainsString('ticker=BBCA', $html);
        $this->assertStringContainsString('ticker=BBRI', $html);
    }

    public function test_show_omits_the_ticker_panel_when_the_body_has_none(): void
    {
        $insight = $this->insight([
            'content' => '<p>Coal prices cooled this week across the sector.</p>',
        ]);

        $this->actingAs($this->author())
            ->get('/insights/'.$insight->slug)
            ->assertOk()
            ->assertDontSee('Tickers in this article');
    }

    public function test_show_404s_for_a_draft_slug(): void
    {
        $draft = $this->insight(['published_at' => null]);

        $this->actingAs($this->author())
            ->get('/insights/'.$draft->slug)
            ->assertNotFound();
    }
}
