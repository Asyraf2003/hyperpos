<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use App\Core\ProductCatalog\Product\Product;

trait ProductWritePayloads
{
    /**
     * @return array<string, string|int|null>
     */
    private function toProductRecord(Product $product): array
    {
        return [
            'id' => $product->id(),
            'kode_barang' => $product->kodeBarang(),
            'nama_barang' => $product->namaBarang(),
            'nama_barang_normalized' => $this->normalizeForSearch($product->namaBarang()),
            'merek' => $product->merek(),
            'merek_normalized' => $this->normalizeForSearch($product->merek()),
            'ukuran' => $product->ukuran(),
            'harga_jual' => $product->hargaJual()->amount(),
            'reorder_point_qty' => $product->reorderPointQty(),
            'critical_threshold_qty' => $product->criticalThresholdQty(),
        ];
    }

    /**
     * @return array{
     *     id:string,
     *     kode_barang:?string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:?int,
     *     harga_jual:int,
     *     reorder_point_qty:?int,
     *     critical_threshold_qty:?int
     * }
     */
    private function toSnapshot(Product $product): array
    {
        return [
            'id' => $product->id(),
            'kode_barang' => $product->kodeBarang(),
            'nama_barang' => $product->namaBarang(),
            'merek' => $product->merek(),
            'ukuran' => $product->ukuran(),
            'harga_jual' => $product->hargaJual()->amount(),
            'reorder_point_qty' => $product->reorderPointQty(),
            'critical_threshold_qty' => $product->criticalThresholdQty(),
        ];
    }

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized);
    }
}
