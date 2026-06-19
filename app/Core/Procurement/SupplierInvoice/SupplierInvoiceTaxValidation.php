<?php

declare(strict_types=1);

namespace App\Core\Procurement\SupplierInvoice;

use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;

trait SupplierInvoiceTaxValidation
{
    /** @param array<int, SupplierInvoiceLine> $lines */
    private static function assertTaxSummaryMatchesGrandTotal(
        SupplierInvoiceTaxSummary $taxSummary,
        Money $grandTotalRupiah,
        array $lines
    ): void {
        $lineGrandTotal = self::sumLineGrandTotalRupiah($lines);
        $lineTaxTotal = self::sumLineTaxRupiah($lines);
        $expectedGrandTotal = $taxSummary->grandTotalAfterTaxRupiah()->amount() + $lineTaxTotal;

        if ($lineGrandTotal !== $grandTotalRupiah->amount()) {
            throw new DomainException('Grand total supplier invoice tidak cocok dengan subtotal dan pajak.');
        }

        if ($expectedGrandTotal !== $grandTotalRupiah->amount()) {
            throw new DomainException('Grand total supplier invoice tidak cocok dengan subtotal dan pajak.');
        }
    }

    /** @param array<int, SupplierInvoiceLine> $lines */
    private static function sumLineGrandTotalRupiah(array $lines): int
    {
        return array_reduce(
            $lines,
            static fn (int $total, SupplierInvoiceLine $line): int => $total + $line->lineTotalRupiah()->amount(),
            0
        );
    }

    /** @param array<int, SupplierInvoiceLine> $lines */
    private static function sumLineTaxRupiah(array $lines): int
    {
        return array_reduce(
            $lines,
            static fn (int $total, SupplierInvoiceLine $line): int => $total + $line->taxAmountRupiah()->amount(),
            0
        );
    }
}
