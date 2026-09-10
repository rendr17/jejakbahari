<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class VesselPositionHistory extends Model
{
    protected $table = 'vessel_position_history';

    public $timestamps = false;

    protected $fillable = [
        'vessel_id',
        'latitude',
        'longitude',
        'sog_knots',
        'cog_degrees',
        'heading_degrees',
        'source_timestamp',
        'received_at',
        'provider_name',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'sog_knots' => 'decimal:2',
        'cog_degrees' => 'decimal:2',
        'heading_degrees' => 'integer',
        'source_timestamp' => 'datetime',
        'received_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                $lat = (float) $model->latitude;
                $lon = (float) $model->longitude;
                $model->position = DB::raw("ST_SetSRID(ST_MakePoint({$lon}, {$lat}), 4326)::geography");
            }
        });
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
