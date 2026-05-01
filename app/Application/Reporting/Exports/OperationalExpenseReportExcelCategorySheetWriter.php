<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class OperationalExpenseReportExcelCategorySheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {}

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Rekap Per Kategori');
        $values = [];

        foreach ($rows as $row) {
            $values[] = [
                (string) ($row['category_code'] ?? ''),
                (string) ($row['category_name'] ?? ''),
                (int) ($row['total_rows'] ?? 0),
                (int) ($row['total_amount_rupiah'] ?? 0),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'Kode Kategori',
            'Kategori',
            'Jumlah Catatan',
            'Total Biaya',
        ], $values);

        $this->tables->autosize($sheet, 4);
    }
}
