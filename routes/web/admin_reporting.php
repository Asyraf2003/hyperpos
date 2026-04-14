<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Reporting\TransactionCashLedgerPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/reports/transaction-cash-ledger', TransactionCashLedgerPageController::class)
        ->name('admin.reports.transaction_cash_ledger.index');
});
