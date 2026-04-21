<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class adminVehicleAvailabilityController extends Controller
{
    private const SYSADMIN_PERSONNEL_ID = '100001';

    public function index()
    {
        $vehicles = AdminVehicleAvailability::query()
            ->orderBy('vehicle_type')
            ->orderBy('vehicle_code')
            ->get();

        $drivers = $this->resolveDriverNames();
        $assignedDrivers = $vehicles->pluck('driver_name')
            ->map(function ($driverName) {
                return trim((string) $driverName);
            })
            ->filter(function (string $driverName) {
                return $driverName !== '';
            })
            ->unique()
            ->values()
            ->all();

        $unassignedDrivers = $drivers
            ->reject(function (string $driverName) use ($assignedDrivers) {
                return in_array($driverName, $assignedDrivers, true);
            })
            ->values();

        return view('admin.vehicle_availability_edit.admin_vehicle_availibility', [
            'vehicles' => $vehicles,
            'totalVehicles' => $vehicles->count(),
            'drivers' => $drivers,
            'unassignedDrivers' => $unassignedDrivers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'create_vehicle_code' => ['required', 'string', 'max:255', 'unique:admin_vehicle_availability,vehicle_code'],
            'create_vehicle_type' => ['required', 'string', 'max:255'],
            'create_capacity_label' => ['nullable', 'string', 'max:255'],
            'create_driver_name' => ['required', 'string', 'max:255'],
            'create_status' => ['required', 'in:Available,On Business Trip,Maintenance,Reserved,Unavailable'],
            'create_vehicle_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $driverName = trim((string) ($validated['create_driver_name'] ?? ''));
        $knownDrivers = $this->resolveDriverNames();

        if (!$knownDrivers->contains($driverName)) {
            throw ValidationException::withMessages([
                'create_driver_name' => 'Select a valid driver from the unassigned driver list.',
            ]);
        }

        $this->ensureDriverIsAssignable($driverName, null);

        $data = [
            'vehicle_code' => trim((string) ($validated['create_vehicle_code'] ?? '')),
            'vehicle_type' => trim((string) ($validated['create_vehicle_type'] ?? '')),
            'capacity_label' => trim((string) ($validated['create_capacity_label'] ?? '')),
            'driver_name' => $driverName,
            'status' => (string) ($validated['create_status'] ?? 'Available'),
        ];

        if ($request->hasFile('create_vehicle_image')) {
            $path = $request->file('create_vehicle_image')->store('vehicle_images', 'public');
            $data['image_url'] = Storage::url($path);
        }

        AdminVehicleAvailability::query()->create($data);

        return redirect()
            ->route('admin.vehicle-availability')
            ->with('admin_vehicle_success', 'New vehicle added successfully.');
    }

    public function update(Request $request, AdminVehicleAvailability $vehicle)
    {
        $validated = $request->validate([
            'driver_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Available,On Business Trip,Maintenance,Reserved,Unavailable'],
            'vehicle_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [
            'driver_name' => null,
            'status' => $validated['status'],
        ];

        $driverName = trim((string) ($validated['driver_name'] ?? ''));
        if ($driverName !== '') {
            $knownDrivers = $this->resolveDriverNames();
            if (!$knownDrivers->contains($driverName)) {
                throw ValidationException::withMessages([
                    'driver_name' => 'Select a valid driver from the available list.',
                ]);
            }

            $this->ensureDriverIsAssignable($driverName, (int) $vehicle->id);
            $data['driver_name'] = $driverName;
        }

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

    private function resolveDriverNames(): Collection
    {
        return User::query()
            ->whereRaw("CONCAT(',', role, ',') LIKE '%,driver,%'")
            ->where('personnel_id', '!=', self::SYSADMIN_PERSONNEL_ID)
            ->orderBy('name')
            ->pluck('name')
            ->map(function ($driverName) {
                return trim((string) $driverName);
            })
            ->filter(function (string $driverName) {
                return $driverName !== '';
            })
            ->unique()
            ->values();
    }

    private function ensureDriverIsAssignable(string $driverName, ?int $ignoreVehicleId): void
    {
        $normalizedDriver = strtolower(trim($driverName));
        if ($normalizedDriver === '') {
            return;
        }

        $query = AdminVehicleAvailability::query()
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->whereRaw('LOWER(TRIM(driver_name)) = ?', [$normalizedDriver]);

        if ($ignoreVehicleId !== null) {
            $query->where('id', '!=', $ignoreVehicleId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'driver_name' => 'This driver is already assigned to another vehicle.',
                'create_driver_name' => 'This driver is already assigned to another vehicle.',
            ]);
        }
    }
}
