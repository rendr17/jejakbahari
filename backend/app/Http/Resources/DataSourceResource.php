<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DataSourceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'source_type' => $this->source_type,
            'url' => $this->url,
            'license_name' => $this->license_name,
            'terms_url' => $this->terms_url,
            'attribution_text' => $this->attribution_text,
            'access_method' => $this->access_method,
            'active' => $this->active,
            'last_reviewed_at' => $this->last_reviewed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
