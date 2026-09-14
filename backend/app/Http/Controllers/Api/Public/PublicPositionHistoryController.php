<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Vessel;
use App\Models\VesselPositionHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicPositionHistoryController extends Controller
{
    use ApiResponse;

    private const MAX_HOURS = 24;

    private const MAX_POINTS = 2000;

    public function history(Request $request, Vessel $vessel): JsonResponse
    {
        if (! $vessel->public_visible || ! $vessel->active) {
            return $this->error('VESSEL_NOT_FOUND', 'Kapal tidak ditemukan.', 404);
        }

        $now = Carbon::now('UTC');
        $from = $request->filled('from')
            ? Carbon::parse($request->string('from'), 'UTC')
            : $now->copy()->subHours(self::MAX_HOURS);
        $to = $request->filled('to')
            ? Carbon::parse($request->string('to'), 'UTC')
            : $now->copy();

        // Enforce MVP maximum 24 hours window.
        if ($from->gt($to)) {
            return $this->error('INVALID_RANGE', 'Parameter from harus lebih awal dari to.', 422);
        }
        if ($to->diffInHours($from) > self::MAX_HOURS) {
            $from = $to->copy()->subHours(self::MAX_HOURS);
        }

        $limit = min($request->integer('limit', 500), self::MAX_POINTS);

        $positions = VesselPositionHistory::query()
            ->where('vessel_id', $vessel->id)
            ->whereBetween('source_timestamp', [$from, $to])
            ->orderBy('source_timestamp')
            ->limit($limit)
            ->get();

        $data = $positions->map(fn ($p) => [
            'latitude' => (float) $p->latitude,
            'longitude' => (float) $p->longitude,
            'sog_knots' => $p->sog_knots !== null ? (float) $p->sog_knots : null,
            'cog_degrees' => $p->cog_degrees !== null ? (float) $p->cog_degrees : null,
            'heading_degrees' => $p->heading_degrees,
            'source_timestamp' => $p->source_timestamp,
            'received_at' => $p->received_at,
        ]);

        return $this->success([
            'vessel_id' => $vessel->id,
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'limit' => $limit,
            'count' => $data->count(),
            'points' => $data,
        ]);
    }
}
