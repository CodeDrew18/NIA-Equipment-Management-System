<?php

namespace App\Http\Middleware;

use App\Support\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!Auth::check()) {
            return $response;
        }

        $routeName = (string) optional($request->route())->getName();

        if ($this->shouldSkipRoute($routeName)) {
            return $response;
        }

        $method = strtoupper((string) $request->method());
        $statusCode = (int) $response->getStatusCode();

        AuditLogger::record(
            Auth::user(),
            $request,
            $this->resolveCategory($method),
            $this->buildDescription($routeName, $method, $statusCode),
            $statusCode >= 500 ? 'FAILED' : ($statusCode >= 400 ? 'WARNING' : 'SUCCESS'),
            [
                'status_code' => $statusCode,
                'query' => $request->query(),
            ]
        );

        return $response;
    }

    private function shouldSkipRoute(string $routeName): bool
    {
        if ($routeName === '') {
            return false;
        }

        if ($routeName === 'login.authenticate') {
            return true;
        }

        // Skip noisy live-refresh endpoints to keep audit records meaningful.
        return str_ends_with($routeName, '.data');
    }

    private function resolveCategory(string $method): string
    {
        return match ($method) {
            'POST', 'PUT', 'PATCH', 'DELETE' => 'DATA_PROCESS',
            default => 'SYSTEM_ACCESS',
        };
    }

    private function buildDescription(string $routeName, string $method, int $statusCode): string
    {
        $routeLabel = $routeName !== ''
            ? str_replace(['.', '_'], [' ', ' '], $routeName)
            : 'unnamed route';

        $action = $method === 'GET' ? 'Viewed' : 'Processed';

        return sprintf('%s %s (%s) with HTTP %d.', $action, $routeLabel, $method, $statusCode);
    }
}
