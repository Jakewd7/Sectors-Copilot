<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Total users');
        $response->assertSee('Edit User');
    }

    public function test_admin_market_insights_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/market-insights');

        $response->assertOk();
        $response->assertSee('Add article');
        $response->assertSee('Save draft');
        $response->assertSee('Publish now');
    }

    public function test_admin_prompt_starters_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/prompt-starters');

        $response->assertOk();
        $response->assertSee('Add prompt');
    }

    public function test_admin_caches_page_renders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/caches');

        $response->assertOk();
        $response->assertSee('Remaining API credits');
        $response->assertSee('Flush');
    }

    public function test_admin_pages_require_authentication(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/caches')->assertRedirect('/login');
    }
}
