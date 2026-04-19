<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Ports\Out\Payment\CustomerRefundWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseCustomerRefundWriterAdapter implements CustomerRefundWriterPort
{
    public function create(CustomerRefund $customerRefund): void
    {
        DB::table('customer_refunds')->insert([
            'id' => $customerRefund->id(),
            'customer_payment_id' => $customerRefund->customerPaymentId(),
            'note_id' => $customerRefund->noteId(),
            'amount_rupiah' => $customerRefund->amountRupiah()->amount(),
            'refunded_at' => $customerRefund->refundedAt()->format('Y-m-d'),
            'reason' => $customerRefund->reason(),
        ]);
    }
}
