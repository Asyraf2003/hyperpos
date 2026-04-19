<?php

declare(strict_types=1);

namespace App\Application\Inventory\Services;

use App\Core\Inventory\Costing\ProductInventoryCosting;
use App\Core\Inventory\ProductInventory\ProductInventory;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Inventory\{ProductInventoryReaderPort, ProductInventoryWriterPort, ProductInventoryCostingReaderPort, ProductInventoryCostingWriterPort};

final class InventoryProjectionService
{
    public function __construct(
        private ProductInventoryReaderPort $inventories,
        private ProductInventoryWriterPort $inventoryWriter,
        private ProductInventoryCostingReaderPort $costings,
        private ProductInventoryCostingWriterPort $costingWriter
    ) {}

    public function applyMovements(array $movements): void
    {
        foreach ($movements as $m) {
            // Update Qty Projection
            $inv = $this->inventories->getByProductId($m->productId()) ?? ProductInventory::create($m->productId(), 0);
            $inv->increase($m->qtyDelta());
            $this->inventoryWriter->upsert($inv);

            // Update Costing Projection (Hanya jika Stock In)
            if ($m->movementType() === 'stock_in') {
                $cost = $this->costings->getByProductId($m->productId()) ?? ProductInventoryCosting::create($m->productId(), Money::zero(), Money::zero());
                $cost->applyIncomingStock($inv->qtyOnHand() - $m->qtyDelta(), $m->qtyDelta(), $m->totalCostRupiah());
                $this->costingWriter->upsert($cost);
            }
        }
    }
}
