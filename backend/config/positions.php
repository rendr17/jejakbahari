<?php

return [
    'live_threshold_minutes' => env('POSITION_LIVE_THRESHOLD_MINUTES', 5),
    'delayed_threshold_minutes' => env('POSITION_DELAYED_THRESHOLD_MINUTES', 30),
    'offline_threshold_hours' => env('POSITION_OFFLINE_THRESHOLD_HOURS', 6),
    'history_retention_days' => env('HISTORY_RETENTION_DAYS', 7),
    'history_moving_sample_seconds' => env('HISTORY_MOVING_SAMPLE_SECONDS', 60),
    'history_stationary_sample_seconds' => env('HISTORY_STATIONARY_SAMPLE_SECONDS', 300),
];
