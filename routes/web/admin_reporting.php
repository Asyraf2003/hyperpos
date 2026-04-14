<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Reporting\EmployeeDebtReportPageController;
use App\Adapters\In\Http\Controllers\Admin\Reporting\InventoryStockValueReportPageController;
use App\Adapters\In\Http\Controllers\Admin\Reporting\OperationalProfitReportPageController;
use App\Adapters\In\Http\Controllers\Admin\Reporting\SupplierPayableReportPageController;
use App\Adapters\In\Http\Controllers\Admin\Reporting\TransactionCashLedgerPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/reports/transaction-cash-ledger', TransactionCashLedgerPageController::class)
        ->name('admin.reports.transaction_cash_ledger.index');

    Route::get('/admin/reports/employee-debts', EmployeeDebtReportPageController::class)
        ->name('admin.reports.employee_debt.index');

    Route::get('/admin/reports/operational-profit', OperationalProfitReportPageController::class)
        ->name('admin.reports.operational_profit.index');

    Route::get('/admin/reports/supplier-payables', SupplierPayableReportPageController::class)
        ->name('admin.reports.supplier_payable.index');

    Route::get('/admin/reports/inventory-stock-value', InventoryStockValueReportPageController::class)
        ->name('admin.reports.inventory_stock_value.index');
});
