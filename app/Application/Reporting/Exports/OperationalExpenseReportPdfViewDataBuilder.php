<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use Carbon\CarbonImmutable;
use Throwable;

final class OperationalExpenseReportPdfViewDataBuilder
{
    public function build(array $dataset, array $filters): array
    {
        $summary = is_array($dataset['summary'] ?? null) ? $dataset['summary'] : [];
        $rows = is_array($dataset['rows'] ?? null) ? $dataset['rows'] : [];

        return [
            'title' => 'Laporan Biaya Operasional',
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
            ['label' => 'Jumlah Catatan', 'value' => $this->integerValue($summary['total_rows'] ?? 0)],
            ['label' => 'Total Biaya', 'value' => $this->rupiah($summary['total_amount_rupiah'] ?? 0)],
            ['label' => 'Kategori Terbesar', 'value' => $this->stringValue($summary['top_category_name'] ?? '-')],
            ['label' => 'Nilai Kategori', 'value' => $this->rupiah($summary['top_category_amount_rupiah'] ?? 0)],
            ['label' => 'Rata-rata Harian', 'value' => $this->rupiah($summary['average_daily_rupiah'] ?? 0)],
        ];
    }

    private function rowData(array $row): array
    {
        return [
            'date' => $this->formatDate($this->stringValue($row['expense_date'] ?? '')),
            'expense_id' => $this->stringValue($row['expense_id'] ?? ''),
            'category_name' => $this->stringValue($row['category_name'] ?? ''),
            'description' => $this->stringValue($row['description'] ?? ''),
            'payment_method' => $this->paymentMethodLabel($this->stringValue($row['payment_method'] ?? '')),
            'reference_no' => $this->stringValue($row['reference_no'] ?? '-'),
            'amount' => $this->rupiah($row['amount_rupiah'] ?? 0),
        ];
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'Tunai',
            'tf', 'transfer', 'bank_transfer' => 'Transfer',
            'debit' => 'Debit',
            'credit' => 'Kredit',
            'qris' => 'QRIS',
            default => strtoupper($method),
        };
    }

    private function formatRange(string $from, string $to): string
    {
        return $this->formatDate($from).' s/d '.$this->formatDate($to);
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
        return 'Rp '.number_format($this->integerValue($value), 0, ',', '.');
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
