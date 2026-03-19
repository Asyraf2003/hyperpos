<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ProcurementInvoiceTablePayload
{
    /**
     * @return array{
     *   rows:list<array<string, int|string>>,
     *   meta:array<string, mixed>
     * }
     */
    private function toTablePayload(LengthAwarePaginator $paginator, ProcurementInvoiceTableQuery $query): array
    {
        $rows = array_map(static fn (object $row): array => [
            'supplier_invoice_id' => (string) $row->supplier_invoice_id,
            'nama_pt_pengirim' => (string) $row->nama_pt_pengirim,
            'shipment_date' => (string) $row->shipment_date,
            'due_date' => (string) $row->due_date,
            'grand_total_rupiah' => (int) $row->grand_total_rupiah,
            'total_paid_rupiah' => (int) $row->total_paid_rupiah,
            'outstanding_rupiah' => (int) $row->outstanding_rupiah,
            'receipt_count' => (int) $row->receipt_count,
            'total_received_qty' => (int) $row->total_received_qty,
        ], $paginator->items());

        return [
            'rows' => $rows,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'sort_by' => $query->sortBy(),
                'sort_dir' => $query->sortDir(),
                'filters' => [
                    'q' => $query->q(),
                    'shipment_date_from' => $query->shipmentDateFrom(),
                    'shipment_date_to' => $query->shipmentDateTo(),
                ],
            ],
        ];
    }
}
