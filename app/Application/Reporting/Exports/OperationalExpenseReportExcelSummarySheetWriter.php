<?php

declare(strict_types=1);

namespace App\Application\Reporting\Exports;

use App\Support\ViewDateFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class OperationalExpenseReportExcelSummarySheetWriter
{
    public function __construct(
        private readonly TransactionReportExcelTableWriter $tables,
    ) {}

    public function write(Worksheet $sheet, array $summary, array $filters): void
    {
        $sheet->setTitle('Ringkasan');
        $sheet->setCellValue('A1', 'Laporan Biaya Operasional');
        $sheet->setCellValue('A2', 'Periode');
        $sheet->setCellValue('B2', ViewDateFormatter::range($filters['date_from'] ?? null, $filters['date_to'] ?? null));
        $sheet->setCellValue('A3', 'Dasar Tanggal');
        $sheet->setCellValue('B3', 'Tanggal biaya operasional');

        $this->tables->writeTable($sheet, 5, ['Metrik', 'Nilai'], [
            ['Jumlah Catatan', (int) ($summary['total_rows'] ?? 0)],
            ['Total Biaya', (int) ($summary['total_amount_rupiah'] ?? 0)],
            ['Kategori Terbesar', (string) ($summary['top_category_name'] ?? '')],
            ['Nilai Kategori', (int) ($summary['top_category_amount_rupiah'] ?? 0)],
            ['Rata-rata Harian', (int) ($summary['average_daily_rupiah'] ?? 0)],
        ]);

        $this->tables->autosize($sheet, 2);
    }
}
