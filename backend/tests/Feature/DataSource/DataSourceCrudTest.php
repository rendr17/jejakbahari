<?php

namespace Tests\Feature\DataSource;

use App\Models\DataSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataSourceCrudTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken;
    }

    public function test_admin_can_list_data_sources(): void
    {
        DataSource::factory()->count(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/v1/admin/data-sources');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [['id', 'name', 'source_type', 'access_method']],
                'meta',
            ]);
    }

    public function test_admin_can_create_data_source(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/admin/data-sources', [
            'name' => 'AIS Stream Provider',
            'source_type' => 'AIS_STREAM',
            'access_method' => 'websocket',
            'url' => 'wss://example.com/ais',
            'active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'AIS Stream Provider');
    }

    public function test_create_data_source_validates_required_fields(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/admin/data-sources', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'source_type', 'access_method']);
    }

    public function test_admin_can_show_data_source(): void
    {
        $source = DataSource::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/v1/admin/data-sources/{$source->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $source->id);
    }

    public function test_admin_can_update_data_source(): void
    {
        $source = DataSource::factory()->create(['name' => 'Old Name']);

        $response = $this->withToken($this->token)->putJson("/api/v1/admin/data-sources/{$source->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_admin_can_delete_data_source(): void
    {
        $source = DataSource::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/v1/admin/data-sources/{$source->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('data_sources', ['id' => $source->id]);
    }

    public function test_unauthenticated_user_cannot_access_data_sources(): void
    {
        $this->getJson('/api/v1/admin/data-sources')->assertUnauthorized();
    }
}
