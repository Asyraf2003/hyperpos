<?php

declare(strict_types=1);

namespace App\Application\Note\Services;

use App\Core\ProductCatalog\Product\Product;
use App\Ports\Out\ProductCatalog\ProductReaderPort;

final class CreateNotePageDataBuilder
{
    public function __construct(
        private readonly ProductReaderPort $products,
    ) {
    }

    /**
     * @return array{
     * lineTypes:list<array{value:string,label:string}>,
     * productOptions:list<array{id:string,label:string,price_rupiah:int}>
     * }
     */
    public function build(): array
    {
        return [
            'lineTypes' => [
                ['value' => 'product', 'label' => 'Produk'],
                ['value' => 'service', 'label' => 'Servis'],
            ],
            'productOptions' => $this->mapProducts($this->products->findAll()),
        ];
    }

    /**
     * @param array<int, Product> $products
     * @return list<array{id:string,label:string,price_rupiah:int}>
     */
    private function mapProducts(array $products): array
    {
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
