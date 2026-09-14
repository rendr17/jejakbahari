<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'route_type' => $this->route_type,
            'bidirectional' => $this->bidirectional,
            'active' => $this->active,
            'origin_port' => $this->whenLoaded('originPort', fn () => [
                'id' => $this->originPort->id,
                'name' => $this->originPort->name,
                'code' => $this->originPort->code,
            ]),
            'destination_port' => $this->whenLoaded('destinationPort', fn () => [
                'id' => $this->destinationPort->id,
                'name' => $this->destinationPort->name,
                'code' => $this->destinationPort->code,
            ]),
        ];
    }
}
