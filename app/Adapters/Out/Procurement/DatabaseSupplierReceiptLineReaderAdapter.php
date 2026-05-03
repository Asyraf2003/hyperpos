<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierReceiptLineReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierReceiptLineReaderAdapter implements SupplierReceiptLineReaderPort
{
    public function getIdsBySupplierReceiptId(string $supplierReceiptId): array
    {
        return DB::table('supplier_receipt_lines')
            ->where('supplier_receipt_id', trim($supplierReceiptId))
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();
    }

    public function getReceivedQtyBySupplierInvoiceLineId(string $supplierInvoiceLineId): int
    {
        return (int) DB::table('supplier_receipt_lines')
            ->join('supplier_receipts', 'supplier_receipts.id', '=', 'supplier_receipt_lines.supplier_receipt_id')
            ->leftJoin(
                'supplier_receipt_reversals',
                'supplier_receipt_reversals.supplier_receipt_id',
                '=',
                'supplier_receipts.id'
            )
            ->where('supplier_receipt_lines.supplier_invoice_line_id', $supplierInvoiceLineId)
            ->whereNull('supplier_receipt_reversals.id')
            ->sum('supplier_receipt_lines.qty_diterima');
    }
}
