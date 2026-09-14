<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PortEventResource;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicPortEventController extends Controller
{
    public function index(Request $request, string $portId): JsonResponse
    {
        $port = Port::where('active', true)->find($portId);

        if (! $port) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PORT_NOT_FOUND',
                    'message' => 'Pelabuhan tidak ditemukan.',
                    'details' => null,
                ],
            ], 404);
        }

        $perPage = min((int) $request->integer('per_page', 20), 100);
        $page = max((int) $request->integer('page', 1), 1);

        $events = $port->portEvents()
            ->with(['vessel', 'port'])
            ->orderByDesc('event_time')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => PortEventResource::collection($events->items()),
            'meta' => [
                'page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'last_page' => $events->lastPage(),
            ],
        ]);
    }
}
