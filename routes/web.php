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
