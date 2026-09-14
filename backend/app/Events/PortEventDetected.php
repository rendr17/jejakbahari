<?php

namespace App\Events;

use App\Models\PortEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a port geofence event is detected.
 * Sent on the public "port-events" channel.
 */
class PortEventDetected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PortEvent $portEvent,
        public string $vesselName,
        public string $portName,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('port-events')];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->portEvent->id,
            'vessel_id' => $this->portEvent->vessel_id,
            'vessel_name' => $this->vesselName,
            'port_id' => $this->portEvent->port_id,
            'port_name' => $this->portName,
            'event_type' => $this->portEvent->event_type,
            'event_time' => $this->portEvent->event_time->toIso8601String(),
            'confidence_score' => $this->portEvent->confidence_score,
        ];
    }

    public function broadcastAs(): string
    {
        return 'port-event';
    }
}
