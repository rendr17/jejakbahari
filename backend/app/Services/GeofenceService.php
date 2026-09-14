<?php

namespace App\Services;

use App\Models\Port;
use App\Models\PortEvent;
use App\Models\Vessel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Evaluates vessel positions against port geofences and generates port events.
 *
 * MVP implementation uses circle radius from port center_point.
 * Polygon geofence support is deferred per docs/21_PORT_GEOFENCE_SPEC.md.
 */
class GeofenceService
{
    public function __construct(
        private readonly float $arrivalSpeedKnots,
        private readonly int $arrivalDwellMinutes,
        private readonly float $departureSpeedKnots,
        private readonly int $cooldownMinutes,
        private readonly int $defaultRadiusM,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            arrivalSpeedKnots: (float) config('geofence.arrival_speed_knots', 2),
            arrivalDwellMinutes: (int) config('geofence.arrival_dwell_minutes', 10),
            departureSpeedKnots: (float) config('geofence.departure_speed_knots', 3),
            cooldownMinutes: (int) config('geofence.cooldown_minutes', 30),
            defaultRadiusM: (int) config('geofence.default_radius_m', 1000),
        );
    }

    /**
     * Evaluate a vessel position against all active ports with geofence data.
     *
     * @param  Vessel  $vessel  The vessel whose position was just ingested
     * @param  float  $latitude  Position latitude
     * @param  float  $longitude  Position longitude
     * @param  float|null  $sogKnots  Speed over ground in knots
     * @param  int|null  $historyId  The vessel_position_history ID for this position
     * @return PortEvent[] Generated port events (may be empty)
     */
    public function evaluate(
        Vessel $vessel,
        float $latitude,
        float $longitude,
        ?float $sogKnots,
        ?int $historyId,
    ): array {
        $ports = $this->getActivePorts();

        if ($ports->isEmpty()) {
            return [];
        }

        $events = [];
        $now = now();

        foreach ($ports as $port) {
            $distance = $this->distanceToPort($latitude, $longitude, $port);
            $radius = $port->geofence_radius_m ?? $this->defaultRadiusM;
            $isInside = $distance !== null && $distance <= $radius;

            $lastEvent = $this->getLastEvent($vessel->id, $port->id);
            $lastEventType = $lastEvent?->event_type;

            // Cooldown check — don't generate events too frequently
            if ($lastEvent && abs($now->diffInMinutes($lastEvent->event_time)) < $this->cooldownMinutes) {
                continue;
            }

            $event = $this->evaluateTransition(
                $vessel,
                $port,
                $isInside,
                $sogKnots,
                $lastEventType,
                $historyId,
                $now,
            );

            if ($event !== null) {
                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Evaluate a single port transition based on previous state and current position.
     */
    private function evaluateTransition(
        Vessel $vessel,
        Port $port,
        bool $isInside,
        ?float $sogKnots,
        ?string $lastEventType,
        ?int $historyId,
        $now,
    ): ?PortEvent {
        // No previous event
        if ($lastEventType === null) {
            if ($isInside) {
                return $this->createEvent($vessel, $port, PortEvent::EVENT_ENTERED, $historyId, $now);
            }

            return null;
        }

        // ENTERED -> check for ARRIVED (low speed inside)
        if ($lastEventType === PortEvent::EVENT_ENTERED && $isInside) {
            if ($sogKnots !== null && $sogKnots <= $this->arrivalSpeedKnots) {
                return $this->createEvent($vessel, $port, PortEvent::EVENT_ARRIVED, $historyId, $now);
            }

            return null;
        }

        // ARRIVED -> check for DEPARTED (speed increased)
        if ($lastEventType === PortEvent::EVENT_ARRIVED) {
            if ($sogKnots !== null && $sogKnots >= $this->departureSpeedKnots) {
                return $this->createEvent($vessel, $port, PortEvent::EVENT_DEPARTED, $historyId, $now);
            }

            return null;
        }

        // DEPARTED -> check for EXITED (left geofence)
        if ($lastEventType === PortEvent::EVENT_DEPARTED && ! $isInside) {
            return $this->createEvent($vessel, $port, PortEvent::EVENT_EXITED, $historyId, $now);
        }

        // EXITED -> check for ENTERED again
        if ($lastEventType === PortEvent::EVENT_EXITED && $isInside) {
            return $this->createEvent($vessel, $port, PortEvent::EVENT_ENTERED, $historyId, $now);
        }

        // Fallback: if inside but last event was EXITED, or outside but last was ENTERED/ARRIVED
        if ($isInside && $lastEventType === PortEvent::EVENT_EXITED) {
            return $this->createEvent($vessel, $port, PortEvent::EVENT_ENTERED, $historyId, $now);
        }

        if (! $isInside && in_array($lastEventType, [PortEvent::EVENT_ENTERED, PortEvent::EVENT_ARRIVED])) {
            return $this->createEvent($vessel, $port, PortEvent::EVENT_EXITED, $historyId, $now);
        }

        return null;
    }

    private function createEvent(
        Vessel $vessel,
        Port $port,
        string $eventType,
        ?int $historyId,
        $eventTime,
    ): PortEvent {
        $event = PortEvent::create([
            'vessel_id' => $vessel->id,
            'port_id' => $port->id,
            'event_type' => $eventType,
            'event_time' => $eventTime,
            'detection_method' => 'RADIUS',
            'confidence_score' => 70.00,
            'source_position_history_id' => $historyId,
            'metadata' => [
                'port_name' => $port->name,
                'vessel_name' => $vessel->name,
            ],
        ]);

        Log::info('geofence_event', [
            'event_type' => $eventType,
            'vessel_id' => $vessel->id,
            'vessel_name' => $vessel->name,
            'port_id' => $port->id,
            'port_name' => $port->name,
        ]);

        return $event;
    }

    /**
     * Get the last port event for a vessel-port pair.
     */
    private function getLastEvent(string $vesselId, string $portId): ?PortEvent
    {
        return PortEvent::where('vessel_id', $vesselId)
            ->where('port_id', $portId)
            ->orderByDesc('event_time')
            ->first();
    }

    /**
     * Get all active ports with geofence data.
     *
     * @return Collection<int, Port>
     */
    private function getActivePorts()
    {
        $query = Port::where('active', true);

        if (DB::connection()->getDriverName() === 'pgsql') {
            $query->selectRaw('*, ST_X(center_point::geometry) AS center_lon, ST_Y(center_point::geometry) AS center_lat');
        }

        return $query->get();
    }

    /**
     * Calculate distance from a point to a port center using Haversine formula.
     * Returns distance in meters, or null if port has no center point.
     */
    private function distanceToPort(float $lat, float $lon, Port $port): ?float
    {
        // For PostgreSQL, use the center_lon/center_lat from the query
        // For SQLite, use the latitude/longitude columns directly
        $portLat = $port->center_lat ?? $port->latitude ?? null;
        $portLon = $port->center_lon ?? $port->longitude ?? null;

        if ($portLat === null || $portLon === null) {
            return null;
        }

        return $this->haversine($lat, $lon, (float) $portLat, (float) $portLon);
    }

    /**
     * Haversine distance between two lat/lon points in meters.
     */
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6_371_000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
