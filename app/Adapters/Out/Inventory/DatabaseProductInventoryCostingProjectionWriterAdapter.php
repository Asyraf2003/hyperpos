<?php

declare(strict_types=1);

namespace App\Adapters\Out\Inventory;

use App\Core\Inventory\Costing\ProductInventoryCosting;
use App\Ports\Out\Inventory\ProductInventoryCostingProjectionWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductInventoryCostingProjectionWriterAdapter implements ProductInventoryCostingProjectionWriterPort
{
    /**
     * @param list<ProductInventoryCosting> $costings
     */
    public function replaceAll(array $costings): void
    {
        DB::table('product_inventory_costing')->delete();

        if ($costings === []) {
            return;
        }

        DB::table('product_inventory_costing')->insert(
            array_map(
                static fn (ProductInventoryCosting $costing): array => [
                    'product_id' => $costing->productId(),
                    'avg_cost_rupiah' => $costing->avgCostRupiah()->amount(),
                    'inventory_value_rupiah' => $costing->inventoryValueRupiah()->amount(),
                ],
                $costings,
            )
        );
    }
}
