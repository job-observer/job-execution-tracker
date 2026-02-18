<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobExecution extends Model
{
    protected $fillable = [
        'job_uuid',
        'job_type',
        'queue',
        'final_status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
