<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Database\Query\Builder;

final class ProcurementInvoiceProjectionTableFilters
{
    public function apply(Builder $query, ProcurementInvoiceTableQuery $filters): Builder
    {
        if ($filters->q() !== null) {
            $keyword = trim($filters->q());
            $normalizedKeyword = mb_strtolower($keyword, 'UTF-8');

            $query->where(function (Builder $builder) use ($keyword, $normalizedKeyword): void {
                $builder
                    ->where('supplier_invoice_list_projection.nomor_faktur', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_invoice_list_projection.nomor_faktur_normalized', 'like', '%' . $normalizedKeyword . '%')
                    ->orWhere('suppliers.nama_pt_pengirim', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_invoice_list_projection.supplier_nama_pt_pengirim_snapshot', 'like', '%' . $keyword . '%');
            });
        }

        if ($filters->paymentStatus() === 'active') {
            $query->whereNull('supplier_invoice_list_projection.voided_at');
        }

        if (in_array($filters->paymentStatus(), ['outstanding', 'paid', 'voided'], true)) {
            $query->where('supplier_invoice_list_projection.payment_status', $filters->paymentStatus());
        }

        if ($filters->shipmentDateFrom() !== null) {
            $query->where('supplier_invoice_list_projection.shipment_date', '>=', $filters->shipmentDateFrom());
        }

        if ($filters->shipmentDateTo() !== null) {
            $query->where('supplier_invoice_list_projection.shipment_date', '<=', $filters->shipmentDateTo());
        }

        return $query;
    }
}
