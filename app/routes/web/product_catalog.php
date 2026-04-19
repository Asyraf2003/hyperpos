<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\ProductCatalog\CreateProductController;
use App\Adapters\In\Http\Controllers\ProductCatalog\UpdateProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/product-catalog/products/create', CreateProductController::class)
        ->name('product-catalog.products.create');

    Route::post('/product-catalog/products/{productId}/update', UpdateProductController::class)
        ->name('product-catalog.products.update');
});
