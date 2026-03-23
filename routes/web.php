<?php

use App\Http\Controllers\authController;
use App\Http\Controllers\landingController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get("/", [landingController::class, 'landingPage']);
