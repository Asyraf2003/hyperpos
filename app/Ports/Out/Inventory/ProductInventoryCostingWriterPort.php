<?php

declare(strict_types=1);

namespace App\Ports\Out\Inventory;

use App\Core\Inventory\Costing\ProductInventoryCosting;

interface ProductInventoryCostingWriterPort
{
    public function upsert(ProductInventoryCosting $costing): void;
}
