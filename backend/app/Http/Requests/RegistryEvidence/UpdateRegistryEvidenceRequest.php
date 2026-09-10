<?php

namespace App\Http\Requests\RegistryEvidence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegistryEvidenceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'data_source_id' => ['sometimes', 'uuid', 'exists:data_sources,id'],
            'evidence_type' => ['sometimes', 'string', 'max:40', Rule::in([
                'MMSI_MATCH',
                'IMO_MATCH',
                'OPERATOR_LISTING',
                'PORT_SCHEDULE',
                'ROUTE_SCHEDULE',
                'REGISTRY_RECORD',
                'NEWS_ARTICLE',
                'OTHER',
            ])],
            'source_reference' => ['sometimes', 'string', 'max:1000'],
            'observed_value' => ['sometimes', 'array'],
            'confidence_score' => ['sometimes', 'numeric', 'between:0,100'],
        ];
    }
}
