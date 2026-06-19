<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;

trait SupplierInvoiceReaderTaxSummaryLineTotals
{
    /** @param list<SupplierInvoiceLine> $lines */
    private function sumLineSubtotalBeforeTaxRupiah(array $lines): int
    {
        $total = 0;

        foreach ($lines as $line) {
            $total += $line->lineSubtotalBeforeTaxRupiah()->amount();
        }

        return $total;
    }

    /** @param list<SupplierInvoiceLine> $lines */
    private function sumLineTaxRupiah(array $lines): int
    {
        $total = 0;

        foreach ($lines as $line) {
            $total += $line->taxAmountRupiah()->amount();
        }

        return $total;
    }

    /** @param list<SupplierInvoiceLine> $lines */
    private function sumLineTotalRupiah(array $lines): int
    {
        $total = 0;

        foreach ($lines as $line) {
            $total += $line->lineTotalRupiah()->amount();
        }

        return $total;
    }
}
