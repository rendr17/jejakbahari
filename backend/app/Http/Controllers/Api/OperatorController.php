<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\StoreOperatorRequest;
use App\Http\Requests\Operator\UpdateOperatorRequest;
use App\Http\Resources\OperatorResource;
use App\Http\Responses\ApiResponse;
use App\Models\Operator;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperatorController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Operator::class);

        $operators = Operator::query()
            ->when($request->boolean('active'), fn (Builder $q) => $q->where('active', true))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim();
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 20), 100), page: $request->integer('page', 1));

        return $this->success(OperatorResource::collection($operators));
    }

    public function store(StoreOperatorRequest $request): JsonResponse
    {
        $this->authorize('create', Operator::class);

        $data = $request->validated();

        $operator = DB::transaction(function () use ($data, $request) {
            $operator = Operator::create($data);
            $operator->refresh();
            $this->audit->log($request->user(), 'operator.created', $operator);

            return $operator;
        });

        return $this->success(new OperatorResource($operator), 201);
    }

    public function show(Operator $operator): JsonResponse
    {
        $this->authorize('view', $operator);

        return $this->success(new OperatorResource($operator));
    }

    public function update(UpdateOperatorRequest $request, Operator $operator): JsonResponse
    {
        $this->authorize('update', $operator);

        $before = $operator->toArray();

        DB::transaction(function () use ($request, $operator, $before) {
            $operator->update($request->validated());
            $this->audit->log($request->user(), 'operator.updated', $operator, $before);
        });

        return $this->success(new OperatorResource($operator));
    }

    public function destroy(Request $request, Operator $operator): JsonResponse
    {
        $this->authorize('delete', $operator);

        DB::transaction(function () use ($request, $operator) {
            $this->audit->log($request->user(), 'operator.deleted', $operator);
            $operator->delete();
        });

        return $this->success();
    }
}
