<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\ValueObjects\Money;

final class SupplierInvoiceTaxSummary
{
    public const MODE_NONE = 'none';
    public const MODE_PERCENT = 'percent';
    public const MODE_FIXED = 'fixed';

    private function __construct(
        private readonly Money $subtotalBeforeTaxRupiah,
        private readonly ?string $taxInput,
        private readonly string $taxMode,
        private readonly ?int $taxRateBasisPoints,
        private readonly Money $taxAmountRupiah,
    ) {
    }

    public static function none(int $subtotalBeforeTaxRupiah): self
    {
        SupplierInvoiceTaxSummaryValidation::assertSubtotalBeforeTax($subtotalBeforeTaxRupiah);

        return new self(
            Money::fromInt($subtotalBeforeTaxRupiah),
            null,
            self::MODE_NONE,
            null,
            Money::zero(),
        );
    }

    public static function rehydrate(
        int $subtotalBeforeTaxRupiah,
        ?string $taxInput,
        string $taxMode,
        ?int $taxRateBasisPoints,
        int $taxAmountRupiah,
    ): self {
        $summary = new self(
            Money::fromInt($subtotalBeforeTaxRupiah),
            self::normalizeNullableString($taxInput),
            trim($taxMode),
            $taxRateBasisPoints,
            Money::fromInt($taxAmountRupiah),
        );

        SupplierInvoiceTaxSummaryValidation::assertValid($summary);

        return $summary;
    }

    public function subtotalBeforeTaxRupiah(): Money
    {
        return $this->subtotalBeforeTaxRupiah;
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

    public function grandTotalAfterTaxRupiah(): Money
    {
        return $this->subtotalBeforeTaxRupiah->add($this->taxAmountRupiah);
    }

    private static function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
