<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteResource;
use App\Http\Responses\ApiResponse;
use App\Models\Route;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicRouteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $routes = Route::query()
            ->with(['originPort', 'destinationPort'])
            ->where('active', true)
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim();
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('origin_port_id'), fn (Builder $q) => $q->where('origin_port_id', $request->string('origin_port_id')))
            ->when($request->filled('destination_port_id'), fn (Builder $q) => $q->where('destination_port_id', $request->string('destination_port_id')))
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 50), 100), page: $request->integer('page', 1));

        return $this->success(RouteResource::collection($routes));
    }

    public function show(Route $route): JsonResponse
    {
        if (! $route->active) {
            return $this->error('ROUTE_NOT_FOUND', 'Lintasan tidak ditemukan.', 404);
        }

        $route->load(['originPort', 'destinationPort']);

        return $this->success(new RouteResource($route));
    }
}
