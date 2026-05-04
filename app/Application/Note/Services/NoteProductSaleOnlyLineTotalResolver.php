<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Ports\Out\ProductCatalog\ProductReaderPort;

final readonly class NoteProductSaleOnlyLineTotalResolver
{
    public function __construct(private ProductReaderPort $products)
    {
    }

    public function resolve(string $productId, int $qty): ?int
    {
        $product = $this->products->getById($productId);

        if ($product === null) {
            return null;
        }

        return $product->hargaJual()->amount() * $qty;
    }
}
