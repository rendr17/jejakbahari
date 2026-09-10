<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VesselLatestPosition extends Model
{
    protected $table = 'vessel_latest_positions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'vessel_id';

    public $timestamps = false;

    protected $fillable = [
        'vessel_id',
        'latitude',
        'longitude',
        'sog_knots',
        'cog_degrees',
        'heading_degrees',
        'nav_status',
        'destination_text',
        'source_timestamp',
        'received_at',
        'provider_name',
        'raw_message_id',
        'updated_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'sog_knots' => 'decimal:2',
        'cog_degrees' => 'decimal:2',
        'heading_degrees' => 'integer',
        'source_timestamp' => 'datetime',
        'received_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
