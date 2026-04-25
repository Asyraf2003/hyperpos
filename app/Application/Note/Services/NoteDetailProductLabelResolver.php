<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class NoteDetailProductLabelResolver
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    public function resolve(string $productId): string
    {
        $product = $this->products->getById(trim($productId));

        if ($product === null) {
            return $productId;
        }

        $name = trim($product->namaBarang());

        return $name !== '' ? $name : $productId;
    }
}
