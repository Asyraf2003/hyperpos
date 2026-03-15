<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\CustomerRefund\CustomerRefund;

interface CustomerRefundWriterPort
{
    public function create(CustomerRefund $customerRefund): void;
}
