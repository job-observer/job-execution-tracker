<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/job-monitor', function () {
    return response()->file(
        public_path('job-monitor/index.html')
    );
});
