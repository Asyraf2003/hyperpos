<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use Illuminate\Support\Facades\DB;

final class InventoryMovementReconciliationDatabaseQuery
{
    public static function get(string $fromMutationDate, string $toMutationDate): array
    {
        $totals = DB::table('inventory_movements')
            ->whereBetween('tanggal_mutasi', [$fromMutationDate, $toMutationDate])
            ->selectRaw(
                'COUNT(DISTINCT product_id) as total_rows, ' .
                'COALESCE(SUM(CASE WHEN qty_delta > 0 THEN qty_delta ELSE 0 END), 0) as qty_in, ' .
                'COALESCE(SUM(CASE WHEN qty_delta < 0 THEN ABS(qty_delta) ELSE 0 END), 0) as qty_out, ' .
                'COALESCE(SUM(qty_delta), 0) as net_qty_delta, ' .
                'COALESCE(SUM(CASE WHEN total_cost_rupiah > 0 THEN total_cost_rupiah ELSE 0 END), 0) as total_in_cost_rupiah, ' .
                'COALESCE(SUM(CASE WHEN total_cost_rupiah < 0 THEN ABS(total_cost_rupiah) ELSE 0 END), 0) as total_out_cost_rupiah, ' .
                'COALESCE(SUM(total_cost_rupiah), 0) as net_cost_delta_rupiah'
            )
            ->first();

        return [
            'total_rows' => (int) ($totals->total_rows ?? 0),
            'qty_in' => (int) ($totals->qty_in ?? 0),
            'qty_out' => (int) ($totals->qty_out ?? 0),
            'net_qty_delta' => (int) ($totals->net_qty_delta ?? 0),
            'total_in_cost_rupiah' => (int) ($totals->total_in_cost_rupiah ?? 0),
            'total_out_cost_rupiah' => (int) ($totals->total_out_cost_rupiah ?? 0),
            'net_cost_delta_rupiah' => (int) ($totals->net_cost_delta_rupiah ?? 0),
        ];
    }
}
