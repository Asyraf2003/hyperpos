<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait ProcurementInvoiceTableBaseQuery
{
    private function baseTableQuery(): Builder
    {
        $paymentTotalsSubquery = DB::table('supplier_payments')
            ->selectRaw('supplier_invoice_id, COALESCE(SUM(amount_rupiah), 0) as total_paid_rupiah')
            ->groupBy('supplier_invoice_id');

        $receiptCountSubquery = DB::table('supplier_receipts')
            ->selectRaw('supplier_invoice_id, COUNT(*) as receipt_count')
            ->groupBy('supplier_invoice_id');

        $receivedQtySubquery = DB::table('supplier_receipts')
            ->join('supplier_receipt_lines', 'supplier_receipt_lines.supplier_receipt_id', '=', 'supplier_receipts.id')
            ->selectRaw(
                'supplier_receipts.supplier_invoice_id, COALESCE(SUM(supplier_receipt_lines.qty_diterima), 0) as total_received_qty'
            )
            ->groupBy('supplier_receipts.supplier_invoice_id');

        return DB::table('supplier_invoices')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_invoices.supplier_id')
            ->leftJoinSub($paymentTotalsSubquery, 'payment_totals', function ($join): void {
                $join->on('payment_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($receiptCountSubquery, 'receipt_counts', function ($join): void {
                $join->on('receipt_counts.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->leftJoinSub($receivedQtySubquery, 'received_qty_totals', function ($join): void {
                $join->on('received_qty_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->select([
                'supplier_invoices.id as supplier_invoice_id',
                'suppliers.nama_pt_pengirim as supplier_nama_pt_pengirim_current',
                'supplier_invoices.supplier_nama_pt_pengirim_snapshot as supplier_nama_pt_pengirim_snapshot',
                'supplier_invoices.tanggal_pengiriman as shipment_date',
                'supplier_invoices.jatuh_tempo as due_date',
                'supplier_invoices.grand_total_rupiah',
            ])
            ->selectRaw('COALESCE(payment_totals.total_paid_rupiah, 0) as total_paid_rupiah')
            ->selectRaw('supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0) as outstanding_rupiah')
            ->selectRaw('COALESCE(receipt_counts.receipt_count, 0) as receipt_count')
            ->selectRaw('COALESCE(received_qty_totals.total_received_qty, 0) as total_received_qty');
    }
}
