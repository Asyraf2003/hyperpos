<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use InvalidArgumentException;

final class SupplierInvoiceLineTaxAllocator
{
    public function __construct(
        private readonly SupplierInvoiceTaxInputCalculator $calculator = new SupplierInvoiceTaxInputCalculator(),
    ) {}

    /**
     * @param array<int, array<string, mixed>> $lines
     * @return array<int, array<string, mixed>>
     */
    public function allocate(array $lines): array
    {
        return array_map(fn (array $line): array => $this->taxedLine($line), $lines);
    }

    /**
     * @param array<string, mixed> $line
     * @return array<string, mixed>
     */
    private function taxedLine(array $line): array
    {
        $lineTotal = (int) ($line['line_total_rupiah'] ?? 0);
        $tax = $this->calculator->calculate($line['tax_input'] ?? null, $lineTotal);

        $taxedLine = array_merge($line, [
            'line_subtotal_before_tax_rupiah' => $lineTotal,
        ], $tax->toArray(), [
            'line_total_rupiah' => $lineTotal + $tax->taxAmountRupiah(),
        ]);

        $this->assertUnitCostInvariant($taxedLine);

        return $taxedLine;
    }

    /**
     * @param array<string, mixed> $line
     */
    private function assertUnitCostInvariant(array $line): void
    {
        $qtyPcs = (int) ($line['qty_pcs'] ?? 0);
        $lineTotal = (int) ($line['line_total_rupiah'] ?? 0);

        if ($qtyPcs > 0 && $lineTotal > 0 && $lineTotal % $qtyPcs !== 0) {
            throw new InvalidArgumentException('Alokasi pajak supplier invoice membuat total line tidak habis dibagi qty.');
        }
    }
}
