<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO;

final class InventoryMovementSummaryRow
{
    public function __construct(
        private readonly string $productId,
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

    public function productId(): string
    {
        return $this->productId;
    }

    public function qtyIn(): int
    {
        return $this->qtyIn;
    }

    public function qtyOut(): int
    {
        return $this->qtyOut;
    }

    public function netQtyDelta(): int
    {
        return $this->netQtyDelta;
    }

    public function totalInCostRupiah(): int
    {
        return $this->totalInCostRupiah;
    }

    public function totalOutCostRupiah(): int
    {
        return $this->totalOutCostRupiah;
    }

    public function netCostDeltaRupiah(): int
    {
        return $this->netCostDeltaRupiah;
    }

    public function currentQtyOnHand(): int
    {
        return $this->currentQtyOnHand;
    }

    public function currentAvgCostRupiah(): int
    {
        return $this->currentAvgCostRupiah;
    }

    public function currentInventoryValueRupiah(): int
    {
        return $this->currentInventoryValueRupiah;
    }

    /**
     * @return array{
     *   product_id:string,
     *   qty_in:int,
     *   qty_out:int,
     *   net_qty_delta:int,
     *   total_in_cost_rupiah:int,
     *   total_out_cost_rupiah:int,
     *   net_cost_delta_rupiah:int,
     *   current_qty_on_hand:int,
     *   current_avg_cost_rupiah:int,
     *   current_inventory_value_rupiah:int
     * }
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId(),
            'qty_in' => $this->qtyIn(),
            'qty_out' => $this->qtyOut(),
            'net_qty_delta' => $this->netQtyDelta(),
            'total_in_cost_rupiah' => $this->totalInCostRupiah(),
            'total_out_cost_rupiah' => $this->totalOutCostRupiah(),
            'net_cost_delta_rupiah' => $this->netCostDeltaRupiah(),
            'current_qty_on_hand' => $this->currentQtyOnHand(),
            'current_avg_cost_rupiah' => $this->currentAvgCostRupiah(),
            'current_inventory_value_rupiah' => $this->currentInventoryValueRupiah(),
        ];
    }
}
