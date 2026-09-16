<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vessel\StoreVesselRequest;
use App\Http\Requests\Vessel\UpdateVesselRequest;
use App\Http\Resources\VesselResource;
use App\Http\Responses\ApiResponse;
use App\Models\Vessel;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VesselController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vessel::class);

        $vessels = Vessel::query()
            ->with('operator')
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim();
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('mmsi', 'like', "%{$search}%")
                        ->orWhere('imo', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('operator_id'), fn (Builder $q) => $q->where('operator_id', $request->string('operator_id')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('verification_status', $request->string('status')))
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 20), 100), page: $request->integer('page', 1));

        return $this->success(VesselResource::collection($vessels));
    }

    public function store(StoreVesselRequest $request): JsonResponse
    {
        $this->authorize('create', Vessel::class);

        $data = $request->validated();

        $vessel = DB::transaction(function () use ($data, $request) {
            $vessel = Vessel::create($data);
            $vessel->refresh();
            $this->audit->log($request->user(), 'vessel.created', $vessel);

            return $vessel;
        });

        return $this->success(new VesselResource($vessel), 201);
    }

    public function show(Vessel $vessel): JsonResponse
    {
        $this->authorize('view', $vessel);

        $vessel->load('operator');

        return $this->success(new VesselResource($vessel));
    }

    public function update(UpdateVesselRequest $request, Vessel $vessel): JsonResponse
    {
        $this->authorize('update', $vessel);

        $before = $vessel->toArray();

        DB::transaction(function () use ($request, $vessel, $before) {
            $vessel->update($request->validated());
            $this->audit->log($request->user(), 'vessel.updated', $vessel, $before);
        });

        return $this->success(new VesselResource($vessel));
    }

    public function destroy(Request $request, Vessel $vessel): JsonResponse
    {
        $this->authorize('delete', $vessel);

        DB::transaction(function () use ($request, $vessel) {
            $this->audit->log($request->user(), 'vessel.deleted', $vessel);
            $vessel->delete();
        });

        return $this->success();
    }

    public function verify(Request $request, Vessel $vessel): JsonResponse
    {
        $this->authorize('verify', $vessel);

        $before = $vessel->toArray();

        DB::transaction(function () use ($request, $vessel, $before) {
            $vessel->update([
                'verification_status' => 'VERIFIED',
                'public_visible' => true,
            ]);
            $this->audit->log($request->user(), 'vessel.verified', $vessel, $before);
        });

        return $this->success(new VesselResource($vessel));
    }

    public function reject(Request $request, Vessel $vessel): JsonResponse
    {
        $this->authorize('verify', $vessel);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $before = $vessel->toArray();

        DB::transaction(function () use ($request, $vessel, $before) {
            $vessel->update([
                'verification_status' => 'REJECTED',
                'public_visible' => false,
            ]);
            $this->audit->log($request->user(), 'vessel.rejected', $vessel, $before, ['reason' => $request->string('reason')]);
        });

        return $this->success(new VesselResource($vessel));
    }
}
