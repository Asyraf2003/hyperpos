<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use App\Ports\Out\Reporting\InventoryMovementReportingSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseInventoryMovementReportingSourceReaderAdapter implements InventoryMovementReportingSourceReaderPort
{
    public function getInventoryMovementSummaryRows(
        string $fromMutationDate,
        string $toMutationDate,
    ): array {
        return DB::table('inventory_movements')
            ->leftJoin('product_inventory', 'product_inventory.product_id', '=', 'inventory_movements.product_id')
            ->leftJoin('product_inventory_costing', 'product_inventory_costing.product_id', '=', 'inventory_movements.product_id')
            ->whereBetween('inventory_movements.tanggal_mutasi', [$fromMutationDate, $toMutationDate])
            ->groupBy(
                'inventory_movements.product_id',
                'product_inventory.qty_on_hand',
                'product_inventory_costing.avg_cost_rupiah',
                'product_inventory_costing.inventory_value_rupiah',
            )
            ->orderBy('inventory_movements.product_id')
            ->get([
                'inventory_movements.product_id',
                DB::raw('COALESCE(SUM(CASE WHEN inventory_movements.qty_delta > 0 THEN inventory_movements.qty_delta ELSE 0 END), 0) as qty_in'),
                DB::raw('COALESCE(SUM(CASE WHEN inventory_movements.qty_delta < 0 THEN ABS(inventory_movements.qty_delta) ELSE 0 END), 0) as qty_out'),
                DB::raw('COALESCE(SUM(inventory_movements.qty_delta), 0) as net_qty_delta'),
                DB::raw('COALESCE(SUM(CASE WHEN inventory_movements.total_cost_rupiah > 0 THEN inventory_movements.total_cost_rupiah ELSE 0 END), 0) as total_in_cost_rupiah'),
                DB::raw('COALESCE(SUM(CASE WHEN inventory_movements.total_cost_rupiah < 0 THEN ABS(inventory_movements.total_cost_rupiah) ELSE 0 END), 0) as total_out_cost_rupiah'),
                DB::raw('COALESCE(SUM(inventory_movements.total_cost_rupiah), 0) as net_cost_delta_rupiah'),
                DB::raw('COALESCE(product_inventory.qty_on_hand, 0) as current_qty_on_hand'),
                DB::raw('COALESCE(product_inventory_costing.avg_cost_rupiah, 0) as current_avg_cost_rupiah'),
                DB::raw('COALESCE(product_inventory_costing.inventory_value_rupiah, 0) as current_inventory_value_rupiah'),
            ])
            ->map(static fn (object $row): array => [
                'product_id' => (string) $row->product_id,
                'qty_in' => (int) $row->qty_in,
                'qty_out' => (int) $row->qty_out,
                'net_qty_delta' => (int) $row->net_qty_delta,
                'total_in_cost_rupiah' => (int) $row->total_in_cost_rupiah,
                'total_out_cost_rupiah' => (int) $row->total_out_cost_rupiah,
                'net_cost_delta_rupiah' => (int) $row->net_cost_delta_rupiah,
                'current_qty_on_hand' => (int) $row->current_qty_on_hand,
                'current_avg_cost_rupiah' => (int) $row->current_avg_cost_rupiah,
                'current_inventory_value_rupiah' => (int) $row->current_inventory_value_rupiah,
            ])
            ->all();
    }

    public function getInventoryMovementSummaryReconciliation(
        string $fromMutationDate,
        string $toMutationDate,
    ): array {
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
