<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Vessel;
use Illuminate\Http\JsonResponse;

class VesselWhitelistController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $vessels = Vessel::query()
            ->where('verification_status', 'VERIFIED')
            ->where('active', true)
            ->pluck('mmsi');

        $whitelist = $vessels->map(fn (string $mmsi) => ['mmsi' => $mmsi])->values();

        $versionHash = hash('sha256', $vessels->sort()->join(','));

        return $this->success([
            'mmsi_list' => $whitelist,
            'count' => $whitelist->count(),
            'version_hash' => $versionHash,
        ]);
    }
}
