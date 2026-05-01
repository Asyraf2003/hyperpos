<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class EmployeeDebtReportExcelStatusSheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Rekap Per Status');
        $values = [];

        foreach (array_values($rows) as $row) {
            $values[] = [
                (string) ($row['status'] ?? ''),
                (int) ($row['total_rows'] ?? 0),
                (int) ($row['total_debt'] ?? 0),
                (int) ($row['total_paid_amount'] ?? 0),
                (int) ($row['total_remaining_balance'] ?? 0),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'Status',
            'Jumlah Data',
            'Total Hutang',
            'Sudah Dibayar',
            'Sisa Hutang',
        ], $values);

        $this->tables->autosize($sheet, 5);
    }
}
