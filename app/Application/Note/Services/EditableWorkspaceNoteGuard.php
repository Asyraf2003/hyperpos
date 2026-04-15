<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Note\NoteReaderPort;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class EditableWorkspaceNoteGuard
{
    public function __construct(
        private readonly NoteReaderPort $notes,
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly CustomerRefundReaderPort $refunds,
        private readonly NoteOperationalStatusEvaluator $statuses,
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

        $allocated = $this->allocations->getTotalAllocatedAmountByNoteId($normalized);
        $allocated->ensureNotNegative('Total alokasi pada note tidak boleh negatif.');

        $refunded = $this->refunds->getTotalRefundedAmountByNoteId($normalized);
        $refunded->ensureNotNegative('Total refund pada note tidak boleh negatif.');

        $netPaid = $allocated->subtract($refunded);
        $netPaid->ensureNotNegative('Net settlement pada note tidak boleh negatif.');

        if ($this->statuses->isClose($note->totalRupiah()->amount(), $netPaid->amount())) {
            throw new DomainException('Nota close tidak boleh diedit lewat workspace.');
        }
    }
}
