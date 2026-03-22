<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Payroll\CreatePayrollPageController;
use App\Adapters\In\Http\Controllers\Admin\Payroll\PayrollIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Payroll\StorePayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/payrolls', PayrollIndexPageController::class)
        ->name('admin.payrolls.index');

    Route::get('/admin/payrolls/create', CreatePayrollPageController::class)
        ->name('admin.payrolls.create');

    Route::post('/admin/payrolls', StorePayrollController::class)
        ->name('admin.payrolls.store');
});
