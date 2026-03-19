<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Procurement\ProcurementInvoiceIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\ProcurementInvoiceTableDataController;
use App\Adapters\In\Http\Controllers\Admin\Supplier\SupplierIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Supplier\SupplierTableDataController;
use App\Adapters\In\Http\Controllers\Procurement\CreateSupplierInvoiceController;
use App\Adapters\In\Http\Controllers\Procurement\ReceiveSupplierInvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page'])->group(function (): void {
    Route::get('/admin/suppliers/table', SupplierTableDataController::class)
        ->name('admin.suppliers.table');

    Route::get('/admin/procurement/supplier-invoices/table', ProcurementInvoiceTableDataController::class)
        ->name('admin.procurement.supplier-invoices.table');
});

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/suppliers', SupplierIndexPageController::class)
        ->name('admin.suppliers.index');

    Route::get('/admin/procurement/supplier-invoices', ProcurementInvoiceIndexPageController::class)
        ->name('admin.procurement.supplier-invoices.index');
});

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/procurement/supplier-invoices/create', CreateSupplierInvoiceController::class)
        ->name('procurement.supplier-invoices.create');

    Route::post('/procurement/supplier-invoices/{supplierInvoiceId}/receive', ReceiveSupplierInvoiceController::class)
        ->name('procurement.supplier-invoices.receive');
});
