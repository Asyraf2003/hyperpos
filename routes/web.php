<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\HealthCheckController;
use App\Adapters\In\Http\Controllers\IdentityAccess\DisableAdminTransactionCapabilityController;
use App\Adapters\In\Http\Controllers\IdentityAccess\EnableAdminTransactionCapabilityController;
use App\Adapters\In\Http\Controllers\Note\CreateNoteController;
use App\Adapters\In\Http\Controllers\Procurement\CreateSupplierInvoiceController;
use App\Adapters\In\Http\Controllers\Procurement\ReceiveSupplierInvoiceController;
use App\Adapters\In\Http\Controllers\ProductCatalog\CreateProductController;
use App\Adapters\In\Http\Controllers\ProductCatalog\UpdateProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', HealthCheckController::class);

Route::post(
    '/identity-access/admin-transaction-capability/enable',
    EnableAdminTransactionCapabilityController::class,
);

Route::post(
    '/identity-access/admin-transaction-capability/disable',
    DisableAdminTransactionCapabilityController::class,
);

Route::middleware('transaction.entry')->group(function (): void {
    Route::post(
        '/notes/create',
        CreateNoteController::class,
    );

    Route::post(
        '/product-catalog/products/create',
        CreateProductController::class,
    );

    Route::post(
        '/product-catalog/products/{productId}/update',
        UpdateProductController::class,
    );

    Route::post(
        '/procurement/supplier-invoices/create',
        CreateSupplierInvoiceController::class,
    );

    Route::post(
        '/procurement/supplier-invoices/{supplierInvoiceId}/receive',
        ReceiveSupplierInvoiceController::class,
    );

    // Employee Finance
    Route::post('/employee-finance/employees/register', \App\Adapters\In\Http\Controllers\EmployeeFinance\RegisterEmployeeController::class);
    Route::post('/employee-finance/employees/{employeeId}/update-salary', \App\Adapters\In\Http\Controllers\EmployeeFinance\UpdateEmployeeBaseSalaryController::class);
    Route::post('/employee-finance/debts/record', \App\Adapters\In\Http\Controllers\EmployeeFinance\RecordEmployeeDebtController::class);
    Route::post('/employee-finance/debts/{debtId}/pay', \App\Adapters\In\Http\Controllers\EmployeeFinance\PayEmployeeDebtController::class);
    Route::post('/employee-finance/payroll/disburse', \App\Adapters\In\Http\Controllers\EmployeeFinance\DisbursePayrollController::class);
});
