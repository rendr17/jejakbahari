<?php

namespace App\Http\Requests\Operator;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperatorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'website_url' => ['nullable', 'string', 'url', 'max:500'],
            'active' => ['boolean'],
        ];
    }
}
