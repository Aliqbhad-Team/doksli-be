<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentVersion>
 */
class DocumentVersionFactory extends Factory
{
    protected $model = DocumentVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'version_number' => 1,
            'file_url' => 'documents/sample-'.fake()->uuid().'.pdf',
            'checksum' => hash('sha256', fake()->text()),
            'size_bytes' => fake()->numberBetween(1024, 10485760),
            'note' => fake()->sentence(),
            'uploaded_by' => User::factory(),
            'uploaded_at' => now(),
        ];
    }
}
