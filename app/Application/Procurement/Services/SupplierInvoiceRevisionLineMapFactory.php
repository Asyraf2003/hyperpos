<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\SupplierInvoice\SupplierInvoice;
use App\Core\Procurement\SupplierInvoice\SupplierInvoiceLine;

final class SupplierInvoiceRevisionLineMapFactory
{
    /**
     * @return array<string, SupplierInvoiceLine>
     */
    public function oldLinesById(SupplierInvoice $invoice): array
    {
        $lines = [];

        foreach ($invoice->lines() as $line) {
            $lines[$line->id()] = $line;
        }

        return $lines;
    }

    /**
     * @return array<int, SupplierInvoiceLine>
     */
    public function newLinesByLineNo(SupplierInvoice $invoice): array
    {
        $lines = [];

        foreach ($invoice->lines() as $line) {
            $lines[$line->lineNo()] = $line;
        }

        return $lines;
    }
}
