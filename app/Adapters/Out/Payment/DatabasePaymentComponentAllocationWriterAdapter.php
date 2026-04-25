<?php

declare(strict_types=1);

namespace App\Adapters\Out\Payment;

use App\Ports\Out\Payment\PaymentComponentAllocationWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabasePaymentComponentAllocationWriterAdapter implements PaymentComponentAllocationWriterPort
{
    public function createMany(array $allocations): void
    {
        if ($allocations === []) {
            return;
        }

        DB::table('payment_component_allocations')->insert(array_map(
            static fn ($allocation): array => [
                'id' => $allocation->id(),
                'customer_payment_id' => $allocation->customerPaymentId(),
                'note_id' => $allocation->noteId(),
                'work_item_id' => $allocation->workItemId(),
                'component_type' => $allocation->componentType(),
                'component_ref_id' => $allocation->componentRefId(),
                'component_amount_rupiah_snapshot' => $allocation->componentAmountRupiahSnapshot()->amount(),
                'allocated_amount_rupiah' => $allocation->allocatedAmountRupiah()->amount(),
                'allocation_priority' => $allocation->allocationPriority(),
            ],
            $allocations
        ));
    }

    public function deleteByNoteId(string $noteId): void
    {
        DB::table('payment_component_allocations')
            ->where('note_id', trim($noteId))
            ->delete();
    }
}
