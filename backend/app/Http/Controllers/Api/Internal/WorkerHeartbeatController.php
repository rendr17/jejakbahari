<?php

namespace App\Http\Controllers\Api\Internal;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WorkerHeartbeatController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'string', 'max:80'],
            'status' => ['required', 'string', 'in:HEALTHY,DEGRADED,DISCONNECTED'],
            'provider_connected' => ['boolean'],
            'messages_received_total' => ['integer', 'min:0'],
            'positions_delivered_total' => ['integer', 'min:0'],
            'delivery_failures_total' => ['integer', 'min:0'],
            'queue_depth' => ['integer', 'min:0'],
            'last_message_timestamp' => ['nullable', 'date'],
            'whitelist_version' => ['nullable', 'string', 'max:64'],
        ]);

        $cacheKey = "worker:heartbeat:{$validated['worker_id']}";
        Cache::put($cacheKey, array_merge($validated, [
            'received_at' => now()->toIso8601String(),
        ]), now()->addMinutes(2));

        return $this->success([
            'worker_id' => $validated['worker_id'],
            'recorded' => true,
        ]);
    }
}
