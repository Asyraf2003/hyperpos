<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentAllocation;
use App\Core\Shared\ValueObjects\Money;

interface PaymentComponentAllocationReaderPort
{
    public function getTotalAllocatedAmountByNoteId(string $noteId): Money;

    public function getTotalAllocatedAmountByCustomerPaymentIdAndNoteId(
        string $customerPaymentId,
        string $noteId,
    ): Money;

    public function getTotalAllocatedAmountByWorkItemId(string $workItemId): Money;

    /**
     * @return list<PaymentComponentAllocation>
     */
    public function listByNoteId(string $noteId): array;
}
