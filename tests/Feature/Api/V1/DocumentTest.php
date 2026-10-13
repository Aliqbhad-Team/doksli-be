<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_it_lists_documents(): void
    {
        Document::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/documents');

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
        $payload = $this->payload();

        $response = $this->postJson('/api/v1/documents', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Laporan Keuangan 2026.pdf')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.folder.id', $payload['folder_id'])
            ->assertJsonPath('data.category.id', $payload['category_id'])
            ->assertJsonPath('data.owner.id', $payload['owner_id']);

        $this->assertDatabaseHas('documents', [
            'id' => $response->json('data.id'),
            'name' => 'Laporan Keuangan 2026.pdf',
            'status' => 'active',
        ]);
    }

    public function test_it_shows_a_document(): void
    {
        $document = Document::factory()->create();

        $response = $this->getJson("/api/v1/documents/{$document->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $document->id)
            ->assertJsonPath('data.name', $document->name);
    }

    public function test_it_updates_a_document(): void
    {
        $document = Document::factory()->create(['name' => 'Old Title']);

        $response = $this->putJson("/api/v1/documents/{$document->id}", [
            'name' => 'New Title Updated',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Title Updated');

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'New Title Updated',
        ]);
    }

    public function test_it_deletes_a_document(): void
    {
        $document = Document::factory()->create();

        $this->deleteJson("/api/v1/documents/{$document->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_it_validates_required_fields(): void
    {
        $this->postJson('/api/v1/documents', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'folder_id', 'category_id', 'owner_id']);
    }
}
