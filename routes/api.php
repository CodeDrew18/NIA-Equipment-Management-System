<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyTripTicketController;
use App\Http\Controllers\Api\DriverPerformanceEvaluationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
    Route::delete('/fcm-token', [AuthController::class, 'clearFcmToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/daily-trip-tickets', [DailyTripTicketController::class, 'index']);
    Route::post('/daily-trip-tickets', [DailyTripTicketController::class, 'store']);
    Route::get('/daily-trip-tickets/{id}', [DailyTripTicketController::class, 'show'])->whereNumber('id');
    Route::get('/driver-performance-evaluations', [DriverPerformanceEvaluationController::class, 'index']);
    Route::get('/driver-performance-evaluations/{id}', [DriverPerformanceEvaluationController::class, 'show'])->whereNumber('id');
});
