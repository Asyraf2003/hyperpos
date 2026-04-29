<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class AutoReverseRefundedStoreStockInventory
{
    public function __construct(
        private readonly RefundComponentAllocationReaderPort $refundAllocations,
        private readonly PaymentComponentAllocationReaderPort $paymentAllocations,
        private readonly InventoryMovementReaderPort $movements,
        private readonly ReverseIssuedInventoryOperation $reverseIssuedInventory,
        private readonly RefundedStoreStockComponentTargets $targets,
    ) {
    }

    public function execute(CustomerRefund $refund): void
    {
        $noteId = $refund->noteId();
        $allocated = $this->targets->totals($this->paymentAllocations->listByNoteId($noteId), false);
        $refunded = $this->targets->totals($this->refundAllocations->listByNoteId($noteId), true);

        foreach ($this->refundAllocations->listByNoteId($noteId) as $allocation) {
            if ($allocation->customerRefundId() !== $refund->id()) {
                continue;
            }

            $type = $allocation->componentType();
            if (! $this->targets->supports($type)) {
                continue;
            }

            $key = $type . '::' . $allocation->componentRefId();
            if (($allocated[$key] ?? 0) < 1 || ($refunded[$key] ?? 0) < ($allocated[$key] ?? 0)) {
                continue;
            }

            $this->reverseTargetLines($type, $allocation->componentRefId(), $refund);
        }
    }

    public function executeFullRowReversal(CustomerRefund $refund): void
    {
        foreach ($this->refundAllocations->listByNoteId($refund->noteId()) as $allocation) {
            if ($allocation->customerRefundId() !== $refund->id()) {
                continue;
            }

            $type = $allocation->componentType();
            if (! $this->targets->supports($type)) {
                continue;
            }

            $this->reverseTargetLines($type, $allocation->componentRefId(), $refund);
        }
    }

    private function reverseTargetLines(string $type, string $componentRefId, CustomerRefund $refund): void
    {
        foreach ($this->targets->lineIds($type, $componentRefId) as $lineId) {
            if ($this->movements->getBySource('work_item_store_stock_line_reversal', $lineId) !== []) {
                continue;
            }

            $this->reverseIssuedInventory->execute(
                'work_item_store_stock_line',
                $lineId,
                $refund->refundedAt(),
                'work_item_store_stock_line_reversal',
            );
        }
    }
}
