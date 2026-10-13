<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Administrator']);
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function staffRole(): Role
    {
        return Role::firstOrCreate(['name' => 'staff'], ['description' => 'Staff']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        // default role non-admin biar lolos aturan singleton admin
        $roleId = $overrides['role_id'] ?? $this->staffRole()->id;
        unset($overrides['role_id']);
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'secret-password',
            'role_id' => $roleId,
        ], $overrides);
    }

    public function test_it_creates_a_user(): void
    {
        $admin = $this->admin();
        $unit = Unit::factory()->create();
        $payload = $this->payload(['unit_id' => $unit->id]);

        $response = $this->actingAs($admin)->postJson('/api/v1/users', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Budi Santoso')
            ->assertJsonPath('data.email', 'budi@example.com')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.unit.id', $unit->id)
            ->assertJsonPath('data.role.id', $payload['role_id'])
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.password_hash');

        $user = User::findOrFail($response->json('data.id'));
        $this->assertTrue(Hash::check('secret-password', $user->password_hash));
    }

    public function test_it_normalizes_email_and_accepts_explicit_status(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/api/v1/users', $this->payload(['email' => '  Budi@Example.COM ', 'status' => 'suspended']))
            ->assertCreated()
            ->assertJsonPath('data.email', 'budi@example.com')
            ->assertJsonPath('data.status', 'suspended')
            ->assertJsonPath('data.unit_id', null);
    }

    public function test_it_rejects_invalid_input(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/api/v1/users', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role_id']);

        $this->actingAs($admin)->postJson('/api/v1/users', $this->payload([
            'role_id' => '00000000-0000-7000-8000-000000000000',
            'unit_id' => '00000000-0000-7000-8000-000000000000',
            'password' => 'short',
            'status' => 'banned',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role_id', 'unit_id', 'password', 'status']);
    }

    public function test_it_rejects_a_duplicate_email(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'budi@example.com']);

        $this->actingAs($admin)->postJson('/api/v1/users', $this->payload(['email' => 'BUDI@example.com']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_lists_users_with_pagination_and_relations(): void
    {
        User::factory()->count(30)->create();

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonCount(25, 'data')
            ->assertJsonPath('meta.total', 30)
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'status', 'role' => ['id', 'name']]]])
            ->assertJsonMissingPath('data.0.password_hash');
    }

    public function test_it_shows_a_user_and_404s_for_unknown(): void
    {
        $user = User::factory()->suspended()->create();

        $this->getJson("/api/v1/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.status', UserStatus::Suspended->value)
            ->assertJsonPath('data.role.id', $user->role_id);

        $this->getJson('/api/v1/users/00000000-0000-7000-8000-000000000000')
            ->assertNotFound();
    }

    public function test_an_authenticated_user_can_partially_update_a_user(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['name' => 'Lama', 'role_id' => $this->staffRole()->id]);

        $this->actingAs($admin)
            ->patchJson("/api/v1/users/{$target->id}", ['name' => 'Baru', 'status' => 'suspended'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Baru')
            ->assertJsonPath('data.status', 'suspended')
            ->assertJsonPath('data.email', $target->email);
    }

    public function test_update_can_change_password_and_clear_unit(): void
    {
        $unit = Unit::factory()->create();
        $target = User::factory()->create(['unit_id' => $unit->id, 'role_id' => $this->staffRole()->id]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patchJson("/api/v1/users/{$target->id}", ['password' => 'new-password-1', 'unit_id' => null])
            ->assertOk()
            ->assertJsonPath('data.unit_id', null);

        $this->assertTrue(Hash::check('new-password-1', $target->fresh()->password_hash));
    }

    public function test_update_allows_own_email_but_rejects_anothers(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['email' => 'target@example.com', 'role_id' => $this->staffRole()->id]);
        $other = User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($admin)
            ->patchJson("/api/v1/users/{$target->id}", ['email' => 'Target@Example.com'])
            ->assertOk();

        $this->actingAs($admin)
            ->patchJson("/api/v1/users/{$target->id}", ['email' => $other->email])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_a_guest_cannot_update_or_delete_a_user(): void
    {
        $target = User::factory()->create(['name' => 'Asli']);

        $this->patchJson("/api/v1/users/{$target->id}", ['name' => 'Diubah'])->assertForbidden();
        $this->deleteJson("/api/v1/users/{$target->id}")->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Asli']);
    }

    public function test_an_authenticated_user_can_delete_a_user(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['role_id' => $this->staffRole()->id]);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/users/{$target->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $staff = User::factory()->create(['role_id' => $this->staffRole()->id]);

        $this->actingAs($staff)->postJson('/api/v1/users', $this->payload())
            ->assertForbidden();
    }

    public function test_admin_cannot_create_admin_user(): void
    {
        $admin = $this->admin();
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)->postJson('/api/v1/users', $this->payload(['role_id' => $adminRole->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role_id']);
    }
}
