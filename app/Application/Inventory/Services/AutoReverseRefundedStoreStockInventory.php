<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class AutoReverseRefundedStoreStockInventory
{
    public function __construct(
        private readonly RefundComponentAllocationReaderPort $refundAllocations,
        private readonly ReverseIssuedInventoryOperation $reverseIssuedInventory,
    ) {
    }

    public function execute(CustomerRefund $refund): void
    {
        $reversedLineIds = [];

        foreach ($this->refundAllocations->listByNoteId($refund->noteId()) as $allocation) {
            if ($allocation->customerRefundId() !== $refund->id()) {
                continue;
            }

            if ($allocation->componentType() !== PaymentComponentType::SERVICE_STORE_STOCK_PART) {
                continue;
            }

            $lineId = $allocation->componentRefId();

            if (isset($reversedLineIds[$lineId])) {
                continue;
            }

            $this->reverseIssuedInventory->execute(
                'work_item_store_stock_line',
                $lineId,
                $refund->refundedAt(),
                'work_item_store_stock_line_reversal',
            );

            $reversedLineIds[$lineId] = true;
        }
    }
}
