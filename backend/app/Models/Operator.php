<?php

namespace App\Models;

use Database\Factories\OperatorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'website_url', 'active'])]
class Operator extends Model
{
    /** @use HasFactory<OperatorFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Operator $operator) {
            $operator->id ??= Str::uuid()->toString();
            $operator->slug ??= Str::slug($operator->name);
        });
    }

    public function vessels(): HasMany
    {
        return $this->hasMany(Vessel::class);
    }
}
