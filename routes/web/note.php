<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Cashier\Note\CreateNotePageController;
use App\Adapters\In\Http\Controllers\Cashier\Note\NoteDetailPageController;
use App\Adapters\In\Http\Controllers\Note\CreateNoteController;
use App\Adapters\In\Http\Controllers\Note\RecordNotePaymentController;
use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureCashierAreaAccess;
use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureTransactionEntryAllowed;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/notes/create', CreateNoteController::class)->name('notes.create');
});

Route::middleware(['auth', EnsureCashierAreaAccess::class, EnsureTransactionEntryAllowed::class, 'app.shell'])
    ->prefix('cashier/notes')
    ->name('cashier.notes.')
    ->group(function (): void {
        Route::get('/create', CreateNotePageController::class)->name('create');
        Route::get('/{noteId}', NoteDetailPageController::class)->name('show');
        Route::post('/{noteId}/payments', RecordNotePaymentController::class)->name('payments.store');
        Route::get('/prototype/{noteId}', NoteDetailPageController::class)->name('prototype.show');
    });
