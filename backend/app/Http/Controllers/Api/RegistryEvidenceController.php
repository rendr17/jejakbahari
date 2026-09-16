<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistryEvidence\StoreRegistryEvidenceRequest;
use App\Http\Requests\RegistryEvidence\UpdateRegistryEvidenceRequest;
use App\Http\Resources\RegistryEvidenceResource;
use App\Http\Responses\ApiResponse;
use App\Models\RegistryEvidence;
use App\Models\Vessel;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistryEvidenceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function index(Request $request, Vessel $vessel): JsonResponse
    {
        $this->authorize('viewAny', RegistryEvidence::class);

        $evidence = $vessel->evidence()
            ->with('dataSource')
            ->when($request->filled('evidence_type'), fn (Builder $q) => $q->where('evidence_type', $request->string('evidence_type')))
            ->orderByDesc('created_at')
            ->paginate(min($request->integer('per_page', 20), 100), page: $request->integer('page', 1));

        return $this->success(RegistryEvidenceResource::collection($evidence));
    }

    public function store(StoreRegistryEvidenceRequest $request, Vessel $vessel): JsonResponse
    {
        $this->authorize('create', RegistryEvidence::class);

        $data = $request->validated();

        $evidence = DB::transaction(function () use ($data, $vessel, $request) {
            $evidence = $vessel->evidence()->create($data);
            $evidence->refresh();
            $this->audit->log($request->user(), 'evidence.created', $evidence);

            return $evidence;
        });

        return $this->success(new RegistryEvidenceResource($evidence), 201);
    }

    public function show(Vessel $vessel, RegistryEvidence $evidence): JsonResponse
    {
        $this->authorize('view', $evidence);

        abort_unless($evidence->vessel_id === $vessel->id, 404);

        $evidence->load('dataSource');

        return $this->success(new RegistryEvidenceResource($evidence));
    }

    public function update(UpdateRegistryEvidenceRequest $request, Vessel $vessel, RegistryEvidence $evidence): JsonResponse
    {
        $this->authorize('update', $evidence);

        abort_unless($evidence->vessel_id === $vessel->id, 404);

        $before = $evidence->toArray();

        DB::transaction(function () use ($request, $evidence, $before) {
            $evidence->update($request->validated());
            $this->audit->log($request->user(), 'evidence.updated', $evidence, $before);
        });

        return $this->success(new RegistryEvidenceResource($evidence));
    }

    public function destroy(Request $request, Vessel $vessel, RegistryEvidence $evidence): JsonResponse
    {
        $this->authorize('delete', $evidence);

        abort_unless($evidence->vessel_id === $vessel->id, 404);

        DB::transaction(function () use ($request, $evidence) {
            $this->audit->log($request->user(), 'evidence.deleted', $evidence);
            $evidence->delete();
        });

        return $this->success();
    }
}
