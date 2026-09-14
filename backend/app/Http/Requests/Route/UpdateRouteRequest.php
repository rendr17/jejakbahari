<?php

namespace App\Http\Requests\Route;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRouteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'origin_port_id' => ['sometimes', 'uuid', 'exists:ports,id'],
            'destination_port_id' => ['sometimes', 'uuid', 'exists:ports,id', 'different:origin_port_id'],
            'name' => ['sometimes', 'string', 'max:220'],
            'route_type' => ['sometimes', 'string', 'in:RORO,ROPAX'],
            'bidirectional' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
