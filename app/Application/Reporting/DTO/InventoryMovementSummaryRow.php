<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

use App\Application\Reporting\DTO\Concerns\InventoryMovementSummaryRowAccessors;

final class InventoryMovementSummaryRow
{
    use InventoryMovementSummaryRowAccessors;

    public function __construct(
        private readonly string $productId,
        private readonly ?string $kodeBarang,
        private readonly string $namaBarang,
        private readonly int $supplyInQty,
        private readonly int $saleOutQty,
        private readonly int $refundReversalQty,
        private readonly int $revisionCorrectionQty,
        private readonly int $qtyIn,
        private readonly int $qtyOut,
        private readonly int $netQtyDelta,
        private readonly int $totalInCostRupiah,
        private readonly int $totalOutCostRupiah,
        private readonly int $netCostDeltaRupiah,
        private readonly int $currentQtyOnHand,
        private readonly int $currentAvgCostRupiah,
        private readonly int $currentInventoryValueRupiah,
    ) {
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'kode_barang' => $this->kodeBarang,
            'nama_barang' => $this->namaBarang,
            'supply_in_qty' => $this->supplyInQty,
            'sale_out_qty' => $this->saleOutQty,
            'refund_reversal_qty' => $this->refundReversalQty,
            'revision_correction_qty' => $this->revisionCorrectionQty,
            'qty_in' => $this->qtyIn,
            'qty_out' => $this->qtyOut,
            'net_qty_delta' => $this->netQtyDelta,
            'total_in_cost_rupiah' => $this->totalInCostRupiah,
            'total_out_cost_rupiah' => $this->totalOutCostRupiah,
            'net_cost_delta_rupiah' => $this->netCostDeltaRupiah,
            'current_qty_on_hand' => $this->currentQtyOnHand,
            'current_avg_cost_rupiah' => $this->currentAvgCostRupiah,
            'current_inventory_value_rupiah' => $this->currentInventoryValueRupiah,
        ];
    }
}
