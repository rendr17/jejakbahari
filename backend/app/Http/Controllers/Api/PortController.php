<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Port\StorePortRequest;
use App\Http\Requests\Port\UpdatePortRequest;
use App\Http\Resources\PortResource;
use App\Http\Responses\ApiResponse;
use App\Models\Port;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Port::class);

        $ports = Port::query()
            ->when($request->boolean('active'), fn (Builder $q) => $q->where('active', true))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim();
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 20), 100), page: $request->integer('page', 1));

        return $this->success(PortResource::collection($ports));
    }

    public function store(StorePortRequest $request): JsonResponse
    {
        $this->authorize('create', Port::class);

        $data = collect($request->validated())
            ->except(['latitude', 'longitude'])
            ->toArray();

        $port = DB::transaction(function () use ($data, $request) {
            $port = new Port($data);
            $port->setRawAttribute('latitude', $request->float('latitude'));
            $port->setRawAttribute('longitude', $request->float('longitude'));
            $port->save();
            $port->refresh();
            $this->audit->log($request->user(), 'port.created', $port);

            return $port;
        });

        return $this->success(new PortResource($port), 201);
    }

    public function show(Port $port): JsonResponse
    {
        $this->authorize('view', $port);

        return $this->success(new PortResource($port));
    }

    public function update(UpdatePortRequest $request, Port $port): JsonResponse
    {
        $this->authorize('update', $port);

        $before = $port->toArray();

        DB::transaction(function () use ($request, $port, $before) {
            $data = collect($request->validated())
                ->except(['latitude', 'longitude'])
                ->toArray();
            $port->fill($data);
            if ($request->has('latitude')) {
                $port->setRawAttribute('latitude', $request->float('latitude'));
            }
            if ($request->has('longitude')) {
                $port->setRawAttribute('longitude', $request->float('longitude'));
            }
            $port->save();
            $this->audit->log($request->user(), 'port.updated', $port, $before);
        });

        return $this->success(new PortResource($port));
    }

    public function destroy(Request $request, Port $port): JsonResponse
    {
        $this->authorize('delete', $port);

        DB::transaction(function () use ($request, $port) {
            $this->audit->log($request->user(), 'port.deleted', $port);
            $port->delete();
        });

        return $this->success();
    }
}
