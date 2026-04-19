<?php

declare(strict_types=1);

namespace App\Application\Note\Policies;

use App\Core\Note\Note\Note;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Payment\CustomerRefundReaderPort;
use App\Ports\Out\Payment\PaymentAllocationReaderPort;

final class NotePaidStatusPolicy
{
    public function __construct(
        private readonly PaymentAllocationReaderPort $allocations,
        private readonly CustomerRefundReaderPort $refunds,
    ) {
    }

    public function isPaid(Note $note): bool
    {
        if ($note->totalRupiah()->equals(Money::zero())) {
            return false;
        }

        $allocated = $this->allocations->getTotalAllocatedAmountByNoteId($note->id());
        $allocated->ensureNotNegative('Total alokasi pada note tidak boleh negatif.');

        $refunded = $this->refunds->getTotalRefundedAmountByNoteId($note->id());
        $refunded->ensureNotNegative('Total refund pada note tidak boleh negatif.');

        $netSettlement = $allocated->subtract($refunded);
        $netSettlement->ensureNotNegative('Net settlement pada note tidak boleh negatif.');

        return $netSettlement->greaterThanOrEqual($note->totalRupiah());
    }

    public function assertNotPaidForStandardMutation(Note $note): void
    {
        if ($this->isPaid($note)) {
            throw new DomainException('Work item pada note yang sudah lunas tidak boleh diubah lewat flow biasa.');
        }
    }

    public function assertPaidForCorrection(Note $note): void
    {
        if ($this->isPaid($note)) {
            return;
        }

        throw new DomainException('Correction hanya boleh dilakukan pada note yang sudah lunas.');
    }
}
