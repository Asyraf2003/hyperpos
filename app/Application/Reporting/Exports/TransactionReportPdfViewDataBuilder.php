<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use Carbon\CarbonImmutable;
use Throwable;

final class TransactionReportPdfViewDataBuilder
{
    public function build(array $dataset, array $filters): array
    {
        $summary = is_array($dataset['summary'] ?? null) ? $dataset['summary'] : [];
        $rows = is_array($dataset['rows'] ?? null) ? $dataset['rows'] : [];

        return [
            'title' => 'Laporan Transaksi',
            'periodLabel' => $this->formatRange(
                $this->stringValue($filters['date_from'] ?? ''),
                $this->stringValue($filters['date_to'] ?? ''),
            ),
            'generatedAt' => CarbonImmutable::now()->format('d/m/Y H:i'),
            'summaryItems' => $this->summaryItems($summary),
            'rows' => array_map(fn (array $row): array => $this->rowData($row), $rows),
        ];
    }

    private function summaryItems(array $summary): array
    {
        return [
            ['label' => 'Jumlah Nota', 'value' => $this->integerValue($summary['total_notes'] ?? 0)],
            ['label' => 'Total Transaksi', 'value' => $this->rupiah($summary['gross_revenue_rupiah'] ?? 0)],
            ['label' => 'Total Dibayar', 'value' => $this->rupiah($summary['paid_rupiah'] ?? 0)],
            ['label' => 'Total Refund', 'value' => $this->rupiah($summary['refund_rupiah'] ?? 0)],
            ['label' => 'Net Dibayar', 'value' => $this->rupiah($summary['net_paid_rupiah'] ?? 0)],
            ['label' => 'Sisa Piutang', 'value' => $this->rupiah($summary['outstanding_rupiah'] ?? 0)],
        ];
    }

    private function rowData(array $row): array
    {
        return [
            'date' => $this->formatDate($this->stringValue($row['transaction_date'] ?? '')),
            'note_id' => $this->stringValue($row['note_id'] ?? ''),
            'customer_name' => $this->stringValue($row['customer_name'] ?? ''),
            'total' => $this->rupiah($row['total_rupiah'] ?? 0),
            'paid' => $this->rupiah($row['paid_rupiah'] ?? 0),
            'refund' => $this->rupiah($row['refund_rupiah'] ?? 0),
            'net_paid' => $this->rupiah($row['net_paid_rupiah'] ?? 0),
            'outstanding' => $this->rupiah($row['outstanding_rupiah'] ?? 0),
            'status' => $this->stringValue($row['payment_status_label'] ?? ''),
        ];
    }

    private function formatRange(string $from, string $to): string
    {
        return $this->formatDate($from) . ' s/d ' . $this->formatDate($to);
    }

    private function formatDate(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        try {
            return CarbonImmutable::parse($value)->format('d/m/Y');
        } catch (Throwable) {
            return $value;
        }
    }

    private function rupiah(mixed $value): string
    {
        return 'Rp ' . number_format($this->integerValue($value), 0, ',', '.');
    }

    private function integerValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
