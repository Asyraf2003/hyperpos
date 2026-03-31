<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class EditableWorkspaceNoteGuard
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly PaymentAllocationReaderPort $allocations,
    ) {
    }

    public function assertEditable(string $noteId): void
    {
        $normalized = trim($noteId);

        if ($normalized === '') {
            throw new DomainException('Note id wajib ada.');
        }

        $note = $this->notes->getById($normalized);

        if ($note === null) {
            throw new DomainException('Nota tidak ditemukan.');
        }

        $allocated = $this->allocations->getTotalAllocatedAmountByNoteId($normalized)->amount();

        if ($allocated > 0) {
            throw new DomainException('Nota yang sudah memiliki pembayaran tidak boleh diedit lewat workspace.');
        }
    }
}
