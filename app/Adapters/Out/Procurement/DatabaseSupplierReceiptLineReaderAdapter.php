<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierReceiptLineReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierReceiptLineReaderAdapter implements SupplierReceiptLineReaderPort
{
    public function getReceivedQtyBySupplierInvoiceLineId(string $supplierInvoiceLineId): int
    {
        return (int) DB::table('supplier_receipt_lines')
            ->where('supplier_invoice_line_id', $supplierInvoiceLineId)
            ->sum('qty_diterima');
    }
}
