<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

trait ProductLifecycleSnapshots
{
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
}
