<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessPayment;
use App\Jobs\SendEmailNotifications;
use App\Jobs\GenerateInvoice;
use App\Jobs\SyncAnalytics;

class StressTestJobs extends Command
{
    protected $signature = 'stress:test-jobs {count=500}';
    protected $description = 'Dispatch many random jobs for stress testing';

    public function handle()
    {
        $count = (int) $this->argument('count');

        for ($i = 0; $i < $count; $i++) {

            match (rand(1,4)) {
                1 => ProcessPayment::dispatch(),
                2 => SendEmailNotifications::dispatch(),
                3 => GenerateInvoice::dispatch(),
                4 => SyncAnalytics::dispatch(),
            };
        }

        $this->info("Dispatched {$count} jobs.");
    }
}
