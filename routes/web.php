<?php

use App\Http\Controllers\evaluationPerformanceController;
use App\Http\Controllers\landingController;
use App\Http\Controllers\requestFormController;
use App\Http\Controllers\vehicleAvailabilityController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


// Landing Page for the viewing of the website
Route::get("/", [landingController::class, 'landingPage'])->name('landing-page');

// Authentication Routes
Route::get("/login", [App\Http\Controllers\auth\loginController::class, 'index'])->name('login');
Route::post("/login", [App\Http\Controllers\auth\loginController::class, 'authenticate'])->name('login.authenticate');
Route::middleware('auth')->group(function () {
    Route::post("/logout", [App\Http\Controllers\auth\loginController::class, 'logout'])->name('logout');

    // Request Form
    Route::get("/request-form", [requestFormController::class, 'requestForm'])->name('request-form');
    Route::post("/request-form", [requestFormController::class, 'submitRequestForm'])->name('request-form.submit');
    Route::get('/request-form/download/{filename}', [requestFormController::class, 'downloadGeneratedForm'])
        ->where('filename', '.*')
        ->name('request-form.download');
    Route::get('/request-form/personnel/{personnelId}', [requestFormController::class, 'personnelLookup'])->name('request-form.personnel-lookup');
    Route::get('/request-form/{transportationRequest}/attachments/{index}', [requestFormController::class, 'viewOwnAttachment'])->name('request-form.attachment.view');

    Route::get("/vehicle-available", [vehicleAvailabilityController::class, 'vehicleAvailability'])->name('vehicle-available');
    Route::get('/vehicle-available/data', [vehicleAvailabilityController::class, 'vehiclesData'])->name('vehicle-available.data');

    Route::get("/evaluation-performance", [evaluationPerformanceController::class, 'index'])->name('evaluation-performance');

    Route::get("/daily-equipment-utilization-report", [App\Http\Controllers\dailyUtilizationReportController::class, 'index'])->name('daily-equipment-utilization-report');

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
    Route::get('/admin/transportation-request/{transportationRequest}/attachments/{index}', [App\Http\Controllers\admin\adminTransportationRequest::class, 'viewAttachment'])->name('admin.transportation-request.attachment.view');

    // Admin User Roles
    Route::get("/admin/user-roles", [App\Http\Controllers\admin\userRolesController::class, 'index'])->name('admin.user_roles');
    Route::post('/admin/user-roles/{user}/role', [App\Http\Controllers\admin\userRolesController::class, 'updateRole'])->name('admin.user_roles.update-role');

    // Admin Fuel Issuance Slip
    Route::get("/admin/fuel-issuance-slip", [App\Http\Controllers\admin\fuelIssuanceController::class, 'index'])->name('admin.fuel_issuance_slip');
    Route::get('/admin/fuel-issuance-slip/data', [App\Http\Controllers\admin\fuelIssuanceController::class, 'data'])->name('admin.fuel_issuance_slip.data');
    Route::post('/admin/fuel-issuance-slip/print', [App\Http\Controllers\admin\fuelIssuanceController::class, 'printOfficeCopy'])->name('admin.fuel_issuance_slip.print');
});

// 404 Fallback Routes
Route::prefix('admin')->group(function () {
    Route::fallback(function () {
        return response()->view('admin.404_admin', [], 404);
    });
});

Route::fallback(function () {
    return response()->view('404', [], 404);
});
