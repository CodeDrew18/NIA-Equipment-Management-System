<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private bool $serviceAccountResolved = false;

    private bool $googleServicesResolved = false;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedServiceAccount = null;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedGoogleServices = null;

    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        $deviceToken = trim($token);
        if ($deviceToken === '') {
            return false;
        }

        $normalizedData = $this->normalizeDataPayload($data);

        if ($this->canUseV1Api()) {
            return $this->sendViaV1($deviceToken, $title, $body, $normalizedData);
        }

        return $this->sendViaLegacy($deviceToken, $title, $body, $normalizedData);
    }

    /**
     * @param array<string, string> $normalizedData
     */
    private function sendViaLegacy(string $deviceToken, string $title, string $body, array $normalizedData): bool
    {
        $serverKey = $this->resolveLegacyServerKey();
        $endpoint = trim((string) config('services.fcm.endpoint', 'https://fcm.googleapis.com/fcm/send'));

        if ($serverKey === '' || $endpoint === '') {
            Log::warning('FCM push skipped due to missing legacy FCM configuration.', [
                'endpoint_configured' => $endpoint !== '',
                'server_key_configured' => $serverKey !== '',
            ]);

            return false;
        }

        $payload = [
            'to' => $deviceToken,
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => array_merge([
                'title' => $title,
                'body' => $body,
            ], $normalizedData),
        ];

        $request = Http::acceptJson()
            ->asJson()
            ->timeout(12);

        // Legacy FCM endpoint requires `Authorization: key=...`.
        $request = $request->withHeaders([
            'Authorization' => 'key=' . $serverKey,
        ]);

        $response = $request->post($endpoint, $payload);

        if (!$response->successful()) {
            Log::warning('Legacy FCM push request failed.', [
                'status' => $response->status(),
                'response_body' => $response->body(),
                'token_suffix' => $this->tokenSuffix($deviceToken),
            ]);

            return false;
        }

        $result = $response->json();
        if (is_array($result) && (int) ($result['failure'] ?? 0) > 0) {
            Log::warning('Legacy FCM push returned failure payload.', [
                'result' => $result,
                'token_suffix' => $this->tokenSuffix($deviceToken),
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param array<string, string> $normalizedData
     */
    private function sendViaV1(string $deviceToken, string $title, string $body, array $normalizedData): bool
    {
        $projectId = $this->resolveProjectId();
        $accessToken = $this->resolveV1AccessToken();

        if ($projectId === '' || $accessToken === '') {
            Log::warning('FCM v1 push skipped due to missing project/auth details.', [
                'project_id_configured' => $projectId !== '',
                'access_token_available' => $accessToken !== '',
            ]);

            return false;
        }

        $endpoint = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'transportation_updates_v2',
                    ],
                ],
                'data' => array_merge([
                    'title' => $title,
                    'body' => $body,
                ], $normalizedData),
            ],
        ];

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout(12)
            ->post($endpoint, $payload);

        if (!$response->successful()) {
            Log::warning('FCM v1 push request failed.', [
                'status' => $response->status(),
                'response_body' => $response->body(),
                'token_suffix' => $this->tokenSuffix($deviceToken),
            ]);

            return false;
        }

        $result = $response->json();
        if (!is_array($result) || !array_key_exists('name', $result)) {
            Log::warning('FCM v1 push returned unexpected payload.', [
                'result' => $result,
                'token_suffix' => $this->tokenSuffix($deviceToken),
            ]);

            return false;
        }

        return true;
    }

    private function normalizeDataPayload(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $payloadKey = trim((string) $key);
            if ($payloadKey === '') {
                continue;
            }

            if (is_bool($value)) {
                $normalized[$payloadKey] = $value ? '1' : '0';
                continue;
            }

            if (is_scalar($value)) {
                $normalized[$payloadKey] = (string) $value;
                continue;
            }

            $normalized[$payloadKey] = json_encode($value, JSON_UNESCAPED_SLASHES);
        }

        return $normalized;
    }

    private function canUseV1Api(): bool
    {
        $projectId = $this->resolveProjectId();
        $serviceAccount = $this->resolveServiceAccount();

        $hasCredentials = is_array($serviceAccount)
            && trim((string) ($serviceAccount['client_email'] ?? '')) !== ''
            && trim((string) ($serviceAccount['private_key'] ?? '')) !== '';

        return $projectId !== '' && $hasCredentials;
    }

    private function resolveLegacyServerKey(): string
    {
        $serverKey = trim((string) config('services.fcm.server_key', ''));

        if ($serverKey !== '') {
            return $serverKey;
        }

        $fallbackKeys = [
            trim((string) env('FCM_SERVER_KEY', '')),
            trim((string) env('FIREBASE_SERVER_KEY', '')),
        ];

        foreach ($fallbackKeys as $fallbackKey) {
            if ($fallbackKey !== '') {
                return $fallbackKey;
            }
        }

        return '';
    }

    private function resolveProjectId(): string
    {
        $projectId = trim((string) config('services.fcm.project_id', ''));

        if ($projectId !== '') {
            return $projectId;
        }

        $serviceAccount = $this->resolveServiceAccount();
        if (is_array($serviceAccount)) {
            $serviceAccountProjectId = trim((string) ($serviceAccount['project_id'] ?? ''));
            if ($serviceAccountProjectId !== '') {
                return $serviceAccountProjectId;
            }
        }

        $googleServices = $this->resolveGoogleServices();
        if (is_array($googleServices)) {
            return trim((string) ($googleServices['project_info']['project_id'] ?? ''));
        }

        return '';
    }

    private function resolveV1AccessToken(): string
    {
        $serviceAccount = $this->resolveServiceAccount();
        if (!is_array($serviceAccount)) {
            return '';
        }

        $cacheKey = 'fcm_v1_access_token_' . md5((string) ($serviceAccount['client_email'] ?? 'default'));
        $cachedToken = (string) Cache::get($cacheKey, '');
        if ($cachedToken !== '') {
            return $cachedToken;
        }

        $tokenUri = trim((string) ($serviceAccount['token_uri'] ?? 'https://oauth2.googleapis.com/token'));
        $jwtAssertion = $this->buildServiceAccountJwt($serviceAccount, $tokenUri);

        if ($jwtAssertion === '') {
            return '';
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(12)
            ->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwtAssertion,
            ]);

        if (!$response->successful()) {
            Log::warning('Failed to obtain FCM v1 OAuth access token.', [
                'status' => $response->status(),
                'response_body' => $response->body(),
            ]);

            return '';
        }

        $json = $response->json();
        if (!is_array($json)) {
            return '';
        }

        $accessToken = trim((string) ($json['access_token'] ?? ''));
        if ($accessToken === '') {
            return '';
        }

        $expiresIn = (int) ($json['expires_in'] ?? 3600);
        $ttlSeconds = max(60, $expiresIn - 60);
        Cache::put($cacheKey, $accessToken, now()->addSeconds($ttlSeconds));

        return $accessToken;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveServiceAccount(): ?array
    {
        if ($this->serviceAccountResolved) {
            return $this->resolvedServiceAccount;
        }

        $this->serviceAccountResolved = true;

        $serviceAccountJson = trim((string) config('services.fcm.service_account_json', ''));
        if ($serviceAccountJson !== '') {
            $decoded = json_decode($serviceAccountJson, true);

            if (!is_array($decoded)) {
                $decodedFromBase64 = json_decode((string) base64_decode($serviceAccountJson, true), true);
                if (is_array($decodedFromBase64)) {
                    $decoded = $decodedFromBase64;
                }
            }

            if (is_array($decoded)) {
                $this->resolvedServiceAccount = $this->sanitizeServiceAccount($decoded);

                return $this->resolvedServiceAccount;
            }
        }

        $serviceAccountPath = trim((string) config('services.fcm.service_account_path', ''));
        if ($serviceAccountPath !== '') {
            if (!str_starts_with($serviceAccountPath, '/') && !preg_match('/^[A-Za-z]:\\\\/', $serviceAccountPath)) {
                $serviceAccountPath = base_path($serviceAccountPath);
            }
        }

        if ($serviceAccountPath !== '' && is_readable($serviceAccountPath)) {
            $decoded = json_decode((string) file_get_contents($serviceAccountPath), true);

            if (is_array($decoded)) {
                $this->resolvedServiceAccount = $this->sanitizeServiceAccount($decoded);

                return $this->resolvedServiceAccount;
            }
        }

        $this->resolvedServiceAccount = null;

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveGoogleServices(): ?array
    {
        if ($this->googleServicesResolved) {
            return $this->resolvedGoogleServices;
        }

        $this->googleServicesResolved = true;

        $path = trim((string) config('services.fcm.google_services_path', base_path('google-services.json')));
        if ($path === '') {
            $this->resolvedGoogleServices = null;

            return null;
        }

        if (!str_starts_with($path, '/') && !preg_match('/^[A-Za-z]:\\\\/', $path)) {
            $path = base_path($path);
        }

        if (!is_readable($path)) {
            $this->resolvedGoogleServices = null;

            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            $this->resolvedGoogleServices = null;

            return null;
        }

        $this->resolvedGoogleServices = $decoded;

        return $this->resolvedGoogleServices;
    }

    /**
     * @param array<string, mixed> $serviceAccount
     * @return array<string, mixed>
     */
    private function sanitizeServiceAccount(array $serviceAccount): array
    {
        if (isset($serviceAccount['private_key']) && is_string($serviceAccount['private_key'])) {
            $serviceAccount['private_key'] = str_replace('\\n', "\n", $serviceAccount['private_key']);
        }

        return $serviceAccount;
    }

    /**
     * @param array<string, mixed> $serviceAccount
     */
    private function buildServiceAccountJwt(array $serviceAccount, string $tokenUri): string
    {
        $clientEmail = trim((string) ($serviceAccount['client_email'] ?? ''));
        $privateKey = (string) ($serviceAccount['private_key'] ?? '');

        if ($clientEmail === '' || $privateKey === '') {
            Log::warning('FCM service account payload is missing required fields.', [
                'has_client_email' => $clientEmail !== '',
                'has_private_key' => $privateKey !== '',
            ]);

            return '';
        }

        $now = time();

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $claims = [
            'iss' => $clientEmail,
            'scope' => self::FCM_SCOPE,
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedClaims = $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES));

        $signatureInput = $encodedHeader . '.' . $encodedClaims;
        $signature = '';

        $isSigned = openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$isSigned) {
            Log::warning('Failed signing FCM service account JWT.', [
                'openssl_error' => openssl_error_string(),
            ]);

            return '';
        }

        return $signatureInput . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function tokenSuffix(string $token): string
    {
        $length = strlen($token);
        if ($length <= 8) {
            return $token;
        }

        return substr($token, $length - 8);
    }
}
