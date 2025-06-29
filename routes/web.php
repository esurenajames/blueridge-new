<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Captain\CaptainBankController;
use App\Http\Controllers\Captain\CaptainCategoryController;
use App\Http\Controllers\Captain\CaptainDashboardController;
use App\Http\Controllers\Captain\CaptainFundOverviewController;
use App\Http\Controllers\Captain\CaptainFundSettingsController;
use App\Http\Controllers\Captain\CaptainRequestController;
use App\Http\Controllers\Captain\CaptainSubcategoryController;
use App\Http\Controllers\Captain\CaptainTransactionHistoryController;
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

// generate purchase request 
Route::get('/requests/{id}/purchase-request-pdf', 
    [OfficialRequestController::class, 'generatePurchaseRequestPDF'
])->name('requests.purchase-request-pdf');

Route::get('/requests/{id}/abstract-of-canvass', 
    [OfficialRequestController::class, 'generateAbstractOfCanvassPDF'   
])->name('requests.abstract-of-canvass');

Route::get('/companies/fetch', [OfficialRequestController::class, 'fetchCompanies']);

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
    Route::get('/categories', [OfficialDashboardController::class, 'getCategories'])->name('categories');

    // Request View Actions
    Route::post('/requests/{id}', [OfficialRequestController::class, 'update'])->name('requests.update');
    Route::post('/requests/{id}/process', [OfficialRequestController::class, 'process'])->name('requests.process');
    Route::post('/requests/{id}/void', [OfficialRequestController::class, 'void'])->name('requests.void');
    Route::post('/requests/{id}/resubmit', [OfficialRequestController::class, 'resubmit'])->name('requests.resubmit');

    // Request Quotation Actions
    Route::post('/officials/requests/{id}/quotation', [OfficialRequestController::class, 'submitQuotation'])->name('officials.requests.quotation.submit');
    Route::post('/officials/requests/{id}/quotation/resubmit', [OfficialRequestController::class, 'resubmitQuotation'])->name('officials.requests.quotation.resubmit');

    // Request Purchase Request Actions
    Route::post('/requests/{id}/process-purchase', [OfficialRequestController::class, 'processPurchaseRequest'])->name('requests.process-purchase');

    Route::get('/officials/requests/companies/search', [OfficialRequestController::class, 'searchCompanies'])
    ->name('officials.requests.companies.search');

    Route::get('/api/companies/search', [OfficialRequestController::class, 'searchCompanies'])->name('companies.search');
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


Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    // User Management
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

    // Fund Management
    Route::get('/captain/funds', [CaptainFundOverviewController::class, 'index'])->name('captain.funds');
    Route::post('/captain/funds/{budget}/income', [CaptainFundOverviewController::class, 'addIncome'])->name('captain.funds.add-income');
    Route::post('/captain/funds/{budget}/proposed-budget', [CaptainFundOverviewController::class, 'addProposedBudget'])->name('captain.funds.add-proposed-budget');

    // Fund Transaction History
    Route::get('/captain/transactions', [CaptainTransactionHistoryController::class, 'index'])->name('captain.transactions');

    // Fund Settings
    Route::get('/captain/fund-settings', [CaptainFundSettingsController::class, 'index'])->name('captain.fund-settings');
    Route::post('/captain/fund-settings/save', [CaptainFundSettingsController::class, 'saveChanges'])->name('captain.fund-settings.save');

    // Manage Fund
    Route::get('/captain/manage-fund', function () { return Inertia::render('captain/CaptainManageFund');})->name('captain.manage.fund');

    // Bank Accounts
    Route::get('/captain/bank-accounts', [CaptainBankController::class, 'index'])->name('captain.bank-accounts');
    Route::post('/captain/bank-accounts', [CaptainBankController::class, 'create'])->name('captain.bank-accounts.create');
    Route::put('/captain/bank-accounts/{bankAccount}', [CaptainBankController::class, 'update'])->name('captain.bank-accounts.update');
    Route::delete('/captain/bank-accounts/{bankAccount}', [CaptainBankController::class, 'destroy'])->name('captain.bank-accounts.destroy');
    Route::get('/bank-accounts', [CaptainBankController::class, 'getBankAccounts'])->name('api.bank-accounts');

    // Category Management
    Route::get('/captain/categories', [CaptainCategoryController::class, 'index'])->name('captain.categories');
    Route::post('/captain/categories', [CaptainCategoryController::class, 'create'])->name('captain.categories.create');
    Route::put('/captain/categories/{category}', [CaptainCategoryController::class, 'update'])->name('captain.categories.update');
    Route::delete('/captain/categories/{category}', [CaptainCategoryController::class, 'destroy'])->name('captain.categories.destroy');

    // Subcategory Management
    Route::get('/captain/subcategories', [CaptainSubcategoryController::class, 'index'])->name('captain.subcategories');
    Route::post('/captain/subcategories', [CaptainSubcategoryController::class, 'create'])->name('captain.subcategories.create');
    Route::put('/captain/subcategories/{subcategory}', [CaptainSubcategoryController::class, 'update'])->name('captain.subcategories.update');
    Route::delete('/captain/subcategories/{subcategory}', [CaptainSubcategoryController::class, 'destroy'])->name('captain.subcategories.destroy');
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
