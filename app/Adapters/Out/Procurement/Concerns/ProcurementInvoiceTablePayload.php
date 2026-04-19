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
            $supplierInvoiceId = (string) $row->supplier_invoice_id;
            $outstandingRupiah = (int) $row->outstanding_rupiah;
            $paymentCount = (int) $row->payment_count;
            $receiptCount = (int) $row->receipt_count;
            $proofAttachmentCount = (int) $row->proof_attachment_count;

            $isLocked = $paymentCount > 0 || $receiptCount > 0;
            $editActionKind = $isLocked ? 'revise' : 'edit';
            $editActionLabel = $isLocked ? 'Koreksi' : 'Edit Nota';
            $editActionRoute = $isLocked
                ? 'admin.procurement.supplier-invoices.revise'
                : 'admin.procurement.supplier-invoices.edit';

            $paymentActionKind = $paymentCount > 0 ? 'proof' : 'pay';
            $paymentActionLabel = $paymentCount > 0 ? 'Bukti Bayar' : 'Bayar';
            $paymentActionMode = $paymentCount > 0 ? 'link' : 'modal';
            $paymentActionUrl = route('admin.procurement.supplier-invoices.payment-proofs.show', [
                'supplierInvoiceId' => $supplierInvoiceId,
            ]);

            return [
                'supplier_invoice_id' => $supplierInvoiceId,
                'nomor_faktur' => $row->nomor_faktur !== null ? (string) $row->nomor_faktur : '',
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
                'receipt_count' => $receiptCount,
                'total_received_qty' => (int) $row->total_received_qty,
                'proof_attachment_count' => $proofAttachmentCount,
                'can_record_payment' => $outstandingRupiah > 0,
                'has_uploaded_proof' => $proofAttachmentCount > 0,
                'policy_state' => $isLocked ? 'locked' : 'editable',

                'payment_action_kind' => $paymentActionKind,
                'payment_action_label' => $paymentActionLabel,
                'payment_action_mode' => $paymentActionMode,
                'payment_action_url' => $paymentActionUrl,

                'edit_action_kind' => $editActionKind,
                'edit_action_label' => $editActionLabel,
                'edit_action_url' => route($editActionRoute, [
                    'supplierInvoiceId' => $supplierInvoiceId,
                ]),

                'void_action_enabled' => ! $isLocked,
                'void_action_label' => 'Hapus Nota',
                'void_action_url' => route('admin.procurement.supplier-invoices.void', [
                    'supplierInvoiceId' => $supplierInvoiceId,
                ]),
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
                    'payment_status' => $query->paymentStatus(),
                    'shipment_date_from' => $query->shipmentDateFrom(),
                    'shipment_date_to' => $query->shipmentDateTo(),
                ],
            ],
        ];
    }
}
