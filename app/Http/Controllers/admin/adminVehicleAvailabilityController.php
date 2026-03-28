<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class adminVehicleAvailabilityController extends Controller
{
    public function index()
    {
        $vehicles = AdminVehicleAvailability::query()
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_code')
            ->get();

        return view('admin.vehicle_availability_edit.admin_vehicle_availibility', [
            'vehicles' => $vehicles,
            'totalVehicles' => $vehicles->count(),
        ]);
    }

    public function update(Request $request, AdminVehicleAvailability $vehicle)
    {
        $validated = $request->validate([
            'driver_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Available,On Business Trip,Maintenance,Reserved,Unavailable'],
            'vehicle_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [
            'driver_name' => $validated['driver_name'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('vehicle_image')) {
            if ($vehicle->image_url && str_starts_with($vehicle->image_url, '/storage/')) {
                $oldPath = ltrim(substr($vehicle->image_url, strlen('/storage/')), '/');
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('vehicle_image')->store('vehicle_images', 'public');
            $data['image_url'] = Storage::url($path);
        }

        $vehicle->update($data);

        return redirect()
            ->route('admin.vehicle-availability')
            ->with('admin_vehicle_success', 'Vehicle details updated successfully.');
    }
}
