<?php

namespace App\Http\Requests\Route;

use Illuminate\Foundation\Http\FormRequest;

class StoreRouteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'origin_port_id' => ['required', 'uuid', 'exists:ports,id'],
            'destination_port_id' => ['required', 'uuid', 'exists:ports,id', 'different:origin_port_id'],
            'name' => ['required', 'string', 'max:220'],
            'route_type' => ['required', 'string', 'in:RORO,ROPAX'],
            'bidirectional' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
