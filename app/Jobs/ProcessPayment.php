<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPayment implements ShouldQueue
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
        sleep(rand(1, 3));
        $this->release(5);
        // 10% failure chance
        if (rand(1, 10) === 1) {
            throw new \Exception("Payment gateway timeout");
        }
    }

}
