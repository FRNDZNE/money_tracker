<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('accounts', AccountController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('categories.sub-categories', SubCategoryController::class)
        ->only(['store', 'update', 'destroy']);
    Route::resource('transactions', TransactionController::class)->except(['show']);
    Route::resource('transfers', TransferController::class)->except(['show']);

    // Phase 2 — Budget Management
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::patch('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    // Phase 2 — Savings Goals
    Route::resource('savings-goals', SavingsGoalController::class)->except(['show']);
    Route::post('/savings-goals/{savings_goal}/top-up', [SavingsGoalController::class, 'topUp'])
        ->name('savings-goals.top-up');

    // Phase 2 — Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');

    // Phase 2 — Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

