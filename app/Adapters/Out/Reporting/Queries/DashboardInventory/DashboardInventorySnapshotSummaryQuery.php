<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardInventory;

use Illuminate\Support\Facades\DB;

final class DashboardInventorySnapshotSummaryQuery
{
    /**
     * @return array<string, int>
     */
    public function get(): array
    {
        $row = DB::table('products')
            ->whereNull('products.deleted_at')
            ->leftJoin('product_inventory', 'product_inventory.product_id', '=', 'products.id')
            ->leftJoin('product_inventory_costing', 'product_inventory_costing.product_id', '=', 'products.id')
            ->where(static function ($query): void {
                $query
                    ->whereNotNull('product_inventory.product_id')
                    ->orWhereNotNull('product_inventory_costing.product_id');
            })
            ->selectRaw('COUNT(*) as snapshot_product_rows')
            ->selectRaw('COALESCE(SUM(COALESCE(product_inventory.qty_on_hand, 0)), 0) as total_qty_on_hand')
            ->selectRaw('COALESCE(SUM(COALESCE(product_inventory_costing.inventory_value_rupiah, 0)), 0) as total_inventory_value_rupiah')
            ->selectRaw(
                'COALESCE(SUM(CASE ' .
                'WHEN products.reorder_point_qty IS NULL OR products.critical_threshold_qty IS NULL THEN 0 ' .
                'WHEN COALESCE(product_inventory.qty_on_hand, 0) > products.reorder_point_qty THEN 1 ' .
                'ELSE 0 END), 0) as stock_safe_product_rows'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE ' .
                'WHEN products.reorder_point_qty IS NULL OR products.critical_threshold_qty IS NULL THEN 0 ' .
                'WHEN COALESCE(product_inventory.qty_on_hand, 0) <= products.critical_threshold_qty THEN 0 ' .
                'WHEN COALESCE(product_inventory.qty_on_hand, 0) <= products.reorder_point_qty THEN 1 ' .
                'ELSE 0 END), 0) as stock_low_product_rows'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE ' .
                'WHEN products.reorder_point_qty IS NULL OR products.critical_threshold_qty IS NULL THEN 0 ' .
                'WHEN COALESCE(product_inventory.qty_on_hand, 0) <= products.critical_threshold_qty THEN 1 ' .
                'ELSE 0 END), 0) as stock_critical_product_rows'
            )
            ->selectRaw(
                'COALESCE(SUM(CASE ' .
                'WHEN products.reorder_point_qty IS NULL OR products.critical_threshold_qty IS NULL THEN 1 ' .
                'ELSE 0 END), 0) as stock_unconfigured_product_rows'
            )
            ->first();

        return [
            'snapshot_product_rows' => (int) ($row->snapshot_product_rows ?? 0),
            'total_qty_on_hand' => (int) ($row->total_qty_on_hand ?? 0),
            'total_inventory_value_rupiah' => (int) ($row->total_inventory_value_rupiah ?? 0),
            'stock_safe_product_rows' => (int) ($row->stock_safe_product_rows ?? 0),
            'stock_low_product_rows' => (int) ($row->stock_low_product_rows ?? 0),
            'stock_critical_product_rows' => (int) ($row->stock_critical_product_rows ?? 0),
            'stock_unconfigured_product_rows' => (int) ($row->stock_unconfigured_product_rows ?? 0),
        ];
    }
}
