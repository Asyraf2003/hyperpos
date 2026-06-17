<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

final class SupplierInvoiceTaxLandedCostAllocation
{
    /**
     * @param array<int, array<string, mixed>> $lines
     */
    public function __construct(
        private readonly int $subtotalBeforeTaxRupiah,
        private readonly SupplierInvoiceTaxInputCalculation $tax,
        private readonly array $lines,
    ) {}

    public function subtotalBeforeTaxRupiah(): int
    {
        return $this->subtotalBeforeTaxRupiah;
    }

    public function tax(): SupplierInvoiceTaxInputCalculation
    {
        return $this->tax;
    }

    public function taxAmountRupiah(): int
    {
        return $this->tax->taxAmountRupiah();
    }

    public function grandTotalAfterTaxRupiah(): int
    {
        return array_sum(array_map(
            static fn (array $line): int => (int) ($line['line_total_rupiah'] ?? 0),
            $this->lines
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function lines(): array
    {
        return $this->lines;
    }
}
