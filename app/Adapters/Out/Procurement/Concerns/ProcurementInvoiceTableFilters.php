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
                    ->where('supplier_invoices.id', 'like', '%' . $keyword . '%')
                    ->orWhere('suppliers.nama_pt_pengirim', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_invoices.supplier_nama_pt_pengirim_snapshot', 'like', '%' . $keyword . '%');
            });
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
