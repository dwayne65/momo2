<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

// API routes (protected by auth middleware)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::match(['GET', 'POST'], '/users', [ApiController::class, 'users']);
    Route::match(['GET', 'POST'], '/payments', [ApiController::class, 'payments']);
    Route::match(['GET', 'POST'], '/transfers', [ApiController::class, 'transfers']);
    Route::match(['GET', 'POST'], '/groups', [ApiController::class, 'groups']);
    Route::post('/verify-user', [ApiController::class, 'verifyUser']);
    Route::post('/process-payment', [ApiController::class, 'processPayment']);
    Route::post('/process-transfer', [ApiController::class, 'processTransfer']);
});
