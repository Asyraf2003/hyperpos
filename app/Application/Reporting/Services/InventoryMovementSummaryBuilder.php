<?php

declare(strict_types=1);

namespace App\Application\Reporting\Services;

use App\Application\Reporting\DTO\InventoryMovementSummaryRow;

final class InventoryMovementSummaryBuilder
{
    /**
     * @param list<array<string,mixed>> $rows
     * @return list<InventoryMovementSummaryRow>
     */
    public function build(array $rows): array
    {
        return array_map(
            static fn (array $row): InventoryMovementSummaryRow => new InventoryMovementSummaryRow(
                $row['product_id'],
                $row['kode_barang'],
                $row['nama_barang'],
                (int) ($row['supply_in_qty'] ?? 0),
                (int) ($row['sale_out_qty'] ?? 0),
                (int) ($row['refund_reversal_qty'] ?? 0),
                (int) ($row['revision_correction_qty'] ?? 0),
                $row['qty_in'],
                $row['qty_out'],
                $row['net_qty_delta'],
                $row['total_in_cost_rupiah'],
                $row['total_out_cost_rupiah'],
                $row['net_cost_delta_rupiah'],
                $row['current_qty_on_hand'],
                $row['current_avg_cost_rupiah'],
                $row['current_inventory_value_rupiah'],
            ),
            $rows,
        );
    }
}
