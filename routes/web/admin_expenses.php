<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Expense\CreateExpenseCategoryPageController;
use App\Adapters\In\Http\Controllers\Admin\Expense\CreateExpensePageController;
use App\Adapters\In\Http\Controllers\Admin\Expense\ExpenseCategoryIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Expense\ExpenseIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Expense\ExpenseTableDataController;
use App\Adapters\In\Http\Controllers\Admin\Expense\StoreExpenseCategoryController;
use App\Adapters\In\Http\Controllers\Admin\Expense\StoreExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page'])->group(function (): void {
    Route::get('/admin/expenses/table', ExpenseTableDataController::class)
        ->name('admin.expenses.table');

    Route::post('/admin/expenses', StoreExpenseController::class)
        ->name('admin.expenses.store');

    Route::post('/admin/expenses/categories', StoreExpenseCategoryController::class)
        ->name('admin.expenses.categories.store');
});

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/expenses', ExpenseIndexPageController::class)
        ->name('admin.expenses.index');

    Route::get('/admin/expenses/create', CreateExpensePageController::class)
        ->name('admin.expenses.create');

    Route::get('/admin/expenses/categories', ExpenseCategoryIndexPageController::class)
        ->name('admin.expenses.categories.index');

    Route::get('/admin/expenses/categories/create', CreateExpenseCategoryPageController::class)
        ->name('admin.expenses.categories.create');
});
