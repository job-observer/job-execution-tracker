<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class JobMonitorController extends Controller
{
    public function index(): View
    {
        return view('job-monitor');
    }
}
