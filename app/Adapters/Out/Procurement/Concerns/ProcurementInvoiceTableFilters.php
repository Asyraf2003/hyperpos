<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Database\Query\Builder;

trait ProcurementInvoiceTableFilters
{
    private function applyTableFilters(Builder $query, ProcurementInvoiceTableQuery $filters): Builder
    {
        if ($filters->q() !== null) {
            $keyword = $filters->q();

            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('supplier_invoices.nomor_faktur', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_invoices.nomor_faktur_normalized', 'like', '%' . mb_strtolower($keyword, 'UTF-8') . '%')
                    ->orWhere('suppliers.nama_pt_pengirim', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_invoices.supplier_nama_pt_pengirim_snapshot', 'like', '%' . $keyword . '%');
            });
        }

        if ($filters->paymentStatus() === 'outstanding') {
            $query->whereRaw(
                'supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0) > 0'
            );
        }

        if ($filters->paymentStatus() === 'paid') {
            $query->whereRaw(
                'supplier_invoices.grand_total_rupiah - COALESCE(payment_totals.total_paid_rupiah, 0) <= 0'
            );
        }

        if ($filters->shipmentDateFrom() !== null) {
            $query->where('supplier_invoices.tanggal_pengiriman', '>=', $filters->shipmentDateFrom());
        }

        if ($filters->shipmentDateTo() !== null) {
            $query->where('supplier_invoices.tanggal_pengiriman', '<=', $filters->shipmentDateTo());
        }

        return $query;
    }
}
