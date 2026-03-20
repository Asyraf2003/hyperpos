<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns;

trait BuildsProcurementInvoiceDetailSummaryView
{
    /**
     * @param array<string, mixed> $summary
     * @return array<string, bool|int|string>
     */
    private function buildSummaryView(array $summary): array
    {
        $outstandingAmount = (int) ($summary['outstanding_rupiah'] ?? 0);

        return [
            'supplier_invoice_id' => (string) ($summary['supplier_invoice_id'] ?? ''),
            'nama_pt_pengirim' => (string) ($summary['nama_pt_pengirim'] ?? ''),
            'shipment_date' => (string) ($summary['shipment_date'] ?? ''),
            'due_date' => (string) ($summary['due_date'] ?? ''),
            'grand_total_label' => $this->formatRupiah((int) ($summary['grand_total_rupiah'] ?? 0)),
            'total_paid_label' => $this->formatRupiah((int) ($summary['total_paid_rupiah'] ?? 0)),
            'outstanding_label' => $this->formatRupiah($outstandingAmount),
            'outstanding_amount' => $outstandingAmount,
            'can_record_payment' => $outstandingAmount > 0,
            'receipt_count' => (int) ($summary['receipt_count'] ?? 0),
            'total_received_qty' => (int) ($summary['total_received_qty'] ?? 0),
        ];
    }
}
