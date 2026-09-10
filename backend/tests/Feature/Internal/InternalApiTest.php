<?php

namespace Tests\Feature\Internal;

use App\Models\Vessel;
use App\Models\VesselLatestPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.internal_worker_token' => 'test-internal-token']);
        $this->token = 'test-internal-token';
    }

    public function test_vessel_whitelist_returns_verified_active_vessels(): void
    {
        Vessel::factory()->verified()->create(['mmsi' => '525111111', 'active' => true]);
        Vessel::factory()->verified()->create(['mmsi' => '525222222', 'active' => true]);
        Vessel::factory()->create(['mmsi' => '525333333', 'verification_status' => 'DRAFT', 'active' => true]);
        Vessel::factory()->verified()->create(['mmsi' => '525444444', 'active' => false]);

        $response = $this->withToken($this->token)
            ->getJson('/api/internal/v1/vessel-whitelist');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.count', 2)
            ->assertJsonStructure([
                'success',
                'data' => ['mmsi_list', 'count', 'version_hash'],
            ]);
    }

    public function test_whitelist_requires_internal_token(): void
    {
        $this->getJson('/api/internal/v1/vessel-whitelist')->assertUnauthorized();
    }

    public function test_whitelist_rejects_invalid_token(): void
    {
        $this->withToken('wrong-token')
            ->getJson('/api/internal/v1/vessel-whitelist')
            ->assertUnauthorized();
    }

    public function test_position_ingestion_accepts_valid_position(): void
    {
        $vessel = Vessel::factory()->verified()->create(['mmsi' => '525123456', 'active' => true]);

        $response = $this->withToken($this->token)
            ->postJson('/api/internal/v1/positions', [
                'mmsi' => '525123456',
                'latitude' => -5.87,
                'longitude' => 105.77,
                'sog_knots' => 12.4,
                'cog_degrees' => 95.2,
                'heading_degrees' => 92,
                'nav_status' => 'UNDER_WAY_USING_ENGINE',
                'destination_text' => 'MERAK',
                'source_timestamp' => now()->toIso8601String(),
                'received_at' => now()->toIso8601String(),
                'provider_name' => 'test-provider',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.accepted', true)
            ->assertJsonPath('data.history_saved', true);

        $this->assertDatabaseHas('vessel_latest_positions', ['vessel_id' => $vessel->id]);
    }

    public function test_position_ingestion_rejects_unknown_mmsi(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/internal/v1/positions', [
                'mmsi' => '999999999',
                'latitude' => -5.87,
                'longitude' => 105.77,
                'source_timestamp' => now()->toIso8601String(),
                'received_at' => now()->toIso8601String(),
                'provider_name' => 'test-provider',
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('error.code', 'UNKNOWN_MMSI');
    }

    public function test_position_ingestion_rejects_stale_message(): void
    {
        Vessel::factory()->verified()->create(['mmsi' => '525123456', 'active' => true]);

        $response = $this->withToken($this->token)
            ->postJson('/api/internal/v1/positions', [
                'mmsi' => '525123456',
                'latitude' => -5.87,
                'longitude' => 105.77,
                'source_timestamp' => now()->subHours(2)->toIso8601String(),
                'received_at' => now()->toIso8601String(),
                'provider_name' => 'test-provider',
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('error.code', 'STALE_MESSAGE');
    }

    public function test_position_ingestion_rejects_out_of_order_message(): void
    {
        $vessel = Vessel::factory()->verified()->create(['mmsi' => '525123456', 'active' => true]);

        VesselLatestPosition::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test-provider',
            'updated_at' => now(),
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/internal/v1/positions', [
                'mmsi' => '525123456',
                'latitude' => -5.80,
                'longitude' => 105.70,
                'source_timestamp' => now()->subMinutes(10)->toIso8601String(),
                'received_at' => now()->toIso8601String(),
                'provider_name' => 'test-provider',
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('error.code', 'STALE_MESSAGE');
    }

    public function test_position_ingestion_validates_required_fields(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/internal/v1/positions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mmsi', 'latitude', 'longitude', 'source_timestamp', 'received_at', 'provider_name']);
    }

    public function test_worker_heartbeat_accepts_valid_payload(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/internal/v1/worker-heartbeat', [
                'worker_id' => 'worker-1',
                'status' => 'HEALTHY',
                'provider_connected' => true,
                'messages_received_total' => 100,
                'positions_delivered_total' => 95,
                'delivery_failures_total' => 0,
                'queue_depth' => 5,
                'last_message_timestamp' => now()->toIso8601String(),
                'whitelist_version' => 'abc123',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.recorded', true);
    }

    public function test_heartbeat_requires_internal_token(): void
    {
        $this->postJson('/api/internal/v1/worker-heartbeat', [
            'worker_id' => 'worker-1',
            'status' => 'HEALTHY',
        ])->assertUnauthorized();
    }
}
