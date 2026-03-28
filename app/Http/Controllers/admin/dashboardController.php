<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TransportationRequestFormModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
                return [
                    'id' => $requestItem->id,
                    'requestDate' => optional($requestItem->request_date)->format('M d, Y') ?? 'N/A',
                    'requestorName' => $requestItem->requestor_name,
                    'requestorPosition' => $requestItem->requestor_position,
                    'requestorInitials' => strtoupper(substr($requestItem->requestor_name, 0, 2)),
                    'vehicleType' => $requestItem->vehicle_type,
                    'vehicleQuantity' => $requestItem->vehicle_quantity,
                    'status' => $requestItem->status,
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
                ->where('status', 'To be Signed')
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
            $activeTripTickets = (clone $allRequestsQuery)->where('status', 'Dispatched')->count();
            $activeTripTicketCapacity = $totalRequestsForPeriod > 0
                ? (int) round(($activeTripTickets / $totalRequestsForPeriod) * 100)
                : 0;

            $trendPeriod = $this->resolveTrendPeriod($from, $to);
            if ($trendPeriod !== null) {
                $previousPeriodStart = $trendPeriod['start']->copy()->subDays($trendPeriod['days']);
                $previousPeriodEnd = $trendPeriod['start']->copy()->subDay();

                $currentPeriodCount = TransportationRequestFormModel::query()
                    ->where('status', 'To be Signed')
                    ->whereDate('request_date', '>=', $trendPeriod['start']->toDateString())
                    ->whereDate('request_date', '<=', $trendPeriod['end']->toDateString())
                    ->count();

                $previousPeriodCount = TransportationRequestFormModel::query()
                    ->where('status', 'To be Signed')
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

    public function updateRequestStatus(Request $request, TransportationRequestFormModel $transportationRequest)
    {
        if (!Schema::hasColumn('transportation_requests_forms', 'status')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('admin_dashboard_success', 'Status column is not available yet. Run migrations first.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Approved,Rejected'],
        ]);

        $transportationRequest->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('admin_dashboard_success', 'Request status updated to ' . $validated['status'] . '.');
    }
}
