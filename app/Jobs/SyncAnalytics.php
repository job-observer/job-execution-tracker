<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncAnalytics implements ShouldQueue
{
    use Queueable;
    public $tries = 3;
    public $backoff = 2;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 60% chance of failure
        if (rand(1, 100) <= 60) {
            throw new \Exception("Analytics sync API failure");
        }
    }
}
