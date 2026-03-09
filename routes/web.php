<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Adapters\In\Http\Controllers\HealthCheckController;

Route::get('/health', HealthCheckController::class);
