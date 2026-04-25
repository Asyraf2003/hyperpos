<?php

declare(strict_types=1);

namespace App\Application\Reporting\DTO\Concerns;

trait InventoryMovementSummaryRowAccessors
{
    public function productId(): string { return $this->productId; }

    public function kodeBarang(): ?string { return $this->kodeBarang; }

    public function namaBarang(): string { return $this->namaBarang; }

    public function qtyIn(): int { return $this->qtyIn; }

    public function qtyOut(): int { return $this->qtyOut; }

    public function netQtyDelta(): int { return $this->netQtyDelta; }

    public function totalInCostRupiah(): int { return $this->totalInCostRupiah; }

    public function totalOutCostRupiah(): int { return $this->totalOutCostRupiah; }

    public function netCostDeltaRupiah(): int { return $this->netCostDeltaRupiah; }

    public function currentQtyOnHand(): int { return $this->currentQtyOnHand; }

    public function currentAvgCostRupiah(): int { return $this->currentAvgCostRupiah; }

    public function currentInventoryValueRupiah(): int { return $this->currentInventoryValueRupiah; }
}
