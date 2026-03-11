<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

use App\Core\ProductCatalog\Product\Product;

interface ProductReaderPort
{
    public function getById(string $productId): ?Product;
}
