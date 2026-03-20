<?php

declare(strict_types=1);

namespace App\Adapters\In\Http\Controllers\Admin\Procurement\Concerns;

trait BuildsProcurementInvoiceDetailViewData
{
    use HandlesProcurementPolicyView;

    /**
     * @param array<string, mixed> $detail
     * @return array<string, mixed>
     */
    private function buildViewData(array $detail): array
    {
        $summary = is_array($detail['summary'] ?? null) ? $detail['summary'] : [];
        $lines = is_array($detail['lines'] ?? null) ? $detail['lines'] : [];

        return [
            'summaryView' => $this->buildSummaryView($summary),
            'linesView' => $this->buildLinesView($lines),
            'policyView' => $this->buildPolicyView($summary),
        ];
    }

    /**
     * @param array<string, mixed> $summary
     * @return array<string, string|int>
     */
    private function buildSummaryView(array $summary): array
    {
        return [
            'supplier_invoice_id' => (string) ($summary['supplier_invoice_id'] ?? ''),
            'nama_pt_pengirim' => (string) ($summary['nama_pt_pengirim'] ?? ''),
            'shipment_date' => (string) ($summary['shipment_date'] ?? ''),
            'due_date' => (string) ($summary['due_date'] ?? ''),
            'grand_total_label' => $this->formatRupiah((int) ($summary['grand_total_rupiah'] ?? 0)),
            'total_paid_label' => $this->formatRupiah((int) ($summary['total_paid_rupiah'] ?? 0)),
            'outstanding_label' => $this->formatRupiah((int) ($summary['outstanding_rupiah'] ?? 0)),
            'receipt_count' => (int) ($summary['receipt_count'] ?? 0),
            'total_received_qty' => (int) ($summary['total_received_qty'] ?? 0),
        ];
    }

    /**
     * @param array<int, mixed> $lines
     * @return array<int, array<string, string|int|null>>
     */
    private function buildLinesView(array $lines): array
    {
        $lineViews = [];

        foreach ($lines as $line) {
            if (! is_array($line)) {
                continue;
            }

            $lineViews[] = [
                'kode_barang' => isset($line['kode_barang']) ? (string) $line['kode_barang'] : null,
                'nama_barang' => (string) ($line['nama_barang'] ?? ''),
                'merek' => (string) ($line['merek'] ?? ''),
                'ukuran' => isset($line['ukuran']) ? (int) $line['ukuran'] : null,
                'qty_pcs' => (int) ($line['qty_pcs'] ?? 0),
                'unit_cost_label' => $this->formatRupiah((int) ($line['unit_cost_rupiah'] ?? 0)),
                'line_total_label' => $this->formatRupiah((int) ($line['line_total_rupiah'] ?? 0)),
            ];
        }

        return $lineViews;
    }
}
