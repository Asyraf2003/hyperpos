<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\Inventory\ProductInventory\ProductInventory;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class CashierNoteProductLookupData
{
    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly ProductInventoryReaderPort $inventories,
    ) {
    }

    /**
     * @return array<int, Product>
     */
    public function searchProducts(string $query): array
    {
        return $this->products->search(trim($query));
    }

    public function getInventoryByProductId(string $productId): ?ProductInventory
    {
        return $this->inventories->getByProductId($productId);
    }
}
