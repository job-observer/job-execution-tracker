<?php

namespace App\Services;

class DashboardProjectionService
{
    public static function project(array $lifecycle): array
    {
        $executionsByType = $lifecycle['executions'] ?? [];
        $allDurations     = $lifecycle['durations'] ?? collect();

        $jobTypes = [];

        foreach ($executionsByType as $jobType => $executions) {

            $items = collect($executions);
            $count = $items->count();

            if ($count === 0) {
                continue;
            }

            /* ================= FULL METRICS ================= */

            $durations = $items
                ->pluck('totalDuration')
                ->sort()
                ->values();

            $avgDuration = round($durations->avg(), 4);

            $p95Index = (int) floor(0.95 * ($count - 1));
            $p99Index = (int) floor(0.99 * ($count - 1));

            $p95 = $durations[$p95Index] ?? 0;
            $p99 = $durations[$p99Index] ?? 0;

            $successCount = $items->where('success', true)->count();
            $failureCount = $count - $successCount;
            $retryExecutions = $items->where('retryCount', '>', 0)->count();

            /* ================= STATE BUCKETS ================= */

            $stateBuckets = [];

            foreach ($items as $exec) {
                foreach ($exec['segments'] as $segment) {
                    $stateBuckets[$segment['state']][] = $segment['duration'];
                }
            }

            $orderedStates = ['queued', 'running', 'retrying'];

            $avgSegments = [];

            foreach ($orderedStates as $state) {
                if (!isset($stateBuckets[$state])) continue;

                $avgSegments[] = [
                    'state'    => $state,
                    'duration' => round(
                        collect($stateBuckets[$state])->avg(),
                        4
                    ),
                ];
            }

            $avgQueueTime = isset($stateBuckets['queued'])
                ? round(collect($stateBuckets['queued'])->avg(), 4)
                : 0;

            $avgRunTime = isset($stateBuckets['running'])
                ? round(collect($stateBuckets['running'])->avg(), 4)
                : 0;

            /* ================= LATEST 50 EXECUTIONS ================= */

            $latestExecutions = $items
                ->sortByDesc('lastOccurredAt')
                ->take(50)
                ->values();

            $jobTypes[] = [
                'jobType'       => class_basename($jobType),
                'executionCount'=> $count,
                'avgDuration'   => $avgDuration,
                'p95'           => round($p95, 4),
                'p99'           => round($p99, 4),

                'successRate' => $count
                    ? round(($successCount / $count) * 100, 2)
                    : 0,

                'failureRate' => $count
                    ? round(($failureCount / $count) * 100, 2)
                    : 0,

                'retryRate' => $count
                    ? round(($retryExecutions / $count) * 100, 2)
                    : 0,

                'avgQueueTime' => $avgQueueTime,
                'avgRunTime'   => $avgRunTime,

                'segments'   => $avgSegments,
                'executions' => $latestExecutions,
            ];
        }

        /* ================= SYSTEM METRICS ================= */

        $systemCount = $allDurations->count();

        $sorted = $allDurations
            ->sort()
            ->values();

        $systemP95Index = (int) floor(0.95 * ($systemCount - 1));
        $systemP99Index = (int) floor(0.99 * ($systemCount - 1));

        $systemP95 = $systemCount
            ? $sorted[$systemP95Index] ?? 0
            : 0;

        $systemP99 = $systemCount
            ? $sorted[$systemP99Index] ?? 0
            : 0;

        return [
            'systemVitals' => [
                'p95' => round($systemP95, 4),
                'p99' => round($systemP99, 4),
                'totalExecutions' => $systemCount,
            ],
            'jobTypes' => $jobTypes,
        ];
    }
}
