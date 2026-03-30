<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class evaluationPerformanceController extends Controller
{
    public function index()
    {
        return view('drivers_evaluation.evaluation_performance');
    }
}
