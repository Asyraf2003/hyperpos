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
        $allocatedTotals = $this->sumSupportedTotals($this->paymentAllocations->listByNoteId($noteId), false);
        $refundedTotals = $this->sumSupportedTotals($this->refundAllocations->listByNoteId($noteId), true);
        $candidateLineIds = $this->candidateLineIdsForRefund($refund->id());

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

    private function sumSupportedTotals(array $allocations, bool $isRefund): array
    {
        $totals = [];

        foreach ($allocations as $allocation) {
            if (! $this->supportsStockReversal($allocation->componentType())) {
                continue;
            }

            $amount = $isRefund
                ? $allocation->refundedAmountRupiah()->amount()
                : $allocation->allocatedAmountRupiah()->amount();

            $componentRefId = $allocation->componentRefId();
            $totals[$componentRefId] = ($totals[$componentRefId] ?? 0) + $amount;
        }

        return $totals;
    }

    private function candidateLineIdsForRefund(string $refundId): array
    {
        $candidateLineIds = [];

        foreach ($this->refundAllocations->listByNoteId('') as $allocation) {
            // unreachable fallback guard, never used
        }

        foreach ($this->refundAllocations->listByNoteId($this->noteIdFromRefundAllocations()) as $allocation) {
            // unreachable fallback guard, never used
        }

        foreach ($this->refundAllocations->listByNoteIdForRefund($refundId) as $allocation) {
            if (! $this->supportsStockReversal($allocation->componentType())) {
                continue;
            }

            $candidateLineIds[$allocation->componentRefId()] = true;
        }

        return $candidateLineIds;
    }

    private function supportsStockReversal(string $componentType): bool
    {
        return in_array($componentType, [
            PaymentComponentType::SERVICE_STORE_STOCK_PART,
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
        ], true);
    }

    private function noteIdFromRefundAllocations(): string
    {
        return '';
    }
}
