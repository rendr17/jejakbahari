<?php

namespace App\Http\Requests\Port;

use Illuminate\Foundation\Http\FormRequest;

class StorePortRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:ports,code'],
            'name' => ['required', 'string', 'max:180'],
            'city_name' => ['nullable', 'string', 'max:120'],
            'province_name' => ['nullable', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_m' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'verification_status' => ['sometimes', 'string', 'in:DRAFT,VERIFIED'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
