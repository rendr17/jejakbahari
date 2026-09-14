<?php

namespace App\Http\Controllers\Api\Internal;

use App\Events\PortEventDetected;
use App\Events\PositionUpdated;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Vessel;
use App\Models\VesselLatestPosition;
use App\Models\VesselPositionHistory;
use App\Services\FreshnessService;
use App\Services\GeofenceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionIngestionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly FreshnessService $freshnessService,
        private readonly GeofenceService $geofenceService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mmsi' => ['required', 'string', 'regex:/^[0-9]{9}$/'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'sog_knots' => ['nullable', 'numeric', 'min:0', 'max:102.2'],
            'cog_degrees' => ['nullable', 'numeric', 'between:0,360'],
            'heading_degrees' => ['nullable', 'integer', 'between:0,359'],
            'nav_status' => ['nullable', 'string', 'max:60'],
            'destination_text' => ['nullable', 'string', 'max:200'],
            'source_timestamp' => ['required', 'date'],
            'received_at' => ['required', 'date'],
            'provider_name' => ['required', 'string', 'max:80'],
            'raw_message_id' => ['nullable', 'string', 'max:120'],
        ]);

        $vessel = Vessel::where('mmsi', $validated['mmsi'])
            ->where('verification_status', 'VERIFIED')
            ->where('active', true)
            ->first();

        if (! $vessel) {
            return $this->error(
                'UNKNOWN_MMSI',
                'MMSI tidak ditemukan dalam whitelist kapal terverifikasi.',
                422,
            );
        }

        $sourceTimestamp = Carbon::parse($validated['source_timestamp']);
        $maxAgeSeconds = (int) config('app.max_message_age_seconds', 300);
        $ageSeconds = abs(Carbon::now()->getTimestamp() - $sourceTimestamp->getTimestamp());

        if ($ageSeconds > $maxAgeSeconds) {
            return $this->error(
                'STALE_MESSAGE',
                "Pesan berusia {$ageSeconds} detik, melebihi batas {$maxAgeSeconds} detik.",
                422,
            );
        }

        $latest = VesselLatestPosition::find($vessel->id);
        if ($latest && $sourceTimestamp->lt($latest->source_timestamp)) {
            return $this->error(
                'STALE_MESSAGE',
                'Pesan lebih lama dari posisi terakhir yang diterima.',
                422,
            );
        }

        $result = DB::transaction(function () use ($validated, $vessel, $sourceTimestamp) {
            $history = VesselPositionHistory::create([
                'vessel_id' => $vessel->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'sog_knots' => $validated['sog_knots'] ?? null,
                'cog_degrees' => $validated['cog_degrees'] ?? null,
                'heading_degrees' => $validated['heading_degrees'] ?? null,
                'source_timestamp' => $sourceTimestamp,
                'received_at' => Carbon::parse($validated['received_at']),
                'provider_name' => $validated['provider_name'],
            ]);

            VesselLatestPosition::updateOrCreate(
                ['vessel_id' => $vessel->id],
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'sog_knots' => $validated['sog_knots'] ?? null,
                    'cog_degrees' => $validated['cog_degrees'] ?? null,
                    'heading_degrees' => $validated['heading_degrees'] ?? null,
                    'nav_status' => $validated['nav_status'] ?? null,
                    'destination_text' => $validated['destination_text'] ?? null,
                    'source_timestamp' => $sourceTimestamp,
                    'received_at' => Carbon::parse($validated['received_at']),
                    'provider_name' => $validated['provider_name'],
                    'raw_message_id' => $validated['raw_message_id'] ?? null,
                    'updated_at' => now(),
                ],
            );

            // Evaluate geofence transitions
            $portEvents = $this->geofenceService->evaluate(
                $vessel,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $validated['sog_knots'] !== null ? (float) $validated['sog_knots'] : null,
                (int) $history->id,
            );

            $geofenceEventData = [];
            foreach ($portEvents as $event) {
                $geofenceEventData[] = [
                    'event_type' => $event->event_type,
                    'port_id' => $event->port_id,
                    'event_time' => $event->event_time->toIso8601String(),
                ];

                // Broadcast port event
                broadcast(new PortEventDetected($event, $vessel->name, $event->port->name));
            }

            // Broadcast position update
            $freshness = $this->freshnessService->calculate($sourceTimestamp);
            broadcast(new PositionUpdated(
                $vessel->id,
                $vessel->name,
                $vessel->mmsi,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $validated['sog_knots'] !== null ? (float) $validated['sog_knots'] : null,
                $validated['cog_degrees'] !== null ? (float) $validated['cog_degrees'] : null,
                $validated['heading_degrees'] !== null ? (int) $validated['heading_degrees'] : null,
                $freshness,
                $sourceTimestamp->toIso8601String(),
            ));

            return [
                'accepted' => true,
                'history_saved' => true,
                'geofence_events' => $geofenceEventData,
            ];
        });

        return $this->success($result);
    }
}

