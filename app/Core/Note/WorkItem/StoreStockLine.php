<?php

declare(strict_types=1);

namespace App\Core\Note\WorkItem;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

final class StoreStockLine
{
    public const TAX_MODE_NONE = 'none';
    public const TAX_MODE_PERCENT = 'percent';
    public const TAX_MODE_FIXED = 'fixed';

    private function __construct(
        private string $id,
        private string $productId,
        private int $qty,
        private Money $lineTotalRupiah,
        private Money $baseTotalRupiah,
        private ?string $taxInput,
        private string $taxMode,
        private ?int $taxRateBasisPoints,
        private Money $taxAmountRupiah,
    ) {
    }

    public static function create(
        string $id,
        string $productId,
        int $qty,
        Money $lineTotalRupiah,
        ?Money $baseTotalRupiah = null,
        ?string $taxInput = null,
        string $taxMode = self::TAX_MODE_NONE,
        ?int $taxRateBasisPoints = null,
        ?Money $taxAmountRupiah = null,
    ): self {
        $taxAmountRupiah ??= Money::zero();
        $baseTotalRupiah ??= Money::fromInt($lineTotalRupiah->amount() - $taxAmountRupiah->amount());

        self::assertValid(
            $id,
            $productId,
            $qty,
            $lineTotalRupiah,
            $baseTotalRupiah,
            $taxMode,
            $taxRateBasisPoints,
            $taxAmountRupiah,
        );

        return new self(
            trim($id),
            trim($productId),
            $qty,
            $lineTotalRupiah,
            $baseTotalRupiah,
            self::normalizeTaxInput($taxInput),
            $taxMode,
            $taxRateBasisPoints,
            $taxAmountRupiah,
        );
    }

    public static function rehydrate(
        string $id,
        string $productId,
        int $qty,
        Money $lineTotalRupiah,
        ?Money $baseTotalRupiah = null,
        ?string $taxInput = null,
        string $taxMode = self::TAX_MODE_NONE,
        ?int $taxRateBasisPoints = null,
        ?Money $taxAmountRupiah = null,
    ): self {
        return self::create(
            $id,
            $productId,
            $qty,
            $lineTotalRupiah,
            $baseTotalRupiah,
            $taxInput,
            $taxMode,
            $taxRateBasisPoints,
            $taxAmountRupiah,
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

    public function qty(): int
    {
        return $this->qty;
    }

    public function lineTotalRupiah(): Money
    {
        return $this->lineTotalRupiah;
    }

    public function baseTotalRupiah(): Money
    {
        return $this->baseTotalRupiah;
    }

    public function taxInput(): ?string
    {
        return $this->taxInput;
    }

    public function taxMode(): string
    {
        return $this->taxMode;
    }

    public function taxRateBasisPoints(): ?int
    {
        return $this->taxRateBasisPoints;
    }

    public function taxAmountRupiah(): Money
    {
        return $this->taxAmountRupiah;
    }

    private static function assertValid(
        string $id,
        string $productId,
        int $qty,
        Money $lineTotalRupiah,
        Money $baseTotalRupiah,
        string $taxMode,
        ?int $taxRateBasisPoints,
        Money $taxAmountRupiah,
    ): void {
        if (trim($id) === '') {
            throw new DomainException('Store stock line id wajib ada.');
        }

        if (trim($productId) === '') {
            throw new DomainException('Product id pada store stock line wajib ada.');
        }

        if ($qty <= 0) {
            throw new DomainException('Qty pada store stock line harus lebih besar dari nol.');
        }

        if ($lineTotalRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Line total rupiah pada store stock line harus lebih besar dari nol.');
        }

        if ($baseTotalRupiah->greaterThan(Money::zero()) === false) {
            throw new DomainException('Base total rupiah pada store stock line harus lebih besar dari nol.');
        }

        if ($taxAmountRupiah->amount() < 0) {
            throw new DomainException('Tax amount rupiah pada store stock line tidak boleh negatif.');
        }

        if (! in_array($taxMode, [self::TAX_MODE_NONE, self::TAX_MODE_PERCENT, self::TAX_MODE_FIXED], true)) {
            throw new DomainException('Tax mode pada store stock line tidak valid.');
        }

        if ($taxMode === self::TAX_MODE_PERCENT && ($taxRateBasisPoints === null || $taxRateBasisPoints < 0)) {
            throw new DomainException('Tax rate basis points pada store stock line tidak valid.');
        }

        if ($taxMode !== self::TAX_MODE_PERCENT && $taxRateBasisPoints !== null) {
            throw new DomainException('Tax rate basis points hanya boleh ada untuk mode percent.');
        }

        if ($taxMode === self::TAX_MODE_NONE && $taxAmountRupiah->amount() !== 0) {
            throw new DomainException('Tax amount harus nol ketika tax mode none.');
        }

        if ($lineTotalRupiah->amount() !== $baseTotalRupiah->amount() + $taxAmountRupiah->amount()) {
            throw new DomainException('Line total rupiah harus sama dengan base total ditambah tax amount.');
        }
    }

    private static function normalizeTaxInput(?string $taxInput): ?string
    {
        if ($taxInput === null) {
            return null;
        }

        $taxInput = trim($taxInput);

        return $taxInput === '' ? null : $taxInput;
    }
}
