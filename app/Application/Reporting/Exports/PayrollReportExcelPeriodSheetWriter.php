<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class PayrollReportExcelPeriodSheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Rekap Per Tanggal');
        $values = [];

        foreach (array_values($rows) as $row) {
            $values[] = [
                ViewDateFormatter::display($row['period_label'] ?? null),
                (int) ($row['total_rows'] ?? 0),
                (int) ($row['total_amount_rupiah'] ?? 0),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'Tanggal',
            'Jumlah Pencairan',
            'Total Nominal',
        ], $values);

        $this->tables->autosize($sheet, 3);
    }
}
