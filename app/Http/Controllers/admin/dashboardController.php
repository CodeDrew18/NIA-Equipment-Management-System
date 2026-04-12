<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AdminVehicleAvailability;
use App\Models\AuditLog;
use App\Models\DailyDriversTripTicket;
use App\Models\FuelIssuance;
use App\Models\FuelIssuancePartnership;
use App\Models\TransportationRequestFormModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class dashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.dashboard', $this->buildDashboardPayload($request));
    }

    public function dashboardData(Request $request)
    {
        $payload = $this->buildDashboardPayload($request);
        $pendingRequests = $payload['pendingRequests'];

        return response()->json([
            'totalPendingRequests' => $payload['totalPendingRequests'],
            'activeTripTickets' => $payload['activeTripTickets'],
            'activeTripTicketCapacity' => $payload['activeTripTicketCapacity'],
            'trendPercentage' => $payload['trendPercentage'],
            'trendIcon' => $payload['trendIcon'],
            'trendIsPositive' => $payload['trendIsPositive'],
            'moduleSummaries' => $payload['moduleSummaries'],
            'charts' => $payload['charts'],
            'summaryText' => 'Showing ' . $pendingRequests->count() . ' of ' . $pendingRequests->total() . ' to be signed entries',
            'pagination' => [
                'currentPage' => $pendingRequests->currentPage(),
                'lastPage' => $pendingRequests->lastPage(),
                'hasMorePages' => $pendingRequests->hasMorePages(),
                'onFirstPage' => $pendingRequests->onFirstPage(),
                'prevPageUrl' => $pendingRequests->previousPageUrl(),
                'nextPageUrl' => $pendingRequests->nextPageUrl(),
                'pageUrls' => $pendingRequests->getUrlRange(1, $pendingRequests->lastPage()),
            ],
            'requests' => $pendingRequests->map(function ($requestItem) {
                $attachments = is_array($requestItem->attachments) ? $requestItem->attachments : [];

                return [
                    'id' => $requestItem->id,
                    'formId' => (string) ($requestItem->form_id ?? ('Request #' . $requestItem->id)),
                    'requestDate' => optional($requestItem->request_date)->format('M d, Y') ?? 'N/A',
                    'requestorName' => $requestItem->requestor_name,
                    'requestorPosition' => $requestItem->requestor_position,
                    'requestorInitials' => strtoupper(substr($requestItem->requestor_name, 0, 2)),
                    'vehicleType' => $requestItem->vehicle_type,
                    'vehicleQuantity' => $requestItem->vehicle_quantity,
                    'status' => $requestItem->status,
                    'rejectionReason' => (string) ($requestItem->rejection_reason ?? ''),
                    'attachments' => collect($attachments)->values()->map(function ($attachment, $index) use ($requestItem) {
                        return [
                            'name' => (string) ($attachment['file_name'] ?? ('Attachment ' . ($index + 1))),
                            'url' => route('admin.transportation-request.attachment.view', [
                                'transportationRequest' => $requestItem->id,
                                'index' => $index,
                            ]),
                        ];
                    })->all(),
                ];
            })->values(),
        ]);
    }

    private function buildDashboardPayload(Request $request): array
    {
        $perPage = 4;
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;
        $trendPercentage = 0.0;
        $trendIcon = 'trending_flat';
        $trendIsPositive = true;
        $activeTripTickets = 0;
        $activeTripTicketCapacity = 0;
        $requestStatusCounts = $this->buildRequestStatusCounts($from, $to);

        $allRequestsQuery = TransportationRequestFormModel::query()
            ->when($from, function ($query) use ($from) {
                $query->whereDate('request_date', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('request_date', '<=', $to);
            });

        $totalRequestsForPeriod = (clone $allRequestsQuery)->count();

        if (Schema::hasColumn('transportation_requests_forms', 'status')) {
            $pendingRequestsQuery = TransportationRequestFormModel::query()
                ->whereIn('status', ['To be Signed', 'Pending'])
                ->when($from, function ($query) use ($from) {
                    $query->whereDate('request_date', '>=', $from);
                })
                ->when($to, function ($query) use ($to) {
                    $query->whereDate('request_date', '<=', $to);
                });

            $pendingRequests = (clone $pendingRequestsQuery)
                ->orderByDesc('request_date')
                ->orderByDesc('id')
                ->paginate($perPage)
                ->withQueryString();

            $totalPendingRequests = (clone $pendingRequestsQuery)->count();
            $activeTripTickets = (int) ($requestStatusCounts['Dispatched'] ?? 0) + (int) ($requestStatusCounts['On Trip'] ?? 0);
            $activeTripTicketCapacity = $totalRequestsForPeriod > 0
                ? (int) round(($activeTripTickets / $totalRequestsForPeriod) * 100)
                : 0;

            $trendPeriod = $this->resolveTrendPeriod($from, $to);
            if ($trendPeriod !== null) {
                $previousPeriodStart = $trendPeriod['start']->copy()->subDays($trendPeriod['days']);
                $previousPeriodEnd = $trendPeriod['start']->copy()->subDay();

                $currentPeriodCount = TransportationRequestFormModel::query()
                    ->whereIn('status', ['To be Signed', 'Pending'])
                    ->whereDate('request_date', '>=', $trendPeriod['start']->toDateString())
                    ->whereDate('request_date', '<=', $trendPeriod['end']->toDateString())
                    ->count();

                $previousPeriodCount = TransportationRequestFormModel::query()
                    ->whereIn('status', ['To be Signed', 'Pending'])
                    ->whereDate('request_date', '>=', $previousPeriodStart->toDateString())
                    ->whereDate('request_date', '<=', $previousPeriodEnd->toDateString())
                    ->count();

                if ($previousPeriodCount > 0) {
                    $trendPercentage = round((($currentPeriodCount - $previousPeriodCount) / $previousPeriodCount) * 100, 1);
                } elseif ($currentPeriodCount > 0) {
                    $trendPercentage = 100.0;
                }

                $trendIcon = $trendPercentage > 0 ? 'trending_up' : ($trendPercentage < 0 ? 'trending_down' : 'trending_flat');
                $trendIsPositive = $trendPercentage >= 0;
            }
        } else {
            $pendingRequestsQuery = TransportationRequestFormModel::query()
                ->when($from, function ($query) use ($from) {
                    $query->whereDate('request_date', '>=', $from);
                })
                ->when($to, function ($query) use ($to) {
                    $query->whereDate('request_date', '<=', $to);
                });

            $pendingRequests = (clone $pendingRequestsQuery)
                ->orderByDesc('request_date')
                ->orderByDesc('id')
                ->paginate($perPage)
                ->withQueryString();

            $totalPendingRequests = (clone $pendingRequestsQuery)->count();
        }

        $signedAssignmentMetrics = $this->buildSignedAssignmentMetrics($from, $to);
        $vehicleStatusCounts = $this->buildVehicleStatusCounts();
        $dttMetrics = $this->buildDailyTripTicketMetrics($from, $to);
        $fuelIssuanceMetrics = $this->buildFuelIssuanceMetrics($from, $to);
        $fuelPartnershipMetrics = $this->buildFuelPartnershipMetrics();
        $auditMetrics = $this->buildAuditMetrics($from, $to);
        $userRoleMetrics = $this->buildUserRoleMetrics();

        $moduleSummaries = $this->buildModuleSummaries(
            $totalRequestsForPeriod,
            $requestStatusCounts,
            $signedAssignmentMetrics,
            $vehicleStatusCounts,
            $dttMetrics,
            $fuelIssuanceMetrics,
            $fuelPartnershipMetrics,
            $auditMetrics,
            $userRoleMetrics
        );

        $requestLifecycleLabels = ['To be Signed', 'Signed', 'Dispatched', 'On Trip', 'For Evaluation', 'Completed', 'Rejected'];
        $vehicleStatusLabels = ['Available', 'Reserved', 'On Business Trip', 'Maintenance', 'Unavailable'];

        $charts = [
            'requestLifecycle' => [
                'labels' => $requestLifecycleLabels,
                'values' => collect($requestLifecycleLabels)
                    ->map(function (string $status) use ($requestStatusCounts) {
                        return (int) ($requestStatusCounts[$status] ?? 0);
                    })
                    ->all(),
            ],
            'vehicleStatus' => [
                'labels' => $vehicleStatusLabels,
                'values' => collect($vehicleStatusLabels)
                    ->map(function (string $status) use ($vehicleStatusCounts) {
                        return (int) ($vehicleStatusCounts[$status] ?? 0);
                    })
                    ->all(),
            ],
            'requestVolume' => $this->buildRequestVolumeSeries($from, $to),
        ];

        return [
            'pendingRequests' => $pendingRequests,
            'totalPendingRequests' => $totalPendingRequests,
            'activeTripTickets' => $activeTripTickets,
            'activeTripTicketCapacity' => $activeTripTicketCapacity,
            'fromDate' => $from,
            'toDate' => $to,
            'trendPercentage' => $trendPercentage,
            'trendIcon' => $trendIcon,
            'trendIsPositive' => $trendIsPositive,
            'moduleSummaries' => $moduleSummaries,
            'charts' => $charts,
        ];
    }

    private function resolveTrendPeriod(?string $from, ?string $to): ?array
    {
        if ($from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
        } elseif (!$from && !$to) {
            $end = now()->endOfDay();
            $start = now()->subDays(29)->startOfDay();
        } else {
            return null;
        }

        if ($start->greaterThan($end)) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
            'days' => $start->diffInDays($end) + 1,
        ];
    }

    private function applyRequestDateFilters(Builder $query, ?string $from, ?string $to): void
    {
        if ($from) {
            $query->whereDate('request_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('request_date', '<=', $to);
        }
    }

    private function buildRequestStatusCounts(?string $from, ?string $to): array
    {
        $statusOrder = ['To be Signed', 'Signed', 'Dispatched', 'On Trip', 'For Evaluation', 'Completed', 'Rejected'];
        $counts = array_fill_keys($statusOrder, 0);

        if (!Schema::hasTable('transportation_requests_forms') || !Schema::hasColumn('transportation_requests_forms', 'status')) {
            return $counts;
        }

        $statusRows = TransportationRequestFormModel::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->when($from, function (Builder $query) use ($from) {
                $query->whereDate('request_date', '>=', $from);
            })
            ->when($to, function (Builder $query) use ($to) {
                $query->whereDate('request_date', '<=', $to);
            })
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        foreach ($statusRows as $status => $total) {
            $normalizedStatus = trim((string) $status);
            $count = (int) $total;

            if ($normalizedStatus === 'Pending') {
                $counts['To be Signed'] += $count;
                continue;
            }

            if (array_key_exists($normalizedStatus, $counts)) {
                $counts[$normalizedStatus] = $count;
            }
        }

        return $counts;
    }

    private function buildSignedAssignmentMetrics(?string $from, ?string $to): array
    {
        if (!Schema::hasTable('transportation_requests_forms') || !Schema::hasColumn('transportation_requests_forms', 'status')) {
            return [
                'forAssignment' => 0,
                'readyForDtt' => 0,
            ];
        }

        $signedBaseQuery = TransportationRequestFormModel::query()
            ->where('status', 'Signed');
        $this->applyRequestDateFilters($signedBaseQuery, $from, $to);

        $forAssignment = (clone $signedBaseQuery)
            ->where(function (Builder $query) {
                $query->whereNull('vehicle_id')
                    ->orWhere('vehicle_id', '')
                    ->orWhereNull('driver_name')
                    ->orWhere('driver_name', '');
            })
            ->count();

        $readyForDtt = (clone $signedBaseQuery)
            ->whereNotNull('vehicle_id')
            ->where('vehicle_id', '!=', '')
            ->whereNotNull('driver_name')
            ->where('driver_name', '!=', '')
            ->count();

        return [
            'forAssignment' => $forAssignment,
            'readyForDtt' => $readyForDtt,
        ];
    }

    private function buildVehicleStatusCounts(): array
    {
        $statusOrder = ['Available', 'Reserved', 'On Business Trip', 'Maintenance', 'Unavailable'];
        $counts = array_fill_keys($statusOrder, 0);

        if (!Schema::hasTable('admin_vehicle_availability') || !Schema::hasColumn('admin_vehicle_availability', 'status')) {
            $counts['total'] = 0;

            return $counts;
        }

        $statusRows = AdminVehicleAvailability::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        foreach ($statusRows as $status => $total) {
            $normalizedStatus = trim((string) $status);
            if (array_key_exists($normalizedStatus, $counts)) {
                $counts[$normalizedStatus] = (int) $total;
            }
        }

        $counts['total'] = array_sum($counts);

        return $counts;
    }

    private function buildDailyTripTicketMetrics(?string $from, ?string $to): array
    {
        if (!Schema::hasTable('daily_drivers_trip_ticket')) {
            return [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
            ];
        }

        $dttQuery = DailyDriversTripTicket::query()
            ->when($from || $to, function (Builder $query) use ($from, $to) {
                $query->whereHas('transportationRequestForm', function (Builder $requestQuery) use ($from, $to) {
                    $this->applyRequestDateFilters($requestQuery, $from, $to);
                });
            });

        $total = (clone $dttQuery)->count();
        $completed = (clone $dttQuery)->whereNotNull('arrival_time_office')->count();

        return [
            'total' => $total,
            'pending' => max(0, $total - $completed),
            'completed' => $completed,
        ];
    }

    private function buildFuelIssuanceMetrics(?string $from, ?string $to): array
    {
        if (!Schema::hasTable('fuel_issuance')) {
            return [
                'records' => 0,
                'totalAmount' => 0.0,
            ];
        }

        $fuelQuery = FuelIssuance::query()
            ->when($from || $to, function (Builder $query) use ($from, $to) {
                $query->whereHas('transportationRequestForm', function (Builder $requestQuery) use ($from, $to) {
                    $this->applyRequestDateFilters($requestQuery, $from, $to);
                });
            });

        $records = (clone $fuelQuery)->count();
        $totalAmount = Schema::hasColumn('fuel_issuance', 'total_amount')
            ? round((float) ((clone $fuelQuery)->sum('total_amount') ?? 0), 2)
            : 0.0;

        return [
            'records' => $records,
            'totalAmount' => $totalAmount,
        ];
    }

    private function buildFuelPartnershipMetrics(): array
    {
        if (!Schema::hasTable('fuel_issuance_partnership')) {
            return [
                'total' => 0,
                'active' => 0,
            ];
        }

        $total = FuelIssuancePartnership::query()->count();
        $active = Schema::hasColumn('fuel_issuance_partnership', 'is_active')
            ? FuelIssuancePartnership::query()->where('is_active', true)->count()
            : $total;

        return [
            'total' => $total,
            'active' => $active,
        ];
    }

    private function buildAuditMetrics(?string $from, ?string $to): array
    {
        if (!Schema::hasTable('audit_logs')) {
            return [
                'events' => 0,
                'securityAlerts' => 0,
                'activeUsers' => 0,
            ];
        }

        $auditQuery = AuditLog::query()
            ->when($from, function (Builder $query) use ($from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when($to, function (Builder $query) use ($to) {
                $query->whereDate('created_at', '<=', $to);
            });

        $events = (clone $auditQuery)->count();
        $securityAlerts = Schema::hasColumn('audit_logs', 'status')
            ? (clone $auditQuery)->whereIn('status', ['FAILED', 'WARNING'])->count()
            : 0;
        $activeUsers = Schema::hasColumn('audit_logs', 'personnel_id')
            ? (clone $auditQuery)
            ->whereNotNull('personnel_id')
            ->where('personnel_id', '!=', '')
            ->distinct('personnel_id')
            ->count('personnel_id')
            : 0;

        return [
            'events' => $events,
            'securityAlerts' => $securityAlerts,
            'activeUsers' => $activeUsers,
        ];
    }

    private function buildUserRoleMetrics(): array
    {
        if (!Schema::hasTable('users')) {
            return [
                'totalUsers' => 0,
                'adminUsers' => 0,
                'driverUsers' => 0,
            ];
        }

        $totalUsers = User::query()->count();
        if (!Schema::hasColumn('users', 'role')) {
            return [
                'totalUsers' => $totalUsers,
                'adminUsers' => 0,
                'driverUsers' => 0,
            ];
        }

        return [
            'totalUsers' => $totalUsers,
            'adminUsers' => User::query()->whereRaw("CONCAT(',', role, ',') LIKE '%,admin,%'")->count(),
            'driverUsers' => User::query()->whereRaw("CONCAT(',', role, ',') LIKE '%,driver,%'")->count(),
        ];
    }

    private function buildModuleSummaries(
        int $totalRequestsForPeriod,
        array $requestStatusCounts,
        array $signedAssignmentMetrics,
        array $vehicleStatusCounts,
        array $dttMetrics,
        array $fuelIssuanceMetrics,
        array $fuelPartnershipMetrics,
        array $auditMetrics,
        array $userRoleMetrics
    ): array {
        return [
            [
                'key' => 'transportation_requests',
                'label' => 'Transportation Requests',
                'value' => $totalRequestsForPeriod,
                'description' => number_format((int) ($requestStatusCounts['To be Signed'] ?? 0)) . ' awaiting admin approval',
                'icon' => 'description',
            ],
            [
                'key' => 'vehicle_assignment',
                'label' => 'Vehicle Assignment Queue',
                'value' => (int) ($signedAssignmentMetrics['forAssignment'] ?? 0),
                'description' => number_format((int) ($signedAssignmentMetrics['readyForDtt'] ?? 0)) . ' signed requests already assigned',
                'icon' => 'assignment_ind',
            ],
            [
                'key' => 'daily_trip_ticket',
                'label' => 'Daily Trip Ticket Pending',
                'value' => (int) ($dttMetrics['pending'] ?? 0),
                'description' => number_format((int) ($dttMetrics['completed'] ?? 0)) . ' tickets completed',
                'icon' => 'confirmation_number',
            ],
            [
                'key' => 'fuel_issuance',
                'label' => 'Fuel Issuance Queue',
                'value' => (int) ($requestStatusCounts['Dispatched'] ?? 0),
                'description' => number_format((int) ($fuelIssuanceMetrics['records'] ?? 0)) . ' issuance records saved',
                'icon' => 'local_gas_station',
            ],
            [
                'key' => 'on_trip_vehicles',
                'label' => 'On Trip Monitoring',
                'value' => (int) ($requestStatusCounts['On Trip'] ?? 0),
                'description' => number_format((int) ($requestStatusCounts['For Evaluation'] ?? 0)) . ' moved to evaluation queue',
                'icon' => 'commute',
            ],
            [
                'key' => 'vehicle_availability',
                'label' => 'Available Vehicles',
                'value' => (int) ($vehicleStatusCounts['Available'] ?? 0),
                'description' => number_format((int) ($vehicleStatusCounts['On Business Trip'] ?? 0)) . ' on business trip, ' . number_format((int) ($vehicleStatusCounts['Reserved'] ?? 0)) . ' reserved',
                'icon' => 'directions_car',
            ],
            [
                'key' => 'fuel_partnerships',
                'label' => 'Active Fuel Partnerships',
                'value' => (int) ($fuelPartnershipMetrics['active'] ?? 0),
                'description' => number_format((int) ($fuelPartnershipMetrics['total'] ?? 0)) . ' total partnerships configured',
                'icon' => 'handshake',
            ],
            [
                'key' => 'audit_logs',
                'label' => 'Audit Log Events',
                'value' => (int) ($auditMetrics['events'] ?? 0),
                'description' => number_format((int) ($auditMetrics['securityAlerts'] ?? 0)) . ' security alerts in period',
                'icon' => 'rule',
            ],
            [
                'key' => 'user_roles',
                'label' => 'Registered User Accounts',
                'value' => (int) ($userRoleMetrics['totalUsers'] ?? 0),
                'description' => number_format((int) ($userRoleMetrics['adminUsers'] ?? 0)) . ' admin, ' . number_format((int) ($userRoleMetrics['driverUsers'] ?? 0)) . ' drivers',
                'icon' => 'groups',
            ],
        ];
    }

    private function buildRequestVolumeSeries(?string $from, ?string $to): array
    {
        if (!Schema::hasTable('transportation_requests_forms')) {
            return [
                'labels' => [],
                'values' => [],
            ];
        }

        if ($from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
        } elseif ($from && !$to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = now()->endOfDay();
        } elseif (!$from && $to) {
            $end = Carbon::parse($to)->endOfDay();
            $start = $end->copy()->subDays(13)->startOfDay();
        } else {
            $end = now()->endOfDay();
            $start = now()->subDays(13)->startOfDay();
        }

        if ($start->greaterThan($end)) {
            return [
                'labels' => [],
                'values' => [],
            ];
        }

        $rows = TransportationRequestFormModel::query()
            ->selectRaw('DATE(request_date) as request_day, COUNT(*) as aggregate')
            ->whereDate('request_date', '>=', $start->toDateString())
            ->whereDate('request_date', '<=', $end->toDateString())
            ->groupBy('request_day')
            ->orderBy('request_day')
            ->pluck('aggregate', 'request_day');

        $labels = [];
        $values = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            $labels[] = $cursor->format('M d');
            $values[] = (int) ($rows[$dateKey] ?? 0);
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function updateRequestStatus(Request $request, TransportationRequestFormModel $transportationRequest)
    {
        if (!Schema::hasColumn('transportation_requests_forms', 'status')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('admin_dashboard_success', 'Status column is not available yet. Run migrations first.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Signed,Rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:2000', 'required_if:status,Rejected'],
        ]);

        $previousVehicleCodes = $this->extractVehicleCodes((string) $transportationRequest->vehicle_id);

        $updatePayload = [
            'status' => $validated['status'],
            'rejection_reason' => $validated['status'] === 'Rejected'
                ? trim((string) ($validated['rejection_reason'] ?? ''))
                : null,
        ];

        // Approval must always pass through admin vehicle assignment first.
        if (in_array($validated['status'], ['Signed', 'Rejected'], true)) {
            $updatePayload['vehicle_id'] = null;
            $updatePayload['driver_name'] = null;
        }

        DB::transaction(function () use ($transportationRequest, $updatePayload, $previousVehicleCodes) {
            $transportationRequest->update($updatePayload);
            $this->releaseVehicleCodes($previousVehicleCodes);
        });

        if ($validated['status'] === 'Signed') {
            return redirect()
                ->route('admin.vehicle_assignment', ['request' => $transportationRequest->id])
                ->with('admin_vehicle_assignment_success', 'Request approved. Assign an available vehicle before generating the Daily Driver\'s Trip Ticket.');
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('admin_dashboard_success', 'Request status updated to ' . $validated['status'] . '.');
    }

    private function releaseVehicleCodes(array $vehicleCodes): void
    {
        if (empty($vehicleCodes)) {
            return;
        }

        AdminVehicleAvailability::query()
            ->whereIn('vehicle_code', $vehicleCodes)
            ->whereIn('status', ['On Business Trip', 'Reserved'])
            ->update([
                'status' => 'Available',
            ]);
    }

    private function extractVehicleCodes(string $vehicleIds): array
    {
        $value = trim($vehicleIds);
        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $tokens = $decoded;
        } else {
            $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
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
            ->unique()
            ->values()
            ->all();
    }
}
