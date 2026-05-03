<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Adapters\Out\ProductCatalog\Concerns\ProductListQuery;
use App\Adapters\Out\ProductCatalog\Concerns\ProductRowHydration;
use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class DatabaseProductReaderAdapter implements ProductReaderPort
{
    use ProductListQuery;
    use ProductRowHydration;

    public function getById(string $productId): ?Product
    {
        $row = $this->baseSelect()->where('id', $productId)->first();

        return $row === null ? null : $this->mapRowToProduct($row);
    }

    /**
     * @return array<int, Product>
     */
    public function findAll(): array
    {
        return $this->mapRowsToProducts($this->applyOrdering($this->baseSelect())->get());
    }

    /**
     * @return array<int, Product>
     */
    public function search(string $query): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return $this->findAll();
        }

        return $this->mapRowsToProducts(
            $this->applyOrdering($this->applySearch($this->baseSelect(), $normalizedQuery))->get()
        );
    }
}
