<?php

namespace Tests\Feature\Api\V1;

use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Administrator']);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function pengelola(): User
    {
        $role = Role::firstOrCreate(['name' => 'Pengelola'], ['description' => 'Pengelola']);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function pengguna(): User
    {
        $role = Role::firstOrCreate(['name' => 'Pengguna'], ['description' => 'Pengguna']);

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_it_rejects_unauthenticated_access_for_guests(): void
    {
        $this->getJson('/api/v1/tags')->assertUnauthorized();
        $this->postJson('/api/v1/tags', ['name' => 'Test Tag'])->assertUnauthorized();
    }

    public function test_it_lists_tags(): void
    {
        $user = User::factory()->create();
        Tag::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/tags');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_by', 'created_dt', 'updated_by', 'updated_dt'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_it_creates_a_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tags', [
            'name' => 'Penting',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Penting')
            ->assertJsonPath('data.created_by', $user->name);

        $this->assertDatabaseHas('tags', [
            'id' => $response->json('data.id'),
            'name' => 'Penting',
        ]);
    }

    public function test_it_shows_a_tag(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/v1/tags/{$tag->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $tag->id)
            ->assertJsonPath('data.name', $tag->name);
    }

    public function test_it_allows_admin_to_update_tag(): void
    {
        $admin = $this->admin();
        $tag = Tag::factory()->create(['name' => 'Tag Lama']);

        $response = $this->actingAs($admin)->putJson("/api/v1/tags/{$tag->id}", [
            'name' => 'Tag Baru',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Tag Baru')
            ->assertJsonPath('data.updated_by', $admin->name);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Tag Baru',
        ]);
    }

    public function test_it_allows_pengelola_to_update_tag(): void
    {
        $pengelola = $this->pengelola();
        $tag = Tag::factory()->create(['name' => 'Tag Lama']);

        $response = $this->actingAs($pengelola)->putJson("/api/v1/tags/{$tag->id}", [
            'name' => 'Tag Diubah Pengelola',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Tag Diubah Pengelola');
    }

    public function test_it_forbids_pengguna_from_updating_tag(): void
    {
        $pengguna = $this->pengguna();
        $tag = Tag::factory()->create(['name' => 'Tag Asli']);

        $this->actingAs($pengguna)
            ->putJson("/api/v1/tags/{$tag->id}", ['name' => 'Tag Diubah'])
            ->assertForbidden();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Tag Asli',
        ]);
    }

    public function test_it_allows_admin_to_delete_tag(): void
    {
        $admin = $this->admin();
        $tag = Tag::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/tags/{$tag->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_it_allows_pengelola_to_delete_tag(): void
    {
        $pengelola = $this->pengelola();
        $tag = Tag::factory()->create();

        $this->actingAs($pengelola)->deleteJson("/api/v1/tags/{$tag->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_it_forbids_pengguna_from_deleting_tag(): void
    {
        $pengguna = $this->pengguna();
        $tag = Tag::factory()->create();

        $this->actingAs($pengguna)
            ->deleteJson("/api/v1/tags/{$tag->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_it_validates_required_tag_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/tags', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
