<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortEvent extends Model
{
    public $incrementing = true;

    protected $keyType = 'int';

    protected $table = 'port_events';

    public const string EVENT_ENTERED = 'ENTERED';

    public const string EVENT_ARRIVED = 'ARRIVED';

    public const string EVENT_DEPARTED = 'DEPARTED';

    public const string EVENT_EXITED = 'EXITED';

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
