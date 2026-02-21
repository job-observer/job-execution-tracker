<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelemetryIngestController;

Route::post('/telemetry/ingest', TelemetryIngestController::class);