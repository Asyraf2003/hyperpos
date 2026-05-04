<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\Services;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\Inventory\ProductInventoryReaderPort;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class EditProductPageData
{
    public function __construct(
        private readonly ProductReaderPort $products,
        private readonly ProductInventoryReaderPort $inventories,
    ) {
    }

    /**
     * @return array{product:Product,currentStock:int}|null
     */
    public function getById(string $productId): ?array
    {
        $product = $this->products->getById($productId);

        if ($product === null) {
            return null;
        }

        $inventory = $this->inventories->getByProductId($productId);

        return [
            'product' => $product,
            'currentStock' => $inventory?->qtyOnHand() ?? 0,
        ];
    }
}
