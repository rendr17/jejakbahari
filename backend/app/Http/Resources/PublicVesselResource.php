<?php

namespace App\Http\Resources;

use App\Services\FreshnessService;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicVesselResource extends JsonResource
{
    public function toArray($request): array
    {
        $position = $this->whenLoaded('latestPosition');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'mmsi' => $this->mmsi,
            'imo' => $this->imo,
            'operator' => $this->whenLoaded('operator', fn () => [
                'id' => $this->operator->id,
                'name' => $this->operator->name,
            ]),
            'freshness' => $position ? app(FreshnessService::class)->compute($position) : 'OFFLINE',
            'last_position_at' => $position?->source_timestamp,
            'verification_status' => $this->verification_status,
        ];
    }
}
