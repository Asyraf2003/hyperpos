<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\InventoryMovementSummaryRow;

final class InventoryMovementReportingReconciliationService
{
    /**
     * @param list<InventoryMovementSummaryRow> $rows
     * @param array{
     *   total_rows:int,
     *   qty_in:int,
     *   qty_out:int,
     *   net_qty_delta:int,
     *   total_in_cost_rupiah:int,
     *   total_out_cost_rupiah:int,
     *   net_cost_delta_rupiah:int
     * } $expected
     */
    public function assertInventoryMovementSummaryMatches(array $rows, array $expected): void
    {
        $actualTotalRows = count($rows);
        $actualQtyIn = 0;
        $actualQtyOut = 0;
        $actualNetQtyDelta = 0;
        $actualTotalInCost = 0;
        $actualTotalOutCost = 0;
        $actualNetCostDelta = 0;

        foreach ($rows as $row) {
            $actualQtyIn += $row->qtyIn();
            $actualQtyOut += $row->qtyOut();
            $actualNetQtyDelta += $row->netQtyDelta();
            $actualTotalInCost += $row->totalInCostRupiah();
            $actualTotalOutCost += $row->totalOutCostRupiah();
            $actualNetCostDelta += $row->netCostDeltaRupiah();
        }

        if ($actualTotalRows !== $expected['total_rows']) {
            throw new \RuntimeException('Reporting mismatch: inventory_total_rows.');
        }

        if ($actualQtyIn !== $expected['qty_in']) {
            throw new \RuntimeException('Reporting mismatch: inventory_qty_in.');
        }

        if ($actualQtyOut !== $expected['qty_out']) {
            throw new \RuntimeException('Reporting mismatch: inventory_qty_out.');
        }

        if ($actualNetQtyDelta !== $expected['net_qty_delta']) {
            throw new \RuntimeException('Reporting mismatch: inventory_net_qty_delta.');
        }

        if ($actualTotalInCost !== $expected['total_in_cost_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: inventory_total_in_cost_rupiah.');
        }

        if ($actualTotalOutCost !== $expected['total_out_cost_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: inventory_total_out_cost_rupiah.');
        }

        if ($actualNetCostDelta !== $expected['net_cost_delta_rupiah']) {
            throw new \RuntimeException('Reporting mismatch: inventory_net_cost_delta_rupiah.');
        }
    }
}
