<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mmsi' => ['required', 'string', 'regex:/^[0-9]{9}$/'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'sog_knots' => ['nullable', 'numeric', 'min:0', 'max:102.2'],
            'cog_degrees' => ['nullable', 'numeric', 'between:0,360'],
            'heading_degrees' => ['nullable', 'integer', 'between:0,359'],
            'nav_status' => ['nullable', 'string', 'max:60'],
            'destination_text' => ['nullable', 'string', 'max:200'],
            'source_timestamp' => ['required', 'date'],
            'received_at' => ['required', 'date'],
            'provider_name' => ['required', 'string', 'max:80'],
            'raw_message_id' => ['nullable', 'string', 'max:120'],
        ];
    }
}
