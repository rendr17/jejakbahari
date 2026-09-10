<?php

namespace App\Http\Requests\Vessel;

use App\Models\Vessel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVesselRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'operator_id' => ['nullable', 'uuid', 'exists:operators,id'],
            'imo' => ['nullable', 'string', 'max:10'],
            'name' => ['sometimes', 'string', 'max:180'],
            'call_sign' => ['nullable', 'string', 'max:32'],
            'vessel_category' => ['sometimes', 'string', Rule::in(['RORO', 'ROPAX', 'FERRY_RORO'])],
            'confidence_score' => ['sometimes', 'numeric', 'between:0,100'],
            'active' => ['boolean'],
            'public_visible' => ['boolean', function (string $attribute, mixed $value, \Closure $fail) {
                if (! $value) {
                    return;
                }

                $status = $this->string('verification_status')->toString();
                if ($status === '') {
                    $vessel = $this->route('vessel');
                    $status = $vessel instanceof Vessel ? (string) $vessel->verification_status : '';
                }

                if ($status !== 'VERIFIED') {
                    $fail('Vessel harus berstatus VERIFIED untuk dapat dipublikasikan.');
                }
            }],
        ];
    }
}
