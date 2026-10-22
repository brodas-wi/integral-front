<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormVersion extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'form_versions';

    protected $casts = [
        'fields' => 'array',
        'settings' => 'array',
    ];
}