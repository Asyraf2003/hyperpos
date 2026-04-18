<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Inventory\Movement\InventoryMovement;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;

final class SupplierInvoiceRevisionDeltaStockGuard
{
    public function __construct(
        private readonly ProductInventoryReaderPort $productInventories,
    ) {
    }

    /**
     * @param list<InventoryMovement> $deltaMovements
     */
    public function canApplyWithoutNegativeStock(array $deltaMovements): bool
    {
        $minusByProduct = [];

        foreach ($deltaMovements as $movement) {
            if ($movement->movementType() === 'stock_out') {
                $productId = $movement->productId();
                $minusByProduct[$productId] = ($minusByProduct[$productId] ?? 0) + abs($movement->qtyDelta());
            }
        }

        foreach ($minusByProduct as $productId => $qtyToSubtract) {
            $qtyOnHand = $this->productInventories->getByProductId($productId)?->qtyOnHand() ?? 0;

            if ($qtyToSubtract > $qtyOnHand) {
                return false;
            }
        }

        return true;
    }
}
