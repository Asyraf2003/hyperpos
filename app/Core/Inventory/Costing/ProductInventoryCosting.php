<?php

declare(strict_types=1);

namespace App\Core\Inventory\Costing;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class ProductInventoryCosting
{
    private function __construct(
        private string $productId,
        private Money $avgCostRupiah,
        private Money $inventoryValueRupiah,
    ) {
    }

    public static function create(
        string $productId,
        Money $avgCostRupiah,
        Money $inventoryValueRupiah,
    ): self {
        self::assertValid($productId, $avgCostRupiah, $inventoryValueRupiah);

        return new self(
            trim($productId),
            $avgCostRupiah,
            $inventoryValueRupiah,
        );
    }

    public static function rehydrate(
        string $productId,
        Money $avgCostRupiah,
        Money $inventoryValueRupiah,
    ): self {
        self::assertValid($productId, $avgCostRupiah, $inventoryValueRupiah);

        return new self(
            trim($productId),
            $avgCostRupiah,
            $inventoryValueRupiah,
        );
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function avgCostRupiah(): Money
    {
        return $this->avgCostRupiah;
    }

    public function inventoryValueRupiah(): Money
    {
        return $this->inventoryValueRupiah;
    }

    public function applyIncomingStock(
        int $existingQtyOnHand,
        int $incomingQty,
        Money $incomingTotalCostRupiah,
    ): void {
        if ($existingQtyOnHand < 0) {
            throw new DomainException('Qty on hand existing pada inventory costing tidak boleh negatif.');
        }

        if ($incomingQty <= 0) {
            throw new DomainException('Incoming qty pada inventory costing harus lebih besar dari nol.');
        }

        if ($incomingTotalCostRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Incoming total cost rupiah pada inventory costing harus lebih besar dari nol.');
        }

        if ($existingQtyOnHand === 0 && $this->inventoryValueRupiah->isZero() === false) {
            throw new DomainException('Inventory costing projection tidak konsisten dengan saldo qty saat ini.');
        }

        if ($existingQtyOnHand > 0 && $this->inventoryValueRupiah->isZero()) {
            throw new DomainException('Inventory costing projection wajib direbuild sebelum menerima stok baru.');
        }

        $newQtyOnHand = $existingQtyOnHand + $incomingQty;

        if ($newQtyOnHand <= 0) {
            throw new DomainException('Qty on hand hasil inventory costing harus lebih besar dari nol.');
        }

        $newInventoryValueRupiah = $this->inventoryValueRupiah->add($incomingTotalCostRupiah);
        $newAvgCostRupiah = Money::fromInt(
            intdiv($newInventoryValueRupiah->amount(), $newQtyOnHand)
        );

        $this->inventoryValueRupiah = $newInventoryValueRupiah;
        $this->avgCostRupiah = $newAvgCostRupiah;
    }

    private static function assertValid(
        string $productId,
        Money $avgCostRupiah,
        Money $inventoryValueRupiah,
    ): void {
        if (trim($productId) === '') {
            throw new DomainException('Product id pada inventory costing wajib ada.');
        }

        if ($avgCostRupiah->isNegative()) {
            throw new DomainException('Average cost rupiah pada inventory costing tidak boleh negatif.');
        }

        if ($inventoryValueRupiah->isNegative()) {
            throw new DomainException('Inventory value rupiah pada inventory costing tidak boleh negatif.');
        }
    }
}
