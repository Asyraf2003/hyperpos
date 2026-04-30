<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\InventoryMovementSummaryRow;

final class InventoryMovementReportingReconciliationService
{
    /**
     * @param list<InventoryMovementSummaryRow> $rows
     * @param array<string,int> $expected
     */
    public function assertInventoryMovementSummaryMatches(array $rows, array $expected): void
    {
        $actualTotalRows = count($rows);
        $actualSupplyInQty = 0;
        $actualSaleOutQty = 0;
        $actualRefundReversalQty = 0;
        $actualRevisionCorrectionQty = 0;
        $actualQtyIn = 0;
        $actualQtyOut = 0;
        $actualNetQtyDelta = 0;
        $actualTotalInCost = 0;
        $actualTotalOutCost = 0;
        $actualNetCostDelta = 0;

        foreach ($rows as $row) {
            $actualSupplyInQty += $row->supplyInQty();
            $actualSaleOutQty += $row->saleOutQty();
            $actualRefundReversalQty += $row->refundReversalQty();
            $actualRevisionCorrectionQty += $row->revisionCorrectionQty();
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

        if ($actualSupplyInQty !== $expected['supply_in_qty']) {
            throw new \RuntimeException('Reporting mismatch: inventory_supply_in_qty.');
        }

        if ($actualSaleOutQty !== $expected['sale_out_qty']) {
            throw new \RuntimeException('Reporting mismatch: inventory_sale_out_qty.');
        }

        if ($actualRefundReversalQty !== $expected['refund_reversal_qty']) {
            throw new \RuntimeException('Reporting mismatch: inventory_refund_reversal_qty.');
        }

        if ($actualRevisionCorrectionQty !== $expected['revision_correction_qty']) {
            throw new \RuntimeException('Reporting mismatch: inventory_revision_correction_qty.');
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
