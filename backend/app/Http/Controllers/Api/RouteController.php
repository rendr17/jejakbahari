<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Route\StoreRouteRequest;
use App\Http\Requests\Route\UpdateRouteRequest;
use App\Http\Resources\RouteResource;
use App\Http\Responses\ApiResponse;
use App\Models\Route;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Route::class);

        $routes = Route::query()
            ->with(['originPort', 'destinationPort'])
            ->when($request->boolean('active'), fn (Builder $q) => $q->where('active', true))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim();
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('origin_port_id'), fn (Builder $q) => $q->where('origin_port_id', $request->string('origin_port_id')))
            ->when($request->filled('destination_port_id'), fn (Builder $q) => $q->where('destination_port_id', $request->string('destination_port_id')))
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 20), 100), page: $request->integer('page', 1));

        return $this->success(RouteResource::collection($routes));
    }

    public function store(StoreRouteRequest $request): JsonResponse
    {
        $this->authorize('create', Route::class);

        $route = DB::transaction(function () use ($request) {
            $route = Route::create($request->validated());
            $route->refresh();
            $this->audit->log($request->user(), 'route.created', $route);

            return $route;
        });

        return $this->success(new RouteResource($route), 201);
    }

    public function show(Route $route): JsonResponse
    {
        $this->authorize('view', $route);

        $route->load(['originPort', 'destinationPort']);

        return $this->success(new RouteResource($route));
    }

    public function update(UpdateRouteRequest $request, Route $route): JsonResponse
    {
        $this->authorize('update', $route);

        $before = $route->toArray();

        DB::transaction(function () use ($request, $route, $before) {
            $route->update($request->validated());
            $this->audit->log($request->user(), 'route.updated', $route, $before);
        });

        return $this->success(new RouteResource($route));
    }

    public function destroy(Request $request, Route $route): JsonResponse
    {
        $this->authorize('delete', $route);

        DB::transaction(function () use ($request, $route) {
            $this->audit->log($request->user(), 'route.deleted', $route);
            $route->delete();
        });

        return $this->success();
    }
}
