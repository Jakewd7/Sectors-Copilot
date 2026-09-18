<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarPrePaintTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    protected function shellPages(): array
    {
        return ['/dashboard', '/admin/users', '/admin/caches', '/workspace/profile'];
    }

    public function test_prepaint_script_is_inline_and_precedes_the_body(): void
    {
        $html = $this->actingAs($this->adminUser())->get('/admin/users')->getContent();

        $themeAt = strpos($html, "getItem('theme')");
        $sidebarAt = strpos($html, "getItem('sidebarCollapsed')");
        $bodyAt = strpos($html, '<body');

        $this->assertNotFalse($themeAt, 'Pre-paint theme logic is missing from the page.');
        $this->assertNotFalse($sidebarAt, 'Pre-paint sidebar logic is missing from the page.');
        $this->assertNotFalse($bodyAt, 'Body tag not found.');

        $this->assertLessThan(
            $bodyAt,
            $themeAt,
            'Theme must be applied before <body> is painted, or it flickers.',
        );
        $this->assertLessThan(
            $bodyAt,
            $sidebarAt,
            'The sidebar rail must be sized before <body> is painted, or it flashes open.',
        );
    }

    public function test_prepaint_script_is_the_single_definition_of_the_chrome_api(): void
    {
        $html = $this->actingAs($this->adminUser())->get('/dashboard')->getContent();

        $this->assertSame(
            1,
            substr_count($html, 'applyTheme: function'),
            'applyTheme must be defined exactly once (pre-paint script owns it).',
        );
        $this->assertSame(
            1,
            substr_count($html, 'applySidebarVars: function'),
            'applySidebarVars must be defined exactly once.',
        );
        $this->assertSame(
            1,
            substr_count($html, 'window.SectorsChrome ='),
            'The chrome API must be exposed exactly once.',
        );
    }

    public function test_collapsed_rail_is_driven_by_css_not_alpine_bindings(): void
    {
        $html = $this->actingAs($this->adminUser())->get('/admin/users')->getContent();

        $this->assertStringContainsString('sidebar-hide-collapsed', $html);
        $this->assertStringContainsString('sidebar-hide-expanded', $html);

        $this->assertStringNotContainsString(
            ":class=\"{ 'lg:hidden': sidebarCollapsed }\"",
            $html,
            'Sidebar labels must not rely on an Alpine :class binding (causes the flash).',
        );
    }

    public function test_sidebar_width_has_no_competing_inline_style_binding(): void
    {
        $html = $this->actingAs($this->adminUser())->get('/admin/users')->getContent();

        $this->assertStringNotContainsString(
            ':style="`--sidebar-w:',
            $html,
            'The sidebar width must come from the inherited --sidebar-w, not an inline binding.',
        );
    }

    public function test_shell_pages_carry_both_halves_of_the_prepaint_state(): void
    {
        $user = $this->adminUser();

        foreach ($this->shellPages() as $uri) {
            $html = $this->actingAs($user)->get($uri)->getContent();

            $this->assertStringContainsString(
                "getItem('theme')",
                $html,
                "Missing pre-paint theme script on {$uri}.",
            );
            $this->assertStringContainsString(
                "getItem('sidebarCollapsed')",
                $html,
                "Missing pre-paint sidebar script on {$uri}.",
            );
        }
    }

    public function test_auth_pages_get_the_theme_half_but_not_the_sidebar_half(): void
    {
        foreach (['/login', '/register'] as $uri) {
            $html = $this->get($uri)->getContent();

            $this->assertStringContainsString(
                "getItem('theme')",
                $html,
                "Missing pre-paint theme script on {$uri}.",
            );
            $this->assertStringNotContainsString(
                "getItem('sidebarCollapsed')",
                $html,
                "{$uri} has no sidebar, so it must not ship the sidebar branch.",
            );
        }
    }

    public function test_built_css_carries_the_collapsed_rail_rules(): void
    {
        $this->assertFileExists(public_path('build/manifest.json'));

        $assets = glob(public_path('build/assets/app-*.css'));
        $this->assertNotEmpty($assets, 'No built app CSS found — run `npm run build`.');

        $built = '';
        foreach ($assets as $asset) {
            $built .= file_get_contents($asset);
        }

        $this->assertStringContainsString('.sidebar-collapsed .sidebar-hide-collapsed', $built);
        $this->assertStringContainsString('.sidebar-collapsed .sidebar-hide-expanded', $built);
        $this->assertStringContainsString('--sidebar-w', $built);
    }
}
