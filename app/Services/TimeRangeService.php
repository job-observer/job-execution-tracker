<?php

namespace App\Services;

use Carbon\Carbon;

class TimeRangeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function resolve(string $range): Carbon
    {
        $now = now();

        return match ($range) {
            '1h'  => $now->copy()->subHour(),
            '6h'  => $now->copy()->subHours(6),
            '24h' => $now->copy()->subDay(),
            '7d'  => $now->copy()->subDays(7),
            '30d' => $now->copy()->subDays(30),
            '3m'  => $now->copy()->subMonths(3),
            '6m'  => $now->copy()->subMonths(6),
            '1y'  => $now->copy()->subYear(),
            default => $now->copy()->subDay(),
        };
    }

}
