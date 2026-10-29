<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Document;
use App\Models\Folder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['created_by' => $user->id]);
        $category = Category::factory()->create();

        return array_merge([
            'name' => 'Laporan Keuangan 2026.pdf',
            'folder_id' => $folder->id,
            'category_id' => $category->id,
            'owner_id' => $user->id,
            'status' => 'active',
        ], $overrides);
    }

    public function test_it_rejects_unauthenticated_access_for_guests(): void
    {
        $this->getJson('/api/v1/documents')->assertUnauthorized();
        $this->postJson('/api/v1/documents', [])->assertUnauthorized();
    }

    public function test_it_lists_documents(): void
    {
        $user = User::factory()->create();
        Document::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/documents');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'folder_id', 'category_id', 'owner_id', 'status', 'created_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_it_creates_a_document(): void
    {
        $user = User::factory()->create();
        $payload = $this->payload(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/v1/documents', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Laporan Keuangan 2026.pdf')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.folder.id', $payload['folder_id'])
            ->assertJsonPath('data.category.id', $payload['category_id'])
            ->assertJsonPath('data.owner.id', $user->id);

        $this->assertDatabaseHas('documents', [
            'id' => $response->json('data.id'),
            'name' => 'Laporan Keuangan 2026.pdf',
            'status' => 'active',
        ]);
    }

    public function test_it_creates_a_document_with_auto_owner_id(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['created_by' => $user->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/documents', [
            'name' => 'Dokumen Tanpa Owner Manual.pdf',
            'folder_id' => $folder->id,
            'category_id' => $category->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Dokumen Tanpa Owner Manual.pdf')
            ->assertJsonPath('data.owner.id', $user->id);

        $this->assertDatabaseHas('documents', [
            'id' => $response->json('data.id'),
            'owner_id' => $user->id,
        ]);
    }

    public function test_it_shows_a_document(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/v1/documents/{$document->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $document->id)
            ->assertJsonPath('data.name', $document->name);
    }

    public function test_it_allows_owner_to_update_document(): void
    {
        $owner = User::factory()->create();
        $document = Document::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Old Title',
        ]);

        $response = $this->actingAs($owner)->putJson("/api/v1/documents/{$document->id}", [
            'name' => 'New Title Updated',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Title Updated');

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'New Title Updated',
        ]);
    }

    public function test_it_allows_admin_to_update_document(): void
    {
        $admin = $this->admin();
        $document = Document::factory()->create(['name' => 'Old Title']);

        $response = $this->actingAs($admin)->putJson("/api/v1/documents/{$document->id}", [
            'name' => 'Admin Updated Title',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Admin Updated Title');
    }

    public function test_it_allows_pengelola_to_update_document(): void
    {
        $pengelola = $this->pengelola();
        $document = Document::factory()->create(['name' => 'Old Title']);

        $response = $this->actingAs($pengelola)->putJson("/api/v1/documents/{$document->id}", [
            'name' => 'Pengelola Updated Title',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Pengelola Updated Title');
    }

    public function test_it_forbids_pengguna_from_updating_others_document(): void
    {
        $owner = User::factory()->create();
        $pengguna = $this->pengguna();
        $document = Document::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($pengguna)
            ->putJson("/api/v1/documents/{$document->id}", ['name' => 'Malicious Edit'])
            ->assertForbidden();
    }

    public function test_it_allows_owner_to_delete_document(): void
    {
        $owner = User::factory()->create();
        $document = Document::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)
            ->deleteJson("/api/v1/documents/{$document->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_it_allows_admin_to_delete_document(): void
    {
        $admin = $this->admin();
        $document = Document::factory()->create();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/documents/{$document->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_it_allows_pengelola_to_delete_document(): void
    {
        $pengelola = $this->pengelola();
        $document = Document::factory()->create();

        $this->actingAs($pengelola)
            ->deleteJson("/api/v1/documents/{$document->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_it_forbids_pengguna_from_deleting_others_document(): void
    {
        $owner = User::factory()->create();
        $pengguna = $this->pengguna();
        $document = Document::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($pengguna)
            ->deleteJson("/api/v1/documents/{$document->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('documents', ['id' => $document->id]);
    }

    public function test_it_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/documents', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'folder_id', 'category_id']);
    }
}
