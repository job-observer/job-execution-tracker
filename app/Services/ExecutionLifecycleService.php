<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ExecutionLifecycleService
{
    public static function build(Collection $events): array
    {
        $grouped = $events->groupBy('job_uuid');

        $executions = [];
        $allDurations = collect();

        foreach ($grouped as $uuid => $jobEvents) {

            // Sort events in chronological order
            $ordered = $jobEvents
                ->sortBy('occurred_at')
                ->values();

            // Must have at least 2 states to calculate duration
            if ($ordered->count() < 2) {
                continue;
            }

            $jobType = $ordered->first()->job_type;

            $segments = [];
            $retryCount = 0;

            for ($i = 0; $i < $ordered->count() - 1; $i++) {

                $current = $ordered[$i];
                $next    = $ordered[$i + 1];

                $duration = abs(
                    $current->occurred_at
                        ->diffInMilliseconds($next->occurred_at)
                ) / 1000;

                if ($current->state === 'retrying') {
                    $retryCount++;
                }

                $segments[] = [
                    'state'    => $current->state,
                    'duration' => round($duration, 4),
                ];
            }

            $totalDuration = collect($segments)->sum('duration');

            $finalState = $ordered->last()->state;

            $execution = [
                'id'             => $uuid,
                'jobType'        => $jobType,
                'segments'       => $segments,
                'totalDuration'  => round($totalDuration, 4),
                'retryCount'     => $retryCount,
                'success'        => $finalState === 'succeeded',
                'status'         => $finalState, // explicit status for UI
                'lastOccurredAt' => $ordered->last()->occurred_at,
            ];

            $executions[$jobType][] = $execution;
            $allDurations->push($totalDuration);
        }

        foreach ($executions as $jobType => $items) {
            $executions[$jobType] = collect($items)
                ->sortByDesc('lastOccurredAt')
                ->take(50)
                ->values()
                ->toArray();
        }

        return [
            'executions' => $executions,
            'durations'  => $allDurations,
        ];
    }
}
