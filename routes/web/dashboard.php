<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\AdminDashboardPageController;
use App\Adapters\In\Http\Controllers\Cashier\CashierDashboardPageController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::redirect('/', '/login');

    Route::middleware(['auth', 'admin.page'])->group(function (): void {
        Route::get('/admin/dashboard', AdminDashboardPageController::class)
            ->name('admin.dashboard');
    });

    Route::middleware(['auth', 'cashier.area'])->group(function (): void {
        Route::get('/cashier/dashboard', CashierDashboardPageController::class)
            ->name('cashier.dashboard');
    });
});