<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\TimeRangeService;
use App\Services\JobEventDataService;
use App\Services\ExecutionLifecycleService;
use App\Services\DashboardProjectionService;

class DashboardController extends Controller
{
    public function metrics(Request $request): JsonResponse
    {
        // Get time range from query (default 24h)
        $range = $request->get('range', '24h');

        // Resolve start time
        $from = TimeRangeService::resolve($range);

        // Fetch events from DB (complete lifecycles only)
        $events = JobEventDataService::fetch($from);

        // Build execution lifecycle structure
        $lifecycle = ExecutionLifecycleService::build($events);

        // Project into dashboard-ready structure
        $projection = DashboardProjectionService::project($lifecycle);

        // Return JSON response
        return response()->json($projection);
    }
}
