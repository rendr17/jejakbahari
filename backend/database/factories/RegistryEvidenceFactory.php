<?php

namespace Database\Factories;

use App\Models\DataSource;
use App\Models\RegistryEvidence;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistryEvidence>
 */
class RegistryEvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vessel_id' => Vessel::factory(),
            'data_source_id' => DataSource::factory(),
            'evidence_type' => $this->faker->randomElement([
                'MMSI_MATCH',
                'IMO_MATCH',
                'OPERATOR_LISTING',
                'PORT_SCHEDULE',
                'ROUTE_SCHEDULE',
                'REGISTRY_RECORD',
                'NEWS_ARTICLE',
                'OTHER',
            ]),
            'source_reference' => $this->faker->url(),
            'observed_value' => ['mmsi' => (string) $this->faker->numerify('#########')],
            'confidence_score' => $this->faker->randomFloat(2, 50, 100),
        ];
    }
}
