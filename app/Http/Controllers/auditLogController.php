<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class auditLogController extends Controller
{
    public function index(Request $request)
    {
        $payload = $this->buildPayload($request);

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return view('audit_log.audit_log', $payload);
    }

    private function buildPayload(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $fromDate = $validated['from'] ?? '';
        $toDate = $validated['to'] ?? '';

        if (!Schema::hasTable('audit_logs')) {
            return [
                'auditLogs' => new LengthAwarePaginator([], 0, 10, 1),
                'rows' => [],
                'search' => $search,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'totalLogs' => 0,
                'trendPercentage' => 0,
                'trendIcon' => 'trending_flat',
                'securityAlerts' => 0,
                'criticalAlerts' => 0,
                'activeUsers' => 0,
                'latestEventLabel' => 'No records yet',
                'summaryText' => 'Showing 0 to 0 of 0 entries',
            ];
        }

        $query = AuditLog::query()
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($nested) use ($search) {
                    $nested->where('personnel_id', 'like', '%' . $search . '%')
                        ->orWhere('user_name', 'like', '%' . $search . '%')
                        ->orWhere('action_category', 'like', '%' . $search . '%')
                        ->orWhere('activity_description', 'like', '%' . $search . '%')
                        ->orWhere('route_name', 'like', '%' . $search . '%')
                        ->orWhere('ip_address', 'like', '%' . $search . '%');
                });
            })
            ->when($fromDate !== '', function ($builder) use ($fromDate) {
                $builder->whereDate('created_at', '>=', $fromDate);
            })
            ->when($toDate !== '', function ($builder) use ($toDate) {
                $builder->whereDate('created_at', '<=', $toDate);
            });

        $auditLogs = (clone $query)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $totalLogs = AuditLog::query()->count();

        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        $currentMonthCount = AuditLog::query()
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $previousMonthCount = AuditLog::query()
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        if ($previousMonthCount > 0) {
            $trendPercentage = round((($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100, 1);
        } elseif ($currentMonthCount > 0) {
            $trendPercentage = 100.0;
        } else {
            $trendPercentage = 0.0;
        }

        $trendIcon = $trendPercentage > 0 ? 'trending_up' : ($trendPercentage < 0 ? 'trending_down' : 'trending_flat');

        $securityAlerts = AuditLog::query()
            ->whereIn('status', ['FAILED', 'WARNING'])
            ->count();

        $criticalAlerts = AuditLog::query()
            ->where('status', 'FAILED')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $activeUsers = AuditLog::query()
            ->whereNotNull('personnel_id')
            ->where('personnel_id', '!=', '')
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->distinct('personnel_id')
            ->count('personnel_id');

        $latestLog = AuditLog::query()->latest('created_at')->first();
        $latestEventLabel = $latestLog?->created_at
            ? Carbon::parse($latestLog->created_at)->diffForHumans()
            : 'No records yet';

        return [
            'auditLogs' => $auditLogs,
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalLogs' => $totalLogs,
            'trendPercentage' => $trendPercentage,
            'trendIcon' => $trendIcon,
            'securityAlerts' => $securityAlerts,
            'criticalAlerts' => $criticalAlerts,
            'activeUsers' => $activeUsers,
            'latestEventLabel' => $latestEventLabel,
            'summaryText' => 'Showing ' . ($auditLogs->firstItem() ?? 0) . ' to ' . ($auditLogs->lastItem() ?? 0) . ' of ' . $auditLogs->total() . ' entries',
            'pagination' => [
                'currentPage' => $auditLogs->currentPage(),
                'lastPage' => $auditLogs->lastPage(),
                'onFirstPage' => $auditLogs->onFirstPage(),
                'hasMorePages' => $auditLogs->hasMorePages(),
                'pageUrls' => $auditLogs->getUrlRange(1, $auditLogs->lastPage()),
            ],
            'rows' => $auditLogs->getCollection()->values()->map(function (AuditLog $log): array {
                return [
                    'timestamp' => optional($log->created_at)->format('M d, Y h:i A') ?? 'N/A',
                    'personnelId' => (string) ($log->personnel_id ?: ('USR-' . $log->user_id)),
                    'userName' => (string) ($log->user_name ?: 'Unknown User'),
                    'actionCategory' => (string) $log->action_category,
                    'activityDescription' => (string) ($log->activity_description ?? ''),
                    'ipAddress' => (string) ($log->ip_address ?: 'N/A'),
                    'status' => (string) $log->status,
                ];
            })->all(),
        ];
    }
}
