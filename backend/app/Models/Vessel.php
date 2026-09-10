<?php

namespace App\Models;

use Database\Factories\VesselFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'operator_id',
    'mmsi',
    'imo',
    'name',
    'normalized_name',
    'call_sign',
    'vessel_category',
    'verification_status',
    'confidence_score',
    'active',
    'public_visible',
])]
class Vessel extends Model
{
    /** @use HasFactory<VesselFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'active' => 'boolean',
        'public_visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Vessel $vessel) {
            $vessel->id ??= Str::uuid()->toString();
            $vessel->normalized_name ??= Str::lower(trim($vessel->name));
        });
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(RegistryEvidence::class);
    }

    public function latestPosition(): HasOne
    {
        return $this->hasOne(VesselLatestPosition::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'VERIFIED';
    }
}
