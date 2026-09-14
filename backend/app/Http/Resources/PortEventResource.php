<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PortEvent
 */
class PortEventResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'vessel_id' => $this->vessel_id,
            'vessel_name' => $this->vessel?->name,
            'port_id' => $this->port_id,
            'port_name' => $this->port?->name,
            'event_type' => $this->event_type,
            'event_time' => $this->event_time->toIso8601String(),
            'detection_method' => $this->detection_method,
            'confidence_score' => (float) $this->confidence_score,
        ];
    }
}
