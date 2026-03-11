<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class SupplierInvoiceLine
{
    private function __construct(
        private string $id,
        private string $productId,
        private int $qtyPcs,
        private Money $lineTotalRupiah,
        private Money $unitCostRupiah,
    ) {
    }

    public static function create(
        string $id,
        string $productId,
        int $qtyPcs,
        Money $lineTotalRupiah,
    ): self {
        self::assertValid($id, $productId, $qtyPcs, $lineTotalRupiah);

        return new self(
            $id,
            trim($productId),
            $qtyPcs,
            $lineTotalRupiah,
            self::calculateUnitCostRupiah($qtyPcs, $lineTotalRupiah),
        );
    }

    public static function rehydrate(
        string $id,
        string $productId,
        int $qtyPcs,
        Money $lineTotalRupiah,
    ): self {
        self::assertValid($id, $productId, $qtyPcs, $lineTotalRupiah);

        return new self(
            $id,
            trim($productId),
            $qtyPcs,
            $lineTotalRupiah,
            self::calculateUnitCostRupiah($qtyPcs, $lineTotalRupiah),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function qtyPcs(): int
    {
        return $this->qtyPcs;
    }

    public function lineTotalRupiah(): Money
    {
        return $this->lineTotalRupiah;
    }

    public function unitCostRupiah(): Money
    {
        return $this->unitCostRupiah;
    }

    private static function assertValid(
        string $id,
        string $productId,
        int $qtyPcs,
        Money $lineTotalRupiah,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Supplier invoice line id wajib ada.');
        }

        if (trim($productId) === '') {
            throw new DomainException('Product id pada supplier invoice line wajib ada.');
        }

        if ($qtyPcs <= 0) {
            throw new DomainException('Qty pcs harus lebih besar dari nol.');
        }

        if ($lineTotalRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Line total rupiah harus lebih besar dari nol.');
        }

        if ($lineTotalRupiah->amount() % $qtyPcs !== 0) {
            throw new DomainException('Line total rupiah harus habis dibagi qty pcs.');
        }
    }

    private static function calculateUnitCostRupiah(
        int $qtyPcs,
        Money $lineTotalRupiah,
    ): Money {
        return Money::fromInt(intdiv($lineTotalRupiah->amount(), $qtyPcs));
    }
}
