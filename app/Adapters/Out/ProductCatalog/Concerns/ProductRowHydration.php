<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use App\Core\ProductCatalog\Product\Product;
use App\Core\Shared\ValueObjects\Money;

trait ProductRowHydration
{
    /**
     * @param iterable<object> $rows
     * @return array<int, Product>
     */
    private function mapRowsToProducts(iterable $rows): array
    {
        $products = [];

        foreach ($rows as $row) {
            $products[] = $this->mapRowToProduct($row);
        }

        return $products;
    }

    private function mapRowToProduct(object $row): Product
    {
        return Product::rehydrate(
            (string) $row->id,
            $row->kode_barang !== null ? (string) $row->kode_barang : null,
            (string) $row->nama_barang,
            (string) $row->merek,
            $row->ukuran !== null ? (int) $row->ukuran : null,
            Money::fromInt((int) $row->harga_jual),
        );
    }
}
