<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Core\Payment\CustomerRefund\CustomerRefund;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use App\Ports\Out\Note\WorkItemStoreStockLineReaderPort;
use App\Ports\Out\Payment\PaymentComponentAllocationReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class AutoReverseRefundedStoreStockInventory
{
    public function __construct(
        private readonly RefundComponentAllocationReaderPort $refundAllocations,
        private readonly PaymentComponentAllocationReaderPort $paymentAllocations,
        private readonly InventoryMovementReaderPort $movements,
        private readonly ReverseIssuedInventoryOperation $reverseIssuedInventory,
        private readonly WorkItemStoreStockLineReaderPort $storeStockLines,
    ) {
    }

    public function execute(CustomerRefund $refund): void
    {
        $noteId = $refund->noteId();
        $allocated = $this->supportedTotals($this->paymentAllocations->listByNoteId($noteId), false);
        $refunded = $this->supportedTotals($this->refundAllocations->listByNoteId($noteId), true);

        foreach ($this->refundAllocations->listByNoteId($noteId) as $allocation) {
            if ($allocation->customerRefundId() !== $refund->id()) {
                continue;
            }

            $type = $allocation->componentType();
            if (! $this->supports($type)) {
                continue;
            }

            $key = $type . '::' . $allocation->componentRefId();
            if (($allocated[$key] ?? 0) < 1 || ($refunded[$key] ?? 0) < 1) {
                continue;
            }

            foreach ($this->targetLineIds($type, $allocation->componentRefId()) as $lineId) {
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

    private function supportedTotals(array $allocations, bool $refund): array
    {
        $totals = [];

        foreach ($allocations as $allocation) {
            $type = $allocation->componentType();
            if (! $this->supports($type)) {
                continue;
            }

            $key = $type . '::' . $allocation->componentRefId();
            $amount = $refund
                ? $allocation->refundedAmountRupiah()->amount()
                : $allocation->allocatedAmountRupiah()->amount();

            $totals[$key] = ($totals[$key] ?? 0) + $amount;
        }

        return $totals;
    }

    private function supports(string $type): bool
    {
        return in_array($type, [
            PaymentComponentType::SERVICE_STORE_STOCK_PART,
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
        ], true);
    }

    private function targetLineIds(string $type, string $componentRefId): array
    {
        return $type === PaymentComponentType::PRODUCT_ONLY_WORK_ITEM
            ? $this->storeStockLines->listIdsByWorkItemId($componentRefId)
            : [trim($componentRefId)];
    }
}
