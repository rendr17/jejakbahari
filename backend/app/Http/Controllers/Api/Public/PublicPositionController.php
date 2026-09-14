<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\LatestPositionResource;
use App\Http\Responses\ApiResponse;
use App\Models\VesselLatestPosition;
use App\Services\FreshnessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPositionController extends Controller
{
    use ApiResponse;

    public function latest(Request $request): JsonResponse
    {
        $query = VesselLatestPosition::query()
            ->with('vessel')
            ->whereHas('vessel', fn (Builder $q) => $q
                ->where('public_visible', true)
                ->where('active', true)
            );

        if ($request->filled('bbox')) {
            $coords = explode(',', $request->string('bbox')->toString());
            if (count($coords) === 4) {
                [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', $coords);
                $query->whereBetween('longitude', [$minLng, $maxLng])
                    ->whereBetween('latitude', [$minLat, $maxLat]);
            }
        }

        if ($request->filled('operator_id')) {
            $query->whereHas('vessel', fn (Builder $q) => $q->where('operator_id', $request->string('operator_id')));
        }

        $positions = $query->limit(500)->get();

        $freshnessService = FreshnessService::fromConfig();

        if ($request->filled('freshness')) {
            $filter = $request->string('freshness')->toString();
            $positions = $positions->filter(
                fn (VesselLatestPosition $p) => $freshnessService->compute($p) === $filter,
            )->values();
        }

        return $this->success(LatestPositionResource::collection($positions));
    }
}
