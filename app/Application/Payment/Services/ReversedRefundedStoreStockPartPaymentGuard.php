<?php

declare(strict_types=1);

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PayableNoteComponent;
use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;
use App\Ports\Out\Payment\RefundComponentAllocationReaderPort;

final class ReversedRefundedStoreStockPartPaymentGuard
{
    private const REVERSAL_SOURCE_TYPE = 'work_item_store_stock_line_reversal';

    public function __construct(
        private readonly RefundComponentAllocationReaderPort $refunds,
        private readonly InventoryMovementReaderPort $inventoryMovements,
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function refundedComponentTotals(string $noteId): array
    {
        $totals = [];

        foreach ($this->refunds->listByNoteId($noteId) as $refund) {
            $key = ExistingPaymentComponentTotals::key($refund->componentType(), $refund->componentRefId());
            $totals[$key] = ($totals[$key] ?? 0) + $refund->refundedAmountRupiah()->amount();
        }

        return $totals;
    }

    /**
     * @param array<string, int> $refunded
     */
    public function shouldSkip(PayableNoteComponent $component, array $refunded): bool
    {
        if ($component->componentType() !== PaymentComponentType::SERVICE_STORE_STOCK_PART) {
            return false;
        }

        $key = ExistingPaymentComponentTotals::key($component->componentType(), $component->componentRefId());

        if (($refunded[$key] ?? 0) <= 0) {
            return false;
        }

        return $this->inventoryMovements->getBySource(
            self::REVERSAL_SOURCE_TYPE,
            $component->componentRefId(),
        ) !== [];
    }
}
