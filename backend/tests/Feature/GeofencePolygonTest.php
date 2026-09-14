<?php

namespace Tests\Feature;

use App\Models\Port;
use App\Models\PortEvent;
use App\Models\Vessel;
use App\Models\VesselPositionHistory;
use App\Services\GeofenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeofencePolygonTest extends TestCase
{
    use RefreshDatabase;

    private GeofenceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Polygon geofence test requires PostgreSQL/PostGIS.');
        }

        $this->service = $this->app->make(GeofenceService::class);
    }

    private function createHistory(Vessel $vessel): int
    {
        $history = VesselPositionHistory::create([
            'vessel_id' => $vessel->id,
            'latitude' => -6.1001,
            'longitude' => 106.8001,
            'sog_knots' => 10.0,
            'source_timestamp' => now(),
            'received_at' => now(),
            'provider_name' => 'test',
        ]);

        return (int) $history->id;
    }

    public function test_polygon_geofence_detects_vessel_inside(): void
    {
        // Square polygon around (-6.10, 106.80) with ~500m sides
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => null, // no radius — polygon only
            'latitude' => -6.10,
            'longitude' => 106.80,
            'geofence_polygon' => [
                [106.795, -6.095],
                [106.805, -6.095],
                [106.805, -6.105],
                [106.795, -6.105],
                [106.795, -6.095], // closed ring
            ],
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();
        $historyId = $this->createHistory($vessel);

        $events = $this->service->evaluate($vessel, -6.10, 106.80, 10.0, $historyId);

        $this->assertCount(1, $events);
        $this->assertSame(PortEvent::EVENT_ENTERED, $events[0]->event_type);
        $this->assertSame('POLYGON', $events[0]->detection_method);
    }

    public function test_polygon_geofence_rejects_vessel_outside(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => null,
            'latitude' => -6.10,
            'longitude' => 106.80,
            'geofence_polygon' => [
                [106.795, -6.095],
                [106.805, -6.095],
                [106.805, -6.105],
                [106.795, -6.105],
                [106.795, -6.095],
            ],
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();
        $historyId = $this->createHistory($vessel);

        // Position far outside polygon
        $events = $this->service->evaluate($vessel, -6.5, 107.0, 10.0, $historyId);

        $this->assertEmpty($events);
    }

    public function test_polygon_takes_precedence_over_radius(): void
    {
        // Polygon is small, but radius is large (5000m)
        // Vessel is inside radius but outside polygon — should NOT detect
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
            'geofence_polygon' => [
                [106.799, -6.099],
                [106.801, -6.099],
                [106.801, -6.101],
                [106.799, -6.101],
                [106.799, -6.099], // very small polygon ~200m
            ],
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();
        $historyId = $this->createHistory($vessel);

        // Position at -6.105, 106.805 — inside 5000m radius but outside small polygon
        $events = $this->service->evaluate($vessel, -6.105, 106.805, 10.0, $historyId);

        $this->assertEmpty($events, 'Polygon should take precedence over radius.');
    }

    public function test_resource_reports_polygon_geofence_type(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'latitude' => -6.10,
            'longitude' => 106.80,
            'geofence_polygon' => [
                [106.795, -6.095],
                [106.805, -6.095],
                [106.805, -6.105],
                [106.795, -6.105],
                [106.795, -6.095],
            ],
        ]);
        $port->save();

        $response = $this->getJson("/api/v1/ports/{$port->id}");

        $response->assertOk();
        $response->assertJsonPath('data.geofence_type', 'POLYGON');
    }
}
