<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\ProcurementInvoiceTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ProcurementInvoiceTablePayload
{
    /**
     * @return array{
     *   rows:list<array<string, bool|int|string>>,
     *   meta:array<string, mixed>
     * }
     */
    private function toTablePayload(LengthAwarePaginator $paginator, ProcurementInvoiceTableQuery $query): array
    {
        $rows = array_map(static function (object $row): array {
            $outstandingRupiah = (int) $row->outstanding_rupiah;
            $paymentCount = (int) $row->payment_count;
            $proofAttachmentCount = (int) $row->proof_attachment_count;

            return [
                'supplier_invoice_id' => (string) $row->supplier_invoice_id,
                'supplier_nama_pt_pengirim_current' => $row->supplier_nama_pt_pengirim_current !== null
                    ? (string) $row->supplier_nama_pt_pengirim_current
                    : '',
                'supplier_nama_pt_pengirim_snapshot' => (string) $row->supplier_nama_pt_pengirim_snapshot,
                'shipment_date' => (string) $row->shipment_date,
                'due_date' => (string) $row->due_date,
                'grand_total_rupiah' => (int) $row->grand_total_rupiah,
                'total_paid_rupiah' => (int) $row->total_paid_rupiah,
                'outstanding_rupiah' => $outstandingRupiah,
                'payment_count' => $paymentCount,
                'receipt_count' => (int) $row->receipt_count,
                'total_received_qty' => (int) $row->total_received_qty,
                'proof_attachment_count' => $proofAttachmentCount,
                'can_record_payment' => $outstandingRupiah > 0,
                'has_uploaded_proof' => $proofAttachmentCount > 0,
            ];
        }, $paginator->items());

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
