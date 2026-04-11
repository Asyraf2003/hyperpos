<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\CreateEmployeeDebtPageController;
use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\EmployeeDebtDetailPageController;
use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\EmployeeDebtIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\EmployeeDebtTableDataController;
use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\StoreEmployeeDebtAdjustmentController;
use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\StoreEmployeeDebtController;
use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\StoreEmployeeDebtPaymentController;
use App\Adapters\In\Http\Controllers\Admin\EmployeeDebt\StoreEmployeeDebtPaymentReversalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page'])->group(function (): void {
    Route::get('/admin/employee-debts/table', EmployeeDebtTableDataController::class)
        ->name('admin.employee-debts.table');

    Route::post('/admin/employee-debts/{debtId}/payments', StoreEmployeeDebtPaymentController::class)
        ->name('admin.employee-debts.payments.store');

    Route::post('/admin/employee-debts/{debtId}/adjustments', StoreEmployeeDebtAdjustmentController::class)
        ->name('admin.employee-debts.adjustments.store');

    Route::post('/admin/employee-debt-payments/{paymentId}/reverse', StoreEmployeeDebtPaymentReversalController::class)
        ->name('admin.employee-debt-payments.reverse.store');
});

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/employee-debts', EmployeeDebtIndexPageController::class)
        ->name('admin.employee-debts.index');

    Route::get('/admin/employee-debts/create', CreateEmployeeDebtPageController::class)
        ->name('admin.employee-debts.create');

    Route::post('/admin/employee-debts', StoreEmployeeDebtController::class)
        ->name('admin.employee-debts.store');

    Route::get('/admin/employee-debts/{debtId}', EmployeeDebtDetailPageController::class)
        ->name('admin.employee-debts.show');
});
