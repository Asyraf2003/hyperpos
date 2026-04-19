<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait SupplierTableBaseQuery
{
    private function baseTableQuery(): Builder
    {
        $paymentTotalsSubquery = DB::table('supplier_payments')
            ->leftJoin(
                'supplier_payment_reversals',
                'supplier_payment_reversals.supplier_payment_id',
                '=',
                'supplier_payments.id'
            )
            ->whereNull('supplier_payment_reversals.id')
            ->selectRaw('supplier_invoice_id, COALESCE(SUM(amount_rupiah), 0) as total_paid_rupiah')
            ->groupBy('supplier_invoice_id');

        $supplierInvoiceSummarySubquery = DB::table('supplier_invoices')
            ->whereNull('supplier_invoices.voided_at')
            ->leftJoinSub($paymentTotalsSubquery, 'payment_totals', function ($join): void {
                $join->on('payment_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->select('supplier_invoices.supplier_id')
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw(
                'COALESCE(SUM(supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0)), 0) as outstanding_rupiah'
            )
            ->selectRaw(
                'SUM(CASE WHEN supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0) > 0 THEN 1 ELSE 0 END) as invoice_unpaid_count'
            )
            ->selectRaw('MAX(supplier_invoices.tanggal_pengiriman) as last_shipment_date')
            ->groupBy('supplier_invoices.supplier_id');

        return DB::table('suppliers')
            ->leftJoinSub($supplierInvoiceSummarySubquery, 'supplier_invoice_summaries', function ($join): void {
                $join->on('supplier_invoice_summaries.supplier_id', '=', 'suppliers.id');
            })
            ->select([
                'suppliers.id',
                'suppliers.nama_pt_pengirim',
            ])
            ->selectRaw('COALESCE(supplier_invoice_summaries.invoice_count, 0) as invoice_count')
            ->selectRaw('COALESCE(supplier_invoice_summaries.outstanding_rupiah, 0) as outstanding_rupiah')
            ->selectRaw('COALESCE(supplier_invoice_summaries.invoice_unpaid_count, 0) as invoice_unpaid_count')
            ->selectRaw('supplier_invoice_summaries.last_shipment_date as last_shipment_date');
    }
}
