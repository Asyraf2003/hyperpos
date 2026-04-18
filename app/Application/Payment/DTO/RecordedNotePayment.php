<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

use App\Core\Payment\CustomerPayment\CustomerPayment;

final class RecordedNotePayment
{
    public function __construct(
        private readonly CustomerPayment $payment,
        private readonly int $allocationCount,
    ) {
    }

    public function payment(): CustomerPayment
    {
        return $this->payment;
    }

    public function allocationCount(): int
    {
        return $this->allocationCount;
    }
}
