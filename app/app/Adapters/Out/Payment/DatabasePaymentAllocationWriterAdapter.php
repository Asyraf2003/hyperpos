<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Core\Payment\PaymentAllocation\PaymentAllocation;
use App\Ports\Out\Payment\PaymentAllocationWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabasePaymentAllocationWriterAdapter implements PaymentAllocationWriterPort
{
    public function create(PaymentAllocation $paymentAllocation): void
    {
        DB::table('payment_allocations')->insert([
            'id' => $paymentAllocation->id(),
            'customer_payment_id' => $paymentAllocation->customerPaymentId(),
            'note_id' => $paymentAllocation->noteId(),
            'amount_rupiah' => $paymentAllocation->amountRupiah()->amount(),
        ]);
    }
}
