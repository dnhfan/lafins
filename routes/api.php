<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\JarsController;
use App\Http\Controllers\Api\OutcomeController;
use Illuminate\Support\Facades\Route;

// -- public routes --
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// -- protected routes --
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
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
});
