<?php

declare(strict_types=1);

namespace App\Adapters\Out\Inventory;

use App\Core\Inventory\Costing\ProductInventoryCosting;
use App\Ports\Out\Inventory\ProductInventoryCostingWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductInventoryCostingWriterAdapter implements ProductInventoryCostingWriterPort
{
    public function upsert(ProductInventoryCosting $costing): void
    {
        DB::table('product_inventory_costing')->updateOrInsert(
            ['product_id' => $costing->productId()],
            [
                'avg_cost_rupiah' => $costing->avgCostRupiah()->amount(),
                'inventory_value_rupiah' => $costing->inventoryValueRupiah()->amount(),
            ],
        );
    }
}
