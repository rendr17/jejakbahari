<?php

namespace App\Http\Resources;

use App\Services\FreshnessService;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicVesselDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $position = $this->whenLoaded('latestPosition');

        $evidence = $this->whenLoaded('evidence', fn () => $this->evidence->map(fn ($e) => [
            'id' => $e->id,
            'evidence_type' => $e->evidence_type,
            'source_reference' => $e->source_reference,
            'observed_value' => $e->observed_value,
            'confidence_score' => $e->confidence_score !== null ? (float) $e->confidence_score : null,
            'data_source' => $e->relationLoaded('dataSource') ? [
                'id' => $e->dataSource->id,
                'name' => $e->dataSource->name,
                'source_type' => $e->dataSource->source_type,
                'url' => $e->dataSource->url,
                'license_name' => $e->dataSource->license_name,
                'attribution_text' => $e->dataSource->attribution_text,
            ] : null,
        ]), []);

        $confidenceScore = $this->confidence_score !== null ? (float) $this->confidence_score : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'mmsi' => $this->mmsi,
            'imo' => $this->imo,
            'call_sign' => $this->call_sign,
            'vessel_category' => $this->vessel_category,
            'operator' => $this->whenLoaded('operator', fn () => [
                'id' => $this->operator->id,
                'name' => $this->operator->name,
            ]),
            'freshness' => $position ? app(FreshnessService::class)->compute($position) : 'OFFLINE',
            'last_position_at' => $position?->source_timestamp,
            'latest_position' => $position ? [
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
                'sog_knots' => $position->sog_knots !== null ? (float) $position->sog_knots : null,
                'cog_degrees' => $position->cog_degrees !== null ? (float) $position->cog_degrees : null,
                'heading_degrees' => $position->heading_degrees,
                'nav_status' => $position->nav_status,
                'destination_text' => $position->destination_text,
                'source_timestamp' => $position->source_timestamp,
                'received_at' => $position->received_at,
            ] : null,
            'verification' => [
                'status' => $this->verification_status,
                'confidence_score' => $confidenceScore,
                'is_verified' => $this->isVerified(),
            ],
            'evidence' => $evidence,
            'disclaimer' => 'Data AIS bersifat indikatif, bukan untuk navigasi atau keselamatan.',
        ];
    }
}
