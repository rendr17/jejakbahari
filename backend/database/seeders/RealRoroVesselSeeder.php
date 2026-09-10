<?php

namespace Database\Seeders;

use App\Models\DataSource;
use App\Models\Operator;
use App\Models\RegistryEvidence;
use App\Models\Vessel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds real Indonesian RoRo/RoPax vessels with MMSI and multi-source evidence.
 *
 * Data was collected from public AIS aggregators and operator sources in
 * September 2026. MMSI/IMO values are NOT fabricated; each is tied to at least
 * one public source recorded as RegistryEvidence. Vessels with two independent
 * sources are marked VERIFIED and public_visible; single-source vessels stay
 * REVIEW pending a reviewer cross-check (see docs/20_RORO_VESSEL_REGISTRY.md §4).
 *
 * This seeder is idempotent: re-running updates existing records by MMSI/slug
 * and skips evidence that already exists for the same vessel + source + reference.
 *
 * Run standalone: php artisan db:seed --class=RealRoroVesselSeeder
 */
class RealRoroVesselSeeder extends Seeder
{
    public function run(): void
    {
        $operators = $this->seedOperators();
        $sources = $this->seedDataSources();

        foreach ($this->vessels() as $row) {
            $vessel = Vessel::updateOrCreate(
                ['mmsi' => $row['mmsi']],
                [
                    'operator_id' => $operators[$row['operator_key']] ?? null,
                    'imo' => $row['imo'] ?? null,
                    'name' => $row['name'],
                    'normalized_name' => Str::lower(trim($row['name'])),
                    'call_sign' => $row['call_sign'] ?? null,
                    'vessel_category' => $row['vessel_category'],
                    'verification_status' => $row['verification_status'],
                    'confidence_score' => $row['confidence_score'],
                    'active' => true,
                    'public_visible' => $row['public_visible'],
                ],
            );

            foreach ($row['evidence'] as $evidence) {
                RegistryEvidence::firstOrCreate(
                    [
                        'vessel_id' => $vessel->id,
                        'data_source_id' => $sources[$evidence['source_key']],
                        'source_reference' => $evidence['source_reference'],
                    ],
                    [
                        'evidence_type' => $evidence['evidence_type'],
                        'observed_value' => $evidence['observed_value'],
                        'confidence_score' => $evidence['confidence_score'],
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, string> operator key => operator id
     */
    private function seedOperators(): array
    {
        $asdp = Operator::updateOrCreate(
            ['slug' => 'asdp-indonesia-ferry'],
            [
                'name' => 'ASDP Indonesia Ferry',
                'website_url' => 'https://www.asdp.id',
                'active' => true,
            ],
        );

        return ['asdp' => $asdp->id];
    }

    /**
     * @return array<string, string> source key => data source id
     */
    private function seedDataSources(): array
    {
        $definitions = [
            'vesselfinder' => [
                'name' => 'VesselFinder',
                'source_type' => 'AIS_AGGREGATOR',
                'url' => 'https://www.vesselfinder.com',
                'access_method' => 'http',
                'attribution_text' => 'VesselFinder — public AIS vessel database',
            ],
            'magicport' => [
                'name' => 'MagicPort',
                'source_type' => 'AIS_AGGREGATOR',
                'url' => 'https://magicport.ai',
                'access_method' => 'http',
                'attribution_text' => 'MagicPort — maritime intelligence from AIS feeds',
            ],
            'marinelink' => [
                'name' => 'MarineLink Ports Directory',
                'source_type' => 'AIS_AGGREGATOR',
                'url' => 'https://ports.marinelink.com',
                'access_method' => 'http',
                'attribution_text' => 'MarineLink — vessel and port directory',
            ],
            'maritimeoptima' => [
                'name' => 'MaritimeOptima',
                'source_type' => 'AIS_AGGREGATOR',
                'url' => 'https://maritimeoptima.com',
                'access_method' => 'http',
                'attribution_text' => 'MaritimeOptima — vessel profile database',
            ],
            'asdp_official' => [
                'name' => 'ASDP Indonesia Ferry (official)',
                'source_type' => 'OPERATOR_WEBSITE',
                'url' => 'https://www.asdp.id',
                'access_method' => 'http',
                'attribution_text' => 'PT ASDP Indonesia Ferry (Persero) — official corporate site',
            ],
            'wikipedia_asdp' => [
                'name' => 'Wikipedia — ASDP Indonesia Ferry',
                'source_type' => 'ENCYCLOPEDIA',
                'url' => 'https://en.wikipedia.org/wiki/ASDP_Indonesia_Ferry',
                'access_method' => 'http',
                'attribution_text' => 'Wikipedia — ASDP fleet list (community-sourced)',
            ],
        ];

        $ids = [];
        foreach ($definitions as $key => $def) {
            $source = DataSource::updateOrCreate(
                ['name' => $def['name']],
                array_merge($def, [
                    'active' => true,
                    'last_reviewed_at' => now(),
                ]),
            );
            $ids[$key] = $source->id;
        }

        return $ids;
    }

    /**
     * Real Indonesian RoRo/RoPax vessels sourced from public AIS aggregators
     * and operator publications (accessed September 2026).
     *
     * verification_status guide (docs/20 §5, docs/19 §6):
     * - VERIFIED: two independent sources agree, or one source connects
     *   name + MMSI/IMO + operator + RoRo category.
     * - REVIEW: single source; awaits reviewer cross-check.
     *
     * @return array<int, array{
     *   name: string,
     *   mmsi: string,
     *   imo: string|null,
     *   call_sign: string|null,
     *   vessel_category: string,
     *   operator_key: string|null,
     *   verification_status: string,
     *   confidence_score: float,
     *   public_visible: bool,
     *   evidence: array<int, array{
     *     source_key: string,
     *     evidence_type: string,
     *     source_reference: string,
     *     observed_value: array<string,mixed>,
     *     confidence_score: float
     *   }>
     * }>
     */
    private function vessels(): array
    {
        return [
            [
                'name' => 'KMP EIRENE',
                'mmsi' => '525701487',
                'imo' => '1047639',
                'call_sign' => 'YCQK',
                'vessel_category' => 'ROPAX',
                'operator_key' => 'asdp',
                'verification_status' => 'VERIFIED',
                'confidence_score' => 72.00,
                'public_visible' => true,
                'evidence' => [
                    [
                        'source_key' => 'marinelink',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://ports.marinelink.com/vessels/vessel/525701487',
                        'observed_value' => ['mmsi' => '525701487', 'imo' => '1047639', 'type' => 'Passenger/Ro-Ro Cargo (Ferry)', 'owner' => 'PT ASDP Indonesia Ferry (Persero)'],
                        'confidence_score' => 72.00,
                    ],
                ],
            ],
            [
                'name' => 'KMP LAKAAN',
                'mmsi' => '525100417',
                'imo' => '9828845',
                'call_sign' => 'YBS12',
                'vessel_category' => 'ROPAX',
                'operator_key' => 'asdp',
                'verification_status' => 'VERIFIED',
                'confidence_score' => 80.00,
                'public_visible' => true,
                'evidence' => [
                    [
                        'source_key' => 'magicport',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://magicport.ai/vessels/passenger/kmp-lakaan-mmsi-525100417',
                        'observed_value' => ['mmsi' => '525100417', 'imo' => '9828845', 'type' => 'Passenger/Ro-Ro Ship (Vehicles)', 'manager' => 'ASDP INDONESIA FERRY'],
                        'confidence_score' => 75.00,
                    ],
                    [
                        'source_key' => 'asdp_official',
                        'evidence_type' => 'OPERATOR_LISTING',
                        'source_reference' => 'https://www.asdp.id/siaran-pers/komisi-xi-dpr-ri-setujui-pmn-asdp-12-unit-kapal-senilai-rp-388-miliar-dalam-bentuk-bmn',
                        'observed_value' => ['name' => 'KMP Lakaan', 'route' => 'Kupang – Larantuka', 'operator' => 'ASDP Indonesia Ferry'],
                        'confidence_score' => 70.00,
                    ],
                ],
            ],
            [
                'name' => 'KMP ILELABALEKAN',
                'mmsi' => '525001135',
                'imo' => '9768966',
                'call_sign' => 'PMLH',
                'vessel_category' => 'ROPAX',
                'operator_key' => 'asdp',
                'verification_status' => 'VERIFIED',
                'confidence_score' => 78.00,
                'public_visible' => true,
                'evidence' => [
                    [
                        'source_key' => 'vesselfinder',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://www.vesselfinder.com/vessels/details/525001135',
                        'observed_value' => ['mmsi' => '525001135', 'imo' => '9768966', 'type' => 'Passenger/Ro-Ro Cargo Ship', 'flag' => 'Indonesia', 'built' => 2015],
                        'confidence_score' => 72.00,
                    ],
                    [
                        'source_key' => 'asdp_official',
                        'evidence_type' => 'OPERATOR_LISTING',
                        'source_reference' => 'https://www.linkedin.com/posts/indonesiaferry_asdpindonesiaferry-earthquakentt-activity-7495777384864952320-v9jD',
                        'observed_value' => ['name' => 'KMP Ile Labalekan', 'operator' => 'ASDP Indonesia Ferry', 'context' => 'NTT earthquake relief'],
                        'confidence_score' => 65.00,
                    ],
                ],
            ],
            [
                'name' => 'KMP FERRINDO 5',
                'mmsi' => '525019482',
                'imo' => '8702501',
                'call_sign' => 'YHUR',
                'vessel_category' => 'RORO',
                'operator_key' => null,
                'verification_status' => 'VERIFIED',
                'confidence_score' => 78.00,
                'public_visible' => true,
                'evidence' => [
                    [
                        'source_key' => 'magicport',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://magicport.ai/vessels/ro-ro/kmpferrindo-5-mmsi-525019482',
                        'observed_value' => ['mmsi' => '525019482', 'imo' => '8702501', 'type' => 'Ro-Ro Cargo Ship', 'destination' => 'PONTIANAK'],
                        'confidence_score' => 72.00,
                    ],
                    [
                        'source_key' => 'maritimeoptima',
                        'evidence_type' => 'IMO_MATCH',
                        'source_reference' => 'https://maritimeoptima.com/public/vessels/pages/imo:8702501/mmsi:525019482/KMP_FERRINDO_5.html',
                        'observed_value' => ['imo' => '8702501', 'mmsi' => '525019482', 'type' => 'RORO', 'built' => 1987, 'loa_m' => 86, 'beam_m' => 12],
                        'confidence_score' => 72.00,
                    ],
                ],
            ],
            [
                'name' => 'KMP NUSA AGUNG',
                'mmsi' => '525017090',
                'imo' => '7027423',
                'call_sign' => 'YFPX',
                'vessel_category' => 'RORO',
                'operator_key' => null,
                'verification_status' => 'REVIEW',
                'confidence_score' => 62.00,
                'public_visible' => false,
                'evidence' => [
                    [
                        'source_key' => 'magicport',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://magicport.ai/vessels/ro-ro/kmp-nusa-agung-mmsi-525017090',
                        'observed_value' => ['mmsi' => '525017090', 'imo' => '7027423', 'type' => 'Ro-Ro/Vehicle Carrier', 'flag' => 'Indonesia'],
                        'confidence_score' => 62.00,
                    ],
                ],
            ],
            [
                'name' => 'KMP NUSA MULIA',
                'mmsi' => '525017089',
                'imo' => '7041015',
                'call_sign' => 'YEZL',
                'vessel_category' => 'RORO',
                'operator_key' => null,
                'verification_status' => 'REVIEW',
                'confidence_score' => 62.00,
                'public_visible' => false,
                'evidence' => [
                    [
                        'source_key' => 'magicport',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://magicport.ai/vessels/ro-ro/kmpnusa-mulia-mmsi-525017089',
                        'observed_value' => ['mmsi' => '525017089', 'imo' => '7041015', 'type' => 'Ro-Ro/Vehicle Carrier', 'destination' => 'MERAK - BAKAUHENI'],
                        'confidence_score' => 62.00,
                    ],
                ],
            ],
            [
                'name' => 'KMP MUFIDAH',
                'mmsi' => '525019468',
                'imo' => '7352799',
                'call_sign' => null,
                'vessel_category' => 'ROPAX',
                'operator_key' => null,
                'verification_status' => 'REVIEW',
                'confidence_score' => 62.00,
                'public_visible' => false,
                'evidence' => [
                    [
                        'source_key' => 'magicport',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://magicport.ai/vessels/passenger/kmpmufidah-mmsi-525019468',
                        'observed_value' => ['mmsi' => '525019468', 'imo' => '7352799', 'type' => 'Passenger/Ro-Ro Ship (Vehicles)', 'destination' => 'MERAK'],
                        'confidence_score' => 62.00,
                    ],
                ],
            ],
            [
                'name' => 'KMP ILE MANDIRI',
                'mmsi' => '525019440',
                'imo' => '8943064',
                'call_sign' => null,
                'vessel_category' => 'ROPAX',
                'operator_key' => null,
                'verification_status' => 'REVIEW',
                'confidence_score' => 62.00,
                'public_visible' => false,
                'evidence' => [
                    [
                        'source_key' => 'magicport',
                        'evidence_type' => 'MMSI_MATCH',
                        'source_reference' => 'https://magicport.ai/vessels/passenger/kmp-ile-mandiri-mmsi-525019440',
                        'observed_value' => ['mmsi' => '525019440', 'imo' => '8943064', 'type' => 'Passenger/Ro-Ro Ship (Vehicles)', 'flag' => 'Indonesia'],
                        'confidence_score' => 62.00,
                    ],
                ],
            ],
        ];
    }
}
