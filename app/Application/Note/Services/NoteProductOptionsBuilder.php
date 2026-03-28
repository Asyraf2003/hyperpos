<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class NoteProductOptionsBuilder
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    /**
     * @return list<array{id:string,label:string,price_rupiah:int}>
     */
    public function build(): array
    {
        $products = $this->products->findAll();
        usort($products, fn (Product $a, Product $b): int => strcmp($a->namaBarang(), $b->namaBarang()));

        return array_map(
            static fn (Product $product): array => [
                'id' => $product->id(),
                'label' => trim(($product->kodeBarang() ?? '-') . ' - ' . $product->namaBarang()),
                'price_rupiah' => $product->hargaJual()->amount(),
            ],
            $products
        );
    }
}
