<?php

namespace Database\Factories;

use App\Models\Port;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
 */
class RouteFactory extends Factory
{
    public function definition(): array
    {
        $origin = Port::factory()->create();
        $destination = Port::factory()->create();

        return [
            'origin_port_id' => $origin->id,
            'destination_port_id' => $destination->id,
            'name' => $origin->name.' – '.$destination->name,
            'route_type' => $this->faker->randomElement(['RORO', 'ROPAX']),
            'bidirectional' => true,
            'active' => true,
        ];
    }
}
