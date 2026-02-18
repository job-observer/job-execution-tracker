<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Horizon\Events\JobPushed;
use Laravel\Horizon\Events\JobReserved;
use Laravel\Horizon\Events\JobReleased;
use Laravel\Horizon\Events\JobDeleted;
use Laravel\Horizon\Events\JobFailed;
use App\Models\JobEvent;
use Carbon\Carbon;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Added to queue
        Event::listen(JobPushed::class, function (JobPushed $event) {
            JobEvent::create([
                'job_uuid'    => $event->payload['uuid'],
                'job_type'    => $event->payload['displayName'],
                'queue'       => $event->queue,
                'state'       => 'queued',
                'occurred_at' => Carbon::createFromTimestamp($event->payload['pushedAt']),
            ]);
        });

        // Starts execution
        Event::listen(JobReserved::class, function (JobReserved $event) {
            JobEvent::create([
                'job_uuid'    => $event->payload['uuid'],
                'job_type'    => $event->payload['displayName'],
                'queue'       => $event->queue,
                'state'       => 'running',
                'occurred_at' => now(),
            ]);
        });

        // Some exception occured starts to retry
        Event::listen(JobReleased::class, function (JobReleased $event) {
            $uuid = $event->payload['uuid'];

            // 1. retrying state
            JobEvent::create([
                'job_uuid'    => $uuid,
                'job_type'    => $event->payload['displayName'],
                'queue'       => $event->queue,
                'state'       => 'retrying',
                'occurred_at' => now(),
            ]);

            // 2. simulate re-queued state
            JobEvent::create([
                'job_uuid'    => $uuid,
                'job_type'    => $event->payload['displayName'],
                'queue'       => $event->queue,
                'state'       => 'queued',
                'occurred_at' => now()->addMilliseconds(1),
            ]);
        });

        // Job completed successfully
        Event::listen(JobDeleted::class, function (JobDeleted $event) {
            JobEvent::create([
                'job_uuid'    => $event->payload['uuid'],
                'job_type'    => $event->payload['displayName'],
                'queue'       => $event->queue,
                'state'       => 'succeeded',
                'occurred_at' => now(),
            ]);
        });

        // Job failed
        Event::listen(JobFailed::class, function (JobFailed $event) {
            JobEvent::create([
                'job_uuid'    => $event->payload['uuid'],
                'job_type'    => $event->payload['displayName'],
                'queue'       => $event->queue,
                'state'       => 'failed',
                'occurred_at' => now(),
            ]);
        });
    }

}
