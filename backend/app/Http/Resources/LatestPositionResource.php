<?php

namespace App\Http\Resources;

use App\Services\FreshnessService;
use Illuminate\Http\Resources\Json\JsonResource;

class LatestPositionResource extends JsonResource
{
    public function toArray($request): array
    {
        $freshness = app(FreshnessService::class)->compute($this->resource);

        return [
            'vessel_id' => $this->vessel_id,
            'name' => $this->whenLoaded('vessel', fn () => $this->vessel->name),
            'mmsi' => $this->whenLoaded('vessel', fn () => $this->vessel->mmsi),
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'sog_knots' => $this->sog_knots !== null ? (float) $this->sog_knots : null,
            'cog_degrees' => $this->cog_degrees !== null ? (float) $this->cog_degrees : null,
            'heading_degrees' => $this->heading_degrees,
            'nav_status' => $this->nav_status,
            'destination_text' => $this->destination_text,
            'freshness' => $freshness,
            'source_timestamp' => $this->source_timestamp,
            'received_at' => $this->received_at,
        ];
    }
}
