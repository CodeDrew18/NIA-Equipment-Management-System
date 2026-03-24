<?php

use App\Http\Controllers\landingController;
use App\Http\Controllers\requestFormController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


// Landing Page for the viewing of the website
Route::get("/", [landingController::class, 'landingPage'])-> name('landing-page');


// Request Form

Route::get("/request-form", [requestFormController::class, 'requestForm'])-> name('request-form');
