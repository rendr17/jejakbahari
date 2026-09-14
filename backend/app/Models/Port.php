<?php

namespace App\Models;

use Database\Factories\PortFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable([
    'code',
    'name',
    'city_name',
    'province_name',
    'geofence_radius_m',
    'verification_status',
    'active',
])]
class Port extends Model
{
    /** @use HasFactory<PortFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'geofence_radius_m' => 'integer',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Port $port) {
            $port->id ??= Str::uuid()->toString();
        });

        static::saving(function (Port $port) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                $lat = $port->attributes['latitude'] ?? null;
                $lon = $port->attributes['longitude'] ?? null;
                if ($lat !== null && $lon !== null) {
                    $port->center_point = DB::raw("ST_SetSRID(ST_MakePoint({$lon}, {$lat}), 4326)::geography");
                }
                // Remove virtual attributes before save — they are not real columns.
                unset($port->attributes['latitude'], $port->attributes['longitude']);
            }
        });
    }

    /**
     * @return HasMany<Route, $this>
     */
    public function originRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'origin_port_id');
    }

    /**
     * @return HasMany<Route, $this>
     */
    public function destinationRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'destination_port_id');
    }

    /**
     * @return HasMany<PortEvent, $this>
     */
    public function portEvents(): HasMany
    {
        return $this->hasMany(PortEvent::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'VERIFIED';
    }
}
