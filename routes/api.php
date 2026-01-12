<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\JarsController;
use App\Http\Controllers\Api\OutcomeController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;

// -- public routes --
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// -- protected routes --
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // auth & user info
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // jars managerment
    Route::prefix('/jars')->group(function () {
        Route::get('/', [JarsController::class, 'index']);
        Route::post('/bulk-update', [JarsController::class, 'bulkUpdate']);
        Route::post('/reset', [JarsController::class, 'reset']);
        Route::post('/delete-all', [JarsController::class, 'deleteAll']);
    });

    // incomes
    Route::apiResource('incomes', IncomeController::class);

    // outcomes
    Route::apiResource('outcomes', OutcomeController::class);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Password
    Route::put('/settings/password', [PasswordController::class, 'update']);

    // 2FA
    Route::prefix('settings/two-factor')->group(function () {
        Route::get('/', [TwoFactorAuthenticationController::class, 'show']);
    });

    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        // GET
        Route::get('/', 'show')->name('profile.show');
        // PATCH
        Route::patch('/', 'update')->name('profile.update');
        // DELETE
        Route::delete('/', 'destroy')->name('profile.destroy');
    });
});
