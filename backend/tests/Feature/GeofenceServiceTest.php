<?php

namespace Tests\Feature;

use App\Models\Port;
use App\Models\PortEvent;
use App\Models\Vessel;
use App\Models\VesselPositionHistory;
use App\Services\GeofenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeofenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeofenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_entered_event_when_vessel_moves_inside_geofence(): void
    {
        // Port near Jakarta: lat -6.1, lon 106.8, radius 5000m
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);

        // Use the saving hook to set center_point
        $port->save();

        $vessel = Vessel::factory()->create([
            'verification_status' => 'VERIFIED',
            'active' => true,
        ]);

        $historyId = $this->createHistory($vessel);

        // Position very close to port (within ~5000m)
        $events = $this->service->evaluate(
            $vessel,
            latitude: -6.1001,
            longitude: 106.8001,
            sogKnots: 10.0,
            historyId: $historyId,
        );

        $this->assertCount(1, $events);
        $this->assertSame(PortEvent::EVENT_ENTERED, $events[0]->event_type);
        $this->assertSame($port->id, $events[0]->port_id);
        $this->assertSame($vessel->id, $events[0]->vessel_id);
    }

    public function test_no_event_when_vessel_outside_geofence(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 1000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();

        $historyId = $this->createHistory($vessel);

        // Position far from port (~50km away)
        $events = $this->service->evaluate(
            $vessel,
            latitude: -6.5,
            longitude: 107.0,
            sogKnots: 10.0,
            historyId: $historyId,
        );

        $this->assertEmpty($events);
    }

    public function test_arrived_event_after_entered_with_low_speed(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();

        // First: ENTERED
        $h1 = $this->createHistory($vessel);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, $h1);

        // Bypass cooldown for test by setting event_time in the past
        PortEvent::where('vessel_id', $vessel->id)
            ->where('port_id', $port->id)
            ->update(['event_time' => now()->subHours(2)]);

        // Second: ARRIVED (low speed inside)
        $h2 = $this->createHistory($vessel);
        $events = $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, $h2);

        $this->assertCount(1, $events);
        $this->assertSame(PortEvent::EVENT_ARRIVED, $events[0]->event_type);
    }

    public function test_departed_event_after_arrived_with_high_speed(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();

        // ENTERED
        $h1 = $this->createHistory($vessel);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, $h1);
        // ARRIVED — bypass cooldown by backdating the ENTERED event
        PortEvent::where('vessel_id', $vessel->id)
            ->where('port_id', $port->id)
            ->where('event_type', PortEvent::EVENT_ENTERED)
            ->update(['event_time' => now()->subHours(3)]);
        $h2 = $this->createHistory($vessel);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, $h2);

        // DEPARTED (high speed inside) — bypass cooldown by backdating the ARRIVED event
        PortEvent::where('vessel_id', $vessel->id)
            ->where('port_id', $port->id)
            ->where('event_type', PortEvent::EVENT_ARRIVED)
            ->update(['event_time' => now()->subHours(2)]);
        $h3 = $this->createHistory($vessel);
        $events = $this->service->evaluate($vessel, -6.1001, 106.8001, 5.0, $h3);

        $this->assertCount(1, $events);
        $this->assertSame(PortEvent::EVENT_DEPARTED, $events[0]->event_type);
    }

    public function test_exited_event_after_departed_and_leaving_geofence(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();

        // ENTERED -> ARRIVED -> DEPARTED
        $h1 = $this->createHistory($vessel);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, $h1);
        PortEvent::where('vessel_id', $vessel->id)
            ->where('event_type', PortEvent::EVENT_ENTERED)
            ->update(['event_time' => now()->subHours(4)]);
        $h2 = $this->createHistory($vessel);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, $h2);
        PortEvent::where('vessel_id', $vessel->id)
            ->where('event_type', PortEvent::EVENT_ARRIVED)
            ->update(['event_time' => now()->subHours(3)]);
        $h3 = $this->createHistory($vessel);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 5.0, $h3);

        // EXITED (moved outside geofence)
        PortEvent::where('vessel_id', $vessel->id)
            ->where('event_type', PortEvent::EVENT_DEPARTED)
            ->update(['event_time' => now()->subHours(2)]);
        $h4 = $this->createHistory($vessel);
        $events = $this->service->evaluate($vessel, -6.5, 107.0, 10.0, $h4);

        $this->assertCount(1, $events);
        $this->assertSame(PortEvent::EVENT_EXITED, $events[0]->event_type);
    }

    public function test_cooldown_prevents_rapid_events(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();

        // ENTERED
        $h1 = $this->createHistory($vessel);
        $events1 = $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, $h1);
        $this->assertCount(1, $events1);

        // Immediately try again — should be blocked by cooldown
        $h2 = $this->createHistory($vessel);
        $events2 = $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, $h2);
        $this->assertEmpty($events2);
    }

    public function test_no_events_for_inactive_ports(): void
    {
        $port = Port::factory()->create([
            'active' => false,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();

        $h1 = $this->createHistory($vessel);
        $events = $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, $h1);

        $this->assertEmpty($events);
    }

    public function test_radius_fallback_when_no_polygon_set(): void
    {
        // Port with radius only (no polygon) — should still detect via radius
        $port = Port::factory()->create([
            'active' => true,
            'geofence_radius_m' => 5000,
            'latitude' => -6.10,
            'longitude' => 106.80,
        ]);
        $port->save();

        $vessel = Vessel::factory()->create();

        $h1 = $this->createHistory($vessel);
        $events = $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, $h1);

        $this->assertCount(1, $events);
        $this->assertSame(PortEvent::EVENT_ENTERED, $events[0]->event_type);
        $this->assertSame('RADIUS', $events[0]->detection_method);
    }
}
