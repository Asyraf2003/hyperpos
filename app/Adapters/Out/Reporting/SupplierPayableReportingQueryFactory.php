<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class SupplierPayableReportingQueryFactory
{
    public function paymentTotalsSubquery(): Builder
    {
        return DB::table('supplier_payments')
            ->leftJoin(
                'supplier_payment_reversals',
                'supplier_payment_reversals.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->whereNull('supplier_payment_reversals.id')
            ->selectRaw('supplier_invoice_id, COALESCE(SUM(amount_rupiah), 0) as total_paid_rupiah')
            ->groupBy('supplier_invoice_id');
    }

    public function receiptCountSubquery(): Builder
    {
        return DB::table('supplier_receipts')
            ->selectRaw('supplier_invoice_id, COUNT(*) as receipt_count')
            ->groupBy('supplier_invoice_id');
    }

    public function receivedQtySubquery(): Builder
    {
        return DB::table('supplier_receipts')
            ->join('supplier_receipt_lines', 'supplier_receipt_lines.supplier_receipt_id', '=', 'supplier_receipts.id')
            ->selectRaw('supplier_receipts.supplier_invoice_id, COALESCE(SUM(supplier_receipt_lines.qty_diterima), 0) as total_received_qty')
            ->groupBy('supplier_receipts.supplier_invoice_id');
    }

    public function filteredInvoicesSubquery(string $fromShipmentDate, string $toShipmentDate): Builder
    {
        return DB::table('supplier_invoices')
            ->select('id', 'grand_total_rupiah')
            ->whereNull('voided_at')
            ->whereBetween('tanggal_pengiriman', [$fromShipmentDate, $toShipmentDate]);
    }
}
