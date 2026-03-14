<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Shared\ValueObjects\Money;

interface CustomerPaymentReaderPort
{
    public function getById(string $id): ?CustomerPayment;

    public function getTotalAllocatedAmountByPaymentId(string $customerPaymentId): Money;
}
