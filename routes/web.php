<?php

use App\Http\Controllers\landingController;
use App\Http\Controllers\requestFormController;
use App\Http\Controllers\vehicleAvailabilityController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


// Landing Page for the viewing of the website
Route::get("/", [landingController::class, 'landingPage'])->name('landing-page');


// Request Form

Route::get("/request-form", [requestFormController::class, 'requestForm'])->name('request-form');
Route::post("/request-form", [requestFormController::class, 'submitRequestForm'])->name('request-form.submit');
Route::get('/request-form/download/{filename}', [requestFormController::class, 'downloadGeneratedForm'])
    ->where('filename', '.*')
    ->name('request-form.download');
Route::get('/request-form/personnel/{personnelId}', [requestFormController::class, 'personnelLookup'])->name('request-form.personnel-lookup');

Route::get("/vehicle-available", [vehicleAvailabilityController::class, 'vehicleAvailability'])->name('vehicle-available');
Route::get('/vehicle-available/data', [vehicleAvailabilityController::class, 'vehiclesData'])->name('vehicle-available.data');


// Admin Dashboard
Route::get("/admin/dashboard", [App\Http\Controllers\admin\dashboardController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/dashboard/data', [App\Http\Controllers\admin\dashboardController::class, 'dashboardData'])->name('admin.dashboard.data');
Route::post(
    '/admin/dashboard/requests/{transportationRequest}/status',
    [App\Http\Controllers\admin\dashboardController::class, 'updateRequestStatus']
)->name('admin.dashboard.requests.update-status');

// Admin Operations
Route::get("/admin/vehicle-availability", [App\Http\Controllers\admin\adminVehicleAvailabilityController::class, 'index'])->name('admin.vehicle-availability');
Route::post(
    '/admin/vehicle-availability/{vehicle}',
    [App\Http\Controllers\admin\adminVehicleAvailabilityController::class, 'update']
)->name('admin.vehicle-availability.update');

// Admin Daily Driver's Trip Ticket
Route::get("/admin/daily-trip-ticket", [App\Http\Controllers\admin\dailyTripTicketController::class, 'index'])->name('admin.daily-trip-ticket');
Route::get('/admin/daily-trip-ticket/data', [App\Http\Controllers\admin\dailyTripTicketController::class, 'data'])->name('admin.daily-trip-ticket.data');
Route::post('/admin/daily-trip-ticket/{transportationRequest}/status', [App\Http\Controllers\admin\dailyTripTicketController::class, 'updateStatus'])->name('admin.daily-trip-ticket.status');
Route::get('/admin/daily-trip-ticket/{transportationRequest}/download', [App\Http\Controllers\admin\dailyTripTicketController::class, 'download'])->name('admin.daily-trip-ticket.download');

// Admin Transportation Request
Route::get("/admin/transportation-request", [App\Http\Controllers\admin\adminTransportationRequest::class, 'index'])->name('admin.transportation-request');
Route::post('/admin/transportation-request/{transportationRequest}/status', [App\Http\Controllers\admin\adminTransportationRequest::class, 'updateStatus'])->name('admin.transportation-request.status');

// 404 Fallback Routes
Route::prefix('admin')->group(function () {
    Route::fallback(function () {
        return response()->view('admin.404_admin', [], 404);
    });
});

Route::fallback(function () {
    return response()->view('404', [], 404);
});
