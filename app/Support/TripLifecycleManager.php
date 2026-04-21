<?php

namespace App\Support;

use App\Models\AdminVehicleAvailability;
use App\Models\DailyDriversTripTicket;
use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use Illuminate\Support\Facades\DB;

class TripLifecycleManager
{
    private const TRIP_TIMEZONE = 'Asia/Manila';

    public function moveFinishedTripsToEvaluationQueue(): int
    {
        $nowInTripTimezone = now(self::TRIP_TIMEZONE)->toDateTimeString();
        $movedTripsCount = 0;

        $dueTrips = TransportationRequestFormModel::query()
            ->where('status', 'On Trip')
            ->whereNotNull('date_time_to')
            ->where('date_time_to', '<=', $nowInTripTimezone)
            ->get(['id', 'driver_name']);

        if ($dueTrips->isNotEmpty()) {
            DB::transaction(function () use ($dueTrips, &$movedTripsCount) {
                $requestIds = $dueTrips->pluck('id')->all();

                $movedTripsCount = TransportationRequestFormModel::query()
                    ->whereIn('id', $requestIds)
                    ->where('status', 'On Trip')
                    ->update([
                        'status' => 'For Evaluation',
                        'updated_at' => now(),
                    ]);

                foreach ($dueTrips as $dueTrip) {
                    $evaluationCopies = $this->buildEvaluationCopies(
                        (int) $dueTrip->id,
                        (string) ($dueTrip->driver_name ?? '')
                    );

                    foreach ($evaluationCopies as $copy) {
                        DriverPerformanceEvaluation::query()->firstOrCreate(
                            [
                                'transportation_request_form_id' => (int) $dueTrip->id,
                                'copy_key' => (string) ($copy['copy_key'] ?? ''),
                            ],
                            [
                                'copy_number' => (int) ($copy['copy_number'] ?? 1),
                                'driver_name' => (string) ($copy['driver_name'] ?? 'N/A'),
                                'status' => 'Pending',
                            ]
                        );
                    }
                }
            });
        }

        $releasableRequestIds = $this->resolveRequestsReadyForVehicleRelease($nowInTripTimezone);
        if ($releasableRequestIds->isEmpty()) {
            return $movedTripsCount;
        }

        $vehicleCodes = TransportationRequestFormModel::query()
            ->whereIn('id', $releasableRequestIds->all())
            ->whereIn('status', ['For Evaluation', 'Completed'])
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
            return $movedTripsCount;
        }

        AdminVehicleAvailability::query()
            ->whereIn('vehicle_code', $vehicleCodes)
            ->whereIn('status', ['On Business Trip', 'Reserved'])
            ->update([
                'status' => 'Available',
                'updated_at' => now(),
            ]);

        return $movedTripsCount;
    }

    private function resolveRequestsReadyForVehicleRelease(string $nowInTripTimezone)
    {
        $candidateRequestIds = TransportationRequestFormModel::query()
            ->whereIn('status', ['For Evaluation', 'Completed'])
            ->whereNotNull('date_time_to')
            ->where('date_time_to', '<=', $nowInTripTimezone)
            ->pluck('id');

        if ($candidateRequestIds->isEmpty()) {
            return collect();
        }

        return DailyDriversTripTicket::query()
            ->whereIn('transportation_request_form_id', $candidateRequestIds->all())
            ->select('transportation_request_form_id')
            ->groupBy('transportation_request_form_id')
            ->havingRaw('COUNT(*) > 0')
            ->havingRaw('COUNT(arrival_time_office) = COUNT(*)')
            ->pluck('transportation_request_form_id');
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

    private function buildEvaluationCopies(int $requestId, string $driverNamesValue): array
    {
        $driverNames = $this->extractDriverNames($driverNamesValue);

        if (empty($driverNames)) {
            $driverNames = ['N/A'];
        }

        return collect($driverNames)
            ->values()
            ->map(function (string $driverName, int $index) use ($requestId): array {
                $copyNumber = $index + 1;

                return [
                    'copy_key' => $this->buildEvaluationCopyKey($requestId, $driverName, $copyNumber),
                    'copy_number' => $copyNumber,
                    'driver_name' => $driverName,
                ];
            })
            ->all();
    }

    private function extractDriverNames(string $value): array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            $tokens = $decoded;
        } else {
            $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return collect($tokens)
            ->map(function ($token): string {
                if (is_array($token)) {
                    return trim((string) ($token['driver_name'] ?? $token['name'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $name): bool {
                return $name !== '';
            })
            ->unique()
            ->values()
            ->all();
    }

    private function buildEvaluationCopyKey(int $requestId, string $driverName, int $copyNumber): string
    {
        $seed = $requestId . '|' . strtolower(trim($driverName)) . '|' . $copyNumber;

        return substr(hash('sha256', $seed), 0, 32);
    }
}
