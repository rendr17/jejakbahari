<?php

namespace App\Http\Requests\RegistryEvidence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistryEvidenceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'data_source_id' => ['required', 'uuid', 'exists:data_sources,id'],
            'evidence_type' => ['required', 'string', 'max:40', Rule::in([
                'MMSI_MATCH',
                'IMO_MATCH',
                'OPERATOR_LISTING',
                'PORT_SCHEDULE',
                'ROUTE_SCHEDULE',
                'REGISTRY_RECORD',
                'NEWS_ARTICLE',
                'OTHER',
            ])],
            'source_reference' => ['required', 'string', 'max:1000'],
            'observed_value' => ['required', 'array'],
            'confidence_score' => ['required', 'numeric', 'between:0,100'],
        ];
    }
}
