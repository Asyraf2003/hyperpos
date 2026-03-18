<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Note\CreateNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/notes/create', CreateNoteController::class)
        ->name('notes.create');
});