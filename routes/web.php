<?php

use App\Http\Controllers\DashboardController;
use \App\Http\Controllers\IncomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('incomes', [IncomeController::class, 'index'])->name('incomes');
    
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
