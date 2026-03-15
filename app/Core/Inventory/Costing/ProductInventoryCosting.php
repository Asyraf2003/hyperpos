<?php

declare(strict_types=1);

namespace App\Core\Inventory\Costing;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class ProductInventoryCosting
{
    use ProductInventoryCostingState;
    use ProductInventoryCostingValidation;

    public static function create(string $pId, Money $avg, Money $val): self
    {
        self::assertValid($pId, $avg, $val);
        return new self(trim($pId), $avg, $val);
    }

    public static function rehydrate(string $pId, Money $avg, Money $val): self
    {
        self::assertValid($pId, $avg, $val);
        return new self(trim($pId), $avg, $val);
    }

    public function applyIncomingStock(int $existingQty, int $incomingQty, Money $incomingTotal): void
    {
        if ($existingQty < 0) throw new DomainException('Existing qty tidak boleh negatif.');
        if ($incomingQty <= 0) throw new DomainException('Incoming qty harus > 0.');
        if (!$incomingTotal->greaterThan(Money::zero())) throw new DomainException('Incoming cost harus > 0.');

        $newQty = $existingQty + $incomingQty;
        $this->inventoryValueRupiah = $this->inventoryValueRupiah->add($incomingTotal);
        $this->avgCostRupiah = Money::fromInt(intdiv($this->inventoryValueRupiah->amount(), $newQty));
    }

    public function applyOutgoingStock(int $existingQty, int $qtyIssue): void
    {
        if ($qtyIssue > $existingQty) throw new DomainException('Qty issue melebihi saldo.');
        
        $outgoingValue = $this->avgCostRupiah->multiply($qtyIssue);
        $this->inventoryValueRupiah = $this->inventoryValueRupiah->subtract($outgoingValue);
        $this->inventoryValueRupiah->ensureNotNegative('Value tidak boleh negatif.');

        if (($existingQty - $qtyIssue) === 0) {
            $this->avgCostRupiah = Money::zero();
        }
    }
}
