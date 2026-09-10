<?php

namespace Database\Factories;

use App\Models\DataSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataSource>
 */
class DataSourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company().' AIS',
            'source_type' => fake()->randomElement(['AIS_STREAM', 'AIS_API', 'MANUAL']),
            'url' => fake()->optional()->url(),
            'license_name' => fake()->optional()->word(),
            'terms_url' => fake()->optional()->url(),
            'attribution_text' => fake()->optional()->sentence(),
            'access_method' => fake()->randomElement(['websocket', 'http', 'manual']),
            'active' => true,
            'last_reviewed_at' => fake()->optional()->dateTime(),
        ];
    }
}
