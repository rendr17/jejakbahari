<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataSource\StoreDataSourceRequest;
use App\Http\Requests\DataSource\UpdateDataSourceRequest;
use App\Http\Resources\DataSourceResource;
use App\Http\Responses\ApiResponse;
use App\Models\DataSource;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataSourceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DataSource::class);

        $sources = DataSource::query()
            ->when($request->boolean('active'), fn (Builder $q) => $q->where('active', true))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim();
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 20), page: $request->integer('page', 1));

        return $this->success(DataSourceResource::collection($sources));
    }

    public function store(StoreDataSourceRequest $request): JsonResponse
    {
        $this->authorize('create', DataSource::class);

        $data = $request->validated();

        $source = DB::transaction(function () use ($data, $request) {
            $source = DataSource::create($data);
            $source->refresh();
            $this->audit->log($request->user(), 'data_source.created', $source);

            return $source;
        });

        return $this->success(new DataSourceResource($source), 201);
    }

    public function show(DataSource $dataSource): JsonResponse
    {
        $this->authorize('view', $dataSource);

        return $this->success(new DataSourceResource($dataSource));
    }

    public function update(UpdateDataSourceRequest $request, DataSource $dataSource): JsonResponse
    {
        $this->authorize('update', $dataSource);

        $before = $dataSource->toArray();

        DB::transaction(function () use ($request, $dataSource, $before) {
            $dataSource->update($request->validated());
            $this->audit->log($request->user(), 'data_source.updated', $dataSource, $before);
        });

        return $this->success(new DataSourceResource($dataSource));
    }

    public function destroy(Request $request, DataSource $dataSource): JsonResponse
    {
        $this->authorize('delete', $dataSource);

        DB::transaction(function () use ($request, $dataSource) {
            $this->audit->log($request->user(), 'data_source.deleted', $dataSource);
            $dataSource->delete();
        });

        return $this->success();
    }
}
