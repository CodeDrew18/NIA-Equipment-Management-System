<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $payload = [
            'personnel_id' => $request->input('personnel_id', $request->input('userId')),
            'password' => $request->input('password'),
            'device_name' => $request->input('device_name'),
            'fcm_token' => $request->input('fcm_token', $request->input('fcmToken', $request->input('token'))),
        ];

        $validator = Validator::make($payload, [
            'personnel_id' => ['required', 'digits:6'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'fcm_token' => ['nullable', 'string', 'max:4096'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return response()->json([
                'message' => $errors->first() ?: 'Validation failed.',
                'errors' => $errors,
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::query()
            ->where('personnel_id', $validated['personnel_id'])
            ->first();

        if (!$user || !Hash::check($validated['password'], (string) $user->password)) {
            return response()->json([
                'message' => 'Invalid personnel ID or password.',
            ], 401);
        }

        $deviceName = trim((string) ($validated['device_name'] ?? 'flutter-mobile'));
        if ($deviceName === '') {
            $deviceName = 'flutter-mobile';
        }

        $incomingFcmToken = trim((string) ($validated['fcm_token'] ?? ''));
        if ($incomingFcmToken !== '') {
            $user->update([
                'fcm_token' => $incomingFcmToken,
            ]);
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'personnel_id' => $user->personnel_id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'fcm_token' => $user->fcm_token,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'personnel_id' => $user->personnel_id,
            'name' => $user->name,
            'role' => $user->role,
            'email' => $user->email,
            'fcm_token' => $user->fcm_token,
        ]);
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $payload = [
            'fcm_token' => $request->input('fcm_token', $request->input('fcmToken', $request->input('token'))),
        ];

        $validator = Validator::make($payload, [
            'fcm_token' => ['required', 'string', 'max:4096'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return response()->json([
                'message' => $errors->first() ?: 'Validation failed.',
                'errors' => $errors,
            ], 422);
        }

        $validated = $validator->validated();

        $request->user()->update([
            'fcm_token' => trim((string) $validated['fcm_token']),
        ]);

        return response()->json([
            'message' => 'FCM token updated successfully.',
        ]);
    }

    public function clearFcmToken(Request $request): JsonResponse
    {
        $request->user()->update([
            'fcm_token' => null,
        ]);

        return response()->json([
            'message' => 'FCM token cleared successfully.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
