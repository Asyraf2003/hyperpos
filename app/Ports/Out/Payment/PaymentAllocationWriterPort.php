<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\PaymentAllocation\PaymentAllocation;

interface PaymentAllocationWriterPort
{
    public function create(PaymentAllocation $paymentAllocation): void;
}
