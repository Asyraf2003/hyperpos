<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

trait ProcurementInvoiceDetailPayload
{
    /**
     * @param list<array{
     *   id:string,
     *   supplier_invoice_id:string,
     *   product_id:string,
     *   kode_barang:?string,
     *   nama_barang:string,
     *   merek:string,
     *   ukuran:?int,
     *   qty_pcs:int,
     *   line_total_rupiah:int,
     *   unit_cost_rupiah:int
     * }> $lines
     * @return array{
     *   summary: array<string, mixed>,
     *   lines: list<array<string, mixed>>
     * }
     */
    private function toDetailPayload(object $summary, array $lines): array
    {
        $totalPaidRupiah = (int) $summary->total_paid_rupiah;
        $receiptCount = (int) $summary->receipt_count;

        $lockReasons = [];

        if ($receiptCount > 0) {
            $lockReasons[] = 'receipt_recorded';
        }

        if ($totalPaidRupiah > 0) {
            $lockReasons[] = 'payment_effective_recorded';
        }

        $policyState = 'editable';

        return [
            'summary' => [
                'supplier_invoice_id' => (string) $summary->supplier_invoice_id,
                'nomor_faktur' => $summary->nomor_faktur !== null
                    ? (string) $summary->nomor_faktur
                    : '',
                'supplier_id' => (string) $summary->supplier_id,
                'supplier_nama_pt_pengirim_current' => $summary->supplier_nama_pt_pengirim_current !== null
                    ? (string) $summary->supplier_nama_pt_pengirim_current
                    : '',
                'supplier_nama_pt_pengirim_snapshot' => (string) $summary->supplier_nama_pt_pengirim_snapshot,
                'shipment_date' => (string) $summary->shipment_date,
                'due_date' => (string) $summary->due_date,
                'grand_total_rupiah' => (int) $summary->grand_total_rupiah,
                'last_revision_no' => (int) $summary->last_revision_no,
                'total_paid_rupiah' => $totalPaidRupiah,
                'outstanding_rupiah' => (int) $summary->outstanding_rupiah,
                'receipt_count' => $receiptCount,
                'latest_receipt_date' => $summary->latest_receipt_date !== null
                    ? (string) $summary->latest_receipt_date
                    : null,
                'total_received_qty' => (int) $summary->total_received_qty,
                'policy_state' => $policyState,
                'lock_reasons' => $lockReasons,
                'allowed_actions' => ['edit'],
            ],
            'lines' => $lines,
        ];
    }
}
