<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class SupplierInvoiceProductOptionsData
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    /**
     * @return list<Product>
     */
    public function findAll(): array
    {
        return $this->products->findAll();
    }
}
