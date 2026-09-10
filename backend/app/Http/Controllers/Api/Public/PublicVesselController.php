<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicVesselResource;
use App\Http\Responses\ApiResponse;
use App\Models\Vessel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicVesselController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $vessels = Vessel::query()
            ->with(['operator', 'latestPosition'])
            ->where('public_visible', true)
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $search = $request->string('q')->trim()->toString();
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('mmsi', 'like', "%{$search}%")
                        ->orWhere('imo', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('operator_id'), fn (Builder $q) => $q->where('operator_id', $request->string('operator_id')))
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 20), 100), page: $request->integer('page', 1));

        return $this->success(PublicVesselResource::collection($vessels));
    }

    public function show(Vessel $vessel): JsonResponse
    {
        if (! $vessel->public_visible) {
            return $this->error('VESSEL_NOT_FOUND', 'Kapal tidak ditemukan.', 404);
        }

        $vessel->load(['operator', 'latestPosition', 'evidence' => fn ($q) => $q->limit(5)]);

        return $this->success(new PublicVesselResource($vessel));
    }
}
