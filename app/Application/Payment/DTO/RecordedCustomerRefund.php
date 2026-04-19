<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

use App\Core\Payment\CustomerRefund\CustomerRefund;

final class RecordedCustomerRefund
{
    public function __construct(
        private readonly CustomerRefund $refund,
        private readonly int $allocationCount,
    ) {
    }

    public function refund(): CustomerRefund
    {
        return $this->refund;
    }

    public function allocationCount(): int
    {
        return $this->allocationCount;
    }
}
