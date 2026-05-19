<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'media_id',
        'cta_text',
        'cta_url',
        'cta_new_tab',
        'display_type',
        'display_mode',
        'page_slugs',
        'priority',
        'is_active',
        'schedule_type',
        'starts_at',
        'ends_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'page_slugs'  => 'array',
        'is_active'   => 'boolean',
        'cta_new_tab' => 'boolean',
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
        'priority'    => 'integer',
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForPage(Builder $query, string $pageSlug): Builder
    {
        return $query->where(function ($q) use ($pageSlug) {
            $q->where('display_type', 'global')
                ->orWhere(function ($sq) use ($pageSlug) {
                    $sq->where('display_type', 'homepage')
                        ->whereIn($pageSlug, ['home', 'inicio', 'index']);
                })
                ->orWhere(function ($sq) use ($pageSlug) {
                    $sq->where('display_type', 'specific_pages')
                        ->where('page_slugs', 'like', '%"' . $pageSlug . '"%');
                });
        });
    }

    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderByDesc('priority')->orderByDesc('created_at');
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        return true;
    }
}
