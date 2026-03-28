<?php

namespace App\Http\Controllers;

use App\Models\AdminVehicleAvailability;
use Illuminate\Http\Request;

class vehicleAvailabilityController extends Controller
{
    public function vehicleAvailability()
    {
        $vehicles = AdminVehicleAvailability::query()
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_code')
            ->get();

        return view('vehicle_availability.vehicle_available', [
            'vehicles' => $vehicles,
            'totalVehicles' => $vehicles->count(),
        ]);
    }

    public function vehiclesData()
    {
        $vehicles = AdminVehicleAvailability::query()
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_code')
            ->get([
                'id',
                'vehicle_code',
                'vehicle_type',
                'capacity_label',
                'driver_name',
                'status',
                'image_url',
            ]);

        return response()->json([
            'totalVehicles' => $vehicles->count(),
            'vehicles' => $vehicles,
        ]);
    }
}
