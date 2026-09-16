<?php

namespace Tests\Feature\Public;

use App\Models\Vessel;
use App\Models\VesselLatestPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_endpoint_reports_pipeline_state(): void
    {
        $response = $this->getJson('/api/v1/status');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'status',
                    'database',
                    'worker' => ['status', 'worker_id', 'last_heartbeat'],
                    'data' => [
                        'tracked_vessels',
                        'vessels_with_positions',
                        'last_position_received_at',
                        'last_position_age_seconds',
                    ],
                    'checked_at',
                ],
            ]);
    }

    public function test_status_is_degraded_when_no_worker_heartbeat(): void
    {
        Cache::flush();

        $response = $this->getJson('/api/v1/status');

        $response->assertOk()
            ->assertJsonPath('data.worker.status', 'OFFLINE')
            ->assertJsonPath('data.status', 'DEGRADED');
    }

    public function test_status_is_operational_with_fresh_heartbeat_and_position(): void
    {
        $vessel = Vessel::factory()->verified()->create(['active' => true]);
        VesselLatestPosition::create([
            'vessel_id' => $vessel->id,
            'latitude' => -6.1,
            'longitude' => 106.8,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
        ]);

        Cache::put('worker:heartbeat:worker-1', [
            'worker_id' => 'worker-1',
            'status' => 'HEALTHY',
            'received_at' => now()->toIso8601String(),
        ], now()->addMinutes(2));
        Cache::put('worker:heartbeat:index', ['worker-1'], now()->addHour());

        $response = $this->getJson('/api/v1/status');

        $response->assertOk()
            ->assertJsonPath('data.status', 'OPERATIONAL')
            ->assertJsonPath('data.worker.status', 'HEALTHY')
            ->assertJsonPath('data.worker.worker_id', 'worker-1')
            ->assertJsonPath('data.data.vessels_with_positions', 1);
    }

    public function test_status_reports_tracked_vessel_counts(): void
    {
        Vessel::factory()->verified()->count(3)->create(['active' => true]);
        Vessel::factory()->create(['verification_status' => 'DRAFT', 'active' => true]);

        $response = $this->getJson('/api/v1/status');

        $response->assertOk()
            ->assertJsonPath('data.data.tracked_vessels', 3);
    }
}
