<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Models\Category;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'folder_id' => Folder::factory(),
            'category_id' => Category::factory(),
            'owner_id' => User::factory(),
            'status' => DocumentStatus::Active,
            'deleted_at' => null,
            'created_at' => now(),
        ];
    }

    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DocumentStatus::Trashed,
            'deleted_at' => now(),
        ]);
    }
}
