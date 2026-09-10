<?php

namespace Tests\Feature\Vessel;

use App\Models\Operator;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VesselCrudTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken;
    }

    public function test_admin_can_list_vessels(): void
    {
        Vessel::factory()->count(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/v1/admin/vessels');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [['id', 'name', 'mmsi', 'verification_status']],
                'meta',
            ]);
    }

    public function test_admin_can_create_vessel(): void
    {
        $operator = Operator::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/v1/admin/vessels', [
            'operator_id' => $operator->id,
            'mmsi' => '525123456',
            'name' => 'KMP Example',
            'vessel_category' => 'RORO',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.mmsi', '525123456')
            ->assertJsonPath('data.name', 'KMP Example')
            ->assertJsonPath('data.verification_status', 'DRAFT');
    }

    public function test_create_vessel_validates_mmsi_format(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/admin/vessels', [
            'mmsi' => 'invalid',
            'name' => 'KMP Test',
            'vessel_category' => 'RORO',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mmsi']);
    }

    public function test_create_vessel_validates_unique_mmsi(): void
    {
        Vessel::factory()->create(['mmsi' => '525123456']);

        $response = $this->withToken($this->token)->postJson('/api/v1/admin/vessels', [
            'mmsi' => '525123456',
            'name' => 'KMP Duplicate',
            'vessel_category' => 'RORO',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mmsi']);
    }

    public function test_admin_can_show_vessel(): void
    {
        $vessel = Vessel::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/v1/admin/vessels/{$vessel->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $vessel->id);
    }

    public function test_admin_can_update_vessel(): void
    {
        $vessel = Vessel::factory()->create(['name' => 'Old Name']);

        $response = $this->withToken($this->token)->putJson("/api/v1/admin/vessels/{$vessel->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_admin_can_delete_vessel(): void
    {
        $vessel = Vessel::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/v1/admin/vessels/{$vessel->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('vessels', ['id' => $vessel->id]);
    }

    public function test_list_vessels_can_filter_by_status(): void
    {
        Vessel::factory()->create(['verification_status' => 'DRAFT']);
        Vessel::factory()->verified()->create();

        $response = $this->withToken($this->token)->getJson('/api/v1/admin/vessels?status=VERIFIED');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_list_vessels_can_search_by_name(): void
    {
        Vessel::factory()->create(['name' => 'KMP Merak']);
        Vessel::factory()->create(['name' => 'KMP Bali']);

        $response = $this->withToken($this->token)->getJson('/api/v1/admin/vessels?q=Merak');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_create_vessel_rejects_public_visible_when_not_verified(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/admin/vessels', [
            'mmsi' => '525999999',
            'name' => 'KMP Test',
            'vessel_category' => 'RORO',
            'public_visible' => true,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['public_visible']);
    }

    public function test_update_vessel_rejects_public_visible_when_not_verified(): void
    {
        $vessel = Vessel::factory()->create(['verification_status' => 'DRAFT']);

        $response = $this->withToken($this->token)
            ->putJson("/api/v1/admin/vessels/{$vessel->id}", [
                'public_visible' => true,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['public_visible']);
    }

    public function test_update_vessel_allows_public_visible_when_verified(): void
    {
        $vessel = Vessel::factory()->verified()->create();

        $response = $this->withToken($this->token)
            ->putJson("/api/v1/admin/vessels/{$vessel->id}", [
                'public_visible' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.public_visible', true);
    }
}
