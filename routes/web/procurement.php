<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Procurement\AttachSupplierPaymentProofController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\CreateSupplierInvoicePageController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\ProductLookupController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\ProcurementInvoiceDetailPageController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\ProcurementInvoiceIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\ProcurementInvoiceTableDataController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\RecordSupplierPaymentController;
use App\Adapters\In\Http\Controllers\Admin\Procurement\StoreSupplierInvoiceController;
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

    Route::get('/admin/procurement/products/lookup', ProductLookupController::class)
        ->name('admin.procurement.products.lookup');

    Route::post('/admin/procurement/supplier-invoices/{supplierInvoiceId}/payments', RecordSupplierPaymentController::class)
        ->name('admin.procurement.supplier-invoices.payments.store');

    Route::post('/admin/procurement/supplier-payments/{supplierPaymentId}/proof', AttachSupplierPaymentProofController::class)
        ->name('admin.procurement.supplier-payments.proof.store');
});

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/suppliers', SupplierIndexPageController::class)
        ->name('admin.suppliers.index');

    Route::get('/admin/procurement/supplier-invoices', ProcurementInvoiceIndexPageController::class)
        ->name('admin.procurement.supplier-invoices.index');

    Route::get('/admin/procurement/supplier-invoices/create', CreateSupplierInvoicePageController::class)
        ->name('admin.procurement.supplier-invoices.create');

    Route::get('/admin/procurement/supplier-invoices/{supplierInvoiceId}', ProcurementInvoiceDetailPageController::class)
        ->name('admin.procurement.supplier-invoices.show');

    Route::post('/admin/procurement/supplier-invoices', StoreSupplierInvoiceController::class)
        ->name('admin.procurement.supplier-invoices.store');
});

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/procurement/supplier-invoices/create', CreateSupplierInvoiceController::class)
        ->name('procurement.supplier-invoices.create');

    Route::post('/procurement/supplier-invoices/{supplierInvoiceId}/receive', ReceiveSupplierInvoiceController::class)
        ->name('procurement.supplier-invoices.receive');
});
