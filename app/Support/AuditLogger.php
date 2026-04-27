<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AuditLogger
{
    public static function record(?User $user, Request $request, string $category, string $description, string $status = 'SUCCESS', array $metadata = []): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        $normalizedStatus = strtoupper(trim($status)) === 'FAILED'
            ? 'FAILED'
            : (strtoupper(trim($status)) === 'WARNING' ? 'WARNING' : 'SUCCESS');

        $resolvedPersonnelId = self::resolvePersonnelId($user, $request);
        $resolvedUserName = self::resolveUserName($user, $resolvedPersonnelId);

        if (self::isDuplicateEvent(
            $user?->id,
            $resolvedPersonnelId,
            $category,
            $description,
            (string) strtoupper((string) $request->method()),
            (string) optional($request->route())->getName(),
            '/' . ltrim($request->path(), '/'),
            self::resolveIpAddress($request),
            $normalizedStatus
        )) {
            return;
        }

        try {
            AuditLog::query()->create([
                'user_id' => $user?->id,
                'personnel_id' => $resolvedPersonnelId !== '' ? $resolvedPersonnelId : null,
                'user_name' => $resolvedUserName,
                'action_category' => $category,
                'activity_description' => $description,
                'method' => strtoupper((string) $request->method()),
                'route_name' => (string) optional($request->route())->getName(),
                'request_path' => '/' . ltrim($request->path(), '/'),
                'ip_address' => self::resolveIpAddress($request),
                'status' => $normalizedStatus,
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

    private static function resolvePersonnelId(?User $user, Request $request): string
    {
        $personnelId = trim((string) ($user?->personnel_id ?? ''));
        if ($personnelId !== '') {
            return $personnelId;
        }

        $candidate = trim((string) ($request->input('personnel_id') ?? $request->input('username') ?? ''));

        return preg_match('/^[0-9A-Za-z_-]{3,32}$/', $candidate) ? $candidate : '';
    }

    private static function resolveUserName(?User $user, string $resolvedPersonnelId): string
    {
        $name = trim((string) ($user?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        if ($resolvedPersonnelId !== '') {
            return 'Personnel ID ' . $resolvedPersonnelId;
        }

        return 'Guest';
    }

    private static function isDuplicateEvent(
        ?int $userId,
        string $personnelId,
        string $category,
        string $description,
        string $method,
        string $routeName,
        string $requestPath,
        ?string $ipAddress,
        string $status
    ): bool {
        $query = AuditLog::query()
            ->where('created_at', '>=', Carbon::now()->subSeconds(10))
            ->where('action_category', $category)
            ->where('activity_description', $description)
            ->where('method', $method)
            ->where('route_name', $routeName)
            ->where('request_path', $requestPath)
            ->where('status', $status)
            ->where(function ($nested) use ($ipAddress) {
                if ($ipAddress === null || trim($ipAddress) === '') {
                    $nested->whereNull('ip_address');
                } else {
                    $nested->where('ip_address', $ipAddress);
                }
            });

        if ($userId !== null) {
            $query->where('user_id', $userId);
        } elseif ($personnelId !== '') {
            $query->where('personnel_id', $personnelId);
        } else {
            $query->whereNull('user_id')
                ->where(function ($nested) {
                    $nested->whereNull('personnel_id')->orWhere('personnel_id', '');
                });
        }

        return $query->exists();
    }
}
