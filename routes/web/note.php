<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Note\CreateNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/notes/create', CreateNoteController::class)
        ->name('notes.create');
});
Route::middleware([
    'auth',
    \App\Adapters\In\Http\Middleware\IdentityAccess\EnsureCashierAreaAccess::class,
    \App\Adapters\In\Http\Middleware\IdentityAccess\EnsureTransactionEntryAllowed::class,
])->prefix('cashier/notes')->name('cashier.notes.')->group(function (): void {
    Route::get('/create', \App\Adapters\In\Http\Controllers\Cashier\Note\CreateNotePageController::class)
        ->name('create');

    Route::get('/prototype/{noteId}', \App\Adapters\In\Http\Controllers\Cashier\Note\NoteDetailPageController::class)
        ->name('prototype.show');
});
