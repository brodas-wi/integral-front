<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ExtraordinaryAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'extraordinary_asset_category_id',
        'name',
        'short_description',
        'image_url',
        'link_url',
        'link_is_external',
        'is_active',
    ];

    protected $casts = [
        'link_is_external' => 'boolean',
        'is_active'         => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExtraordinaryAssetCategory::class, 'extraordinary_asset_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
    }
}
