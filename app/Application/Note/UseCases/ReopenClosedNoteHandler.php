<?php

declare(strict_types=1);

namespace App\Application\Note\UseCases;

use App\Application\Note\Services\ReopenClosedNoteTransaction;
use App\Application\Shared\DTO\Result;

final class ReopenClosedNoteHandler
{
    public function __construct(
        private readonly ReopenClosedNoteTransaction $transaction,
    ) {
    }

    public function handle(string $noteId, string $reason, string $performedByActorId): Result
    {
        if (trim($reason) === '') {
            return Result::failure('Alasan reopen wajib diisi.', ['note' => ['AUDIT_REASON_REQUIRED']]);
        }

        return $this->transaction->run($noteId, $reason, $performedByActorId);
    }
}
