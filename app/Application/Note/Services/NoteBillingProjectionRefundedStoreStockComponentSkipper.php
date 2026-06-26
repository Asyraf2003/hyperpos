<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Ports\Out\Inventory\InventoryMovementReaderPort;

final class NoteBillingProjectionRefundedStoreStockComponentSkipper
{
    private const REVERSAL_SOURCE_TYPE = 'work_item_store_stock_line_reversal';

    public function __construct(
        private readonly InventoryMovementReaderPort $inventoryMovements,
    ) {
    }

    public function shouldSkip(
        string $componentType,
        string $componentRefId,
        int $refunded,
        int $allocated = 0,
    ): bool
    {
        if ($componentType === PaymentComponentType::PRODUCT_ONLY_WORK_ITEM
            && $refunded > 0
            && ($allocated <= 0 || $refunded >= $allocated)) {
            return true;
        }

        return $componentType === PaymentComponentType::SERVICE_STORE_STOCK_PART
            && $refunded > 0
            && $this->inventoryMovements->getBySource(self::REVERSAL_SOURCE_TYPE, $componentRefId) !== [];
    }
}
