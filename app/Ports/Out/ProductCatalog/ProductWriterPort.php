<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

use App\Core\ProductCatalog\Product\Product;

interface ProductWriterPort
{
    public function create(Product $product): void;

    public function update(Product $product): void;
}
