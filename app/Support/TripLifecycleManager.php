<?php

namespace App\Support;

use App\Models\AdminVehicleAvailability;
use App\Models\TransportationRequestFormModel;
use Illuminate\Support\Facades\DB;

class TripLifecycleManager
{
    public function moveFinishedTripsToEvaluationQueue(): int
    {
        $finishedTrips = TransportationRequestFormModel::query()
            ->where('status', 'On Trip')
            ->whereNotNull('date_time_to')
            ->where('date_time_to', '<=', now())
            ->get(['id', 'vehicle_id']);

        if ($finishedTrips->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($finishedTrips) {
            $requestIds = $finishedTrips->pluck('id')->all();

            TransportationRequestFormModel::query()
                ->whereIn('id', $requestIds)
                ->where('status', 'On Trip')
                ->update([
                    'status' => 'For Evaluation',
                    'updated_at' => now(),
                ]);

            $vehicleCodes = $finishedTrips
                ->pluck('vehicle_id')
                ->filter(function ($vehicleId) {
                    return trim((string) $vehicleId) !== '';
                })
                ->flatMap(function ($vehicleId) {
                    return $this->extractVehicleCodes((string) $vehicleId);
                })
                ->unique()
                ->values()
                ->all();

            if (empty($vehicleCodes)) {
                return;
            }

            AdminVehicleAvailability::query()
                ->whereIn('vehicle_code', $vehicleCodes)
                ->whereIn('status', ['On Business Trip', 'Reserved'])
                ->update([
                    'status' => 'Available',
                    'updated_at' => now(),
                ]);
        });

        return $finishedTrips->count();
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
