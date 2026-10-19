<?php

namespace Tests\Feature\Api\V1;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_document_versions(): void
    {
        DocumentVersion::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/document-versions');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'document_id', 'version_number', 'file_url', 'checksum', 'size_bytes', 'uploaded_by', 'uploaded_at'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_it_creates_a_document_version_with_explicit_data(): void
    {
        $document = Document::factory()->create();
        $user = User::factory()->create();

        $payload = [
            'document_id' => $document->id,
            'version_number' => 1,
            'file_url' => 's3://bucket/docs/laporan-v1.pdf',
            'checksum' => hash('sha256', 'dummy-file-content'),
            'size_bytes' => 2048,
            'note' => 'Versi awal draft',
            'uploaded_by' => $user->id,
        ];

        $response = $this->postJson('/api/v1/document-versions', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.document_id', $document->id)
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonPath('data.file_url', 's3://bucket/docs/laporan-v1.pdf')
            ->assertJsonPath('data.size_bytes', 2048)
            ->assertJsonPath('data.note', 'Versi awal draft');

        $this->assertDatabaseHas('document_versions', [
            'id' => $response->json('data.id'),
            'document_id' => $document->id,
            'version_number' => 1,
        ]);
    }

    public function test_it_creates_a_document_version_with_file_upload(): void
    {
        Storage::fake('local');
        $document = Document::factory()->create();
        $user = User::factory()->create();

        $fakeFile = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');

        $payload = [
            'document_id' => $document->id,
            'file' => $fakeFile,
            'note' => 'Upload kontrak asli',
            'uploaded_by' => $user->id,
        ];

        $response = $this->postJson('/api/v1/document-versions', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.document_id', $document->id)
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonPath('data.note', 'Upload kontrak asli');

        $this->assertNotNull($response->json('data.checksum'));
        $this->assertGreaterThan(0, $response->json('data.size_bytes'));
    }

    public function test_it_auto_increments_version_number(): void
    {
        $document = Document::factory()->create();
        $user = User::factory()->create();

        // Create version 1
        DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'version_number' => 1,
        ]);

        // Post without version_number
        $response = $this->postJson('/api/v1/document-versions', [
            'document_id' => $document->id,
            'file_url' => 's3://bucket/docs/laporan-v2.pdf',
            'checksum' => hash('sha256', 'v2-content'),
            'size_bytes' => 1024,
            'uploaded_by' => $user->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.version_number', 2);
    }

    public function test_it_shows_a_document_version(): void
    {
        $version = DocumentVersion::factory()->create();

        $response = $this->getJson("/api/v1/document-versions/{$version->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $version->id)
            ->assertJsonPath('data.version_number', $version->version_number);
    }

    public function test_it_deletes_a_document_version(): void
    {
        $version = DocumentVersion::factory()->create();

        $this->deleteJson("/api/v1/document-versions/{$version->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('document_versions', ['id' => $version->id]);
    }
}
