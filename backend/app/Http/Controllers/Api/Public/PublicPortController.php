<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortResource;
use App\Http\Responses\ApiResponse;
use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPortController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $ports = Port::query()
            ->where('active', true)
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim();
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 50), 100), page: $request->integer('page', 1));

        return $this->success(PortResource::collection($ports));
    }

    public function show(Port $port): JsonResponse
    {
        if (! $port->active) {
            return $this->error('PORT_NOT_FOUND', 'Pelabuhan tidak ditemukan.', 404);
        }

        return $this->success(new PortResource($port));
    }
}
