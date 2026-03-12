<?php

declare(strict_types=1);

namespace App\Ports\Out\Inventory;

use App\Core\Inventory\Costing\ProductInventoryCosting;

interface ProductInventoryCostingProjectionWriterPort
{
    /**
     * @param list<ProductInventoryCosting> $costings
     */
    public function replaceAll(array $costings): void;
}
