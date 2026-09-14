<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geofence Thresholds
    |--------------------------------------------------------------------------
    |
    | Thresholds for port geofence event detection. Values are defaults
    | and can be overridden per port via the ports.geofence_radius_m column
    | and future per-port config fields.
    |
    */

    // Speed below which a vessel inside a geofence is considered "arrived"
    'arrival_speed_knots' => (float) env('GEOFENCE_ARRIVAL_SPEED_KNOTS', 2),

    // Minutes a vessel must stay below arrival speed to trigger ARRIVED
    'arrival_dwell_minutes' => (int) env('GEOFENCE_ARRIVAL_DWELL_MINUTES', 10),

    // Speed above which an arrived vessel is considered "departed"
    'departure_speed_knots' => (float) env('GEOFENCE_DEPARTURE_SPEED_KNOTS', 3),

    // Minutes after an event before another can be generated (hysteresis)
    'cooldown_minutes' => (int) env('GEOFENCE_COOLDOWN_MINUTES', 30),

    // Default geofence radius in meters if port has no explicit radius
    'default_radius_m' => (int) env('GEOFENCE_DEFAULT_RADIUS_M', 1000),

];
