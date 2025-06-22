<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Officials\OfficialRequestController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Add your company search route here
Route::get('/companies/search', [OfficialRequestController::class, 'searchCompanies']);

