<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\DashboardInventory;

use Illuminate\Support\Facades\DB;

final class DashboardInventorySummaryQuery
{
    /**
     * @return array{
     *   snapshot_product_rows:int,
     *   movement_product_rows:int,
     *   total_qty_on_hand:int,
     *   total_inventory_value_rupiah:int,
     *   stock_safe_product_rows:int,
     *   stock_low_product_rows:int,
     *   stock_critical_product_rows:int,
     *   stock_unconfigured_product_rows:int,
     *   period_supply_in_qty:int,
     *   period_sale_out_qty:int,
     *   period_refund_reversal_qty:int,
     *   period_revision_correction_qty:int,
     *   period_qty_in:int,
     *   period_qty_out:int,
     *   period_net_qty_delta:int,
     *   period_total_in_cost_rupiah:int,
     *   period_total_out_cost_rupiah:int,
     *   period_net_cost_delta_rupiah:int
     * }
     */
    public function get(string $fromMutationDate, string $toMutationDate): array
    {
        $snapshot = $this->snapshotSummary();
        $movement = $this->movementSummary($fromMutationDate, $toMutationDate);

        return array_merge($snapshot, $movement);
    }

    /**
     * @return array<string, int>
     */
    private function snapshotSummary(): array
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

    /**
     * @return array<string, int>
     */
    private function movementSummary(string $fromMutationDate, string $toMutationDate): array
    {
        $row = DB::table('inventory_movements')
            ->whereBetween('tanggal_mutasi', [$fromMutationDate, $toMutationDate])
            ->selectRaw('COUNT(DISTINCT product_id) as movement_product_rows')
            ->selectRaw("COALESCE(SUM(CASE WHEN source_type = 'supplier_receipt_line' AND qty_delta > 0 THEN qty_delta ELSE 0 END), 0) as period_supply_in_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN source_type IN ('work_item_store_stock_line', 'note', 'customer_transaction_line') AND qty_delta < 0 THEN ABS(qty_delta) ELSE 0 END), 0) as period_sale_out_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN source_type = 'work_item_store_stock_line_reversal' AND qty_delta > 0 THEN qty_delta ELSE 0 END), 0) as period_refund_reversal_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN source_type NOT IN ('supplier_receipt_line', 'work_item_store_stock_line', 'note', 'customer_transaction_line', 'work_item_store_stock_line_reversal') THEN ABS(qty_delta) ELSE 0 END), 0) as period_revision_correction_qty")
            ->selectRaw("COALESCE(SUM(CASE WHEN source_type = 'supplier_receipt_line' AND qty_delta > 0 THEN qty_delta ELSE 0 END), 0) as period_qty_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN source_type IN ('work_item_store_stock_line', 'note', 'customer_transaction_line') AND qty_delta < 0 THEN ABS(qty_delta) ELSE 0 END), 0) as period_qty_out")
            ->selectRaw('COALESCE(SUM(qty_delta), 0) as period_net_qty_delta')
            ->selectRaw('COALESCE(SUM(CASE WHEN total_cost_rupiah > 0 THEN total_cost_rupiah ELSE 0 END), 0) as period_total_in_cost_rupiah')
            ->selectRaw('COALESCE(SUM(CASE WHEN total_cost_rupiah < 0 THEN ABS(total_cost_rupiah) ELSE 0 END), 0) as period_total_out_cost_rupiah')
            ->selectRaw('COALESCE(SUM(total_cost_rupiah), 0) as period_net_cost_delta_rupiah')
            ->first();

        return [
            'movement_product_rows' => (int) ($row->movement_product_rows ?? 0),
            'period_supply_in_qty' => (int) ($row->period_supply_in_qty ?? 0),
            'period_sale_out_qty' => (int) ($row->period_sale_out_qty ?? 0),
            'period_refund_reversal_qty' => (int) ($row->period_refund_reversal_qty ?? 0),
            'period_revision_correction_qty' => (int) ($row->period_revision_correction_qty ?? 0),
            'period_qty_in' => (int) ($row->period_qty_in ?? 0),
            'period_qty_out' => (int) ($row->period_qty_out ?? 0),
            'period_net_qty_delta' => (int) ($row->period_net_qty_delta ?? 0),
            'period_total_in_cost_rupiah' => (int) ($row->period_total_in_cost_rupiah ?? 0),
            'period_total_out_cost_rupiah' => (int) ($row->period_total_out_cost_rupiah ?? 0),
            'period_net_cost_delta_rupiah' => (int) ($row->period_net_cost_delta_rupiah ?? 0),
        ];
    }
}
