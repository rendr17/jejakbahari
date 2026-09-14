<?php

namespace Tests\Feature\Public;

use App\Models\Operator;
use App\Models\Vessel;
use App\Models\VesselPositionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_returns_positions_for_public_vessel(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);
        VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'sog_knots' => 12.4,
            'cog_degrees' => 95.2,
            'heading_degrees' => 92,
            'source_timestamp' => now()->subMinutes(10),
            'received_at' => now()->subMinutes(9),
            'provider_name' => 'test',
        ]);
        VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.88,
            'longitude' => 105.78,
            'sog_knots' => 11.8,
            'cog_degrees' => 96.0,
            'heading_degrees' => 93,
            'source_timestamp' => now()->subMinutes(5),
            'received_at' => now()->subMinutes(4),
            'provider_name' => 'test',
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}/positions/history");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.count', 2)
            ->assertJsonPath('data.points.0.latitude', -5.87)
            ->assertJsonPath('data.points.1.latitude', -5.88);
    }

    public function test_history_returns_404_for_private_vessel(): void
    {
        $vessel = Vessel::factory()->create([
            'public_visible' => false,
            'active' => true,
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}/positions/history");

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'VESSEL_NOT_FOUND');
    }

    public function test_history_respects_limit_parameter(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);
        for ($i = 0; $i < 10; $i++) {
            VesselPositionHistory::create([
                'vessel_id' => $vessel->id,
                'latitude' => -5.87 + ($i * 0.01),
                'longitude' => 105.77 + ($i * 0.01),
                'source_timestamp' => now()->subMinutes(10 - $i),
                'received_at' => now()->subMinutes(9 - $i),
                'provider_name' => 'test',
            ]);
        }

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}/positions/history?limit=3");

        $response->assertOk()
            ->assertJsonPath('data.count', 3)
            ->assertJsonPath('data.limit', 3);
    }

    public function test_history_caps_at_max_24_hours(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);
        // Position 30 hours ago — should be excluded by 24h cap.
        VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'source_timestamp' => now()->subHours(30),
            'received_at' => now()->subHours(30),
            'provider_name' => 'test',
        ]);
        // Position 2 hours ago — should be included.
        VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.88,
            'longitude' => 105.78,
            'source_timestamp' => now()->subHours(2),
            'received_at' => now()->subHours(2),
            'provider_name' => 'test',
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}/positions/history");

        $response->assertOk()
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.points.0.latitude', -5.88);
    }

    public function test_history_rejects_inverted_date_range(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}/positions/history?from=2026-09-14T12:00:00Z&to=2026-09-13T12:00:00Z");

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_RANGE');
    }
}
