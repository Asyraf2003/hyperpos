<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/health', HealthCheckController::class)
        ->name('health');
});