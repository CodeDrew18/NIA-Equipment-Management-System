<?php

namespace App\Http\Controllers;

use App\Models\DriverPerformanceEvaluation;
use App\Models\TransportationRequestFormModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function index(): View
    {
        $personnelId = (string) (Auth::user()?->personnel_id ?? '');

        return view('dashboard.user_dashboard', $this->buildDashboardPayload($personnelId));
    }

    public function data(Request $request): JsonResponse
    {
        $personnelId = (string) ($request->user()?->personnel_id ?? '');

        return response()->json($this->buildDashboardPayload($personnelId));
    }

    public function requestOverview(Request $request): View
    {
        $personnelId = (string) ($request->user()?->personnel_id ?? '');
        $search = trim((string) $request->query('search', ''));

        $requests = TransportationRequestFormModel::query()
            ->where('form_creator_id', $personnelId)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchTerm = '%' . $search . '%';

                    $searchQuery
                        ->where('form_id', 'like', $searchTerm)
                        ->orWhere('requested_by', 'like', $searchTerm)
                        ->orWhere('destination', 'like', $searchTerm)
                        ->orWhere('purpose', 'like', $searchTerm)
                        ->orWhere('status', 'like', $searchTerm);
                });
            })
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('dashboard.request_overview', [
            'requests' => $requests,
            'search' => $search,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboardPayload(string $personnelId): array
    {
        $trendMonths = collect(range(5, 0))
            ->map(function (int $offset) {
                return now()->startOfMonth()->subMonths($offset);
            })
            ->values();

        $trendLabels = $trendMonths
            ->map(function (Carbon $month) {
                return $month->format('M Y');
            })
            ->all();

        $statusOrder = [
            'To be Signed',
            'Signed',
            'Dispatched',
            'On Trip',
            'For Evaluation',
            'Completed',
            'Rejected',
        ];

        if ($personnelId === '') {
            return [
                'requestsThisMonth' => 0,
                'activeTrips' => 0,
                'completedTrips' => 0,
                'pendingEvaluations' => 0,
                'recentRequests' => [],
                'statusChartLabels' => $statusOrder,
                'statusChartValues' => array_fill(0, count($statusOrder), 0),
                'trendChartLabels' => $trendLabels,
                'trendChartValues' => array_fill(0, count($trendLabels), 0),
                'refreshedAt' => now()->toDateTimeString(),
            ];
        }

        $baseRequestsQuery = TransportationRequestFormModel::query()
            ->where('form_creator_id', $personnelId);

        $requestsThisMonth = (clone $baseRequestsQuery)
            ->whereYear('request_date', (int) now()->year)
            ->whereMonth('request_date', (int) now()->month)
            ->count();

        $activeTrips = (clone $baseRequestsQuery)
            ->whereIn('status', ['Signed', 'Dispatched', 'On Trip', 'For Evaluation'])
            ->count();

        $completedTrips = (clone $baseRequestsQuery)
            ->where('status', 'Completed')
            ->count();

        $pendingEvaluations = DriverPerformanceEvaluation::query()
            ->where('status', 'Pending')
            ->whereHas('transportationRequestForm', function (Builder $query) use ($personnelId) {
                $query->where('form_creator_id', $personnelId)
                    ->whereIn('status', ['For Evaluation', 'Completed']);
            })
            ->count();

        $recentRequests = (clone $baseRequestsQuery)
            ->select(['id', 'form_id', 'request_date', 'destination', 'status', 'date_time_to'])
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(function (TransportationRequestFormModel $request): array {
                return [
                    'form_id' => (string) ($request->form_id ?: 'N/A'),
                    'request_date_label' => optional($request->request_date)->format('M d, Y') ?: 'N/A',
                    'destination' => (string) ($request->destination ?: 'N/A'),
                    'status' => (string) ($request->status ?: 'Unknown'),
                ];
            })
            ->values()
            ->all();

        $statusCounts = array_fill_keys($statusOrder, 0);
        $statusRows = (clone $baseRequestsQuery)->select(['status'])->get();

        foreach ($statusRows as $statusRow) {
            $statusValue = trim((string) ($statusRow->status ?? ''));

            if ($statusValue === '' || !array_key_exists($statusValue, $statusCounts)) {
                continue;
            }

            $statusCounts[$statusValue] += 1;
        }

        $trendMap = $trendMonths
            ->mapWithKeys(function (Carbon $month) {
                return [$month->format('Y-m') => 0];
            })
            ->all();

        $trendStartDate = $trendMonths->first()?->copy()->startOfMonth();

        $trendRows = (clone $baseRequestsQuery)
            ->when($trendStartDate !== null, function (Builder $query) use ($trendStartDate) {
                $query->whereDate('request_date', '>=', $trendStartDate->toDateString());
            })
            ->whereNotNull('request_date')
            ->select(['request_date'])
            ->get();

        foreach ($trendRows as $trendRow) {
            if (!$trendRow->request_date) {
                continue;
            }

            $monthKey = Carbon::parse((string) $trendRow->request_date)->format('Y-m');
            if (array_key_exists($monthKey, $trendMap)) {
                $trendMap[$monthKey] += 1;
            }
        }

        $trendValues = array_values($trendMap);

        return [
            'requestsThisMonth' => $requestsThisMonth,
            'activeTrips' => $activeTrips,
            'completedTrips' => $completedTrips,
            'pendingEvaluations' => $pendingEvaluations,
            'recentRequests' => $recentRequests,
            'statusChartLabels' => array_keys($statusCounts),
            'statusChartValues' => array_values($statusCounts),
            'trendChartLabels' => $trendLabels,
            'trendChartValues' => $trendValues,
            'refreshedAt' => now()->toDateTimeString(),
        ];
    }
}
