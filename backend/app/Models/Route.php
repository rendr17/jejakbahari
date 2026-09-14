<?php

namespace App\Models;

use Database\Factories\RouteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'origin_port_id',
    'destination_port_id',
    'name',
    'route_type',
    'bidirectional',
    'active',
])]
class Route extends Model
{
    /** @use HasFactory<RouteFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'bidirectional' => 'boolean',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Route $route) {
            $route->id ??= Str::uuid()->toString();
        });
    }

    /**
     * @return BelongsTo<Port, $this>
     */
    public function originPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    /**
     * @return BelongsTo<Port, $this>
     */
    public function destinationPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }
}
