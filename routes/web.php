<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::middleware(['auth', 'verified', 'role:official'])->group(function () {
//     // ...existing routes...
//     Route::get('/active-users', [DashboardController::class, 'getActiveUsers'])->name('users.active');
// });

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:official'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:official'])->group(function () {
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::post('/dashboard/store-request', [DashboardController::class, 'storeRequest'])->name('dashboard.store-request');
    Route::get('/requests/{id}', [DashboardController::class, 'show'])->name('requests.view');
    Route::post('/requests/{id}/process', [RequestController::class, 'process'])->name('requests.process');
    Route::post('/requests/{id}/void', [RequestController::class, 'void'])->name('requests.void');
    Route::get('requests/{id}/download/{filename}', [RequestController::class, 'downloadFile'])->name('requests.download-file');
});


Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserManagementController::class, 'create'])->name('admin.users.create');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::put('/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('admin.users.update-password');
});

Route::get('/request', function () {
    return Inertia::render('RequestView');
})->name('request.view');

// Route::get('captain/dashboard', function () {
//     return Inertia::render('CaptainDashboard');
// })->middleware(['auth', 'verified', 'role:captain'])->name('captain.dashboard');

// Route::get('secretary/dashboard', function () {
//     return Inertia::render('SecretaryDashboard');
// })->middleware(['auth', 'verified', 'role:secretary'])->name('secretary.dashboard');

// Route::get('treasurer/dashboard', function () {
//     return Inertia::render('TreasurerDashboard');
// })->middleware(['auth', 'verified', 'role:treasurer'])->name('treasurer.dashboard');

// Route::middleware(['auth', 'role:admin'])->group(function () {
//     Route::get('/admin/active-users', [ActiveUserController::class, 'index'])->name('admin.active-users');
// });

Route::get('unauthorized', function () {
    return Inertia::render('Unauthorize');
})->name('unauthorized');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';