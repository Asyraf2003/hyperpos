<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Auth\LoginPageController;
use App\Adapters\In\Http\Controllers\Auth\RegisterPageController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function (): void {
    Route::get('/login', LoginPageController::class)->name('login');
    Route::get('/register', RegisterPageController::class)->name('register');
});
