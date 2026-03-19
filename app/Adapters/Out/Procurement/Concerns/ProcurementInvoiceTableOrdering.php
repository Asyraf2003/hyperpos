<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Database\Query\Builder;

trait ProcurementInvoiceTableOrdering
{
    private function applyTableSorting(Builder $query, ProcurementInvoiceTableQuery $filters): Builder
    {
        $sortColumn = match ($filters->sortBy()) {
            'due_date' => 'supplier_invoices.jatuh_tempo',
            'nama_pt_pengirim' => 'suppliers.nama_pt_pengirim',
            'grand_total_rupiah' => 'supplier_invoices.grand_total_rupiah',
            'total_paid_rupiah' => 'total_paid_rupiah',
            'outstanding_rupiah' => 'outstanding_rupiah',
            'receipt_count' => 'receipt_count',
            'total_received_qty' => 'total_received_qty',
            default => 'supplier_invoices.tanggal_pengiriman',
        };

        return $query
            ->orderBy($sortColumn, $filters->sortDir())
            ->orderBy('supplier_invoices.id');
    }
}
