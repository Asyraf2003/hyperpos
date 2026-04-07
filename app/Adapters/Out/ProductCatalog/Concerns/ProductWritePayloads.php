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
        ];
    }

    /**
     * @return array{
     *     id:string,
     *     kode_barang:?string,
     *     nama_barang:string,
     *     merek:string,
     *     ukuran:?int,
     *     harga_jual:int
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
        ];
    }

    /**
     * @return array<string, string|int|null>
     */
    private function toDeletedSnapshot(
        object $row,
        string $deletedAt,
        ?string $actorId,
    ): array {
        return [
            'id' => (string) $row->id,
            'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
            'nama_barang' => (string) $row->nama_barang,
            'merek' => (string) $row->merek,
            'ukuran' => $row->ukuran !== null ? (int) $row->ukuran : null,
            'harga_jual' => (int) $row->harga_jual,
            'deleted_at' => $deletedAt,
            'deleted_by_actor_id' => $actorId,
        ];
    }

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized);
    }
}
