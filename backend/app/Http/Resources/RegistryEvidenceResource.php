<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistryEvidenceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'vessel_id' => $this->vessel_id,
            'data_source_id' => $this->data_source_id,
            'evidence_type' => $this->evidence_type,
            'source_reference' => $this->source_reference,
            'observed_value' => $this->observed_value,
            'confidence_score' => $this->confidence_score,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at,
            'created_at' => $this->created_at,
        ];
    }
}
