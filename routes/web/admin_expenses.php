<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Expense\CreateExpenseCategoryPageController;
use App\Adapters\In\Http\Controllers\Admin\Expense\ExpenseCategoryIndexPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/expenses/categories', ExpenseCategoryIndexPageController::class)
        ->name('admin.expenses.categories.index');

    Route::get('/admin/expenses/categories/create', CreateExpenseCategoryPageController::class)
        ->name('admin.expenses.categories.create');
});
