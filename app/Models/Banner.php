<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'image_alt',
        'category',
        'btn_primary_text',
        'btn_primary_url',
        'btn_primary_style',
        'btn_primary_external',
        'btn_secondary_text',
        'btn_secondary_url',
        'btn_secondary_style',
        'btn_secondary_external',
        'is_active',
        'order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'btn_primary_external'   => 'boolean',
        'btn_secondary_external' => 'boolean',
        'order'                  => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        $storageUrl = config('app.storage_url', config('app.url') . '/storage');
        return rtrim($storageUrl, '/') . '/' . ltrim($this->image_path, '/');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
