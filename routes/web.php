<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Captain\CaptainDashboardController;
use App\Http\Controllers\Captain\CaptainRequestController;
use App\Http\Controllers\Officials\OfficialDashboardController;
use App\Http\Controllers\Officials\OfficialRequestController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('unauthorized', function () {
    return Inertia::render('Unauthorize');
})->name('unauthorized');

// download file from request view
Route::get('requests/{id}/download/{filename}',
    [OfficialRequestController::class, 'downloadFile'
])->name('requests.download-file');

/*
|--------------------------------------------------------------------------
| Official Routes
|--------------------------------------------------------------------------
*/
// Official Dashboard
Route::get('dashboard', [OfficialDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:official'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:official'])->group(function () {
    // Request Viewing and Creation
    Route::get('/requests/view/{id}', [OfficialRequestController::class, 'show'])->name('requests.view');  // Move this route first
    Route::get('/requests/{tab?}', [OfficialRequestController::class, 'index'])->name('requests.index');    // Move this route second
    Route::post('/dashboard/store-request', [OfficialDashboardController::class, 'storeRequest'])->name('dashboard.store-request');

    // Request View Actions
    Route::post('/requests/{id}', [OfficialRequestController::class, 'update'])->name('requests.update');
    Route::post('/requests/{id}/process', [OfficialRequestController::class, 'process'])->name('requests.process');
    Route::post('/requests/{id}/void', [OfficialRequestController::class, 'void'])->name('requests.void');
    Route::post('/requests/{id}/resubmit', [OfficialRequestController::class, 'resubmit'])->name('requests.resubmit');

    Route::post('/officials/requests/{id}/quotation', [OfficialRequestController::class, 'submitQuotation'])->name('officials.requests.quotation.submit');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
// Admin Dashboard
Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admin.dashboard');

// User Management
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserManagementController::class, 'create'])->name('admin.users.create');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::put('/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('admin.users.update-password');
});

/*
|--------------------------------------------------------------------------
| Captain Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:captain'])->group(function () {
    // Captain Dashboard
    Route::get('/captain/dashboard', [CaptainDashboardController::class, 'index'])
        ->name('captain.dashboard');

    // Request Management
    Route::get('/captain/requests', [CaptainRequestController::class, 'index'])->name('captain.requests');
    Route::get('/captain/requests/{id}', [CaptainRequestController::class, 'show'])->name('captain.requests.view');
    Route::post('/requests/{id}/approve', [CaptainRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{id}/decline', [CaptainRequestController::class, 'decline'])->name('requests.decline');
    Route::post('/requests/{id}/return', [CaptainRequestController::class, 'return'])->name('requests.return');
});

/*
|--------------------------------------------------------------------------
| Treasurer Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:treasurer'])->group(function () {
    // Captain Dashboard
    Route::get('/treasurer/dashboard', [CaptainDashboardController::class, 'index'])
        ->name('treasurer.dashboard');

});

/*
|--------------------------------------------------------------------------
| Additional Route Files
|--------------------------------------------------------------------------
*/
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Commented Routes (For Future Reference)
|--------------------------------------------------------------------------
*/
// Secretary & Treasurer Routes (Currently Disabled)
/*
Route::get('secretary/dashboard', function () {
    return Inertia::render('SecretaryDashboard');
})->middleware(['auth', 'verified', 'role:secretary'])->name('secretary.dashboard');

Route::get('treasurer/dashboard', function () {
    return Inertia::render('TreasurerDashboard');
})->middleware(['auth', 'verified', 'role:treasurer'])->name('treasurer.dashboard');
*/

// Active Users Routes (Currently Disabled)
/*
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/active-users', [ActiveUserController::class, 'index'])->name('admin.active-users');
});

Route::middleware(['auth', 'verified', 'role:official'])->group(function () {
    Route::get('/active-users', [DashboardController::class, 'getActiveUsers'])->name('users.active');
});
*/
