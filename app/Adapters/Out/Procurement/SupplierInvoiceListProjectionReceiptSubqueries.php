<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class SupplierInvoiceListProjectionReceiptSubqueries
{
    public function counts(): Builder
    {
        return DB::table('supplier_receipts')
            ->selectRaw('supplier_invoice_id, COUNT(*) as receipt_count')
            ->groupBy('supplier_invoice_id');
    }

    public function receivedQtyTotals(): Builder
    {
        return DB::table('supplier_receipts')
            ->join('supplier_receipt_lines', 'supplier_receipt_lines.supplier_receipt_id', '=', 'supplier_receipts.id')
            ->selectRaw(
                'supplier_receipts.supplier_invoice_id, COALESCE(SUM(supplier_receipt_lines.qty_diterima), 0) as total_received_qty'
            )
            ->groupBy('supplier_receipts.supplier_invoice_id');
    }
}
