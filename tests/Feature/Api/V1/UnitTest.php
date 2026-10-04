<?php

namespace Tests\Feature\Api\V1;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_unit(): void
    {
        $this->postJson('/api/v1/units', ['name' => 'Keuangan', 'description' => 'Divisi keuangan'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Keuangan')
            ->assertJsonStructure(['data' => ['id', 'name', 'description']]);

        $this->assertDatabaseHas('units', ['name' => 'Keuangan']);
    }

    public function test_it_rejects_invalid_input(): void
    {
        $this->postJson('/api/v1/units', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        Unit::factory()->create(['name' => 'Keuangan']);

        $this->postJson('/api/v1/units', ['name' => 'Keuangan'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_lists_units_with_pagination(): void
    {
        Unit::factory()->count(30)->create();

        $this->getJson('/api/v1/units')
            ->assertOk()
            ->assertJsonCount(25, 'data')
            ->assertJsonPath('meta.total', 30);
    }

    public function test_it_shows_a_unit_and_404s_for_unknown(): void
    {
        $unit = Unit::factory()->create();

        $this->getJson("/api/v1/units/{$unit->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $unit->id);

        $this->getJson('/api/v1/units/00000000-0000-7000-8000-000000000000')
            ->assertNotFound();
    }

    public function test_an_authenticated_user_can_update_a_unit(): void
    {
        $unit = Unit::factory()->create(['name' => 'old']);

        $this->actingAs(User::factory()->create())
            ->patchJson("/api/v1/units/{$unit->id}", ['name' => 'new'])
            ->assertOk()
            ->assertJsonPath('data.name', 'new');
    }

    public function test_a_guest_cannot_update_or_delete_a_unit(): void
    {
        $unit = Unit::factory()->create(['name' => 'keep']);

        $this->patchJson("/api/v1/units/{$unit->id}", ['name' => 'changed'])->assertForbidden();
        $this->deleteJson("/api/v1/units/{$unit->id}")->assertForbidden();

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => 'keep']);
    }

    public function test_deleting_a_unit_clears_it_from_its_users(): void
    {
        $unit = Unit::factory()->create();
        $member = User::factory()->create(['unit_id' => $unit->id]);

        $this->actingAs(User::factory()->create())
            ->deleteJson("/api/v1/units/{$unit->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);
        $this->assertNull($member->fresh()->unit_id);
    }
}
