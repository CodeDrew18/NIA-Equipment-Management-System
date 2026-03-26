<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class vehicleAvailabilityController extends Controller
{
    function vehicleAvailability()
    {
        return view('vehicle_availability.vehicle_available');
    }
}
