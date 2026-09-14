<?php

namespace Tests\Feature;

use App\Models\Port;
use App\Models\PortEvent;
use App\Models\Vessel;
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

        // Position very close to port (within ~5000m)
        $events = $this->service->evaluate(
            $vessel,
            latitude: -6.1001,
            longitude: 106.8001,
            sogKnots: 10.0,
            historyId: 1,
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

        // Position far from port (~50km away)
        $events = $this->service->evaluate(
            $vessel,
            latitude: -6.5,
            longitude: 107.0,
            sogKnots: 10.0,
            historyId: 1,
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
        $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, 1);

        // Bypass cooldown for test by setting event_time in the past
        PortEvent::where('vessel_id', $vessel->id)
            ->where('port_id', $port->id)
            ->update(['event_time' => now()->subHours(2)]);

        // Second: ARRIVED (low speed inside)
        $events = $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, 2);

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
        $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, 1);
        // ARRIVED
        PortEvent::where('vessel_id', $vessel->id)
            ->where('port_id', $port->id)
            ->update(['event_time' => now()->subHours(3)]);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, 2);

        // DEPARTED (high speed inside)
        PortEvent::where('vessel_id', $vessel->id)
            ->where('port_id', $port->id)
            ->update(['event_time' => now()->subHours(2)]);
        $events = $this->service->evaluate($vessel, -6.1001, 106.8001, 5.0, 3);

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
        $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, 1);
        PortEvent::where('vessel_id', $vessel->id)->update(['event_time' => now()->subHours(4)]);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, 2);
        PortEvent::where('vessel_id', $vessel->id)->update(['event_time' => now()->subHours(3)]);
        $this->service->evaluate($vessel, -6.1001, 106.8001, 5.0, 3);

        // EXITED (moved outside geofence)
        PortEvent::where('vessel_id', $vessel->id)->update(['event_time' => now()->subHours(2)]);
        $events = $this->service->evaluate($vessel, -6.5, 107.0, 10.0, 4);

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
        $events1 = $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, 1);
        $this->assertCount(1, $events1);

        // Immediately try again — should be blocked by cooldown
        $events2 = $this->service->evaluate($vessel, -6.1001, 106.8001, 1.0, 2);
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

        $events = $this->service->evaluate($vessel, -6.1001, 106.8001, 10.0, 1);

        $this->assertEmpty($events);
    }
}
