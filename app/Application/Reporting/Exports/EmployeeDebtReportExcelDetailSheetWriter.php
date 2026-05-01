<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class EmployeeDebtReportExcelDetailSheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Detail Hutang');
        $values = [];

        foreach (array_values($rows) as $index => $row) {
            $values[] = [
                $index + 1,
                ViewDateFormatter::display($row['recorded_at'] ?? null),
                (string) ($row['debt_id'] ?? ''),
                (string) ($row['employee_id'] ?? ''),
                (string) ($row['status'] ?? ''),
                (int) ($row['total_debt'] ?? 0),
                (int) ($row['total_paid_amount'] ?? 0),
                (int) ($row['remaining_balance'] ?? 0),
                $this->notes($row['notes'] ?? null),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'No',
            'Tanggal Catat',
            'Referensi Hutang',
            'Employee ID',
            'Status',
            'Total',
            'Dibayar',
            'Sisa',
            'Catatan',
        ], $values);

        $this->tables->autosize($sheet, 9);
    }

    private function notes(mixed $value): string
    {
        return is_string($value) && $value !== '' ? $value : '-';
    }
}
