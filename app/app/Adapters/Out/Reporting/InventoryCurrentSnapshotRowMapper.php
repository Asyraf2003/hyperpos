<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

final class InventoryCurrentSnapshotRowMapper
{
    public static function map(object $row): array
    {
        return [
            'product_id' => (string) $row->product_id,
            'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
            'nama_barang' => (string) $row->nama_barang,
            'merek' => (string) $row->merek,
            'ukuran' => $row->ukuran !== null ? (int) $row->ukuran : null,
            'reorder_point_qty' => $row->reorder_point_qty !== null ? (int) $row->reorder_point_qty : null,
            'critical_threshold_qty' => $row->critical_threshold_qty !== null ? (int) $row->critical_threshold_qty : null,
            'current_qty_on_hand' => (int) $row->current_qty_on_hand,
            'current_avg_cost_rupiah' => (int) $row->current_avg_cost_rupiah,
            'current_inventory_value_rupiah' => (int) $row->current_inventory_value_rupiah,
        ];
    }
}
