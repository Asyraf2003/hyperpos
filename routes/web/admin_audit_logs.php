<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Admin\AuditLog\AuditLogIndexPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin.page', 'app.shell'])
    ->prefix('admin/audit-logs')
    ->name('admin.audit-logs.')
    ->group(function (): void {
        Route::get('/', AuditLogIndexPageController::class)->name('index');
    });
