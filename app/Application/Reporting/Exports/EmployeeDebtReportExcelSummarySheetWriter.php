<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class EmployeeDebtReportExcelSummarySheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {
    }

    public function write(Worksheet $sheet, array $summary, array $filters): void
    {
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Laporan Hutang Karyawan');
        $sheet->setCellValue('A2', 'Periode');
        $sheet->setCellValue('B2', ViewDateFormatter::range($filters['date_from'] ?? null, $filters['date_to'] ?? null));
        $sheet->setCellValue('A3', 'Dasar Tanggal');
        $sheet->setCellValue('B3', 'Tanggal pencatatan hutang');

        $this->tables->writeTable($sheet, 5, ['Metrik', 'Nilai'], [
            ['Total Hutang', (int) ($summary['total_debt'] ?? 0)],
            ['Sudah Dibayar', (int) ($summary['total_paid_amount'] ?? 0)],
            ['Sisa Hutang', (int) ($summary['total_remaining_balance'] ?? 0)],
            ['Jumlah Data', (int) ($summary['total_rows'] ?? 0)],
            ['Status Lunas', (int) ($summary['paid_rows'] ?? 0)],
            ['Status Belum Lunas', (int) ($summary['unpaid_rows'] ?? 0)],
        ]);

        $this->tables->autosize($sheet, 2);
    }
}
