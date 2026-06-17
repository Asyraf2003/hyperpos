<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use InvalidArgumentException;

final class SupplierInvoiceTaxLandedCostAllocator
{
    public function __construct(
        private readonly SupplierInvoiceTaxInputCalculator $calculator,
        private readonly ?SupplierInvoiceTaxLineAllocator $lineAllocator = null,
    ) {
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    public function allocate(array $lines, null|string|int $taxInput): SupplierInvoiceTaxLandedCostAllocation
    {
        $subtotal = $this->subtotalBeforeTax($lines);
        $tax = $this->calculator->calculate($taxInput, $subtotal);

        if ($tax->taxAmountRupiah() === 0) {
            return new SupplierInvoiceTaxLandedCostAllocation($subtotal, $tax, $lines);
        }

        if ($subtotal <= 0) {
            throw new InvalidArgumentException('Subtotal supplier invoice harus lebih dari 0 untuk alokasi pajak.');
        }

        return new SupplierInvoiceTaxLandedCostAllocation(
            $subtotal,
            $tax,
            $this->taxLineAllocator()->allocate($lines, $subtotal, $tax->taxAmountRupiah()),
        );
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    private function subtotalBeforeTax(array $lines): int
    {
        $subtotal = 0;

        foreach ($lines as $line) {
            if (! is_array($line)) {
                throw new InvalidArgumentException('Line supplier invoice tidak valid.');
            }

            $lineTotal = (int) ($line['line_total_rupiah'] ?? 0);

            if ($lineTotal < 0) {
                throw new InvalidArgumentException('Total line supplier invoice tidak boleh negatif.');
            }

            $subtotal += $lineTotal;
        }

        return $subtotal;
    }

    private function taxLineAllocator(): SupplierInvoiceTaxLineAllocator
    {
        return $this->lineAllocator ?? new SupplierInvoiceTaxLineAllocator();
    }
}
