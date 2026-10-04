<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_role(): void
    {
        $this->postJson('/api/v1/roles', ['name' => 'admin', 'description' => 'Administrator'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'admin')
            ->assertJsonPath('data.description', 'Administrator')
            ->assertJsonStructure(['data' => ['id', 'name', 'description']]);

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
    }

    public function test_description_is_optional(): void
    {
        $this->postJson('/api/v1/roles', ['name' => 'viewer'])
            ->assertCreated()
            ->assertJsonPath('data.description', null);
    }

    public function test_it_rejects_invalid_input(): void
    {
        $this->postJson('/api/v1/roles', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        Role::factory()->create(['name' => 'admin']);

        $this->postJson('/api/v1/roles', ['name' => 'admin'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_lists_roles_with_pagination(): void
    {
        Role::factory()->count(30)->create();

        $this->getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonCount(25, 'data')
            ->assertJsonPath('meta.total', 30);
    }

    public function test_it_shows_a_role_and_404s_for_unknown(): void
    {
        $role = Role::factory()->create();

        $this->getJson("/api/v1/roles/{$role->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $role->id);

        $this->getJson('/api/v1/roles/00000000-0000-7000-8000-000000000000')
            ->assertNotFound();
    }

    public function test_an_authenticated_user_can_update_a_role(): void
    {
        $role = Role::factory()->create(['name' => 'old']);

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/v1/roles/{$role->id}", ['name' => 'new'])
            ->assertOk()
            ->assertJsonPath('data.name', 'new');

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'new']);
    }

    public function test_update_allows_keeping_the_same_name(): void
    {
        $role = Role::factory()->create(['name' => 'same']);

        $this->actingAs(User::factory()->create())
            ->putJson("/api/v1/roles/{$role->id}", ['name' => 'same', 'description' => 'changed'])
            ->assertOk()
            ->assertJsonPath('data.description', 'changed');
    }

    public function test_a_guest_cannot_update_or_delete_a_role(): void
    {
        $role = Role::factory()->create(['name' => 'keep']);

        $this->patchJson("/api/v1/roles/{$role->id}", ['name' => 'changed'])->assertForbidden();
        $this->deleteJson("/api/v1/roles/{$role->id}")->assertForbidden();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'keep']);
    }

    public function test_an_authenticated_user_can_delete_an_unused_role(): void
    {
        $role = Role::factory()->create();

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/v1/roles/{$role->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_a_role_in_use_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/v1/roles/{$user->role_id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('roles', ['id' => $user->role_id]);
    }
}
