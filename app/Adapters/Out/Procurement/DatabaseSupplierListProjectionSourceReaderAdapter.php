<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Ports\Out\Procurement\SupplierListProjectionSourceReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseSupplierListProjectionSourceReaderAdapter implements SupplierListProjectionSourceReaderPort
{
    public function findBySupplierId(string $supplierId): ?array
    {
        $normalizedSupplierId = trim($supplierId);

        if ($normalizedSupplierId === '') {
            return null;
        }

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

        $row = DB::table('suppliers')
            ->leftJoin('supplier_invoices', function ($join): void {
                $join->on('supplier_invoices.supplier_id', '=', 'suppliers.id')
                    ->whereNull('supplier_invoices.voided_at');
            })
            ->leftJoinSub($paymentTotalsSubquery, 'payment_totals', function ($join): void {
                $join->on('payment_totals.supplier_invoice_id', '=', 'supplier_invoices.id');
            })
            ->where('suppliers.id', $normalizedSupplierId)
            ->groupBy('suppliers.id', 'suppliers.nama_pt_pengirim')
            ->first([
                'suppliers.id as supplier_id',
                'suppliers.nama_pt_pengirim',
                DB::raw('COUNT(supplier_invoices.id) as invoice_count'),
                DB::raw('COALESCE(SUM(COALESCE(supplier_invoices.grand_total_rupiah, 0) - COALESCE(payment_totals.total_paid_rupiah, 0)), 0) as outstanding_rupiah'),
                DB::raw('COALESCE(SUM(CASE WHEN supplier_invoices.id IS NOT NULL AND (supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0)) > 0 THEN 1 ELSE 0 END), 0) as invoice_unpaid_count'),
                DB::raw('MAX(supplier_invoices.tanggal_pengiriman) as last_shipment_date'),
            ]);

        if ($row === null) {
            return null;
        }

        return [
            'supplier_id' => (string) $row->supplier_id,
            'nama_pt_pengirim' => (string) $row->nama_pt_pengirim,
            'invoice_count' => (int) $row->invoice_count,
            'outstanding_rupiah' => (int) $row->outstanding_rupiah,
            'invoice_unpaid_count' => (int) $row->invoice_unpaid_count,
            'last_shipment_date' => $row->last_shipment_date !== null ? (string) $row->last_shipment_date : null,
        ];
    }
}
