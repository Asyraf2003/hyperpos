<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Shared\ValueObjects\Money;

interface CustomerRefundReaderPort
{
    public function getTotalRefundedAmountByNoteId(string $noteId): Money;

    public function getTotalRefundedAmountByCustomerPaymentIdAndNoteId(string $customerPaymentId, string $noteId): Money;
}
