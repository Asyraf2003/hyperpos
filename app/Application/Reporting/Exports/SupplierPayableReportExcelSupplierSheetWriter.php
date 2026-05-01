<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SupplierPayableReportExcelSupplierSheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $rows): void
    {
        $sheet->setTitle('Rekap Per Supplier');
        $values = [];

        foreach (array_values($rows) as $row) {
            $values[] = [
                (string) ($row['supplier_name'] ?? $row['supplier_id'] ?? ''),
                (int) ($row['total_rows'] ?? 0),
                (int) ($row['grand_total_rupiah'] ?? 0),
                (int) ($row['total_paid_rupiah'] ?? 0),
                (int) ($row['outstanding_rupiah'] ?? 0),
            ];
        }

        $this->tables->writeTable($sheet, 1, [
            'Supplier',
            'Invoice',
            'Total Tagihan',
            'Dibayar',
            'Outstanding',
        ], $values);

        $this->tables->autosize($sheet, 5);
    }
}
