<?php

declare(strict_types=1);

namespace App\Adapters\Out\Inventory;

use App\Core\Inventory\Costing\ProductInventoryCosting;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\Inventory\ProductInventoryCostingReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseProductInventoryCostingReaderAdapter implements ProductInventoryCostingReaderPort
{
    public function getByProductId(string $productId): ?ProductInventoryCosting
    {
        $row = DB::table('product_inventory_costing')
            ->select([
                'product_id',
                'avg_cost_rupiah',
                'inventory_value_rupiah',
            ])
            ->where('product_id', $productId)
            ->first();

        if ($row === null) {
            return null;
        }

        return ProductInventoryCosting::rehydrate(
            (string) $row->product_id,
            Money::fromInt((int) $row->avg_cost_rupiah),
            Money::fromInt((int) $row->inventory_value_rupiah),
        );
    }
}
