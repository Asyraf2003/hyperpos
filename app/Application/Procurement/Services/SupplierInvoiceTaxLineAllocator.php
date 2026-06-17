<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use InvalidArgumentException;

final class SupplierInvoiceTaxLineAllocator
{
    /**
     * @param list<array<string, mixed>> $lines
     * @return list<array<string, mixed>>
     */
    public function allocate(array $lines, int $subtotal, int $taxAmount): array
    {
        $allocatedLines = [];
        $remainders = [];
        $allocatedTaxTotal = 0;

        foreach ($lines as $index => $line) {
            $lineTotal = (int) ($line['line_total_rupiah'] ?? 0);
            $numerator = $lineTotal * $taxAmount;
            $allocatedTax = intdiv($numerator, $subtotal);

            $allocatedLines[$index] = [
                ...$line,
                'line_total_rupiah' => $lineTotal + $allocatedTax,
            ];
            $allocatedTaxTotal += $allocatedTax;
            $remainders[] = ['index' => $index, 'remainder' => $numerator % $subtotal];
        }

        $this->allocateRemainingTax($allocatedLines, $remainders, $taxAmount - $allocatedTaxTotal);
        ksort($allocatedLines);

        $allocatedLines = array_values($allocatedLines);
        $this->assertUnitCostInvariant($allocatedLines);

        return $allocatedLines;
    }

    /**
     * @param array<int, array<string, mixed>> $allocatedLines
     * @param list<array{index:int|string,remainder:int}> $remainders
     */
    private function allocateRemainingTax(array &$allocatedLines, array $remainders, int $remainingTax): void
    {
        usort($remainders, static function (array $left, array $right): int {
            $byRemainder = $right['remainder'] <=> $left['remainder'];

            return $byRemainder !== 0 ? $byRemainder : ($left['index'] <=> $right['index']);
        });

        for ($i = 0; $i < $remainingTax; $i++) {
            $targetIndex = (int) $remainders[$i]['index'];
            $allocatedLines[$targetIndex]['line_total_rupiah'] =
                (int) $allocatedLines[$targetIndex]['line_total_rupiah'] + 1;
        }
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    private function assertUnitCostInvariant(array $lines): void
    {
        foreach ($lines as $line) {
            $qtyPcs = (int) ($line['qty_pcs'] ?? 0);
            $lineTotal = (int) ($line['line_total_rupiah'] ?? 0);

            if ($qtyPcs < 1) {
                throw new InvalidArgumentException('Qty supplier invoice harus lebih dari 0 untuk alokasi pajak.');
            }

            if ($lineTotal % $qtyPcs !== 0) {
                throw new InvalidArgumentException('Alokasi pajak supplier invoice membuat total line tidak habis dibagi qty.');
            }
        }
    }
}
