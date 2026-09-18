<?php

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_page_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/agent/workspace');

        $response->assertOk();
        $response->assertSee('copilotWorkspace');
        $response->assertSee('New Research Session');
    }

    public function test_workspace_requires_authentication(): void
    {
        $response = $this->get('/agent/workspace');

        $response->assertRedirect('/login');
    }

    public function test_session_can_be_created_and_loaded(): void
    {
        $user = User::factory()->create();

        $create = $this->actingAs($user)->postJson('/agent/sessions', [
            'title' => 'Test Research',
        ]);

        $create->assertCreated();
        $create->assertJsonPath('success', true);

        $sessionId = $create->json('session.id');

        $load = $this->actingAs($user)->getJson("/agent/sessions/{$sessionId}");

        $load->assertOk();
        $load->assertJsonPath('success', true);
        $load->assertJsonPath('session.title', 'Test Research');
    }

    public function test_session_pin_can_be_toggled(): void
    {
        $user = User::factory()->create();
        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Pin me',
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($user)->patchJson("/agent/sessions/{$session->id}/pin");

        $response->assertOk();
        $response->assertJsonPath('is_pinned', true);
    }

    public function test_session_can_be_renamed(): void
    {
        $user = User::factory()->create();
        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'is_pinned' => false,
        ]);

        $response = $this->actingAs($user)->patchJson("/agent/sessions/{$session->id}/rename", [
            'title' => 'New title',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('title', 'New title');
        $this->assertDatabaseHas('chat_sessions', ['id' => $session->id, 'title' => 'New title']);
    }

    public function test_session_rename_requires_a_title(): void
    {
        $user = User::factory()->create();
        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Keep me',
            'is_pinned' => false,
        ]);

        $this->actingAs($user)
            ->patchJson("/agent/sessions/{$session->id}/rename", ['title' => ''])
            ->assertStatus(422);

        $this->assertDatabaseHas('chat_sessions', ['id' => $session->id, 'title' => 'Keep me']);
    }

    public function test_session_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Delete me',
            'is_pinned' => false,
        ]);

        $this->actingAs($user)
            ->deleteJson("/agent/sessions/{$session->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('chat_sessions', ['id' => $session->id]);
    }

    public function test_session_actions_are_scoped_to_the_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $session = ChatSession::create([
            'user_id' => $owner->id,
            'title' => 'Private',
            'is_pinned' => false,
        ]);

        $this->actingAs($intruder)->patchJson("/agent/sessions/{$session->id}/rename", ['title' => 'Hacked'])
            ->assertNotFound();
        $this->actingAs($intruder)->deleteJson("/agent/sessions/{$session->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('chat_sessions', ['id' => $session->id, 'title' => 'Private']);
    }
}
