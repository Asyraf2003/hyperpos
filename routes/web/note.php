<?php

declare(strict_types=1);

use App\Adapters\In\Http\Controllers\Cashier\Note\CreateNotePageController;
use App\Adapters\In\Http\Controllers\Cashier\Note\NoteDetailPageController;
use App\Adapters\In\Http\Controllers\Note\AddNoteRowsController;
use App\Adapters\In\Http\Controllers\Note\CorrectPaidServiceOnlyWorkItemController;
use App\Adapters\In\Http\Controllers\Note\CorrectPaidWorkItemStatusController;
use App\Adapters\In\Http\Controllers\Note\CreateNoteController;
use App\Adapters\In\Http\Controllers\Note\RecordNotePaymentController;
use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureCashierAreaAccess;
use App\Adapters\In\Http\Middleware\IdentityAccess\EnsureTransactionEntryAllowed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'transaction.entry'])->group(function (): void {
    Route::post('/notes/create', CreateNoteController::class)->name('notes.create');
});

Route::middleware(['auth', EnsureCashierAreaAccess::class, EnsureTransactionEntryAllowed::class, 'app.shell'])
    ->prefix('cashier/notes')
    ->name('cashier.notes.')
    ->group(function (): void {
        Route::get('/create', CreateNotePageController::class)->name('create');
        Route::get('/prototype/{noteId}', fn (string $noteId): RedirectResponse => redirect()->route('cashier.notes.show', ['noteId' => $noteId]))->name('prototype.show');
        Route::get('/{noteId}', NoteDetailPageController::class)->name('show');
        Route::post('/{noteId}/rows', AddNoteRowsController::class)->name('rows.store');
        Route::post('/{noteId}/payments', RecordNotePaymentController::class)->name('payments.store');
        Route::post('/{noteId}/corrections/status', CorrectPaidWorkItemStatusController::class)->name('corrections.status.store');
        Route::post('/{noteId}/corrections/service-only', CorrectPaidServiceOnlyWorkItemController::class)->name('corrections.service-only.store');
    });
