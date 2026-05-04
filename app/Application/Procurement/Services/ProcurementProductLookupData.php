<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class ProcurementProductLookupData
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    /**
     * @return list<Product>
     */
    public function search(string $search): array
    {
        return $this->products->search(trim($search));
    }
}
