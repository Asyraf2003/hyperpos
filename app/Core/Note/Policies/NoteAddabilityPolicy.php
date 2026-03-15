<?php

declare(strict_types=1);

namespace App\Core\Note\Policies;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class NoteAddabilityPolicy
{
    public function __construct(private PaymentAllocationReaderPort $allocations) {}

    public function assertAllowed(Note $note): void
    {
        if ($note->totalRupiah()->equals(Money::zero())) return;

        $allocated = $this->allocations->getTotalAllocatedAmountByNoteId($note->id());
        $allocated->ensureNotNegative('Total alokasi pada note tidak boleh negatif.');

        if ($allocated->greaterThanOrEqual($note->totalRupiah())) {
            throw new DomainException('Item baru tidak boleh ditambahkan ke note yang sudah lunas.');
        }
    }
}
