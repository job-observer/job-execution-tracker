<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobEvent extends Model
{
    protected $fillable = [
        'job_uuid',
        'job_type',
        'queue',
        'state',
        'attempt',
        'occurred_at',
    ];
    
    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
