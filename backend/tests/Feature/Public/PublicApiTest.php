<?php

namespace Tests\Feature\Public;

use App\Models\Operator;
use App\Models\Vessel;
use App\Models\VesselLatestPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_vessel_list_returns_only_public_visible_vessels(): void
    {
        $operator = Operator::factory()->create();
        $public = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Public',
        ]);
        $private = Vessel::factory()->create([
            'operator_id' => $operator->id,
            'public_visible' => false,
            'name' => 'KMP Private',
        ]);

        $response = $this->getJson('/api/v1/vessels');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['name' => 'KMP Public'])
            ->assertJsonMissing(['name' => 'KMP Private']);
    }

    public function test_vessel_list_supports_search(): void
    {
        $operator = Operator::factory()->create();
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Merak',
            'mmsi' => '525123456',
        ]);
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Bali',
            'mmsi' => '525789012',
        ]);

        $response = $this->getJson('/api/v1/vessels?q=Merak');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'KMP Merak'])
            ->assertJsonMissing(['name' => 'KMP Bali']);
    }

    public function test_vessel_detail_returns_public_vessel(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Test',
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'KMP Test');
    }

    public function test_vessel_detail_includes_verification_and_evidence(): void
    {
        $operator = Operator::factory()->create();
        $dataSource = \App\Models\DataSource::factory()->create([
            'name' => 'MarineTraffic',
            'source_type' => 'AIS_PROVIDER',
        ]);
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Verified',
            'confidence_score' => 85.50,
        ]);
        \App\Models\RegistryEvidence::factory()->create([
            'vessel_id' => $vessel->id,
            'data_source_id' => $dataSource->id,
            'evidence_type' => 'MMSI_MATCH',
            'source_reference' => 'https://example.com/source',
            'observed_value' => ['mmsi' => $vessel->mmsi],
            'confidence_score' => 90.00,
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}");

        $response->assertOk()
            ->assertJsonPath('data.verification.status', 'VERIFIED')
            ->assertJsonPath('data.verification.is_verified', true)
            ->assertJsonPath('data.verification.confidence_score', 85.5)
            ->assertJsonPath('data.evidence.0.evidence_type', 'MMSI_MATCH')
            ->assertJsonPath('data.evidence.0.data_source.name', 'MarineTraffic')
            ->assertJsonPath('data.disclaimer', 'Data AIS bersifat indikatif, bukan untuk navigasi atau keselamatan.');
    }

    public function test_vessel_detail_includes_latest_position(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);
        VesselLatestPosition::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'sog_knots' => 12.4,
            'cog_degrees' => 95.2,
            'heading_degrees' => 92,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}");

        $response->assertOk()
            ->assertJsonPath('data.latest_position.latitude', -5.87)
            ->assertJsonPath('data.latest_position.longitude', 105.77)
            ->assertJsonPath('data.latest_position.sog_knots', 12.4)
            ->assertJsonPath('data.freshness', 'LIVE');
    }

    public function test_vessel_detail_returns_404_for_private_vessel(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->create([
            'operator_id' => $operator->id,
            'public_visible' => false,
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}");

        $response->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VESSEL_NOT_FOUND');
    }

    public function test_latest_positions_returns_positions_for_public_vessels(): void
    {
        $operator = Operator::factory()->create();
        $public = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);
        $private = Vessel::factory()->create([
            'operator_id' => $operator->id,
            'public_visible' => false,
        ]);

        VesselLatestPosition::create([
            'vessel_id' => $public->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);
        VesselLatestPosition::create([
            'vessel_id' => $private->id,
            'latitude' => -6.0,
            'longitude' => 106.0,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/positions/latest');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['vessel_id' => $public->id])
            ->assertJsonMissing(['vessel_id' => $private->id]);
    }

    public function test_latest_positions_supports_bbox_filter(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);

        VesselLatestPosition::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/positions/latest?bbox=105.0,-6.0,106.0,-5.0');

        $response->assertOk()
            ->assertJsonFragment(['vessel_id' => $vessel->id]);
    }

    public function test_latest_positions_excludes_outside_bbox(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);

        VesselLatestPosition::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/positions/latest?bbox=110.0,-8.0,115.0,-7.0');

        $response->assertOk()
            ->assertJsonMissing(['vessel_id' => $vessel->id]);
    }

    public function test_vessel_list_includes_freshness(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
        ]);

        VesselLatestPosition::create([
            'vessel_id' => $vessel->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/vessels');

        $response->assertOk()
            ->assertJsonFragment(['freshness' => 'LIVE']);
    }

    public function test_vessel_without_position_shows_offline(): void
    {
        $operator = Operator::factory()->create();
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP No Position',
        ]);

        $response = $this->getJson('/api/v1/vessels');

        $response->assertOk()
            ->assertJsonFragment(['freshness' => 'OFFLINE', 'name' => 'KMP No Position']);
    }

    public function test_vessel_list_excludes_inactive_vessels(): void
    {
        $operator = Operator::factory()->create();
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'active' => true,
            'name' => 'KMP Active',
        ]);
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'active' => false,
            'name' => 'KMP Inactive',
        ]);

        $response = $this->getJson('/api/v1/vessels');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'KMP Active'])
            ->assertJsonMissing(['name' => 'KMP Inactive']);
    }

    public function test_vessel_detail_returns_404_for_inactive_vessel(): void
    {
        $operator = Operator::factory()->create();
        $vessel = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'active' => false,
        ]);

        $response = $this->getJson("/api/v1/vessels/{$vessel->id}");

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'VESSEL_NOT_FOUND');
    }

    public function test_vessel_list_supports_status_filter(): void
    {
        $operator = Operator::factory()->create();
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Verified',
        ]);

        $response = $this->getJson('/api/v1/vessels?status=VERIFIED');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'KMP Verified']);
    }

    public function test_latest_positions_excludes_inactive_vessels(): void
    {
        $operator = Operator::factory()->create();
        $active = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'active' => true,
        ]);
        $inactive = Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'active' => false,
        ]);

        VesselLatestPosition::create([
            'vessel_id' => $active->id,
            'latitude' => -5.87,
            'longitude' => 105.77,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);
        VesselLatestPosition::create([
            'vessel_id' => $inactive->id,
            'latitude' => -6.0,
            'longitude' => 106.0,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/positions/latest');

        $response->assertOk()
            ->assertJsonFragment(['vessel_id' => $active->id])
            ->assertJsonMissing(['vessel_id' => $inactive->id]);
    }

    public function test_public_vessel_response_does_not_expose_verification_status(): void
    {
        $operator = Operator::factory()->create();
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Test',
        ]);

        $response = $this->getJson('/api/v1/vessels');

        $response->assertOk()
            ->assertJsonMissingPath('data.0.verification_status');
    }

    public function test_public_vessel_response_includes_category_and_call_sign(): void
    {
        $operator = Operator::factory()->create();
        Vessel::factory()->verified()->create([
            'operator_id' => $operator->id,
            'public_visible' => true,
            'name' => 'KMP Test',
            'vessel_category' => 'RORO',
            'call_sign' => 'YBTI',
        ]);

        $response = $this->getJson('/api/v1/vessels');

        $response->assertOk()
            ->assertJsonFragment(['vessel_category' => 'RORO', 'call_sign' => 'YBTI']);
    }
}
