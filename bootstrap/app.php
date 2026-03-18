<?php

use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureAdminPageAccess;
use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureCashierAreaAccess;
use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureTransactionEntryAllowed;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'transaction.entry' => EnsureTransactionEntryAllowed::class,
            'admin.page' => EnsureAdminPageAccess::class,
            'cashier.area' => EnsureCashierAreaAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();