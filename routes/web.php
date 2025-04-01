<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:official'])->name('dashboard');

Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
});

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