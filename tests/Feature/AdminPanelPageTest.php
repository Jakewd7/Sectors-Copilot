<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelPageTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    protected function superAdminUser(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }

    public function test_admin_users_page_renders_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Total Users');
        $response->assertSee('Edit User');
    }

    public function test_admin_market_insights_page_renders(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/admin/market-insights');

        $response->assertOk();
        $response->assertSee('Add article');
        $response->assertSee('Save draft');
        $response->assertSee('Publish now');
    }

    public function test_admin_prompt_starters_page_renders(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/admin/prompt-starters');

        $response->assertOk();
        $response->assertSee('Add prompt');
    }

    public function test_admin_caches_page_renders(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/admin/caches');

        $response->assertOk();
        $response->assertSee('Sectors API Credits');
        $response->assertSee('Cache per company / sector');
    }

    public function test_admin_pages_require_authentication(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/caches')->assertRedirect('/login');
    }

    public function test_admin_without_permission_cannot_open_admin_pages(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/caches')->assertForbidden();
    }

    public function test_roles_and_access_is_super_admin_only(): void
    {
        $this->actingAs($this->adminUser())->get('/admin/roles')->assertForbidden();

        $this->actingAs($this->superAdminUser())->get('/admin/roles')->assertOk();
    }

    public function test_roles_page_renders_role_list_and_edit_matrix(): void
    {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)->get('/admin/roles')
            ->assertOk()
            ->assertSee('Role & Access Control', escape: false)
            ->assertSee('super-admin')
            ->assertSee('permissions');

        $editUrl = '/admin/roles/'.Role::where('name', 'super-admin')->firstOrFail()->id.'/edit';

        $this->actingAs($superAdmin)->get($editUrl)
            ->assertOk()
            ->assertSee('Edit Role & Access')
            ->assertSee('Permission configuration');
    }

    public function test_article_form_uses_wysiwyg_editor_not_markdown_textarea(): void
    {
        $html = $this->actingAs($this->adminUser())->get('/admin/market-insights')->getContent();

        $this->assertStringContainsString('editorHost', $html, 'The Quill mount point is missing.');
        $this->assertStringContainsString('admin-editor', $html, 'The page must load its own editor bundle.');

        $this->assertStringNotContainsString('Content (Markdown)', $html);
        $this->assertStringNotContainsString('form.content.trim()', $html);
        $this->assertStringNotContainsString('renderMarkdown', $html, 'Markdown preview must be gone.');

        $this->assertSame(
            3,
            substr_count($html, 'syncContent'),
            'Publish and Save draft must both call syncContent().',
        );
    }

    public function test_admin_navigation_is_sidebar_only_and_permission_filtered(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $cases = [
            'admin' => [
                'visible' => ['Users', 'Market Articles', 'Prompt Starters', 'Cache & Usage'],
                'hidden' => ['Roles & Access'],
            ],
            'super-admin' => [
                'visible' => ['Users', 'Market Articles', 'Prompt Starters', 'Cache & Usage', 'Roles & Access'],
                'hidden' => [],
            ],
        ];

        foreach ($cases as $role => $expectation) {
            $user = User::factory()->create();
            $user->assignRole($role);

            $html = $this->actingAs($user)->get('/admin/users')->getContent();

            $this->assertStringNotContainsString(
                'aria-label="Section navigation"',
                $html,
                "[{$role}] admin pages must not render a top-bar navigation region.",
            );
            $this->assertStringNotContainsString('<header', $html, "[{$role}] the shell must have no header bar.");

            $links = $this->sidebarAdminLinks($html);

            foreach ($expectation['visible'] as $label) {
                $this->assertArrayHasKey($label, $links, "[{$role}] '{$label}' is missing from the sidebar.");
            }

            foreach ($expectation['hidden'] as $label) {
                $this->assertArrayNotHasKey($label, $links, "[{$role}] must not see '{$label}' in the sidebar.");
            }

            foreach ($links as $label => $href) {
                $this->assertSame(
                    200,
                    $this->actingAs($user)->get($href)->getStatusCode(),
                    "[{$role}] sidebar links '{$label}' to {$href}, which does not answer 200.",
                );
            }
        }
    }

    protected function sidebarAdminLinks(string $html): array
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $links = [];
        foreach ((new \DOMXPath($dom))->query('//nav[@aria-label="Main navigation"]//a') ?: [] as $a) {
            $label = trim($a->textContent);
            if (str_starts_with($a->getAttribute('href'), url('/admin'))) {
                $links[$label] = $a->getAttribute('href');
            }
        }

        return $links;
    }
}
