<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\JarsController;
use App\Http\Controllers\Api\OutcomeController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// -- health check --
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'lafins-backend',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
    ]);
});

Route::get('/status', function () {
    try {
        // Thử ping vào Database phát
        DB::connection()->getPdo();

        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'time' => microtime(true) - LARAVEL_START,  // Đo thời gian phản hồi
        ]);
    } catch (\Exception $e) {
        // Nếu DB sập, trả về lỗi 500 để Docker biết đường mà restart
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'message' => $e->getMessage()
        ], 500);
    }
});

// -- public routes --
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// -- 2FA challenge route (requires temp token with 2fa-challenge ability) --
Route::post('/two-factor-challenge', [AuthController::class, 'twoFactorChallenge'])
    ->middleware(['auth:sanctum', 'abilities:2fa-challenge', 'throttle:two-factor']);

// -- protected routes --
Route::name('api.')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::middleware('abilities:*')->group(function () {
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
        Route::prefix('settings/2fa')->group(function () {
            Route::get('/', [TwoFactorAuthenticationController::class, 'show']);
            Route::post('/enable', [TwoFactorAuthenticationController::class, 'store']);
            Route::post('/confirm', [TwoFactorAuthenticationController::class, 'confirm']);
            Route::delete('/disable', [TwoFactorAuthenticationController::class, 'destroy']);

            Route::get('/qr-code', [TwoFactorAuthenticationController::class, 'showQrCode']);
            Route::get('/secret-key', [TwoFactorAuthenticationController::class, 'showSecretKey']);

            Route::post('/recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes']);
            Route::post('/recovery-codes/show', [TwoFactorAuthenticationController::class, 'recoveryCodes']);
        });

        Route::controller(ProfileController::class)->prefix('profile')->group(function () {
            // GET
            Route::get('/', 'show')->name('profile.show');
            // PATCH
            Route::patch('/', 'update')->name('profile.update');
            // DELETE
            Route::delete('/', 'destroy')->name('profile.destroy');
        });

        Route::prefix('sessions')->group(function () {
            Route::get('/', [TokenController::class, 'index']);
            Route::delete('/others', [TokenController::class, 'destroyOthers']);
            Route::delete('/{tokenId}', [TokenController::class, 'destroy']);
        });
    });
});
