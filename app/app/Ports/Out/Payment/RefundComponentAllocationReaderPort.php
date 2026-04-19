<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\RefundComponentAllocation\RefundComponentAllocation;
use App\Core\Shared\ValueObjects\Money;

interface RefundComponentAllocationReaderPort
{
    public function getTotalRefundedAmountByNoteId(string $noteId): Money;

    public function getTotalRefundedAmountByCustomerPaymentIdAndNoteId(
        string $customerPaymentId,
        string $noteId,
    ): Money;

    public function getTotalRefundedAmountByWorkItemId(string $workItemId): Money;

    /**
     * @return list<RefundComponentAllocation>
     */
    public function listByNoteId(string $noteId): array;
}
