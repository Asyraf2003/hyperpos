<?php

declare(strict_types=1);

namespace App\Ports\Out\Payment;

use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Core\Payment\CustomerPayment\CustomerPaymentCashDetail;

interface CustomerPaymentWriterPort
{
    public function create(CustomerPayment $customerPayment, ?CustomerPaymentCashDetail $cashDetail = null): void;
}
