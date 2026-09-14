<?php

namespace Database\Factories;

use App\Models\Port;
use App\Models\PortEvent;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortEvent>
 */
class PortEventFactory extends Factory
{
    protected $model = PortEvent::class;

    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'port_id' => Port::factory(),
            'event_type' => $this->faker->randomElement([
                PortEvent::EVENT_ENTERED,
                PortEvent::EVENT_ARRIVED,
                PortEvent::EVENT_DEPARTED,
                PortEvent::EVENT_EXITED,
            ]),
            'event_time' => now(),
            'detection_method' => 'RADIUS',
            'confidence_score' => $this->faker->randomFloat(2, 50, 90),
            'metadata' => null,
        ];
    }
}
