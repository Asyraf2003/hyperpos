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
            'reorder_point_qty' => $row->reorder_point_qty !== null ? (int) $row->reorder_point_qty : null,
            'critical_threshold_qty' => $row->critical_threshold_qty !== null ? (int) $row->critical_threshold_qty : null,
            'deleted_at' => $deletedAt,
            'deleted_by_actor_id' => $actorId,
        ];
    }

    /**
     * @return array<string, string|int|null>
     */
    private function toRestoredSnapshot(object $row): array
    {
        return [
            'id' => (string) $row->id,
            'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
            'nama_barang' => (string) $row->nama_barang,
            'merek' => (string) $row->merek,
            'ukuran' => $row->ukuran !== null ? (int) $row->ukuran : null,
            'harga_jual' => (int) $row->harga_jual,
            'reorder_point_qty' => $row->reorder_point_qty !== null ? (int) $row->reorder_point_qty : null,
            'critical_threshold_qty' => $row->critical_threshold_qty !== null ? (int) $row->critical_threshold_qty : null,
            'deleted_at' => null,
            'deleted_by_actor_id' => null,
        ];
    }

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized);
    }
}
