<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Procurement\CreateSupplierInvoiceController;
use App\Adapters\In\Http\Controllers\Procurement\ReceiveSupplierInvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/procurement/supplier-invoices/create', CreateSupplierInvoiceController::class)
        ->name('procurement.supplier-invoices.create');

    Route::post('/procurement/supplier-invoices/{supplierInvoiceId}/receive', ReceiveSupplierInvoiceController::class)
        ->name('procurement.supplier-invoices.receive');
});
