<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    public static function record(?User $user, Request $request, string $category, string $description, string $status = 'SUCCESS', array $metadata = []): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        try {
            AuditLog::query()->create([
                'user_id' => $user?->id,
                'personnel_id' => $user?->personnel_id,
                'user_name' => $user?->name,
                'action_category' => $category,
                'activity_description' => $description,
                'method' => strtoupper((string) $request->method()),
                'route_name' => (string) optional($request->route())->getName(),
                'request_path' => '/' . ltrim($request->path(), '/'),
                'ip_address' => self::resolveIpAddress($request),
                'status' => strtoupper(trim($status)) === 'FAILED' ? 'FAILED' : (strtoupper(trim($status)) === 'WARNING' ? 'WARNING' : 'SUCCESS'),
                'metadata' => empty($metadata) ? null : $metadata,
            ]);
        } catch (\Throwable $e) {
            // Ignore logging failures to avoid affecting the main request flow.
        }
    }

    public static function resolveIpAddress(Request $request): ?string
    {
        $forwardedFor = trim((string) $request->header('X-Forwarded-For', ''));
        if ($forwardedFor !== '') {
            $parts = preg_split('/\s*,\s*/', $forwardedFor) ?: [];
            $firstIp = trim((string) ($parts[0] ?? ''));

            if ($firstIp !== '') {
                return $firstIp;
            }
        }

        return $request->ip();
    }
}
