<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

final class InventoryMovementSummaryRowMapper
{
    public static function map(object $row): array
    {
        return [
            'product_id' => (string) $row->product_id,
            'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
            'nama_barang' => (string) $row->nama_barang,
            'supply_in_qty' => (int) ($row->supply_in_qty ?? 0),
            'sale_out_qty' => (int) ($row->sale_out_qty ?? 0),
            'refund_reversal_qty' => (int) ($row->refund_reversal_qty ?? 0),
            'revision_correction_qty' => (int) ($row->revision_correction_qty ?? 0),
            'qty_in' => (int) $row->qty_in,
            'qty_out' => (int) $row->qty_out,
            'net_qty_delta' => (int) $row->net_qty_delta,
            'total_in_cost_rupiah' => (int) $row->total_in_cost_rupiah,
            'total_out_cost_rupiah' => (int) $row->total_out_cost_rupiah,
            'net_cost_delta_rupiah' => (int) $row->net_cost_delta_rupiah,
            'current_qty_on_hand' => (int) $row->current_qty_on_hand,
            'current_avg_cost_rupiah' => (int) $row->current_avg_cost_rupiah,
            'current_inventory_value_rupiah' => (int) $row->current_inventory_value_rupiah,
        ];
    }
}
