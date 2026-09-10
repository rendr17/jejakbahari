<?php

namespace App\Http\Requests\DataSource;

use Illuminate\Foundation\Http\FormRequest;

class StoreDataSourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'source_type' => ['required', 'string', 'max:40'],
            'url' => ['nullable', 'string', 'url', 'max:500'],
            'license_name' => ['nullable', 'string', 'max:120'],
            'terms_url' => ['nullable', 'string', 'url', 'max:500'],
            'attribution_text' => ['nullable', 'string'],
            'access_method' => ['required', 'string', 'max:40'],
            'active' => ['boolean'],
            'last_reviewed_at' => ['nullable', 'date'],
        ];
    }
}
