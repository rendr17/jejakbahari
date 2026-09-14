<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortEvent extends Model
{
    public $incrementing = true;

    protected $keyType = 'int';

    protected $table = 'port_events';

    public const UPDATED_AT = null;

    protected $fillable = [
        'vessel_id',
        'port_id',
        'event_type',
        'event_time',
        'detection_method',
        'confidence_score',
        'source_position_history_id',
        'metadata',
    ];

    public const string EVENT_ENTERED = 'ENTERED_GEOFENCE';

    public const string EVENT_ARRIVED = 'ARRIVED';

    public const string EVENT_DEPARTED = 'DEPARTED';

    public const string EVENT_EXITED = 'EXITED_GEOFENCE';

    protected $casts = [
        'event_time' => 'datetime',
        'confidence_score' => 'float',
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<Vessel, $this>
     */
    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    /**
     * @return BelongsTo<Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}
