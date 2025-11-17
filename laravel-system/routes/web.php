<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ExportController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/verify-user', [UserController::class, 'showVerifyForm'])->name('users.verify');
    Route::post('/verify-user', [UserController::class, 'verify']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/process-payment', [PaymentController::class, 'showProcessForm'])->name('payments.process');
    Route::post('/process-payment', [PaymentController::class, 'process']);

    // Transfers
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/make-transfer', [TransferController::class, 'showProcessForm'])->name('transfers.process');
    Route::post('/make-transfer', [TransferController::class, 'process']);

    // Groups
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [GroupController::class, 'store']);
    Route::post('/groups/add-member', [GroupController::class, 'addMember'])->name('groups.add-member');

    // Export
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::post('/export', [ExportController::class, 'export'])->name('export.download');
});

// Redirect root to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});
