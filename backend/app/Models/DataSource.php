<?php

namespace App\Models;

use Database\Factories\DataSourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'source_type',
    'url',
    'license_name',
    'terms_url',
    'attribution_text',
    'access_method',
    'active',
    'last_reviewed_at',
])]
class DataSource extends Model
{
    /** @use HasFactory<DataSourceFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'active' => 'boolean',
        'last_reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DataSource $source) {
            $source->id ??= Str::uuid()->toString();
        });
    }
}
