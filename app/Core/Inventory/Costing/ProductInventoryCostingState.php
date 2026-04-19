<?php

declare(strict_types=1);

namespace App\Core\Inventory\Costing;

use App\Core\Shared\ValueObjects\Money;

trait ProductInventoryCostingState
{
    private function __construct(
        private string $productId,
        private Money $avgCostRupiah,
        private Money $inventoryValueRupiah,
    ) {}

    public function productId(): string { return $this->productId; }
    public function avgCostRupiah(): Money { return $this->avgCostRupiah; }
    public function inventoryValueRupiah(): Money { return $this->inventoryValueRupiah; }
}
