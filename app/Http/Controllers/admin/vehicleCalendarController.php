<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View as ViewContract;

class vehicleCalendarController extends Controller
{
    private const CALENDAR_STATUSES = ['On Trip'];

    public function index(Request $request): ViewContract
    {
        $monthInput = trim((string) $request->query('month', now()->format('Y-m')));
        $vehicleTypeFilter = strtolower(trim((string) $request->query('vehicle_type', 'all')));
        $statusFilter = 'On Trip';

        try {
            $selectedMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable $exception) {
            $selectedMonth = now()->startOfMonth();
        }

        $monthStart = $selectedMonth->copy()->startOfMonth();
        $monthEnd = $selectedMonth->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $allRequests = TransportationRequestFormModel::query()
            ->select([
                'id',
                'form_id',
                'request_date',
                'requested_by',
                'destination',
                'date_time_from',
                'date_time_to',
                'vehicle_id',
                'driver_name',
                'status',
            ])
            ->whereNotNull('vehicle_id')
            ->where('vehicle_id', '!=', '')
            ->whereIn('status', self::CALENDAR_STATUSES)
            ->orderByDesc('date_time_from')
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->get();

        $calendarItems = $allRequests
            ->map(function (TransportationRequestFormModel $requestItem) use ($monthStart, $monthEnd) {
                [$rangeStart, $rangeEnd] = $this->resolveRequestRange($requestItem);

                if ($rangeEnd->lt($monthStart) || $rangeStart->gt($monthEnd)) {
                    return null;
                }

                return [
                    'request' => $requestItem,
                    'start' => $rangeStart->copy(),
                    'end' => $rangeEnd->copy(),
                    'vehicleCodes' => $this->extractVehicleCodes((string) $requestItem->vehicle_id),
                    'driverNames' => $this->extractDriverNames((string) $requestItem->driver_name),
                ];
            })
            ->filter()
            ->values();

        $vehicleCodes = $calendarItems
            ->flatMap(function (array $item) {
                return $item['vehicleCodes'];
            })
            ->filter()
            ->unique()
            ->values();

        $vehiclesByCode = AdminVehicleAvailability::query()
            ->whereIn('vehicle_code', $vehicleCodes)
            ->get()
            ->keyBy('vehicle_code');

        $calendarEvents = $calendarItems->flatMap(function (array $item) use ($monthStart, $monthEnd, $vehiclesByCode, $vehicleTypeFilter, $statusFilter) {
            $requestItem = $item['request'];
            $start = $item['start'];
            $end = $item['end'];
            $vehicleCodes = $item['vehicleCodes'];
            $driverNames = $item['driverNames'];
            $eventRows = [];

            foreach ($vehicleCodes as $index => $vehicleCode) {
                $vehicleRecord = $vehiclesByCode->get($vehicleCode);
                $vehicleType = $this->resolveVehicleType($vehicleCode, $vehicleRecord);

                if ($vehicleTypeFilter !== 'all' && $vehicleType !== $vehicleTypeFilter) {
                    continue;
                }

                if ((string) $requestItem->status !== $statusFilter) {
                    continue;
                }

                $driverName = trim((string) ($driverNames[$index] ?? ($vehicleRecord?->driver_name ?? '')));
                $current = $start->copy()->greaterThan($monthStart) ? $start->copy() : $monthStart->copy();
                $rangeEnd = $end->copy()->lessThan($monthEnd) ? $end->copy() : $monthEnd->copy();

                while ($current->lte($rangeEnd)) {
                    $eventRows[] = [
                        'date' => $current->toDateString(),
                        'requestId' => (int) $requestItem->id,
                        'formId' => (string) $requestItem->form_id,
                        'destination' => (string) ($requestItem->destination ?: 'N/A'),
                        'status' => (string) $requestItem->status,
                        'rangeStart' => $start->toDateString(),
                        'rangeEnd' => $end->toDateString(),
                        'vehicleCode' => $vehicleCode,
                        'vehicleType' => $vehicleType,
                        'vehicleLabel' => (string) ($vehicleRecord?->vehicle_type ?: $this->vehicleTypeLabel($vehicleType)),
                        'capacityLabel' => (string) ($vehicleRecord?->capacity_label ?? ''),
                        'driverName' => $driverName !== '' ? $driverName : 'N/A',
                        'timeLabel' => $this->resolveTimeLabel($requestItem),
                    ];

                    $current->addDay();
                }
            }

            return $eventRows;
        })->values();

        $groupedEvents = $calendarEvents
            ->groupBy('date')
            ->map(function (Collection $events) {
                return $events
                    ->sortBy(function (array $event) {
                        return $event['status'] . '|' . $event['formId'] . '|' . $event['vehicleCode'];
                    })
                    ->values()
                    ->all();
            })
            ->all();

        $scheduleEntries = collect($calendarEvents)
            ->unique(function (array $event) {
                return $event['requestId'] . '|' . $event['vehicleCode'];
            })
            ->values();

        $weekCount = max(1, (int) ceil(($gridStart->diffInDays($gridEnd) + 1) / 7));
        $calendarWeeks = [];

        for ($weekIndex = 0; $weekIndex < $weekCount; $weekIndex++) {
            $weekStart = $gridStart->copy()->addWeeks($weekIndex);
            $weekDays = [];

            for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                $dayDate = $weekStart->copy()->addDays($dayOffset);
                $dayKey = $dayDate->toDateString();

                $weekDays[] = [
                    'date' => $dayDate,
                    'isCurrentMonth' => $dayDate->month === $monthStart->month,
                    'isToday' => $dayDate->isToday(),
                    'events' => $groupedEvents[$dayKey] ?? [],
                ];
            }

            $calendarWeeks[] = [
                'index' => $weekIndex,
                'start' => $weekStart->copy(),
                'days' => $weekDays,
                'segments' => [],
                'laneCount' => 1,
            ];
        }

        $weekSegments = array_fill(0, $weekCount, []);

        foreach ($scheduleEntries as $event) {
            $eventStart = Carbon::parse((string) $event['rangeStart'])->startOfDay();
            $eventEnd = Carbon::parse((string) $event['rangeEnd'])->endOfDay();

            if ($eventEnd->lt($gridStart) || $eventStart->gt($gridEnd)) {
                continue;
            }

            for ($weekIndex = 0; $weekIndex < $weekCount; $weekIndex++) {
                $weekStart = $gridStart->copy()->addWeeks($weekIndex)->startOfDay();
                $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();

                if ($eventEnd->lt($weekStart) || $eventStart->gt($weekEnd)) {
                    continue;
                }

                $segmentStart = $eventStart->greaterThan($weekStart) ? $eventStart->copy() : $weekStart->copy();
                $segmentEnd = $eventEnd->lessThan($weekEnd) ? $eventEnd->copy() : $weekEnd->copy();

                $startColumn = $weekStart->diffInDays($segmentStart->copy()->startOfDay()) + 1;
                $endColumn = $weekStart->diffInDays($segmentEnd->copy()->startOfDay()) + 1;
                $span = max(1, $endColumn - $startColumn + 1);

                $weekSegments[$weekIndex][] = [
                    'startColumn' => $startColumn,
                    'endColumn' => $endColumn,
                    'span' => $span,
                    'event' => $event,
                ];
            }
        }

        foreach ($weekSegments as $weekIndex => $segments) {
            usort($segments, function (array $left, array $right) {
                return [$left['startColumn'], $left['endColumn'], $left['event']['formId'], $left['event']['vehicleCode']]
                    <=> [$right['startColumn'], $right['endColumn'], $right['event']['formId'], $right['event']['vehicleCode']];
            });

            $laneEndColumns = [];

            foreach ($segments as $segment) {
                $laneIndex = 0;

                while (array_key_exists($laneIndex, $laneEndColumns) && $laneEndColumns[$laneIndex] >= $segment['startColumn']) {
                    $laneIndex += 1;
                }

                $laneEndColumns[$laneIndex] = $segment['endColumn'];
                $segment['lane'] = $laneIndex;
                $calendarWeeks[$weekIndex]['segments'][] = $segment;
                $calendarWeeks[$weekIndex]['laneCount'] = max($calendarWeeks[$weekIndex]['laneCount'], $laneIndex + 1);
            }

            $calendarWeeks[$weekIndex]['segments'] = collect($calendarWeeks[$weekIndex]['segments'])
                ->sortBy(function (array $segment) {
                    return $segment['lane'] . '|' . $segment['startColumn'] . '|' . $segment['event']['formId'];
                })
                ->values()
                ->all();
        }

        $calendarDays = [];
        $cursor = $gridStart->copy();

        while ($cursor->lte($gridEnd)) {
            $calendarDays[] = [
                'date' => $cursor->copy(),
                'isCurrentMonth' => $cursor->month === $monthStart->month,
                'isToday' => $cursor->isToday(),
                'events' => $groupedEvents[$cursor->toDateString()] ?? [],
            ];

            $cursor->addDay();
        }

        $statusCounts = collect(self::CALENDAR_STATUSES)
            ->mapWithKeys(function (string $status) use ($scheduleEntries) {
                return [$status => $scheduleEntries->where('status', $status)->count()];
            })
            ->all();

        $typeCounts = collect(['coaster', 'van', 'pickup'])
            ->mapWithKeys(function (string $type) use ($scheduleEntries) {
                return [$type => $scheduleEntries->where('vehicleType', $type)->count()];
            })
            ->all();

        $upcomingEvents = collect($calendarEvents)
            ->sortBy(function (array $event) {
                return $event['date'] . '|' . $event['timeLabel'] . '|' . $event['formId'] . '|' . $event['vehicleCode'];
            })
            ->take(8)
            ->values()
            ->all();

        return view('admin.vehicle_calendar.vehicle_calendar', [
            'calendarTitle' => $selectedMonth->format('F Y'),
            'calendarMonth' => $selectedMonth,
            'calendarDays' => $calendarDays,
            'calendarWeeks' => $calendarWeeks,
            'upcomingEvents' => $upcomingEvents,
            'calendarEventCount' => $scheduleEntries->count(),
            'statusCounts' => $statusCounts,
            'typeCounts' => $typeCounts,
            'selectedVehicleType' => $vehicleTypeFilter,
            'selectedStatus' => $statusFilter,
            'vehicleTypeOptions' => [
                'all' => 'All Vehicles',
                'coaster' => 'Coaster',
                'van' => 'Van',
                'pickup' => 'Pick-up',
            ],
            'statusOptions' => [
                'On Trip' => 'On Trip',
            ],
            'previousMonthUrl' => route('admin.vehicle_calendar', array_filter([
                'month' => $selectedMonth->copy()->subMonth()->format('Y-m'),
                'vehicle_type' => $vehicleTypeFilter !== 'all' ? $vehicleTypeFilter : null,
                'status' => $statusFilter,
            ], static fn($value) => $value !== null)),
            'nextMonthUrl' => route('admin.vehicle_calendar', array_filter([
                'month' => $selectedMonth->copy()->addMonth()->format('Y-m'),
                'vehicle_type' => $vehicleTypeFilter !== 'all' ? $vehicleTypeFilter : null,
                'status' => $statusFilter,
            ], static fn($value) => $value !== null)),
            'todayMonthUrl' => route('admin.vehicle_calendar', array_filter([
                'month' => now()->format('Y-m'),
                'vehicle_type' => $vehicleTypeFilter !== 'all' ? $vehicleTypeFilter : null,
                'status' => $statusFilter,
            ], static fn($value) => $value !== null)),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRequestRange(TransportationRequestFormModel $requestItem): array
    {
        $start = $requestItem->date_time_from
            ? Carbon::parse((string) $requestItem->date_time_from)
            : ($requestItem->request_date ? Carbon::parse((string) $requestItem->request_date) : now());

        $end = $requestItem->date_time_to
            ? Carbon::parse((string) $requestItem->date_time_to)
            : $start->copy();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }

    /**
     * @return array<int, string>
     */
    private function extractVehicleCodes(string $vehicleId): array
    {
        return collect(preg_split('/[\s,;]+/', $vehicleId) ?: [])
            ->map(function (string $value) {
                return trim($value);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function extractDriverNames(string $driverName): array
    {
        return collect(preg_split('/[\s,;]+/', $driverName) ?: [])
            ->map(function (string $value) {
                return trim($value);
            })
            ->filter()
            ->values()
            ->all();
    }

    private function resolveVehicleType(string $vehicleCode, ?AdminVehicleAvailability $vehicleRecord = null): string
    {
        if ($vehicleRecord && $vehicleRecord->vehicle_type !== '') {
            return strtolower(trim((string) $vehicleRecord->vehicle_type));
        }

        $code = strtoupper(trim($vehicleCode));

        if (str_contains($code, '-CO-')) {
            return 'coaster';
        }

        if (str_contains($code, '-VN-')) {
            return 'van';
        }

        if (str_contains($code, '-PU-')) {
            return 'pickup';
        }

        return 'other';
    }

    private function vehicleTypeLabel(string $vehicleType): string
    {
        return match ($vehicleType) {
            'coaster' => 'Coaster',
            'van' => 'Van',
            'pickup' => 'Pick-up',
            default => 'Vehicle',
        };
    }

    private function resolveTimeLabel(TransportationRequestFormModel $requestItem): string
    {
        if ($requestItem->date_time_from && $requestItem->date_time_to) {
            return Carbon::parse((string) $requestItem->date_time_from)->format('h:i A') . ' - ' . Carbon::parse((string) $requestItem->date_time_to)->format('h:i A');
        }

        if ($requestItem->date_time_from) {
            return Carbon::parse((string) $requestItem->date_time_from)->format('h:i A');
        }

        return 'All day';
    }
}
