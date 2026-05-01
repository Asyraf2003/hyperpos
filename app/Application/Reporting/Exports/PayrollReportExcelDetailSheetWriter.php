<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class PayrollReportExcelDetailSheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {}

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Detail Gaji');
        $values = [];

        foreach (array_values($rows) as $index => $row) {
            $values[] = [
                $index + 1,
                ViewDateFormatter::display($row['disbursement_date'] ?? null),
                (string) ($row['employee_name'] ?? ''),
                (string) ($row['mode_label'] ?? ''),
                (string) ($row['notes'] ?? '-'),
                (int) ($row['amount_rupiah'] ?? 0),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'No',
            'Tanggal',
            'Karyawan',
            'Mode',
            'Catatan',
            'Nominal',
        ], $values);

        $this->tables->autosize($sheet, 6);
    }
}
