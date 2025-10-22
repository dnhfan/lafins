<?php

use App\Http\Controllers\DashboardController;
use \App\Http\Controllers\IncomeController;
use App\Http\Controllers\OutcomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('incomes', [IncomeController::class, 'index'])->name('incomes');
    Route::post('incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::delete('incomes/{income}', [IncomeController::class, 'destroy'])->name('incomes.destroy');
    Route::put('incomes/{income}', [IncomeController::class, 'update'])->name('incomes.update');

    Route::get('outcomes', [OutcomeController::class, 'index'])->name('outcomes');
    Route::post('outcomes', [OutcomeController::class, 'store'])->name('outcomes.store');
    Route::put('outcomes/{outcome}', [OutcomeController::class, 'update'])->name('outcomes.update');
    Route::delete('outcomes/{outcome}', [OutcomeController::class, 'destroy'])->name('outcomes.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
