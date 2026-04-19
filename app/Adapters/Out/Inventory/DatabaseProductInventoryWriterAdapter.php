<?php

declare(strict_types=1);

namespace App\Adapters\Out\Inventory;

use App\Core\Inventory\ProductInventory\ProductInventory;
use App\Ports\Out\Inventory\ProductInventoryWriterPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductInventoryWriterAdapter implements ProductInventoryWriterPort
{
    public function upsert(ProductInventory $productInventory): void
    {
        DB::table('product_inventory')->updateOrInsert(
            ['product_id' => $productInventory->productId()],
            ['qty_on_hand' => $productInventory->qtyOnHand()],
        );
    }
}
