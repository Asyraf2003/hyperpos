<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Application\Reporting\Exports\Concerns\FormatsPdfReportValues;
use Carbon\CarbonImmutable;

final class EmployeeDebtReportPdfViewDataBuilder
{
    use FormatsPdfReportValues;

    public function build(array $dataset, array $filters): array
    {
        $summary = is_array($dataset['summary'] ?? null) ? $dataset['summary'] : [];
        $rows = is_array($dataset['rows'] ?? null) ? $dataset['rows'] : [];
        $periodRows = is_array($dataset['period_rows'] ?? null) ? $dataset['period_rows'] : [];
        $statusRows = is_array($dataset['status_rows'] ?? null) ? $dataset['status_rows'] : [];

        return [
            'title' => 'Laporan Hutang Karyawan',
            'periodLabel' => $this->formatRange(
                $this->stringValue($filters['date_from'] ?? ''),
                $this->stringValue($filters['date_to'] ?? ''),
            ),
            'generatedAt' => CarbonImmutable::now()->format('d/m/Y H:i'),
            'summaryItems' => $this->summaryItems($summary),
            'periodRows' => array_map(fn (array $row): array => $this->periodRowData($row), $periodRows),
            'statusRows' => array_map(fn (array $row): array => $this->statusRowData($row), $statusRows),
            'rows' => array_map(fn (array $row): array => $this->rowData($row), $rows),
        ];
    }

    private function summaryItems(array $summary): array
    {
        return [
            ['label' => 'Total Hutang', 'value' => $this->rupiah($summary['total_debt'] ?? 0)],
            ['label' => 'Sudah Dibayar', 'value' => $this->rupiah($summary['total_paid_amount'] ?? 0)],
            ['label' => 'Sisa Hutang', 'value' => $this->rupiah($summary['total_remaining_balance'] ?? 0)],
            ['label' => 'Jumlah Data', 'value' => $this->integerValue($summary['total_rows'] ?? 0)],
            ['label' => 'Status Lunas', 'value' => $this->integerValue($summary['paid_rows'] ?? 0)],
            ['label' => 'Status Belum Lunas', 'value' => $this->integerValue($summary['unpaid_rows'] ?? 0)],
        ];
    }

    private function periodRowData(array $row): array
    {
        return [
            'period_label' => $this->formatDate($this->stringValue($row['period_label'] ?? '')),
            'total_rows' => $this->integerValue($row['total_rows'] ?? 0),
            'total_debt' => $this->rupiah($row['total_debt'] ?? 0),
            'total_paid_amount' => $this->rupiah($row['total_paid_amount'] ?? 0),
            'total_remaining_balance' => $this->rupiah($row['total_remaining_balance'] ?? 0),
        ];
    }

    private function statusRowData(array $row): array
    {
        return [
            'status' => $this->stringValue($row['status'] ?? ''),
            'total_rows' => $this->integerValue($row['total_rows'] ?? 0),
            'total_debt' => $this->rupiah($row['total_debt'] ?? 0),
            'total_paid_amount' => $this->rupiah($row['total_paid_amount'] ?? 0),
            'total_remaining_balance' => $this->rupiah($row['total_remaining_balance'] ?? 0),
        ];
    }

    private function rowData(array $row): array
    {
        return [
            'recorded_at' => $this->formatDate($this->stringValue($row['recorded_at'] ?? '')),
            'debt_id' => $this->stringValue($row['debt_id'] ?? ''),
            'employee_id' => $this->stringValue($row['employee_id'] ?? ''),
            'status' => $this->stringValue($row['status'] ?? ''),
            'total_debt' => $this->rupiah($row['total_debt'] ?? 0),
            'total_paid_amount' => $this->rupiah($row['total_paid_amount'] ?? 0),
            'remaining_balance' => $this->rupiah($row['remaining_balance'] ?? 0),
            'notes' => $this->nullableString($row['notes'] ?? null),
        ];
    }
}
