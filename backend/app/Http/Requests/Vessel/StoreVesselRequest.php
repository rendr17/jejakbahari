<?php

namespace App\Http\Requests\Vessel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVesselRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'operator_id' => ['nullable', 'uuid', 'exists:operators,id'],
            'mmsi' => ['required', 'string', 'regex:/^[0-9]{9}$/', 'unique:vessels,mmsi'],
            'imo' => ['nullable', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:180'],
            'call_sign' => ['nullable', 'string', 'max:32'],
            'vessel_category' => ['required', 'string', Rule::in(['RORO', 'ROPAX', 'FERRY_RORO'])],
            'confidence_score' => ['numeric', 'between:0,100'],
            'active' => ['boolean'],
            'public_visible' => ['boolean', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value && $this->string('verification_status')->toString() !== 'VERIFIED') {
                    $fail('Vessel harus berstatus VERIFIED untuk dapat dipublikasikan.');
                }
            }],
        ];
    }
}
