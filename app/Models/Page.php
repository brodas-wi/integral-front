<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'navbar_id',
        'footer_id',
        'html_content',
        'css_content',
        'js_content',
        'components_json',
        'styles_json',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function navbar()
    {
        return $this->belongsTo(Navbar::class);
    }

    public function footer()
    {
        return $this->belongsTo(Footer::class);
    }
}