<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a vessel's latest position is updated.
 * Sent on the public "vessel-positions" channel.
 */
class PositionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $vesselId,
        public string $vesselName,
        public ?string $mmsi,
        public float $latitude,
        public float $longitude,
        public ?float $sogKnots,
        public ?float $cogDegrees,
        public ?int $headingDegrees,
        public string $freshness,
        public string $sourceTimestamp,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('vessel-positions')];
    }

    public function broadcastWith(): array
    {
        return [
            'vessel_id' => $this->vesselId,
            'name' => $this->vesselName,
            'mmsi' => $this->mmsi,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'sog_knots' => $this->sogKnots,
            'cog_degrees' => $this->cogDegrees,
            'heading_degrees' => $this->headingDegrees,
            'freshness' => $this->freshness,
            'source_timestamp' => $this->sourceTimestamp,
        ];
    }

    public function broadcastAs(): string
    {
        return 'position-updated';
    }
}
