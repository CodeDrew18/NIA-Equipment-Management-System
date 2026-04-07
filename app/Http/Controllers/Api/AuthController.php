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
        ];

        $validator = Validator::make($payload, [
            'personnel_id' => ['required', 'digits:6'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
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
