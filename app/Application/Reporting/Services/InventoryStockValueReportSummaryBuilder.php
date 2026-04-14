<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

final class InventoryStockValueReportSummaryBuilder
{
    public function build(array $snapshotRows, array $movementRows): array
    {
        return [
            'snapshot_product_rows' => count($snapshotRows),
            'movement_product_rows' => count($movementRows),
            'total_qty_on_hand' => array_sum(array_column($snapshotRows, 'current_qty_on_hand')),
            'total_inventory_value_rupiah' => array_sum(array_column($snapshotRows, 'current_inventory_value_rupiah')),
            'period_qty_in' => array_sum(array_column($movementRows, 'qty_in')),
            'period_qty_out' => array_sum(array_column($movementRows, 'qty_out')),
            'period_net_qty_delta' => array_sum(array_column($movementRows, 'net_qty_delta')),
            'period_total_in_cost_rupiah' => array_sum(array_column($movementRows, 'total_in_cost_rupiah')),
            'period_total_out_cost_rupiah' => array_sum(array_column($movementRows, 'total_out_cost_rupiah')),
            'period_net_cost_delta_rupiah' => array_sum(array_column($movementRows, 'net_cost_delta_rupiah')),
        ];
    }
}
