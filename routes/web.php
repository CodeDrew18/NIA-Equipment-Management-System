<?php

use App\Http\Controllers\evaluationPerformanceController;
use App\Http\Controllers\landingController;
use App\Http\Controllers\NotificationModalController;
use App\Http\Controllers\requestFormController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\vehicleAvailabilityController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });q


// Landing Page for the viewing of the website
Route::get("/", [landingController::class, 'landingPage'])->name('landing-page');
// Authentication Routes
Route::get("/login", [App\Http\Controllers\auth\loginController::class, 'index'])->name('login');
Route::post("/login", [App\Http\Controllers\auth\loginController::class, 'authenticate'])->name('login.authenticate');
Route::middleware('auth')->group(function () {
    Route::post("/logout", [App\Http\Controllers\auth\loginController::class, 'logout'])->name('logout');

    Route::middleware('role.page:user-area')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/dashboard/data', [UserDashboardController::class, 'data'])->name('user.dashboard.data');
        Route::get('/dashboard/request-overview', [UserDashboardController::class, 'requestOverview'])->name('user.request-overview');

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
        Route::post('/evaluation-performance/submit', [evaluationPerformanceController::class, 'submit'])->name('evaluation-performance.submit');
        Route::get('/notifications/pending-evaluations', [NotificationModalController::class, 'userPendingEvaluations'])->name('user.notifications.pending-evaluations');


        Route::get("/monthly-official-travel-report", [App\Http\Controllers\monthlyTravelReportController::class, 'index'])->name('monthly-official-travel-report');
    });


    // Admin Routes ***************************************************************

    Route::middleware('role.page:admin-area')->group(function () {
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

        // Admin Vehicle Assignment
        Route::get("/admin/vehicle-assignment", [App\Http\Controllers\admin\vehicleAssignmentController::class, 'index'])->name('admin.vehicle_assignment');
        Route::post('/admin/vehicle-assignment/{transportationRequest}/assign', [App\Http\Controllers\admin\vehicleAssignmentController::class, 'assign'])->name('admin.vehicle_assignment.assign');

        // Admin User Roles
        Route::get("/admin/user-roles", [App\Http\Controllers\admin\userRolesController::class, 'index'])->name('admin.user_roles');
        Route::post('/admin/user-roles/{user}/role', [App\Http\Controllers\admin\userRolesController::class, 'updateRole'])->name('admin.user_roles.update-role');

        // Admin Fuel Issuance Slip
        Route::get("/admin/fuel-issuance-slip", [App\Http\Controllers\admin\fuelIssuanceController::class, 'index'])->name('admin.fuel_issuance_slip');
        Route::get('/admin/fuel-issuance-slip/data', [App\Http\Controllers\admin\fuelIssuanceController::class, 'data'])->name('admin.fuel_issuance_slip.data');
        Route::post('/admin/fuel-issuance-slip/print', [App\Http\Controllers\admin\fuelIssuanceController::class, 'printOfficeCopy'])->name('admin.fuel_issuance_slip.print');
        Route::post('/admin/fuel-issuance-slip/{transportationRequest}/dispatch', [App\Http\Controllers\admin\fuelIssuanceController::class, 'dispatchVehicle'])->name('admin.fuel_issuance_slip.dispatch');

        // Admin Fuel Partnerships
        Route::get('/admin/fuel-partnerships', [App\Http\Controllers\admin\fuelPartnershipController::class, 'index'])->name('admin.fuel_partnerships');
        Route::post('/admin/fuel-partnerships', [App\Http\Controllers\admin\fuelPartnershipController::class, 'store'])->name('admin.fuel_partnerships.store');
        Route::post('/admin/fuel-partnerships/{fuelPartnership}/activate', [App\Http\Controllers\admin\fuelPartnershipController::class, 'activate'])->name('admin.fuel_partnerships.activate');
        Route::put('/admin/fuel-partnerships/{fuelPartnership}', [App\Http\Controllers\admin\fuelPartnershipController::class, 'update'])->name('admin.fuel_partnerships.update');
        Route::delete('/admin/fuel-partnerships/{fuelPartnership}', [App\Http\Controllers\admin\fuelPartnershipController::class, 'destroy'])->name('admin.fuel_partnerships.destroy');

        Route::get("/admin/on-trip-vehicle", [App\Http\Controllers\admin\onTripVehicleController::class, 'index'])->name('admin.on_trip_vehicles');
        Route::get('/admin/on-trip-vehicle/data', [App\Http\Controllers\admin\onTripVehicleController::class, 'data'])->name('admin.on_trip_vehicles.data');


        Route::get("/admin/audit-log", [App\Http\Controllers\admin\auditLogController::class, 'index'])->name('audit-log');

        // Route::get("/admin/daily-equipment-utilization-report", [App\Http\Controllers\admin\dailyUtilizationReportController::class, 'index'])->name('daily-equipment-utilization-report');

        Route::get('/admin/reports/travel', [App\Http\Controllers\admin\travelReportController::class, 'index'])->name('admin.travel-reports');
        Route::get('/admin/reports/travel/export', [App\Http\Controllers\admin\travelReportController::class, 'export'])->name('admin.travel-reports.export');
        Route::get('/admin/reports/fuel-consumption', [App\Http\Controllers\admin\fuelConsumptionReportController::class, 'index'])->name('admin.fuel-consumption-report');
        Route::get('/admin/notifications/pending-transportation-requests', [NotificationModalController::class, 'adminPendingTransportationRequests'])->name('admin.notifications.pending-transportation-requests');

        //  Route::get("/audit-log", [App\Http\Controllers\admin\auditLogController::class, 'index'])->name('audit-log');
    });
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
