<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessTelemetryEnvelopeJob;

class TelemetryIngestController
{
    public function __invoke(Request $request)
    {
        $data = $request->all();

        \Log::info('Telemetry received', $data);

        if (
            !isset($data['schema_version']) ||
            !isset($data['application']) ||
            !isset($data['executions'])
        ) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        ProcessTelemetryEnvelopeJob::dispatch($data);

        return response()->json(['status' => 'accepted'], 202);
    }

    // public function store(Request $request)
    // {
    //     \Log::info('Telemetry received', $data);
    //     $data = $request->all();

    //     // Basic validation
    //     if (
    //         !isset($data['schema_version']) ||
    //         !isset($data['application']) ||
    //         !isset($data['executions'])
    //     ) {
    //         return response()->json([
    //             'error' => 'Invalid payload'
    //         ], 400);
    //     }

    //     // Dispatch background processor
    //     ProcessTelemetryEnvelopeJob::dispatch($data);

    //     return response()->json([
    //         'status' => 'accepted'
    //     ], 202);
    // }
}