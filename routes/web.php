<?php

use App\Http\Controllers\ActiveUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:official'])->name('dashboard');

Route::get('admin/dashboard', function () {
    return Inertia::render('AdminDashboard');
})->middleware(['auth', 'verified', 'role:admin'])->name('admin.dashboard');

// Route::get('captain/dashboard', function () {
//     return Inertia::render('CaptainDashboard');
// })->middleware(['auth', 'verified', 'role:captain'])->name('captain.dashboard');

// Route::get('secretary/dashboard', function () {
//     return Inertia::render('SecretaryDashboard');
// })->middleware(['auth', 'verified', 'role:secretary'])->name('secretary.dashboard');

// Route::get('treasurer/dashboard', function () {
//     return Inertia::render('TreasurerDashboard');
// })->middleware(['auth', 'verified', 'role:treasurer'])->name('treasurer.dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/active-users', [ActiveUserController::class, 'index'])->name('admin.active-users');
});

Route::get('unauthorized', function () {
    return Inertia::render('Unauthorize');
})->name('unauthorized');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';