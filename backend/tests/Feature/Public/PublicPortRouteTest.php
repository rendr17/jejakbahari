<?php

namespace Tests\Feature\Public;

use App\Models\Port;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_port_list_returns_active_ports(): void
    {
        Port::factory()->create([
            'name' => 'Pelabuhan Merak',
            'active' => true,
        ]);
        Port::factory()->create([
            'name' => 'Pelabuhan Bakkauheni',
            'active' => true,
        ]);
        Port::factory()->create([
            'name' => 'Pelabuhan Nonaktif',
            'active' => false,
        ]);

        $response = $this->getJson('/api/v1/ports');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Pelabuhan Bakkauheni')
            ->assertJsonPath('data.1.name', 'Pelabuhan Merak')
            ->assertJsonMissing(['name' => 'Pelabuhan Nonaktif']);
    }

    public function test_port_list_supports_search(): void
    {
        Port::factory()->create(['name' => 'Pelabuhan Merak', 'active' => true]);
        Port::factory()->create(['name' => 'Pelabuhan Bakkauheni', 'active' => true]);

        $response = $this->getJson('/api/v1/ports?q=Merak');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Pelabuhan Merak')
            ->assertJsonMissing(['name' => 'Pelabuhan Bakkauheni']);
    }

    public function test_port_detail_returns_404_for_inactive_port(): void
    {
        $port = Port::factory()->create(['active' => false]);

        $response = $this->getJson("/api/v1/ports/{$port->id}");

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'PORT_NOT_FOUND');
    }

    public function test_route_list_returns_active_routes(): void
    {
        $origin = Port::factory()->create(['name' => 'Pelabuhan Merak', 'active' => true]);
        $destination = Port::factory()->create(['name' => 'Pelabuhan Bakkauheni', 'active' => true]);
        Route::factory()->create([
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'name' => 'Merak – Bakkauheni',
            'active' => true,
        ]);
        Route::factory()->create([
            'name' => 'Lintasan Nonaktif',
            'active' => false,
        ]);

        $response = $this->getJson('/api/v1/routes');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Merak – Bakkauheni')
            ->assertJsonMissing(['name' => 'Lintasan Nonaktif']);
    }

    public function test_route_detail_includes_origin_and_destination(): void
    {
        $origin = Port::factory()->create(['name' => 'Pelabuhan Merak', 'active' => true]);
        $destination = Port::factory()->create(['name' => 'Pelabuhan Bakkauheni', 'active' => true]);
        $route = Route::factory()->create([
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'name' => 'Merak – Bakkauheni',
            'active' => true,
        ]);

        $response = $this->getJson("/api/v1/routes/{$route->id}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Merak – Bakkauheni')
            ->assertJsonPath('data.origin_port.name', 'Pelabuhan Merak')
            ->assertJsonPath('data.destination_port.name', 'Pelabuhan Bakkauheni');
    }

    public function test_route_detail_returns_404_for_inactive_route(): void
    {
        $route = Route::factory()->create(['active' => false]);

        $response = $this->getJson("/api/v1/routes/{$route->id}");

        $response->assertNotFound()
            ->assertJsonPath('error.code', 'ROUTE_NOT_FOUND');
    }
}
