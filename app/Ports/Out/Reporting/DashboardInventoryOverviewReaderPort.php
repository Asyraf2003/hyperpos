<?php

declare(strict_types=1);

namespace App\Ports\Out\Reporting;

interface DashboardInventoryOverviewReaderPort
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
    public function getInventorySummary(string $fromMutationDate, string $toMutationDate): array;

    /**
     * @return list<array{
     *   product_id:string,
     *   kode_barang:string|null,
     *   nama_barang:string,
     *   current_qty_on_hand:int,
     *   reorder_point_qty:int|null,
     *   critical_threshold_qty:int|null,
     *   status:string,
     *   status_label:string
     * }>
     */
    public function getRestockPriorityRows(int $limit): array;
}
