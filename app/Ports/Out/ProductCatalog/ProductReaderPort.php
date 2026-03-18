<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

use App\Core\ProductCatalog\Product\Product;

interface ProductReaderPort
{
    public function getById(string $productId): ?Product;

    /**
     * @return array<int, Product>
     */
    public function findAll(): array;

    /**
     * @return array<int, Product>
     */
    public function search(string $query): array;
}
