<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\AdminDashboardPageController;
use App\Adapters\In\Http\Controllers\Cashier\CashierDashboardPageController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::redirect('/', '/login');

    Route::get('/admin/dashboard', AdminDashboardPageController::class)
        ->name('admin.dashboard');

    Route::get('/cashier/dashboard', CashierDashboardPageController::class)
        ->name('cashier.dashboard');
});
