<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\CustomerPayment\CustomerPayment;

interface CustomerPaymentWriterPort
{
    public function create(CustomerPayment $customerPayment): void;
}
