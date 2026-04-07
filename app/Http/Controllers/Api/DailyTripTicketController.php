<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyDriversTripTicket;
use App\Models\TransportationRequestFormModel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DailyTripTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $driverUser = $this->resolveDriverUser($request);
        if ($driverUser instanceof JsonResponse) {
            return $driverUser;
        }

        $tickets = DailyDriversTripTicket::query()
            ->orderByDesc('id')
            ->get();

        $driverTickets = $tickets->map(function (DailyDriversTripTicket $ticket) use ($driverUser) {
            if (!$this->ticketBelongsToDriver($ticket, $driverUser)) {
                return null;
            }

            return $this->mapTicketPayload($ticket, $driverUser);
        })->filter()->values();

        return response()->json([
            'message' => 'Driver daily trip tickets fetched successfully.',
            'driver' => [
                'id' => $driverUser->id,
                'personnel_id' => $driverUser->personnel_id,
                'name' => $driverUser->name,
                'role' => $driverUser->role,
            ],
            'total' => $driverTickets->count(),
            'data' => $driverTickets,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $driverUser = $this->resolveDriverUser($request);
        if ($driverUser instanceof JsonResponse) {
            return $driverUser;
        }

        $ticket = DailyDriversTripTicket::query()->find($id);
        if (!$ticket) {
            return response()->json([
                'message' => 'Daily trip ticket not found.',
            ], 404);
        }

        if (!$this->ticketBelongsToDriver($ticket, $driverUser)) {
            return response()->json([
                'message' => 'You are not allowed to access this trip ticket.',
            ], 403);
        }

        return response()->json([
            'message' => 'Driver daily trip ticket fetched successfully.',
            'data' => $this->mapTicketPayload($ticket, $driverUser),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $driverUser = $this->resolveDriverUser($request);
        if ($driverUser instanceof JsonResponse) {
            return $driverUser;
        }

        $validator = Validator::make($request->all(), [
            'transportation_request_form_id' => ['required', 'integer', 'exists:transportation_requests_forms,id'],
            'request_form_data' => ['nullable', 'array'],
            'departure_time' => ['nullable', 'date'],
            'arrival_time_destination' => ['nullable', 'date'],
            'departure_time_destination' => ['nullable', 'date'],
            'arrival_time_office' => ['nullable', 'date'],
            'odometer_end' => ['nullable', 'numeric'],
            'odometer_start' => ['nullable', 'numeric'],
            'distance_travelled' => ['nullable', 'numeric'],
            'fuel_balance_before' => ['nullable', 'numeric'],
            'fuel_issued_regional' => ['nullable', 'numeric'],
            'fuel_purchased_trip' => ['nullable', 'numeric'],
            'fuel_issued_nia' => ['nullable', 'numeric'],
            'fuel_total' => ['nullable', 'numeric'],
            'fuel_used' => ['nullable', 'numeric'],
            'fuel_balance_after' => ['nullable', 'numeric'],
            'gear_oil_liters' => ['nullable', 'numeric'],
            'engine_oil_liters' => ['nullable', 'numeric'],
            'grease_kgs' => ['nullable', 'numeric'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return response()->json([
                'message' => $errors->first() ?: 'Validation failed.',
                'errors' => $errors,
            ], 422);
        }

        $validated = $validator->validated();

        $transportationRequest = TransportationRequestFormModel::query()
            ->find((int) $validated['transportation_request_form_id']);

        if (!$transportationRequest) {
            return response()->json([
                'message' => 'Transportation request not found.',
            ], 404);
        }

        $assignedDriverNames = $this->parseDriverNames((string) ($transportationRequest->driver_name ?? ''));
        if (!$this->driverMatchesAssignment((string) $driverUser->name, $assignedDriverNames)) {
            return response()->json([
                'message' => 'You are not allowed to submit this trip ticket.',
            ], 403);
        }

        $existingTicket = DailyDriversTripTicket::query()
            ->where('transportation_request_form_id', (int) $validated['transportation_request_form_id'])
            ->first();

        $baseSnapshot = $this->buildRequestFormSnapshot($transportationRequest);
        $existingSnapshot = $existingTicket
            ? $this->decodeRequestFormData($existingTicket->request_form_data)
            : [];
        $payloadSnapshot = is_array($validated['request_form_data'] ?? null)
            ? $validated['request_form_data']
            : [];

        $requestFormData = array_replace($baseSnapshot, $existingSnapshot, $payloadSnapshot);
        $requestFormData['driver_name'] = (string) ($transportationRequest->driver_name ?? '');

        $updateData = [
            'request_form_data' => $requestFormData,
        ];

        foreach (
            [
                'departure_time',
                'arrival_time_destination',
                'departure_time_destination',
                'arrival_time_office',
                'odometer_end',
                'odometer_start',
                'fuel_balance_before',
                'fuel_issued_regional',
                'fuel_purchased_trip',
                'fuel_issued_nia',
                'fuel_used',
                'gear_oil_liters',
                'engine_oil_liters',
                'grease_kgs',
                'remarks',
            ] as $field
        ) {
            if ($request->exists($field)) {
                $updateData[$field] = $validated[$field] ?? null;
            }
        }

        // Formula 1: Distance Travelled = odometer_end - odometer_start
        $odometerStart = $this->resolveNumericField($request, $validated, $existingTicket, 'odometer_start');
        $odometerEnd = $this->resolveNumericField($request, $validated, $existingTicket, 'odometer_end');

        $distanceTravelled = null;
        if ($odometerStart !== null && $odometerEnd !== null) {
            $distanceTravelled = round($odometerEnd - $odometerStart, 2);
        }

        // Formula 2: Total = fuel_balance_before + fuel_issued_regional + fuel_purchased_trip + fuel_issued_nia
        $fuelBalanceBefore = $this->resolveNumericField($request, $validated, $existingTicket, 'fuel_balance_before');
        $fuelIssuedRegional = $this->resolveNumericField($request, $validated, $existingTicket, 'fuel_issued_regional');
        $fuelPurchasedTrip = $this->resolveNumericField($request, $validated, $existingTicket, 'fuel_purchased_trip');
        $fuelIssuedNia = $this->resolveNumericField($request, $validated, $existingTicket, 'fuel_issued_nia');

        $fuelValues = [$fuelBalanceBefore, $fuelIssuedRegional, $fuelPurchasedTrip, $fuelIssuedNia];
        $hasFuelValue = collect($fuelValues)->contains(function ($value) {
            return $value !== null;
        });

        $fuelTotal = $hasFuelValue
            ? round(array_sum(array_map(function ($value) {
                return (float) ($value ?? 0.0);
            }, $fuelValues)), 2)
            : null;

        // Formula 3: Estimated Balance in tank after =
        // Total - (Distance Travelled / (fuel_balance_before + fuel_issued_regional + fuel_purchased_trip + fuel_issued_nia) / 4)
        $fuelBalanceAfter = null;
        if ($fuelTotal !== null && $fuelTotal != 0.0 && $distanceTravelled !== null) {
            $fuelBalanceAfter = round($fuelTotal - (($distanceTravelled / $fuelTotal) / 4), 2);
        }

        $updateData['distance_travelled'] = $distanceTravelled;
        $updateData['fuel_total'] = $fuelTotal;
        $updateData['fuel_balance_after'] = $fuelBalanceAfter;

        $ticket = DailyDriversTripTicket::query()->updateOrCreate(
            ['transportation_request_form_id' => (int) $validated['transportation_request_form_id']],
            $updateData
        );

        return response()->json([
            'message' => $ticket->wasRecentlyCreated
                ? 'Daily trip ticket inserted successfully.'
                : 'Daily trip ticket saved successfully.',
            'data' => $this->mapTicketPayload($ticket, $driverUser),
        ]);
    }

    private function resolveDriverUser(Request $request): User|JsonResponse
    {
        $authUser = $request->user();
        $authUserName = trim((string) ($authUser?->name ?? ''));

        if ($authUserName === '') {
            return response()->json([
                'message' => 'Authenticated user has no name configured.',
                'data' => [],
            ], 422);
        }

        $driverUser = User::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($authUserName)])
            ->first(['id', 'personnel_id', 'name', 'role']);

        if (!$driverUser) {
            return response()->json([
                'message' => 'No driver account matched the authenticated user name.',
                'data' => [],
            ], 404);
        }

        return $driverUser;
    }

    private function ticketBelongsToDriver(DailyDriversTripTicket $ticket, User $driverUser): bool
    {
        $requestFormData = $this->decodeRequestFormData($ticket->request_form_data);
        $driverNames = $this->parseDriverNames($requestFormData['driver_name'] ?? '');

        return $this->driverMatchesAssignment((string) $driverUser->name, $driverNames);
    }

    private function mapTicketPayload(DailyDriversTripTicket $ticket, User $driverUser): array
    {
        $requestFormData = $this->decodeRequestFormData($ticket->request_form_data);

        return [
            'id' => $ticket->id,
            'transportation_request_form_id' => $ticket->transportation_request_form_id,
            'driver' => [
                'id' => $driverUser->id,
                'personnel_id' => $driverUser->personnel_id,
                'name' => $driverUser->name,
                'role' => $driverUser->role,
            ],
            'request_form_data' => $requestFormData,
            'departure_time' => optional($ticket->departure_time)->toDateTimeString(),
            'arrival_time_destination' => optional($ticket->arrival_time_destination)->toDateTimeString(),
            'departure_time_destination' => optional($ticket->departure_time_destination)->toDateTimeString(),
            'arrival_time_office' => optional($ticket->arrival_time_office)->toDateTimeString(),
            'odometer_start' => $ticket->odometer_start,
            'odometer_end' => $ticket->odometer_end,
            'distance_travelled' => $ticket->distance_travelled,


            'fuel_balance_before' => $ticket->fuel_balance_before,
            'fuel_issued_regional' => $ticket->fuel_issued_regional,
            'fuel_purchased_trip' => $ticket->fuel_purchased_trip,
            'fuel_issued_nia' => $ticket->fuel_issued_nia,
            'fuel_total' => $ticket->fuel_total,
            'fuel_used' => $ticket->fuel_used,
            'fuel_balance_after' => $ticket->fuel_balance_after,


            'gear_oil_liters' => $ticket->gear_oil_liters,
            'engine_oil_liters' => $ticket->engine_oil_liters,
            'grease_kgs' => $ticket->grease_kgs,


            'remarks' => $ticket->remarks,
            'created_at' => optional($ticket->created_at)->toDateTimeString(),
            'updated_at' => optional($ticket->updated_at)->toDateTimeString(),
        ];
    }

    private function buildRequestFormSnapshot(TransportationRequestFormModel $transportationRequest): array
    {
        return [
            'transportation_request_form_id' => $transportationRequest->id,
            'form_id' => (string) ($transportationRequest->form_id ?? ''),
            'form_creator_id' => (string) ($transportationRequest->form_creator_id ?? ''),
            'request_date' => optional($transportationRequest->request_date)->toDateString(),
            'requested_by' => (string) ($transportationRequest->requested_by ?? ''),
            'requestor_name' => (string) ($transportationRequest->requestor_name ?? ''),
            'destination' => (string) ($transportationRequest->destination ?? ''),
            'date_time_from' => optional($transportationRequest->date_time_from)->toDateTimeString(),
            'date_time_to' => optional($transportationRequest->date_time_to)->toDateTimeString(),
            'vehicle_type' => (string) ($transportationRequest->vehicle_type ?? ''),
            'vehicle_quantity' => $transportationRequest->vehicle_quantity,
            'vehicle_id' => (string) ($transportationRequest->vehicle_id ?? ''),
            'driver_name' => (string) ($transportationRequest->driver_name ?? ''),
            'status' => (string) ($transportationRequest->status ?? ''),
        ];
    }

    private function decodeRequestFormData(mixed $requestFormData): array
    {
        if (is_array($requestFormData)) {
            return $requestFormData;
        }

        if (is_string($requestFormData) && trim($requestFormData) !== '') {
            $decoded = json_decode($requestFormData, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function resolveNumericField(Request $request, array $validated, ?DailyDriversTripTicket $existingTicket, string $field): ?float
    {
        if ($request->exists($field)) {
            return $this->toNullableFloat($validated[$field] ?? null);
        }

        return $this->toNullableFloat($existingTicket?->{$field});
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseDriverNames(mixed $value): array
    {
        if (is_array($value)) {
            $tokens = $value;
        } else {
            $stringValue = trim((string) $value);
            if ($stringValue === '') {
                return [];
            }

            $decoded = json_decode($stringValue, true);
            if (is_array($decoded)) {
                $tokens = $decoded;
            } else {
                $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $stringValue, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            }
        }

        return collect($tokens)
            ->map(function ($token) {
                if (is_array($token)) {
                    return trim((string) ($token['driver_name'] ?? $token['name'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $name) {
                return $name !== '';
            })
            ->values()
            ->all();
    }

    private function driverMatchesAssignment(string $driverName, array $assignedDriverNames): bool
    {
        $normalizedDriverName = strtolower(trim($driverName));
        if ($normalizedDriverName === '') {
            return false;
        }

        return collect($assignedDriverNames)
            ->map(function (string $name) {
                return strtolower(trim($name));
            })
            ->contains(function (string $name) use ($normalizedDriverName) {
                return $name === $normalizedDriverName;
            });
    }
}
