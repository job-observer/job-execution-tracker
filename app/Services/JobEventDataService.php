<?php

namespace App\Services;

use App\Models\JobEvent;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class JobEventDataService
{
    public static function fetch(Carbon $from): Collection
    {
        // Get UUIDs of jobs that completed in time range
        $uuids = JobEvent::whereIn('state', ['succeeded', 'failed'])
            ->where('occurred_at', '>=', $from)
            ->distinct()
            ->pluck('job_uuid');

        if ($uuids->isEmpty()) {
            return collect();
        }

        // Fetch ALL events for those executions
        return JobEvent::whereIn('job_uuid', $uuids)
            ->orderBy('occurred_at')
            ->get();
    }
}
