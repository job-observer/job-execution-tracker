<?php

namespace App\Jobs;

use App\Models\JobExecution;
use App\Models\JobEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessTelemetryEnvelopeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $dontTrack = true;

    public int $tries = 3;
    public int $backoff = 5;

    /**
     * Payload from executor
     */
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;

    }

    public function handle(): void
    {
        if (($this->data['schema_version'] ?? null) !== '1.0') {
            return;
        }

        $executions = $this->data['executions'] ?? [];

        if (empty($executions)) {
            return;
        }

        foreach ($executions as $execution) {

            $uuid = $execution['uuid'] ?? null;

            if (!$uuid) {
                continue;
            }

            if (!empty($execution['job_type']) &&
                str_contains($execution['job_type'], 'Telemetry')) {
                continue;
            }

            DB::transaction(function () use ($execution, $uuid) {

                JobExecution::updateOrCreate(
                    ['job_uuid' => $uuid],
                    [
                        'job_type'     => $execution['job_type'] ?? 'unknown',
                        'queue'        => $execution['queue'] ?? 'default',
                        'final_status' => $execution['final_status'] ?? null,
                        'started_at'   => $execution['started_at'] ?? null,
                        'ended_at'     => $execution['ended_at'] ?? null,
                    ]
                );

                foreach ($execution['events'] ?? [] as $event) {

                    if (empty($event['state']) || empty($event['occurred_at'])) {
                        continue;
                    }

                    JobEvent::updateOrCreate(
                        [
                            'job_uuid'    => $uuid,
                            'state'       => $event['state'],
                            'occurred_at' => $event['occurred_at'],
                        ],
                        [
                            'job_type' => $execution['job_type'] ?? 'unknown',
                            'queue'    => $execution['queue'] ?? 'default',
                            'attempt'  => $event['attempt'] ?? null,
                        ]
                    );
                }
            });
        }
    }
}