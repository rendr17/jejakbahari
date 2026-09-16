<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Vessel;
use App\Models\VesselLatestPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Public system status endpoint for monitoring and transparency.
 *
 * Reports data pipeline health so users and uptime monitors can tell
 * whether displayed positions are backed by a live ingestion pipeline.
 */
class PublicStatusController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        // Database connectivity
        $dbOk = true;
        try {
            DB::selectOne('SELECT 1');
        } catch (\Throwable) {
            $dbOk = false;
        }

        // Latest position freshness — how recent is the newest data point?
        $latestPosition = VesselLatestPosition::orderByDesc('received_at')->first();
        $lastReceivedAt = $latestPosition?->received_at;
        $lastAgeSeconds = $lastReceivedAt
            ? abs(now()->getTimestamp() - $lastReceivedAt->getTimestamp())
            : null;

        // Worker heartbeat — any heartbeat recorded in the cache window?
        $heartbeat = $this->findAnyHeartbeat();

        $workerStatus = $heartbeat ? ($heartbeat['status'] ?? 'HEALTHY') : 'OFFLINE';

        $pipelineStatus = match (true) {
            ! $dbOk => 'DOWN',
            $workerStatus === 'OFFLINE' || $lastAgeSeconds === null => 'DEGRADED',
            $lastAgeSeconds > 600 => 'DEGRADED',
            default => 'OPERATIONAL',
        };

        return $this->success([
            'status' => $pipelineStatus,
            'database' => $dbOk ? 'ok' : 'unreachable',
            'worker' => [
                'status' => $workerStatus,
                'worker_id' => $heartbeat['worker_id'] ?? null,
                'last_heartbeat' => $heartbeat['received_at'] ?? null,
            ],
            'data' => [
                'tracked_vessels' => Vessel::where('verification_status', 'VERIFIED')->where('active', true)->count(),
                'vessels_with_positions' => VesselLatestPosition::count(),
                'last_position_received_at' => $lastReceivedAt?->toIso8601String(),
                'last_position_age_seconds' => $lastAgeSeconds,
            ],
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Scan the worker registry for the freshest heartbeat.
     * WorkerHeartbeatController maintains `worker:heartbeat:index`
     * with all worker IDs seen in the last hour.
     */
    private function findAnyHeartbeat(): ?array
    {
        $workerIds = Cache::get('worker:heartbeat:index', ['worker-1']);
        $freshest = null;

        foreach ($workerIds as $workerId) {
            $hb = Cache::get("worker:heartbeat:{$workerId}");
            if (is_array($hb)) {
                if ($freshest === null || ($hb['received_at'] ?? '') > ($freshest['received_at'] ?? '')) {
                    $freshest = $hb;
                }
            }
        }

        return $freshest;
    }
}
