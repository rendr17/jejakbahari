<?php

namespace Database\Factories;

use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vessel>
 */
class VesselFactory extends Factory
{
    public function definition(): array
    {
        return [
            'operator_id' => null,
            'mmsi' => (string) fake()->numberBetween(100000000, 999999999),
            'imo' => fake()->optional()->bothify('IMO#######'),
            'name' => 'KMP '.fake()->words(2, true),
            'call_sign' => fake()->optional()->bothify('??##??'),
            'vessel_category' => fake()->randomElement(['RORO', 'ROPAX', 'FERRY_RORO']),
            'verification_status' => 'DRAFT',
            'confidence_score' => fake()->randomFloat(2, 0, 100),
            'active' => true,
            'public_visible' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'verification_status' => 'VERIFIED',
            'public_visible' => true,
        ]);
    }
}
