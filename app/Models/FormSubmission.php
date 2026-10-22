<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends Model
{
    use SoftDeletes;

    protected $table = 'form_submissions';

    protected $fillable = [
        'uuid',
        'form_id',
        'form_version_id',
        'answers',
        'email',
        'search_text',
        'status',
        'source_url',
        'ip_address',
        'user_agent',
        'notify_status',
        'confirmation_status',
    ];

    protected $casts = [
        'answers' => 'array',
    ];
}