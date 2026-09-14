<?php

namespace App\Services;

use App\Models\VesselLatestPosition;
use Carbon\Carbon;

class FreshnessService
{
    public function __construct(
        private readonly int $liveThresholdMinutes = 5,
        private readonly int $delayedThresholdMinutes = 30,
        private readonly int $offlineThresholdHours = 6,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            (int) config('positions.live_threshold_minutes', 5),
            (int) config('positions.delayed_threshold_minutes', 30),
            (int) config('positions.offline_threshold_hours', 6),
        );
    }

    public function compute(VesselLatestPosition $position): string
    {
        $sourceTimestamp = $position->source_timestamp
            ? Carbon::parse($position->source_timestamp)
            : null;

        return $this->computeFromTimestamp($sourceTimestamp);
    }

    public function computeFromTimestamp(?Carbon $sourceTimestamp): string
    {
        if (! $sourceTimestamp) {
            return 'OFFLINE';
        }

        $ageSeconds = abs(now()->getTimestamp() - $sourceTimestamp->getTimestamp());

        return match (true) {
            $ageSeconds <= $this->liveThresholdMinutes * 60 => 'LIVE',
            $ageSeconds <= $this->delayedThresholdMinutes * 60 => 'DELAYED',
            $ageSeconds <= $this->offlineThresholdHours * 3600 => 'STALE',
            default => 'OFFLINE',
        };
    }
}
