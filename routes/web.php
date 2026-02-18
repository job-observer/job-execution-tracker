<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobMonitorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);

Route::get('/monitor', [JobMonitorController::class, 'index']);

