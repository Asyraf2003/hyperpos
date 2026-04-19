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
            'stock_safe_product_rows' => $this->countSafeRows($snapshotRows),
            'stock_low_product_rows' => $this->countLowRows($snapshotRows),
            'stock_critical_product_rows' => $this->countCriticalRows($snapshotRows),
            'stock_unconfigured_product_rows' => $this->countUnconfiguredRows($snapshotRows),
            'period_qty_in' => array_sum(array_column($movementRows, 'qty_in')),
            'period_qty_out' => array_sum(array_column($movementRows, 'qty_out')),
            'period_net_qty_delta' => array_sum(array_column($movementRows, 'net_qty_delta')),
            'period_total_in_cost_rupiah' => array_sum(array_column($movementRows, 'total_in_cost_rupiah')),
            'period_total_out_cost_rupiah' => array_sum(array_column($movementRows, 'total_out_cost_rupiah')),
            'period_net_cost_delta_rupiah' => array_sum(array_column($movementRows, 'net_cost_delta_rupiah')),
        ];
    }

    private function countSafeRows(array $rows): int
    {
        return count(array_filter($rows, fn (array $row): bool => $this->classify($row) === 'safe'));
    }

    private function countLowRows(array $rows): int
    {
        return count(array_filter($rows, fn (array $row): bool => $this->classify($row) === 'low'));
    }

    private function countCriticalRows(array $rows): int
    {
        return count(array_filter($rows, fn (array $row): bool => $this->classify($row) === 'critical'));
    }

    private function countUnconfiguredRows(array $rows): int
    {
        return count(array_filter($rows, fn (array $row): bool => $this->classify($row) === 'unconfigured'));
    }

    private function classify(array $row): string
    {
        $reorderPointQty = $row['reorder_point_qty'] ?? null;
        $criticalThresholdQty = $row['critical_threshold_qty'] ?? null;

        if ($reorderPointQty === null || $criticalThresholdQty === null) {
            return 'unconfigured';
        }

        $qtyOnHand = (int) ($row['current_qty_on_hand'] ?? 0);

        if ($qtyOnHand <= (int) $criticalThresholdQty) {
            return 'critical';
        }

        if ($qtyOnHand <= (int) $reorderPointQty) {
            return 'low';
        }

        return 'safe';
    }
}
