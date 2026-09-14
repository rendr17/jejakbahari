<?php

namespace Database\Factories;

use App\Models\Port;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Port>
 */
class PortFactory extends Factory
{
    public function definition(): array
    {
        $city = $this->faker->city();

        return [
            'code' => strtoupper(Str::slug($city, '_')),
            'name' => 'Pelabuhan '.$city,
            'city_name' => $city,
            'province_name' => $this->faker->state(),
            'geofence_radius_m' => $this->faker->optional()->numberBetween(500, 5000),
            'verification_status' => $this->faker->randomElement(['DRAFT', 'VERIFIED']),
            'active' => true,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['verification_status' => 'VERIFIED']);
    }
}
