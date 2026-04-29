<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Core\Payment\PaymentComponentAllocation\PaymentComponentType;
use App\Ports\Out\Note\WorkItemStoreStockLineReaderPort;

final class RefundedStoreStockComponentTargets
{
    public function __construct(
        private readonly WorkItemStoreStockLineReaderPort $storeStockLines,
    ) {
    }

    public function supports(string $type): bool
    {
        return in_array($type, [
            PaymentComponentType::SERVICE_STORE_STOCK_PART,
            PaymentComponentType::PRODUCT_ONLY_WORK_ITEM,
        ], true);
    }

    /**
     * @return list<string>
     */
    public function lineIds(string $type, string $componentRefId): array
    {
        return $type === PaymentComponentType::PRODUCT_ONLY_WORK_ITEM
            ? $this->storeStockLines->listIdsByWorkItemId($componentRefId)
            : [trim($componentRefId)];
    }

    public function totals(array $allocations, bool $refund): array
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
}
