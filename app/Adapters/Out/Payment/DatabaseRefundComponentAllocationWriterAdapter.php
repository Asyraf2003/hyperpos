<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Ports\Out\Payment\RefundComponentAllocationWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseRefundComponentAllocationWriterAdapter implements RefundComponentAllocationWriterPort
{
    public function createMany(array $allocations): void
    {
        if ($allocations === []) {
            return;
        }

        DB::table('refund_component_allocations')->insert(array_map(
            static fn ($allocation): array => [
                'id' => $allocation->id(),
                'customer_refund_id' => $allocation->customerRefundId(),
                'customer_payment_id' => $allocation->customerPaymentId(),
                'note_id' => $allocation->noteId(),
                'work_item_id' => $allocation->workItemId(),
                'component_type' => $allocation->componentType(),
                'component_ref_id' => $allocation->componentRefId(),
                'refunded_amount_rupiah' => $allocation->refundedAmountRupiah()->amount(),
                'refund_priority' => $allocation->refundPriority(),
            ],
            $allocations
        ));
    }
}
