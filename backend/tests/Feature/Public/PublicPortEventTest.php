<?php

namespace Tests\Feature\Public;

use App\Models\Port;
use App\Models\PortEvent;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_port_events(): void
    {
        $port = Port::factory()->create([
            'active' => true,
            'verification_status' => 'VERIFIED',
        ]);

        $vessel = Vessel::factory()->create([
            'verification_status' => 'VERIFIED',
            'active' => true,
            'public_visible' => true,
        ]);

        PortEvent::create([
            'vessel_id' => $vessel->id,
            'port_id' => $port->id,
            'event_type' => PortEvent::EVENT_ENTERED,
            'event_time' => now(),
            'detection_method' => 'RADIUS',
            'confidence_score' => 70.00,
        ]);

        PortEvent::create([
            'vessel_id' => $vessel->id,
            'port_id' => $port->id,
            'event_type' => PortEvent::EVENT_ARRIVED,
            'event_time' => now()->addMinute(),
            'detection_method' => 'RADIUS',
            'confidence_score' => 75.00,
        ]);

        $response = $this->getJson("/api/v1/ports/{$port->id}/events");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertCount(2, $response->json('data'));
        // Most recent first
        $this->assertSame('ARRIVED', $response->json('data.0.event_type'));
    }

    public function test_port_events_404_for_inactive_port(): void
    {
        $port = Port::factory()->create(['active' => false]);

        $response = $this->getJson("/api/v1/ports/{$port->id}/events");

        $response->assertNotFound();
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error.code', 'PORT_NOT_FOUND');
    }

    public function test_port_events_404_for_unknown_port(): void
    {
        $response = $this->getJson('/api/v1/ports/nonexistent-uuid/events');

        $response->assertNotFound();
    }

    public function test_port_events_pagination(): void
    {
        $port = Port::factory()->create(['active' => true]);
        $vessel = Vessel::factory()->create();

        for ($i = 0; $i < 25; $i++) {
            PortEvent::create([
                'vessel_id' => $vessel->id,
                'port_id' => $port->id,
                'event_type' => PortEvent::EVENT_ENTERED,
                'event_time' => now()->subMinutes(25 - $i),
                'detection_method' => 'RADIUS',
                'confidence_score' => 70.00,
            ]);
        }

        $response = $this->getJson("/api/v1/ports/{$port->id}/events?per_page=10");

        $response->assertOk();
        $this->assertCount(10, $response->json('data'));
        $this->assertSame(25, $response->json('meta.total'));
        $this->assertSame(3, $response->json('meta.last_page'));
    }
}
