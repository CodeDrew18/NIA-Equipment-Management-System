<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class monthlyTravelReportController extends Controller
{
    public function index()
    {
        return view('monthly_official_travel_report.monthly_travel_report');
    }
}
