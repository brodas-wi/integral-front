<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Script extends Model
{
    protected $fillable = [
        'name',
        'description',
        'scope',
        'page_slugs',
        'js_content',
        'css_content',
        'status',
        'is_active',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'page_slugs'  => 'array',
        'is_active'   => 'boolean',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'approved')->where('is_active', true);
    }

    public function scopeForPage(Builder $query, string $pageSlug): Builder
    {
        return $query->where(function ($q) use ($pageSlug) {
            $q->where('scope', 'global')
                ->orWhere(function ($sq) use ($pageSlug) {
                    $sq->where('scope', 'per_page')
                        ->where('page_slugs', 'like', '%"' . $pageSlug . '"%');
                });
        });
    }

    public function hasCss(): bool
    {
        return !empty($this->css_content);
    }
}
