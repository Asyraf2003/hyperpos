<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Product\CreateProductPageController;
use App\Adapters\In\Http\Controllers\Admin\Product\DeleteProductController;
use App\Adapters\In\Http\Controllers\Admin\Product\EditProductPageController;
use App\Adapters\In\Http\Controllers\Admin\Product\EditProductStockPageController;
use App\Adapters\In\Http\Controllers\Admin\Product\ProductIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Product\ProductTableDataController;
use App\Adapters\In\Http\Controllers\Admin\Product\RecordProductStockAdjustmentController;
use App\Adapters\In\Http\Controllers\Admin\Product\RestoreProductController;
use App\Adapters\In\Http\Controllers\Admin\Product\ReverseProductStockAdjustmentController;
use App\Adapters\In\Http\Controllers\Admin\Product\ShowProductPageController;
use App\Adapters\In\Http\Controllers\Admin\Product\StoreProductController;
use App\Adapters\In\Http\Controllers\Admin\Product\UpdateProductController as WebUpdateProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page'])->group(function (): void {
    Route::get('/admin/products/table', ProductTableDataController::class)
        ->name('admin.products.table');

    Route::post('/admin/products/{productId}/stock-adjustments', RecordProductStockAdjustmentController::class)
        ->name('admin.products.stock-adjustments.store');

    Route::patch('/admin/products/{productId}/stock-adjustments/{adjustmentId}/reverse', ReverseProductStockAdjustmentController::class)
        ->name('admin.products.stock-adjustments.reverse');

});

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/products', ProductIndexPageController::class)
        ->name('admin.products.index');

    Route::get('/admin/products/create', CreateProductPageController::class)
        ->name('admin.products.create');

    Route::post('/admin/products', StoreProductController::class)
        ->name('admin.products.store');

    Route::get('/admin/products/{productId}', ShowProductPageController::class)
        ->name('admin.products.show');

    Route::get('/admin/products/{productId}/edit', EditProductPageController::class)
        ->name('admin.products.edit');


    Route::get('/admin/products/{productId}/stock', EditProductStockPageController::class)
        ->name('admin.products.stock.edit');

    Route::put('/admin/products/{productId}', WebUpdateProductController::class)
        ->name('admin.products.update');

    Route::patch('/admin/products/{productId}/restore', RestoreProductController::class)
        ->name('admin.products.restore');

    Route::delete('/admin/products/{productId}', DeleteProductController::class)
        ->name('admin.products.delete');

});
