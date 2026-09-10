<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VesselResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mmsi' => $this->mmsi,
            'imo' => $this->imo,
            'call_sign' => $this->call_sign,
            'vessel_category' => $this->vessel_category,
            'verification_status' => $this->verification_status,
            'confidence_score' => (float) $this->confidence_score,
            'active' => $this->active,
            'public_visible' => $this->public_visible,
            'operator' => $this->whenLoaded('operator', fn () => [
                'id' => $this->operator->id,
                'name' => $this->operator->name,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
