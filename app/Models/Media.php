<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'filename',
        'alt',
        'stored_filename',
        'mime_type',
        'type',
        'size',
        'path',
        'disk',
        'width',
        'height',
        'uploaded_by',
    ];

    protected $casts = [
        'size'   => 'integer',
        'width'  => 'integer',
        'height' => 'integer',
    ];

    public function getUrlAttribute(): string
    {
        $storageUrl = config('app.storage_url', config('app.url') . '/storage');
        return rtrim($storageUrl, '/') . '/' . ltrim($this->path, '/');
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }
}
