<?php

declare(strict_types=1);

namespace App\Adapters\Out\Inventory;

use App\Core\Inventory\ProductInventory\ProductInventory;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductInventoryReaderAdapter implements ProductInventoryReaderPort
{
    public function getByProductId(string $productId): ?ProductInventory
    {
        $row = DB::table('product_inventory')
            ->select(['product_id', 'qty_on_hand'])
            ->where('product_id', $productId)
            ->first();

        if ($row === null) {
            return null;
        }

        return ProductInventory::rehydrate(
            (string) $row->product_id,
            (int) $row->qty_on_hand,
        );
    }
}
