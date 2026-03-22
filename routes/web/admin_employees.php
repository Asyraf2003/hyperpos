<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\Employee\CreateEmployeePageController;
use App\Adapters\In\Http\Controllers\Admin\Employee\EditEmployeePageController;
use App\Adapters\In\Http\Controllers\Admin\Employee\EmployeeDetailPageController;
use App\Adapters\In\Http\Controllers\Admin\Employee\EmployeeIndexPageController;
use App\Adapters\In\Http\Controllers\Admin\Employee\EmployeeTableDataController;
use App\Adapters\In\Http\Controllers\Admin\Employee\StoreEmployeeController;
use App\Adapters\In\Http\Controllers\Admin\Employee\UpdateEmployeeController;
use App\Adapters\In\Http\Controllers\Admin\Employee\EmployeePayrollTableDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page'])->group(function (): void {
    Route::get('/admin/employees/table', EmployeeTableDataController::class)->name('admin.employees.table');
    Route::get('/admin/employees/{employeeId}/payroll-table', EmployeePayrollTableDataController::class)->name('admin.employees.payroll-table');
});

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])->group(function (): void {
    Route::get('/admin/employees', EmployeeIndexPageController::class)->name('admin.employees.index');
    Route::get('/admin/employees/create', CreateEmployeePageController::class)->name('admin.employees.create');
    Route::post('/admin/employees', StoreEmployeeController::class)->name('admin.employees.store');
    Route::get('/admin/employees/{employeeId}', EmployeeDetailPageController::class)->name('admin.employees.show');
    Route::get('/admin/employees/{employeeId}/edit', EditEmployeePageController::class)->name('admin.employees.edit');
    Route::put('/admin/employees/{employeeId}', UpdateEmployeeController::class)->name('admin.employees.update');
});