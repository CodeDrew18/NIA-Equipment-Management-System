<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RolePageAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $area)
    {
        $user = $request->user();

        if (!$user) {
            return response()->view('404', [], 404);
        }

        $roles = collect(explode(',', (string) $user->role))
            ->map(function ($value) {
                return strtolower(trim((string) $value));
            })
            ->filter();

        $allowedRoles = match ($area) {
            'admin-area' => ['admin', 'chief_of_motorpool_section'],
            'user-area' => ['user', 'operator', 'driver'],
            default => [],
        };

        if (empty($allowedRoles) || $roles->intersect($allowedRoles)->isEmpty()) {
            return response()->view('404', [], 404);
        }

        return $next($request);
    }
}
