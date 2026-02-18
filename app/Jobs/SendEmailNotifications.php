<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailNotifications implements ShouldQueue
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
        usleep(rand(200000, 800000)); // 0.2 - 0.8 sec

        if (rand(1, 15) === 1) {
            throw new \Exception("SMTP failure");
        }
    }
}
