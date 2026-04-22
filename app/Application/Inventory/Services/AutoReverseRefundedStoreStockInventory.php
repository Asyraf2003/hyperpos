<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
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
    ) {
    }

    public function execute(CustomerRefund $refund): void
    {
        $noteId = $refund->noteId();
        $paymentAllocations = $this->paymentAllocations->listByNoteId($noteId);
        $refundAllocations = $this->refundAllocations->listByNoteId($noteId);

        $allocatedTotals = [];
        foreach ($paymentAllocations as $allocation) {
            if (! $this->supportsStockReversal($allocation->componentType())) {
                continue;
            }

            $componentRefId = $allocation->componentRefId();
            $allocatedTotals[$componentRefId] = ($allocatedTotals[$componentRefId] ?? 0)
                + $allocation->allocatedAmountRupiah()->amount();
        }

        $refundedTotals = [];
        foreach ($refundAllocations as $allocation) {
            if (! $this->supportsStockReversal($allocation->componentType())) {
                continue;
            }

            $componentRefId = $allocation->componentRefId();
            $refundedTotals[$componentRefId] = ($refundedTotals[$componentRefId] ?? 0)
                + $allocation->refundedAmountRupiah()->amount();
        }

        $candidateLineIds = [];
        foreach ($refundAllocations as $allocation) {
            if ($allocation->customerRefundId() !== $refund->id()) {
                continue;
            }

            if (! $this->supportsStockReversal($allocation->componentType())) {
                continue;
            }

            $candidateLineIds[$allocation->componentRefId()] = true;
        }

        foreach (array_keys($candidateLineIds) as $lineId) {
            $allocated = (int) ($allocatedTotals[$lineId] ?? 0);
            $refunded = (int) ($refundedTotals[$lineId] ?? 0);

            if ($allocated < 1 || $refunded < $allocated) {
                continue;
            }

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

    private function supportsStockReversal(string $componentType): bool
    {
        return in_array($componentType, [
            PaymentComponentType::SERVICE_STORE_STOCK_PART,
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
        ], true);
    }
}
