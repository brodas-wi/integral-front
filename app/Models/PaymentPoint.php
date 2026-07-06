<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PaymentPoint extends Model
{
    protected $fillable = [
        'correspondent',
        'department',
        'municipality',
        'affiliate',
        'branch',
        'address',
        'zone',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'latitude'   => 'decimal:7',
        'longitude'  => 'decimal:7',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithCoordinates(Builder $query): Builder
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }
}
