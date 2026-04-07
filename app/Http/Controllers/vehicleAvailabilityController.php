<?php

namespace App\Http\Controllers;

use App\Models\AdminVehicleAvailability;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;

class vehicleAvailabilityController extends Controller
{
    public function vehicleAvailability()
    {
        $this->syncUnusedVehiclesToAvailable();

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
        $this->syncUnusedVehiclesToAvailable();

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

    private function syncUnusedVehiclesToAvailable(): void
    {
        $activeVehicleCodes = TransportationRequestFormModel::query()
            ->whereIn('status', ['Signed', 'Dispatched', 'On Trip'])
            ->whereNotNull('vehicle_id')
            ->where('vehicle_id', '!=', '')
            ->pluck('vehicle_id')
            ->flatMap(function ($vehicleId) {
                return $this->extractVehicleCodes((string) $vehicleId);
            })
            ->unique()
            ->values();

        $staleStatusQuery = AdminVehicleAvailability::query()
            ->whereIn('status', ['On Business Trip', 'Reserved']);

        if ($activeVehicleCodes->isNotEmpty()) {
            $staleStatusQuery->whereNotIn('vehicle_code', $activeVehicleCodes->all());
        }

        $staleStatusQuery->update([
            'status' => 'Available',
            'updated_at' => now(),
        ]);
    }

    private function extractVehicleCodes(string $vehicleIds): array
    {
        $value = trim($vehicleIds);
        if ($value === '') {
            return [];
        }

        $tokens = preg_split('/\s*,\s*/', $value) ?: [];

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $tokens = $decoded;
        }

        return collect($tokens)
            ->map(function ($token) {
                if (is_array($token)) {
                    return trim((string) ($token['vehicle_code'] ?? $token['code'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $code) {
                return $code !== '';
            })
            ->values()
            ->all();
    }
}
