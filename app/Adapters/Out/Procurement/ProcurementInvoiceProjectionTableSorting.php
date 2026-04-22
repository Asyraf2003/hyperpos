<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Database\Query\Builder;

final class ProcurementInvoiceProjectionTableSorting
{
    public function apply(Builder $query, ProcurementInvoiceTableQuery $filters): Builder
    {
        $sortDir = $filters->sortDir() === 'asc' ? 'asc' : 'desc';

        return match ($filters->sortBy()) {
            'due_date' => $this->sort($query, 'supplier_invoice_list_projection.due_date', $sortDir),
            'nama_pt_pengirim' => $this->sort($query, 'supplier_invoice_list_projection.supplier_nama_pt_pengirim_snapshot', $sortDir),
            'grand_total_rupiah' => $this->sort($query, 'supplier_invoice_list_projection.grand_total_rupiah', $sortDir),
            'total_paid_rupiah' => $this->sort($query, 'supplier_invoice_list_projection.total_paid_rupiah', $sortDir),
            'outstanding_rupiah' => $this->sort($query, 'supplier_invoice_list_projection.outstanding_rupiah', $sortDir),
            'receipt_count' => $this->sort($query, 'supplier_invoice_list_projection.receipt_count', $sortDir),
            'total_received_qty' => $this->sort($query, 'supplier_invoice_list_projection.total_received_qty', $sortDir),
            default => $this->sort($query, 'supplier_invoice_list_projection.shipment_date', $sortDir),
        };
    }

    private function sort(Builder $query, string $column, string $direction): Builder
    {
        return $query
            ->orderBy($column, $direction)
            ->orderBy('supplier_invoice_list_projection.supplier_invoice_id');
    }
}
