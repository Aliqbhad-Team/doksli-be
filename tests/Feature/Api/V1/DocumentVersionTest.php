<?php

namespace Tests\Feature\Api\V1;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVersionTest extends TestCase
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
        $this->getJson('/api/v1/document-versions')->assertUnauthorized();
        $this->postJson('/api/v1/document-versions', [])->assertUnauthorized();
    }

    public function test_it_lists_document_versions(): void
    {
        $user = User::factory()->create();
        DocumentVersion::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/document-versions');

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

        $response = $this->actingAs($user)->postJson('/api/v1/document-versions', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.document_id', $document->id)
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonPath('data.file_url', 's3://bucket/docs/laporan-v1.pdf')
            ->assertJsonPath('data.size_bytes', 2048)
            ->assertJsonPath('data.note', 'Versi awal draft')
            ->assertJsonPath('data.uploaded_by', $user->id);

        $this->assertDatabaseHas('document_versions', [
            'id' => $response->json('data.id'),
            'document_id' => $document->id,
            'version_number' => 1,
        ]);
    }

    public function test_it_creates_a_document_version_with_auto_uploader(): void
    {
        $document = Document::factory()->create();
        $user = User::factory()->create();

        $payload = [
            'document_id' => $document->id,
            'file_url' => 's3://bucket/docs/laporan-auto-uploader.pdf',
            'checksum' => hash('sha256', 'dummy-auto-content'),
            'size_bytes' => 1024,
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/document-versions', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.document_id', $document->id)
            ->assertJsonPath('data.uploaded_by', $user->id);

        $this->assertDatabaseHas('document_versions', [
            'id' => $response->json('data.id'),
            'uploaded_by' => $user->id,
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
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/document-versions', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.document_id', $document->id)
            ->assertJsonPath('data.version_number', 1)
            ->assertJsonPath('data.note', 'Upload kontrak asli')
            ->assertJsonPath('data.uploaded_by', $user->id);

        $this->assertNotNull($response->json('data.checksum'));
        $this->assertGreaterThan(0, $response->json('data.size_bytes'));
    }

    public function test_it_auto_increments_version_number(): void
    {
        $document = Document::factory()->create();
        $user = User::factory()->create();

        DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/document-versions', [
            'document_id' => $document->id,
            'file_url' => 's3://bucket/docs/laporan-v2.pdf',
            'checksum' => hash('sha256', 'v2-content'),
            'size_bytes' => 1024,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.version_number', 2);
    }

    public function test_it_shows_a_document_version(): void
    {
        $user = User::factory()->create();
        $version = DocumentVersion::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/v1/document-versions/{$version->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $version->id)
            ->assertJsonPath('data.version_number', $version->version_number);
    }

    public function test_it_allows_uploader_to_delete_version(): void
    {
        $uploader = User::factory()->create();
        $version = DocumentVersion::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($uploader)->deleteJson("/api/v1/document-versions/{$version->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('document_versions', ['id' => $version->id]);
    }

    public function test_it_allows_document_owner_to_delete_version(): void
    {
        $owner = User::factory()->create();
        $otherUploader = User::factory()->create();
        $document = Document::factory()->create(['owner_id' => $owner->id]);
        $version = DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'uploaded_by' => $otherUploader->id,
        ]);

        $this->actingAs($owner)->deleteJson("/api/v1/document-versions/{$version->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('document_versions', ['id' => $version->id]);
    }

    public function test_it_allows_admin_to_delete_version(): void
    {
        $admin = $this->admin();
        $version = DocumentVersion::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/document-versions/{$version->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('document_versions', ['id' => $version->id]);
    }

    public function test_it_allows_pengelola_to_delete_version(): void
    {
        $pengelola = $this->pengelola();
        $version = DocumentVersion::factory()->create();

        $this->actingAs($pengelola)->deleteJson("/api/v1/document-versions/{$version->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('document_versions', ['id' => $version->id]);
    }

    public function test_it_forbids_pengguna_from_deleting_others_version(): void
    {
        $uploader = User::factory()->create();
        $pengguna = $this->pengguna();
        $version = DocumentVersion::factory()->create(['uploaded_by' => $uploader->id]);

        $this->actingAs($pengguna)->deleteJson("/api/v1/document-versions/{$version->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('document_versions', ['id' => $version->id]);
    }
}
