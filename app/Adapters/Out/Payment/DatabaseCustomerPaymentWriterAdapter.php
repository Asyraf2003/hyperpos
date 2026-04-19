<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Payment\CustomerPayment\CustomerPayment;
use App\Ports\Out\Payment\CustomerPaymentWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseCustomerPaymentWriterAdapter implements CustomerPaymentWriterPort
{
    public function create(CustomerPayment $customerPayment): void
    {
        DB::table('customer_payments')->insert([
            'id' => $customerPayment->id(),
            'amount_rupiah' => $customerPayment->amountRupiah()->amount(),
            'paid_at' => $customerPayment->paidAt()->format('Y-m-d'),
        ]);
    }
}
